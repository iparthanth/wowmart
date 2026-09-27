<?php

namespace Tests\Feature\Admin;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_seeded_posts_and_categories_are_listed(): void
    {
        $this->actingAs($this->admin)->get('/admin/blog')
            ->assertOk()
            ->assertSee('Add a post')
            ->assertSee('WowMart opens a new warehouse');

        $this->actingAs($this->admin)->get('/admin/blog-category')
            ->assertOk()
            ->assertSee('Add a category')
            ->assertSee('Buying Guides');
        $this->assertSame(2, BlogCategory::count());
        $this->assertSame(3, Blog::count());
    }

    public function test_a_post_banner_is_chosen_from_the_media_library(): void
    {
        $guides = BlogCategory::where('slug', 'guides')->firstOrFail();
        $upload = Upload::create([
            'file_original_name' => 'cover', 'file_name' => 'uploads/all/cover.png',
            'user_id' => $this->admin->id, 'file_size' => 1024, 'extension' => 'png', 'type' => 'image',
        ]);

        $this->actingAs($this->admin)->post('/admin/blog', [
            'blog_category_id' => $guides->id, 'title' => 'Banner post', 'banner' => $upload->id, 'status' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame($upload->id, Blog::where('title', 'Banner post')->firstOrFail()->banner);

        // A bogus banner id and a non-image file are both refused.
        $this->actingAs($this->admin)->post('/admin/blog', [
            'blog_category_id' => $guides->id, 'title' => 'Bad banner', 'banner' => 99999, 'status' => '1',
        ])->assertSessionHasErrors('banner');

        $pdf = Upload::create([
            'file_original_name' => 'doc', 'file_name' => 'uploads/all/doc.pdf',
            'user_id' => $this->admin->id, 'file_size' => 2048, 'extension' => 'pdf', 'type' => 'document',
        ]);
        $this->actingAs($this->admin)->post('/admin/blog', [
            'blog_category_id' => $guides->id, 'title' => 'Pdf banner', 'banner' => $pdf->id, 'status' => '1',
        ])->assertSessionHasErrors('banner');
        $this->assertSame(4, Blog::count()); // the three seeded posts plus "Banner post"

        $this->actingAs($this->admin)->get('/admin/blog')
            ->assertOk()->assertSee('Choose from library');
    }

    public function test_a_post_is_created_with_a_derived_slug_edited_and_deleted(): void
    {
        $guides = BlogCategory::where('slug', 'guides')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/blog', [
            'blog_category_id' => $guides->id, 'title' => 'Picking winter clothes', 'slug' => '', 'status' => '1',
        ])->assertRedirect('/admin/blog')->assertSessionHasNoErrors();

        $post = Blog::where('title', 'Picking winter clothes')->firstOrFail();
        $this->assertSame('picking-winter-clothes', $post->slug);

        $this->actingAs($this->admin)->get("/admin/blog/{$post->id}/edit")
            ->assertOk()->assertSee('value="Picking winter clothes"', false);

        $this->actingAs($this->admin)->put("/admin/blog/{$post->id}", [
            'blog_category_id' => $guides->id, 'title' => 'Picking winter clothes', 'slug' => 'winter-clothes-guide', 'status' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertSame('winter-clothes-guide', $post->fresh()->slug);
        $this->assertFalse($post->fresh()->status);

        $this->actingAs($this->admin)->delete("/admin/blog/{$post->id}")
            ->assertRedirect('/admin/blog')->assertSessionHas('status');
        $this->assertNull($post->fresh());
    }

    public function test_a_duplicate_post_slug_is_refused(): void
    {
        $news = BlogCategory::where('slug', 'news')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/blog', [
            'blog_category_id' => $news->id, 'title' => 'Duplicate', 'slug' => 'how-to-choose-a-rice-cooker', 'status' => '1',
        ])->assertSessionHasErrors();
        $this->assertSame(3, Blog::count());
    }

    public function test_a_category_is_created_edited_and_deleted(): void
    {
        $this->actingAs($this->admin)->post('/admin/blog-category', [
            'name' => 'Recipes', 'slug' => '', 'status' => '1',
        ])->assertRedirect('/admin/blog-category')->assertSessionHasNoErrors();

        $category = BlogCategory::where('name', 'Recipes')->firstOrFail();
        $this->assertSame('recipes', $category->slug);

        $this->actingAs($this->admin)->put("/admin/blog-category/{$category->id}", [
            'name' => 'Recipes', 'slug' => 'kitchen-recipes', 'status' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame('kitchen-recipes', $category->fresh()->slug);

        $this->actingAs($this->admin)->delete("/admin/blog-category/{$category->id}")
            ->assertRedirect('/admin/blog-category')->assertSessionHas('status');
        $this->assertNull($category->fresh());
    }

    public function test_a_category_with_posts_cannot_be_deleted(): void
    {
        $news = BlogCategory::where('slug', 'news')->firstOrFail();

        $this->actingAs($this->admin)->delete("/admin/blog-category/{$news->id}")
            ->assertRedirect()->assertSessionHas('error');
        $this->assertNotNull($news->fresh());
    }
}
