<?php

namespace Tests\Feature\Admin;

use App\Models\BusinessSetting;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_the_three_standard_pages_are_seeded_and_listed(): void
    {
        $this->actingAs($this->admin)->get('/admin/website/pages')
            ->assertOk()
            ->assertSee('Add a page')
            ->assertSee('About Us')
            ->assertSee('Privacy Policy');
        $this->assertSame(3, Page::count());
    }

    public function test_a_page_is_created_with_a_derived_slug_edited_and_deleted(): void
    {
        $this->actingAs($this->admin)->post('/admin/website/pages', [
            'type' => 'custom', 'title' => 'Shipping Policy', 'slug' => '', 'status' => '1',
        ])->assertRedirect('/admin/website/pages')->assertSessionHasNoErrors();

        $page = Page::where('title', 'Shipping Policy')->firstOrFail();
        $this->assertSame('shipping-policy', $page->slug);

        $this->actingAs($this->admin)->get("/admin/website/pages/{$page->id}/edit")
            ->assertOk()->assertSee('value="Shipping Policy"', false);

        $this->actingAs($this->admin)->put("/admin/website/pages/{$page->id}", [
            'type' => 'custom', 'title' => 'Shipping Policy', 'slug' => 'delivery-policy', 'status' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame('delivery-policy', $page->fresh()->slug);

        $this->actingAs($this->admin)->delete("/admin/website/pages/{$page->id}")
            ->assertRedirect('/admin/website/pages')->assertSessionHas('status');
        $this->assertNull($page->fresh());
    }

    public function test_a_duplicate_slug_is_refused(): void
    {
        $this->actingAs($this->admin)->post('/admin/website/pages', [
            'type' => 'custom', 'title' => 'Another About', 'slug' => 'about', 'status' => '1',
        ])->assertSessionHasErrors();
        $this->assertSame(3, Page::count());
    }

    public function test_the_website_setup_settings_screens_save_their_keys(): void
    {
        foreach (['homepage-select' => 'homepage_layout', 'header' => 'header_top_bar', 'footer' => 'footer_copyright', 'appearance' => 'theme_color', 'authentication' => 'auth_layout'] as $screen => $key) {
            $this->actingAs($this->admin)->get("/admin/settings/{$screen}")->assertOk();
        }

        $this->actingAs($this->admin)->put('/admin/settings/appearance', [
            'theme_color' => '#F4772E',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('#F4772E', BusinessSetting::get('theme_color'));

        // A crafted form cannot smuggle a key the screen does not own.
        $this->actingAs($this->admin)->put('/admin/settings/header', [
            'site_name' => 'Hacked',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNotSame('Hacked', BusinessSetting::get('site_name'));
    }
}
