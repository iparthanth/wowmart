<?php

namespace Tests\Feature\Admin;

use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('sitemaps'));
        Storage::deleteDirectory('update-packages');
        parent::tearDown();
    }

    public function test_the_three_system_screens_render(): void
    {
        $this->actingAs($this->admin)->get('/admin/system/update')
            ->assertOk()->assertSee('Update system');

        $this->actingAs($this->admin)->get('/admin/system/server-status')
            ->assertOk()->assertSee('Core')->assertSee('Extensions')->assertSee('writable');

        $this->actingAs($this->admin)->get('/admin/system/sitemap-generator')
            ->assertOk()->assertSee('Sitemap generator');
    }

    public function test_an_update_package_is_filed(): void
    {
        Storage::fake('local');

        $this->actingAs($this->admin)->post('/admin/system/update', [
            'update_zip' => UploadedFile::fake()->create('wowmart-1.0.1.zip', 20),
        ])->assertRedirect()->assertSessionHas('status');

        $packages = collect(json_decode((string) BusinessSetting::get('system_update_packages'), true));
        $this->assertCount(1, $packages);
        $this->assertSame('wowmart-1.0.1.zip', $packages[0]['original']);
        $this->assertNotNull(BusinessSetting::get('system_last_update_check'));

        // A non-zip upload is rejected.
        $this->actingAs($this->admin)->post('/admin/system/update', [
            'update_zip' => UploadedFile::fake()->create('evil.exe', 5),
        ])->assertSessionHasErrors('update_zip');
    }

    public function test_sitemaps_are_generated_listed_and_deleted(): void
    {
        $this->actingAs($this->admin)->post('/admin/system/sitemap-generator')
            ->assertRedirect()->assertSessionHas('status');

        foreach (['sitemap-categories.xml', 'sitemap-products.xml', 'sitemap-blogs.xml'] as $name) {
            $this->assertFileExists(public_path("sitemaps/{$name}"));
        }

        $this->actingAs($this->admin)->get('/admin/system/sitemap-generator')
            ->assertOk()->assertSee('sitemap-products.xml');

        $xml = file_get_contents(public_path('sitemaps/sitemap-products.xml'));
        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString('/product/', $xml);

        $this->actingAs($this->admin)->post('/admin/system/sitemap-generator/sitemap-blogs.xml/delete')
            ->assertRedirect()->assertSessionHas('status');
        $this->assertFileDoesNotExist(public_path('sitemaps/sitemap-blogs.xml'));

        // Traversal attempts never reach the controller: the router 404s them.
        $this->actingAs($this->admin)->post('/admin/system/sitemap-generator/..%2F..%2F.env/delete')
            ->assertNotFound();
    }
}
