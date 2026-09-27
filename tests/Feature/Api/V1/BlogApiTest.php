<?php

namespace Tests\Feature\Api\V1;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_a_post_round_trips_with_its_category(): void
    {
        $guides = BlogCategory::where('slug', 'guides')->firstOrFail();

        $id = $this->postJson('/api/v1/blogs', [
            'blog_category_id' => $guides->id, 'title' => 'API post', 'slug' => 'api-post', 'status' => true,
        ])->assertCreated()
            ->assertJsonPath('data.title', 'API post')
            ->assertJsonPath('data.category.slug', 'guides')
            ->json('data.id');

        $this->putJson("/api/v1/blogs/{$id}", [
            'blog_category_id' => $guides->id, 'title' => 'API post', 'slug' => 'api-post-renamed', 'status' => false,
        ])->assertOk()->assertJsonPath('data.slug', 'api-post-renamed')->assertJsonPath('data.status', false);

        $this->patchJson("/api/v1/blogs/{$id}", ['title' => 'x'])->assertMethodNotAllowed();

        $this->deleteJson("/api/v1/blogs/{$id}")->assertNoContent();
        $this->assertSame(3, Blog::count());
    }

    public function test_a_category_round_trips_and_is_409_while_posts_remain(): void
    {
        $id = $this->postJson('/api/v1/blog-categories', [
            'name' => 'API Category', 'slug' => 'api-category', 'status' => true,
        ])->assertCreated()->assertJsonPath('data.name', 'API Category')->json('data.id');

        $this->putJson("/api/v1/blog-categories/{$id}", [
            'name' => 'API Category', 'slug' => 'api-category-2', 'status' => true,
        ])->assertOk()->assertJsonPath('data.slug', 'api-category-2');

        $this->deleteJson("/api/v1/blog-categories/{$id}")->assertNoContent();

        $news = BlogCategory::where('slug', 'news')->firstOrFail();
        $this->deleteJson("/api/v1/blog-categories/{$news->id}")
            ->assertConflict()->assertJsonPath('message', fn ($m) => str_contains($m, 'still has'));
    }
}
