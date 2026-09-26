<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function makeRole(string $name, array $keys = []): Role
    {
        $role = Role::create(['name' => $name]);
        $role->permissions()->createMany(array_map(fn (string $k) => ['permission' => $k], $keys));

        return $role;
    }

    private function makeStaff(string $name, Role $role): User
    {
        $staff = User::create(['name' => $name, 'email' => strtolower(str_replace(' ', '.', $name)).'@wowmart.test', 'password' => 'password']);
        $staff->forceFill(['user_type' => 'staff', 'role_id' => $role->id])->save();

        return $staff;
    }

    public function test_the_list_shows_admins_and_staff_but_nobody_else(): void
    {
        $role = $this->makeRole('Catalog');
        $staff = $this->makeStaff('Rita Staff', $role);

        $response = $this->actingAs($this->admin)->get('/admin/staff')
            ->assertOk()
            ->assertSee('WowMart Admin')
            ->assertSee($staff->name)
            ->assertSee('Add a member')
            ->assertViewHas('members', fn ($page) => $page->total() === 2);

        // Sellers are people too, but not staff.
        $this->assertStringNotContainsString('Circuit House', $response->getContent());
    }

    public function test_a_staff_member_needs_a_role_and_an_admin_must_not_have_one(): void
    {
        $role = $this->makeRole('Catalog', ['products']);

        $this->actingAs($this->admin)->post('/admin/staff', [
            'name' => 'No Role', 'email' => 'no.role@wowmart.test', 'password' => 'password123',
        ])->assertSessionHasErrors('role_id');

        $this->actingAs($this->admin)->post('/admin/staff', [
            'name' => 'Bad Mix', 'email' => 'bad.mix@wowmart.test', 'password' => 'password123',
            'is_admin' => '1', 'role_id' => $role->id,
        ])->assertSessionHasErrors('role_id');

        $this->actingAs($this->admin)->post('/admin/staff', [
            'name' => 'Fine Staff', 'email' => 'fine.staff@wowmart.test', 'password' => 'password123', 'role_id' => $role->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame('staff', User::where('email', 'fine.staff@wowmart.test')->first()->user_type->value);

        $this->actingAs($this->admin)->post('/admin/staff', [
            'name' => 'Second Admin', 'email' => 'second.admin@wowmart.test', 'password' => 'password123', 'is_admin' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame('admin', User::where('email', 'second.admin@wowmart.test')->first()->user_type->value);
    }

    public function test_you_cannot_remove_your_own_account(): void
    {
        $this->actingAs($this->admin)->delete("/admin/staff/{$this->admin->id}")
            ->assertRedirect('/admin/staff')
            ->assertSessionHas('error', 'You cannot remove your own account.');

        $this->assertNotNull($this->admin->fresh());
    }

    public function test_the_last_administrator_cannot_be_removed(): void
    {
        $role = $this->makeRole('Staff admin', ['staff']);
        $staff = $this->makeStaff('Rita Staff', $role);

        // A staff member holding the "staff" permission may open these routes,
        // and is exactly the actor the last-admin guard exists for.
        $this->actingAs($staff)->delete("/admin/staff/{$this->admin->id}")
            ->assertRedirect('/admin/staff')
            ->assertSessionHas('error', 'The last administrator cannot be removed.');

        $this->assertNotNull($this->admin->fresh());
    }

    public function test_the_last_administrator_cannot_be_demoted(): void
    {
        $role = $this->makeRole('Support');

        $this->actingAs($this->admin)->put("/admin/staff/{$this->admin->id}", [
            'name' => $this->admin->name, 'email' => $this->admin->email, 'is_admin' => '0', 'role_id' => $role->id,
        ])->assertSessionHas('error', 'The last administrator cannot be demoted.');

        $this->assertSame('admin', $this->admin->fresh()->user_type->value);
    }

    public function test_a_second_administrator_can_be_removed_and_demoted(): void
    {
        $second = User::create(['name' => 'Second Admin', 'email' => 'second@wowmart.test', 'password' => 'password']);
        $second->forceFill(['user_type' => 'admin'])->save();

        $this->actingAs($this->admin)->put("/admin/staff/{$second->id}", [
            'name' => 'Second Admin', 'email' => 'second@wowmart.test', 'role_id' => $this->makeRole('Support')->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame('staff', $second->fresh()->user_type->value);

        $this->actingAs($this->admin)->delete("/admin/staff/{$second->id}")
            ->assertRedirect('/admin/staff')->assertSessionHas('status');
        $this->assertNull($second->fresh());
    }
}
