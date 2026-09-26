<?php

namespace Tests\Feature\Api\V1;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StaffApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
        Sanctum::actingAs($this->admin);
    }

    private function role(): Role
    {
        $role = Role::create(['name' => 'Catalog']);
        $role->permissions()->createMany([['permission' => 'products']]);

        return $role;
    }

    public function test_the_list_has_admins_first_and_nobody_outside_staff(): void
    {
        $role = $this->role();
        $staff = User::create(['name' => 'Rita Staff', 'email' => 'rita@wowmart.test', 'password' => 'password']);
        $staff->forceFill(['user_type' => 'staff', 'role_id' => $role->id])->save();

        $this->getJson('/api/v1/staff')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.email', $this->admin->email)
            ->assertJsonPath('data.1.email', 'rita@wowmart.test');

        $this->assertStringNotContainsString('Circuit House', $this->getJson('/api/v1/staff')->getContent());
    }

    public function test_a_staff_member_is_created_and_replaced(): void
    {
        $role = $this->role();

        $id = $this->postJson('/api/v1/staff', [
            'name' => 'Rita Staff', 'email' => 'rita@wowmart.test', 'password' => 'password123', 'role_id' => $role->id,
        ])->assertCreated()->assertJsonPath('data.type', 'staff')->json('data.id');

        $this->postJson('/api/v1/staff', ['name' => '', 'email' => 'bad'])
            ->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'password']);

        $this->postJson('/api/v1/staff', ['name' => 'Shorty', 'email' => 's@wowmart.test', 'password' => 'short'])
            ->assertUnprocessable()->assertJsonValidationErrors('password');

        $this->putJson("/api/v1/staff/{$id}", [
            'name' => 'Rita Renamed', 'email' => 'rita@wowmart.test', 'role_id' => $role->id,
        ])->assertOk()->assertJsonPath('data.name', 'Rita Renamed');

        $this->patchJson("/api/v1/staff/{$id}", ['name' => 'x'])->assertMethodNotAllowed();

        $this->getJson("/api/v1/staff/{$id}")->assertOk()->assertJsonPath('data.name', 'Rita Renamed');
    }

    public function test_an_administrator_is_created_without_a_role(): void
    {
        $response = $this->postJson('/api/v1/staff', [
            'name' => 'Second Admin', 'email' => 'second@wowmart.test', 'password' => 'password123', 'is_admin' => true,
        ])->assertCreated()->assertJsonPath('data.type', 'admin');

        $id = $response->json('data.id');
        $this->assertNull(User::find($id)->role_id);
        $this->assertSame('admin', User::find($id)->user_type->value);
        $this->assertArrayNotHasKey('password', $response->json('data')); // never leaked
    }

    public function test_you_cannot_delete_yourself(): void
    {
        $this->deleteJson("/api/v1/staff/{$this->admin->id}")
            ->assertConflict()->assertJsonPath('message', 'You cannot remove your own account.');

        $this->assertNotNull($this->admin->fresh());
    }

    public function test_the_last_administrator_is_409_and_a_real_member_delete_removes_the_right_row(): void
    {
        $role = $this->role();
        $staff = User::create(['name' => 'Rita Staff', 'email' => 'rita@wowmart.test', 'password' => 'password']);
        $staff->forceFill(['user_type' => 'staff', 'role_id' => $role->id])->save();

        // The regression: deleting the staff member must remove the staff row,
        // never the signed-in admin that issued the request.
        $this->deleteJson("/api/v1/staff/{$staff->id}")->assertNoContent();
        $this->assertNull($staff->fresh());
        $this->assertNotNull($this->admin->fresh());
        $this->assertSame('admin', $this->admin->fresh()->user_type->value);
    }

    public function test_a_staff_holder_cannot_delete_the_last_administrator(): void
    {
        $role = Role::create(['name' => 'Staff admin']);
        $role->permissions()->createMany([['permission' => 'staff']]);
        $staff = User::create(['name' => 'Rita Staff', 'email' => 'rita@wowmart.test', 'password' => 'password']);
        $staff->forceFill(['user_type' => 'staff', 'role_id' => $role->id])->save();

        Sanctum::actingAs($staff);

        $this->deleteJson("/api/v1/staff/{$this->admin->id}")
            ->assertConflict()->assertJsonPath('message', 'The last administrator cannot be removed.');

        $this->assertNotNull($this->admin->fresh());
    }
}
