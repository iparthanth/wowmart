<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Tests\TestCase;

/**
 * The panel's one security property, checked against the router itself.
 *
 * Routes are enumerated rather than listed by hand, so a screen added next
 * month is covered the moment it exists - nobody has to remember to add it here.
 */
class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    /** Everything under admin.* except the sign-in and sign-out routes themselves. */
    private function panelRoutes(): array
    {
        return collect(Router::getRoutes()->getRoutes())
            ->filter(fn (Route $r) => str_starts_with((string) $r->getName(), 'admin.'))
            ->reject(fn (Route $r) => in_array($r->getName(), ['admin.login', 'admin.logout'], true))
            ->values()
            ->all();
    }

    /** GET routes with no URL parameters can be requested directly. */
    private function plainGetRoutes(): array
    {
        return array_values(array_filter(
            $this->panelRoutes(),
            fn (Route $r) => in_array('GET', $r->methods(), true) && $r->parameterNames() === [],
        ));
    }

    public function test_the_enumeration_is_not_empty(): void
    {
        // Guards against the suite passing vacuously if route names ever change.
        $this->assertNotEmpty($this->plainGetRoutes());
    }

    public function test_every_panel_route_requires_an_admin(): void
    {
        foreach ($this->panelRoutes() as $route) {
            $middleware = $route->gatherMiddleware();

            $this->assertContains('auth', $middleware, "{$route->getName()} is missing auth");
            $this->assertContains('admin', $middleware, "{$route->getName()} is missing the admin check");
        }
    }

    public function test_a_guest_is_redirected_from_every_screen(): void
    {
        foreach ($this->plainGetRoutes() as $route) {
            $this->get('/'.$route->uri())->assertRedirect('/login');
        }
    }

    public function test_a_customer_is_forbidden_from_every_screen(): void
    {
        $customer = User::factory()->create();

        foreach ($this->plainGetRoutes() as $route) {
            $this->actingAs($customer)->get('/'.$route->uri())->assertForbidden();
        }
    }

    public function test_an_admin_can_open_every_screen(): void
    {
        $this->seed();
        $admin = User::factory()->admin()->create();

        foreach ($this->plainGetRoutes() as $route) {
            $this->actingAs($admin)->get('/'.$route->uri())->assertOk();
        }
    }
}
