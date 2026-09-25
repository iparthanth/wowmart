<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Seed: 9 reviews (ratings 5,4,5,3,4,2,5,4,3), the 2-star one hidden, one per product. */
class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_the_list_is_paginated_and_filtered(): void
    {
        $this->getJson('/api/v1/reviews?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 9)
            ->assertJsonStructure(['data' => [['id', 'rating', 'comment', 'status', 'status_label', 'created_at', 'updated_at', 'product' => ['id', 'name', 'rating'], 'customer' => ['id', 'name']]], 'links', 'meta']);

        $this->getJson('/api/v1/reviews?rating=5')->assertJsonPath('meta.total', 3);
        $this->getJson('/api/v1/reviews?status=hidden')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.status', false)
            ->assertJsonPath('data.0.rating', 2);
        $this->getJson('/api/v1/reviews?search=rafi&sort=lowest')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('data.0.rating', 2);
        $this->getJson('/api/v1/reviews?rating=0&status=1&search[]=x')->assertJsonPath('meta.total', 9);
    }

    public function test_show_uses_types_a_client_can_trust(): void
    {
        $review = Review::where('rating', 5)->orderBy('id')->firstOrFail();

        $this->getJson("/api/v1/reviews/{$review->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $review->id)
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.status', true)
            ->assertJsonPath('data.product.id', $review->product_id)
            ->assertJsonPath('data.product.rating', '5.00')
            ->assertJsonPath('data.customer.name', 'Rafi Ahmed');
    }

    public function test_flags_are_idempotent_and_recompute_the_rating(): void
    {
        $review = Review::where('rating', 5)->orderBy('id')->firstOrFail();

        foreach ([1, 2] as $_) {
            $this->patchJson("/api/v1/reviews/{$review->id}/flags", ['status' => false])
                ->assertOk()
                ->assertJsonPath('data.status', false)
                ->assertJsonPath('data.product.rating', '0.00');
        }
        $this->assertSame(2, Review::where('status', false)->count());

        $this->patchJson("/api/v1/reviews/{$review->id}/flags", ['status' => true])
            ->assertOk()
            ->assertJsonPath('data.status', true)
            ->assertJsonPath('data.product.rating', '5.00');
    }

    public function test_invalid_flags_are_422(): void
    {
        $review = Review::firstOrFail();

        $this->patchJson("/api/v1/reviews/{$review->id}/flags", ['status' => 'maybe'])->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->patchJson("/api/v1/reviews/{$review->id}/flags", [])->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->assertTrue($review->fresh()->status);
    }

    public function test_delete_is_204_and_resets_the_rating(): void
    {
        $review = Review::where('rating', 5)->orderBy('id')->firstOrFail();

        $this->deleteJson("/api/v1/reviews/{$review->id}")->assertNoContent();
        $this->getJson("/api/v1/reviews/{$review->id}")->assertNotFound();
        $this->assertSame('0.00', Product::findOrFail($review->product_id)->rating);
    }

    public function test_reviews_cannot_be_written_through_the_api(): void
    {
        $review = Review::firstOrFail();

        $this->postJson('/api/v1/reviews', ['product_id' => 1, 'user_id' => 1, 'rating' => 5])->assertMethodNotAllowed();
        $this->putJson("/api/v1/reviews/{$review->id}", ['rating' => 1])->assertMethodNotAllowed();
        $this->assertSame(9, Review::count());
    }
}
