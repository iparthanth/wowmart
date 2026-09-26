<?php

namespace Tests\Feature\Admin;

use App\Models\PickupPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PickupPointTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'GEC Circle branch',
            'phone' => '+8801700000001',
            'address' => ['address' => 'House 12, Road 4, GEC Circle', 'city' => 'Chattogram', 'country' => 'Bangladesh'],
            'status' => '1',
        ], $overrides);
    }

    public function test_the_list_starts_empty_with_the_add_form(): void
    {
        $this->actingAs($this->admin)->get('/admin/pickup-points')
            ->assertOk()
            ->assertSee('No pickup points yet')
            ->assertSee('Add a pickup point');
    }

    public function test_a_point_is_created_edited_and_deleted(): void
    {
        $this->actingAs($this->admin)->post('/admin/pickup-points', $this->payload())
            ->assertRedirect('/admin/pickup-points')->assertSessionHasNoErrors();

        $point = PickupPoint::where('name', 'GEC Circle branch')->firstOrFail();
        $this->assertSame('Chattogram', $point->address['city']);
        $this->assertTrue($point->status);

        $this->actingAs($this->admin)->get("/admin/pickup-points/{$point->id}/edit")
            ->assertOk()->assertSee('value="GEC Circle branch"', false);

        $this->actingAs($this->admin)->put("/admin/pickup-points/{$point->id}", $this->payload([
            'name' => 'Agrabad branch', 'status' => '0',
        ]))->assertSessionHasNoErrors();
        $this->assertSame('Agrabad branch', $point->fresh()->name);
        $this->assertFalse($point->fresh()->status);

        $this->actingAs($this->admin)->delete("/admin/pickup-points/{$point->id}")
            ->assertRedirect('/admin/pickup-points')->assertSessionHas('status');
        $this->assertNull($point->fresh());
    }

    public static function invalid(): array
    {
        return [
            'no name' => [['name' => '']],
            'no phone' => [['phone' => '']],
            'no city' => [['address' => ['address' => 'x', 'country' => 'y']]],
            'address not an array' => [['address' => 'plain text']],
        ];
    }

    #[DataProvider('invalid')]
    public function test_a_bad_payload_is_refused(array $overrides): void
    {
        $this->actingAs($this->admin)->post('/admin/pickup-points', $this->payload($overrides))
            ->assertSessionHasErrors();

        $this->assertSame(0, PickupPoint::count());
    }
}
