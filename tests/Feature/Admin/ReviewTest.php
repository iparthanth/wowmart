<?php

namespace Tests\Feature\Admin;

use App\Enums\DeliveryStatus;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\ReviewService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The seed gives 9 verified-purchase reviews (ratings 5,4,5,3,4,2,5,4,3), one
 * hidden (the 2-star one), each on a different product - so exact numbers work.
 */
class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    private function customer(string $name): User
    {
        return User::where('email', strtolower($name).'@customers.wowmart.test')->firstOrFail();
    }

    /** The seeded 5-star review by Rafi on the first product. */
    private function rafisReview(): Review
    {
        return Review::where('user_id', $this->customer('Rafi')->id)->where('rating', 5)->firstOrFail();
    }

    private function rating(int $productId): string
    {
        return Product::findOrFail($productId)->rating;
    }

    public function test_seeded_reviews_are_all_verified_purchases(): void
    {
        $this->assertSame(9, Review::count());
        $this->assertSame(1, Review::where('status', false)->count());

        foreach (Review::all() as $review) {
            $delivered = DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')
                ->where('orders.user_id', $review->user_id)
                ->where('order_details.product_id', $review->product_id)
                ->where('orders.delivery_status', DeliveryStatus::Delivered->value)
                ->exists();

            $this->assertTrue($delivered, "Review {$review->id} has no delivered order line");
        }
    }

    public function test_seeded_ratings_count_only_visible_reviews(): void
    {
        $this->assertSame('5.00', $this->rating($this->rafisReview()->product_id));
        $this->assertSame('0.00', $this->rating(Review::where('status', false)->value('product_id')));
    }

    public function test_the_list_shows_the_summary_and_each_review(): void
    {
        $review = $this->rafisReview()->load('product');

        $this->get('/admin/reviews')
            ->assertOk()
            ->assertSee('9 reviews · average rating 3.89 / 5 · 1 hidden')
            ->assertSee('Rated 5 out of 5')
            ->assertSee(route('admin.products.edit', $review->product))
            ->assertSee(route('admin.reviews.toggle', [$review, 'status']));
    }

    public function test_a_long_comment_is_cut_to_140_characters_with_the_full_text_in_details(): void
    {
        $review = $this->rafisReview();
        $long = str_repeat('Lovely board. ', 20).'THE END';
        $review->forceFill(['comment' => $long])->save();

        $html = $this->get('/admin/reviews')->assertOk()->getContent();

        $this->assertStringContainsString('<summary>'.e(Str::limit($long, 140)).'</summary>', $html);
        $this->assertStringContainsString('THE END', $html);
    }

    public function test_filters_give_exact_counts(): void
    {
        $count = fn (string $query) => $this->get('/admin/reviews?'.$query)->assertOk()->viewData('reviews')->total();

        $this->assertSame(9, $count(''));
        $this->assertSame(3, $count('rating=5'));
        $this->assertSame(3, $count('rating=4'));
        $this->assertSame(2, $count('rating=3'));
        $this->assertSame(1, $count('rating=2'));
        $this->assertSame(0, $count('rating=1'));
        $this->assertSame(1, $count('status=hidden'));
        $this->assertSame(8, $count('status=visible'));
        $this->assertSame(3, $count('search=nusrat'));
        $this->assertSame(1, $count('search='.urlencode($this->rafisReview()->product->name)));
        $this->assertSame(0, $count('search=%25'));  // % matches literally
        $this->assertSame(1, $count('search=tanvir&rating=4'));

        // Unknown or malformed values are ignored, not errors.
        $this->assertSame(9, $count('rating=9&status=gone&sort=sideways&search[]=x'));
    }

    public function test_sorts_order_by_rating_and_date(): void
    {
        $ratings = fn (string $sort) => $this->get('/admin/reviews?sort='.$sort)->viewData('reviews')->pluck('rating')->all();

        $this->assertSame([2, 3, 3, 4, 4, 4, 5, 5, 5], $ratings('lowest'));
        $this->assertSame([5, 5, 5, 4, 4, 4, 3, 3, 2], $ratings('highest'));
        $this->assertSame(Review::orderBy('id')->pluck('id')->all(), $this->get('/admin/reviews?sort=oldest')->viewData('reviews')->pluck('id')->all());
    }

    public function test_the_list_does_not_query_per_row(): void
    {
        $count = function () {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get('/admin/reviews')->assertOk();

            return count(DB::getQueryLog());
        };

        $before = $count();

        $product = Product::orderBy('id')->skip(30)->firstOrFail();
        foreach (User::factory()->count(5)->create() as $user) {
            (new Review(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 4]))->save();
        }

        $this->assertSame($before, $count());
    }

    public function test_hiding_recomputes_the_rating_and_unhiding_restores_it(): void
    {
        $rafi = $this->rafisReview();

        // Two more visible reviews on the same product: (5 + 4 + 4) / 3 = 4.33
        foreach (['Nusrat', 'Tanvir'] as $name) {
            (new Review(['product_id' => $rafi->product_id, 'user_id' => $this->customer($name)->id, 'rating' => 4]))->save();
        }
        app(ReviewService::class)->setFlags($rafi, ['status' => true]); // no-op that recomputes
        $this->assertSame('4.33', $this->rating($rafi->product_id));

        $this->from('/admin/reviews')->post(route('admin.reviews.toggle', [$rafi, 'status']))
            ->assertRedirect('/admin/reviews')
            ->assertSessionHas('status', 'The review is hidden from the storefront.');
        $this->assertFalse($rafi->fresh()->status);
        $this->assertSame('4.00', $this->rating($rafi->product_id));

        $this->post(route('admin.reviews.toggle', [$rafi, 'status']))
            ->assertSessionHas('status', 'The review is visible on the storefront again.');
        $this->assertTrue($rafi->fresh()->status);
        $this->assertSame('4.33', $this->rating($rafi->product_id));
    }

    public function test_only_status_can_be_toggled(): void
    {
        $this->post('/admin/reviews/'.$this->rafisReview()->id.'/toggle/rating')->assertNotFound();
    }

    public function test_deleting_the_last_visible_review_sets_the_rating_to_zero(): void
    {
        $review = $this->rafisReview();

        $this->from('/admin/reviews')->delete(route('admin.reviews.destroy', $review))
            ->assertRedirect('/admin/reviews')
            ->assertSessionHas('status');

        $this->assertModelMissing($review);
        $this->assertSame('0.00', $this->rating($review->product_id));
        $this->assertSame(8, Review::count());
    }

    public function test_deleting_a_hidden_review_leaves_the_visible_average_alone(): void
    {
        $rafi = $this->rafisReview();
        $hidden = new Review(['product_id' => $rafi->product_id, 'user_id' => $this->customer('Nusrat')->id, 'rating' => 1]);
        $hidden->forceFill(['status' => false])->save();

        $this->delete(route('admin.reviews.destroy', $hidden))->assertRedirect();

        $this->assertSame('5.00', $this->rating($rafi->product_id));
    }
}
