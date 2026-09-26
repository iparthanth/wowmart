<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_the_list_starts_empty_and_the_form_offers_the_registry(): void
    {
        $this->actingAs($this->admin)->get('/admin/roles')
            ->assertOk()
            ->assertSee('No roles yet')
            ->assertSee('Add a role')
            ->assertSee('Products & catalogue');
    }

    public function test_a_role_is_created_with_its_permissions(): void
    {
        $this->actingAs($this->admin)->post('/admin/roles', [
            'name' => 'Catalog',
            'permissions' => ['products', 'media'],
        ])->assertRedirect('/admin/roles/1/edit')->assertSessionHasNoErrors();

        $role = Role::where('name', 'Catalog')->firstOrFail();
        $this->assertEqualsCanonicalizing(['products', 'media'], $role->permissionKeys());
    }

    public function test_a_role_name_is_unique_and_permissions_must_be_registry_keys(): void
    {
        Role::create(['name' => 'Catalog']);

        $this->actingAs($this->admin)->post('/admin/roles', [
            'name' => 'Catalog', 'permissions' => [],
        ])->assertSessionHasErrors('name');

        $this->actingAs($this->admin)->post('/admin/roles', [
            'name' => 'Other', 'permissions' => ['not-a-key'],
        ])->assertSessionHasErrors('permissions.*');

        $this->assertSame(1, Role::count());
    }

    public function test_an_update_is_a_full_replacement(): void
    {
        $role = Role::create(['name' => 'Catalog']);
        $role->permissions()->createMany([['permission' => 'products'], ['permission' => 'media']]);

        $this->actingAs($this->admin)->put("/admin/roles/{$role->id}", [
            'name' => 'Catalog', 'permissions' => ['orders'],
        ])->assertSessionHasNoErrors();
        $this->assertSame(['orders'], $role->fresh()->permissionKeys());

        // Sending an empty list empties the role - the checkbox form's real use.
        $this->actingAs($this->admin)->put("/admin/roles/{$role->id}", [
            'name' => 'Catalog', 'permissions' => [],
        ])->assertSessionHasNoErrors();
        $this->assertSame([], $role->fresh()->permissionKeys());
    }

    public function test_a_role_in_use_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'Catalog']);
        $staff = User::create(['name' => 'Rita', 'email' => 'rita@wowmart.test', 'password' => 'password']);
        $staff->forceFill(['user_type' => 'staff', 'role_id' => $role->id])->save();

        $this->actingAs($this->admin)->delete("/admin/roles/{$role->id}")
            ->assertRedirect()
            ->assertSessionHas('error', '1 staff member uses this role; assign them a different role first.');

        $this->assertNotNull($role->fresh());

        $staff->delete();
        $this->actingAs($this->admin)->delete("/admin/roles/{$role->id}")
            ->assertRedirect('/admin/roles')->assertSessionHas('status');
        $this->assertNull($role->fresh());
    }
}
