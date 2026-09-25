<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private function login(string $email, string $password = 'password')
    {
        return $this->postJson('/api/v1/auth/login', ['email' => $email, 'password' => $password, 'device_name' => 'phpunit']);
    }

    public function test_an_admin_gets_a_bearer_token_that_works(): void
    {
        $admin = User::factory()->admin()->create();

        $token = $this->login($admin->email)
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', $admin->email)
            ->json('token');

        $this->assertNotEmpty($this->login($admin->email)->json('expires_at'), 'tokens must expire');

        $this->withToken($token)->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $admin->id);
    }

    public function test_every_failure_gets_the_same_message(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $banned = User::factory()->admin()->banned()->create();

        foreach ([[$admin->email, 'wrong'], ['nobody@example.com', 'password'], [$customer->email, 'password'], [$banned->email, 'password']] as [$email, $password]) {
            $this->login($email, $password)
                ->assertUnprocessable()
                ->assertJsonPath('errors.email.0', 'Those credentials do not match our records.');
        }
    }

    public function test_requests_without_a_token_get_401_json_not_a_redirect(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized()->assertJsonStructure(['message']);
        // Even without an Accept header, api/* answers in JSON.
        $this->get('/api/v1/auth/me')->assertUnauthorized()->assertHeader('content-type', 'application/json');
    }

    public function test_a_customer_token_is_refused(): void
    {
        $customer = User::factory()->create();
        $token = $customer->createToken('x')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertForbidden();
    }

    public function test_banning_an_admin_stops_their_existing_token(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('x')->plainTextToken;

        $admin->forceFill(['banned' => true])->save();

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertForbidden();
    }

    public function test_logout_revokes_only_that_token(): void
    {
        $admin = User::factory()->admin()->create();
        $phone = $admin->createToken('phone')->plainTextToken;
        $laptop = $admin->createToken('laptop')->plainTextToken;

        $this->withToken($phone)->postJson('/api/v1/auth/logout')->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->app['auth']->forgetGuards();
        $this->withToken($laptop)->getJson('/api/v1/auth/me')->assertOk();
    }

    public function test_login_is_throttled(): void
    {
        foreach (range(1, 5) as $_) {
            $this->login('someone@example.com', 'wrong');
        }

        $this->login('someone@example.com', 'wrong')->assertTooManyRequests();
    }

    /** Same guarantee as AdminRoutesTest, for the API: enumerated, so new endpoints are covered automatically. */
    public function test_every_api_route_except_login_requires_an_admin_token(): void
    {
        $routes = collect(Router::getRoutes()->getRoutes())
            ->filter(fn (Route $r) => str_starts_with((string) $r->getName(), 'api.v1.'))
            ->reject(fn (Route $r) => $r->getName() === 'api.v1.auth.login');

        $this->assertNotEmpty($routes);

        foreach ($routes as $route) {
            $middleware = $route->gatherMiddleware();
            $this->assertContains('auth:sanctum', $middleware, "{$route->getName()} is missing auth:sanctum");
            $this->assertContains('admin', $middleware, "{$route->getName()} is missing the admin check");
        }
    }

    /** Every update is a full replacement, so PATCH on it would wipe whatever the client left out. */
    public function test_every_api_update_is_put_only(): void
    {
        $updates = collect(Router::getRoutes()->getRoutes())
            ->filter(fn (Route $r) => preg_match('/^api\.v1\.[\w-]+\.update$/', (string) $r->getName()));

        $this->assertNotEmpty($updates);

        foreach ($updates as $route) {
            $this->assertSame(['PUT'], $route->methods(), "{$route->getName()} must accept PUT only");
        }
    }
}
