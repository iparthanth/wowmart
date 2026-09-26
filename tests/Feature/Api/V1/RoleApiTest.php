<?php

namespace Tests\Feature\Api\V1;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_roles_are_created_with_registry_permissions(): void
    {
        $response = $this->postJson('/api/v1/roles', [
            'name' => 'Catalog', 'permissions' => ['products', 'media'],
        ])->assertCreated()->assertJsonPath('data.name', 'Catalog');

        $this->assertEqualsCanonicalizing(['products', 'media'], $response->json('data.permissions'));
        $id = $response->json('data.id');

        $this->getJson("/api/v1/roles/{$id}")->assertOk()->assertJsonPath('data.name', 'Catalog');
    }

    public function test_unknown_permissions_and_duplicate_names_are_422(): void
    {
        $response = $this->postJson('/api/v1/roles', ['name' => 'Catalog', 'permissions' => ['not-a-key']])
            ->assertUnprocessable();

        // Array element errors are keyed as literal "permissions.0" in the bag.
        $this->assertSame(
            'One of the chosen permissions is not in the registry.',
            $response->json('errors')['permissions.0'][0],
        );

        $this->postJson('/api/v1/roles', ['name' => 'Catalog', 'permissions' => ['orders']])->assertCreated();

        $this->postJson('/api/v1/roles', ['name' => 'Catalog', 'permissions' => []])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
    }

    public function test_an_update_is_a_full_replacement_and_patch_is_refused(): void
    {
        $role = Role::create(['name' => 'Catalog']);
        $role->permissions()->createMany([['permission' => 'products']]);

        $this->putJson("/api/v1/roles/{$role->id}", ['name' => 'Catalog', 'permissions' => ['orders']])
            ->assertOk()->assertJsonPath('data.permissions', ['orders']);

        $this->putJson("/api/v1/roles/{$role->id}", ['name' => 'Catalog', 'permissions' => []])
            ->assertOk()->assertJsonPath('data.permissions', []);

        $this->patchJson("/api/v1/roles/{$role->id}", ['name' => 'x'])->assertMethodNotAllowed();
    }

    public function test_a_role_in_use_is_409_on_delete(): void
    {
        $role = Role::create(['name' => 'Catalog']);
        $staff = User::create(['name' => 'Rita', 'email' => 'rita@wowmart.test', 'password' => 'password']);
        $staff->forceFill(['user_type' => 'staff', 'role_id' => $role->id])->save();

        $this->deleteJson("/api/v1/roles/{$role->id}")
            ->assertConflict()->assertJsonPath('message', '1 staff member uses this role; assign them a different role first.');

        $staff->delete();
        $this->deleteJson("/api/v1/roles/{$role->id}")->assertNoContent();
        $this->assertSame(0, Role::count());
    }
}
