<?php

namespace Tests\Feature\Admin;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_sent_from_the_panel_to_the_sign_in_page(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_the_sign_in_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in')->assertSee('WowMart');
    }

    public function test_an_admin_can_sign_in(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_wrong_password_is_refused_with_a_generic_message(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => 'Those credentials do not match our records.']);

        $this->assertGuest();
    }

    public function test_a_customer_with_a_correct_password_still_cannot_sign_in(): void
    {
        $customer = User::factory()->create();

        // Same message as a wrong password, so the form never confirms that an
        // email belongs to a real (non-admin) account.
        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Those credentials do not match our records.']);

        $this->assertGuest();
    }

    public function test_a_banned_admin_cannot_sign_in(): void
    {
        $admin = User::factory()->admin()->banned()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_signed_in_customer_is_forbidden_from_the_panel(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_sign_in_is_throttled_after_five_attempts(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (range(1, 5) as $_) {
            $this->post('/login', ['email' => $admin->email, 'password' => 'wrong']);
        }

        $this->post('/login', ['email' => $admin->email, 'password' => 'wrong'])->assertTooManyRequests();
    }

    public function test_an_admin_can_sign_out(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_a_role_can_never_be_set_through_mass_assignment(): void
    {
        $user = new User(['name' => 'Mallory', 'email' => 'm@example.com', 'password' => 'x', 'user_type' => 'admin']);

        $this->assertNull($user->user_type);
        $this->assertNotSame(UserType::Admin, $user->user_type);
    }
}
