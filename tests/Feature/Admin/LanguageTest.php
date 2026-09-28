<?php

namespace Tests\Feature\Admin;

use App\Models\BusinessSetting;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_a_language_is_created_edited_and_deleted(): void
    {
        $this->actingAs($this->admin)->get('/admin/languages')
            ->assertOk()->assertSee('Add a language')->assertSee('English');

        $this->actingAs($this->admin)->post('/admin/languages', [
            'name' => 'Español', 'code' => 'es', 'rtl' => '0', 'status' => '1',
        ])->assertRedirect('/admin/languages')->assertSessionHasNoErrors();

        $language = Language::where('code', 'es')->firstOrFail();
        $this->assertSame('Español', $language->name);
        $this->assertFalse($language->rtl);

        $this->actingAs($this->admin)->get("/admin/languages/{$language->id}/edit")
            ->assertOk()->assertSee('value="Español"', false);

        $this->actingAs($this->admin)->put("/admin/languages/{$language->id}", [
            'name' => 'Español', 'code' => 'es', 'rtl' => '1', 'status' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertTrue($language->fresh()->rtl);

        $this->actingAs($this->admin)->delete("/admin/languages/{$language->id}")
            ->assertRedirect('/admin/languages')->assertSessionHas('status');
        $this->assertNull($language->fresh());
    }

    public function test_a_language_code_must_be_unique_and_short(): void
    {
        $this->actingAs($this->admin)->post('/admin/languages', [
            'name' => 'Duplicate', 'code' => 'en',
        ])->assertSessionHasErrors('code');

        $this->actingAs($this->admin)->post('/admin/languages', [
            'name' => 'Bad code', 'code' => 'Not A Code',
        ])->assertSessionHasErrors('code');

        $this->assertSame(4, Language::count()); // the four seeded languages
    }

    public function test_the_default_language_cannot_be_deleted(): void
    {
        $english = Language::where('code', 'en')->firstOrFail();

        $this->actingAs($this->admin)->delete("/admin/languages/{$english->id}")
            ->assertRedirect()->assertSessionHas('error');
        $this->assertNotNull($english->fresh());
    }

    public function test_a_language_can_be_made_the_default(): void
    {
        $bangla = Language::where('code', 'bn')->firstOrFail();

        $this->actingAs($this->admin)->post("/admin/languages/{$bangla->id}/default")
            ->assertRedirect('/admin/languages')->assertSessionHas('status');
        $this->assertSame('bn', BusinessSetting::get('default_language'));

        // And now English (no longer default) can be deleted.
        $english = Language::where('code', 'en')->firstOrFail();
        $this->actingAs($this->admin)->delete("/admin/languages/{$english->id}")
            ->assertRedirect('/admin/languages')->assertSessionHas('status');
        $this->assertNull($english->fresh());
    }

    public function test_a_disabled_language_cannot_become_the_default(): void
    {
        $arabic = Language::where('code', 'ar')->firstOrFail(); // seeded disabled

        $this->actingAs($this->admin)->post("/admin/languages/{$arabic->id}/default")
            ->assertRedirect()->assertSessionHas('error');
        $this->assertSame('en', BusinessSetting::get('default_language', 'en'));
    }
}
