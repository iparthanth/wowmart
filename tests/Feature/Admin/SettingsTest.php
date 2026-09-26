<?php

namespace Tests\Feature\Admin;

use App\Mail\TestEmail;
use App\Models\BusinessSetting;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_the_general_screen_shows_its_seeded_values(): void
    {
        $this->actingAs($this->admin)->get('/admin/settings/general')
            ->assertOk()
            ->assertSee('General')
            ->assertSee('value="WowMart"', false)
            ->assertSee('name="site_email"', false)
            ->assertSee('name="maintenance_mode"', false);
    }

    public function test_an_unknown_screen_is_404(): void
    {
        $this->actingAs($this->admin)->get('/admin/settings/not-a-screen')->assertNotFound();
        $this->actingAs($this->admin)->put('/admin/settings/not-a-screen')->assertNotFound();
    }

    public function test_an_update_saves_only_the_keys_the_screen_declares(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings/general', [
            'site_phone' => '+1 555 0199',
            'maintenance_mode' => '1',
            'system_currency' => '999',   // belongs to no screen
            'evil_key' => 'nope',          // invented key
        ])->assertRedirect('/admin/settings/general')->assertSessionHasNoErrors();

        $this->assertSame('+1 555 0199', BusinessSetting::get('site_phone'));
        $this->assertSame('1', BusinessSetting::get('maintenance_mode'));

        // The crafted system_currency did not overwrite the seeded USD id.
        $usd = Currency::where('code', 'USD')->firstOrFail();
        $this->assertSame((string) $usd->id, (string) BusinessSetting::get('system_currency'));
        $this->assertNull(BusinessSetting::where('type', 'evil_key')->first());
    }

    public function test_a_bad_value_is_refused_and_keeps_the_stored_one(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings/general', [
            'site_email' => 'not-an-email',
        ])->assertSessionHasErrors('site_email');

        $this->assertSame('support@wowmart.test', BusinessSetting::get('site_email'));
    }

    public function test_a_secret_is_stored_encrypted_and_never_rendered(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings/smtp', [
            'mail_password' => 'app-secret-123',
        ])->assertSessionHasNoErrors();

        $stored = BusinessSetting::where('type', 'mail_password')->value('value');
        $this->assertNotSame('app-secret-123', $stored);
        $this->assertSame('app-secret-123', Crypt::decryptString($stored));

        $response = $this->actingAs($this->admin)->get('/admin/settings/smtp')->assertOk();
        $this->assertStringNotContainsString('app-secret-123', $response->getContent());
        $this->assertStringContainsString('A value is saved - leave blank to keep it.', $response->getContent());
    }

    public function test_a_blank_secret_keeps_the_stored_value(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings/smtp', ['mail_password' => 'first-secret']);
        $first = BusinessSetting::where('type', 'mail_password')->value('value');

        $this->actingAs($this->admin)->put('/admin/settings/smtp', ['mail_password' => '   '])
            ->assertSessionHasNoErrors();

        $this->assertSame($first, BusinessSetting::where('type', 'mail_password')->value('value'));
    }

    public function test_the_smtp_screen_can_send_a_test_email(): void
    {
        Mail::fake();

        $this->actingAs($this->admin)->post('/admin/settings/test-mail', [
            'test_email' => 'owner@wowmart.test',
        ])->assertRedirect()->assertSessionHas('status', 'Test email sent to owner@wowmart.test.');

        Mail::assertSent(TestEmail::class, fn (TestEmail $mail) => $mail->hasTo('owner@wowmart.test'));
    }

    public function test_the_test_email_needs_a_real_address(): void
    {
        $this->actingAs($this->admin)->post('/admin/settings/test-mail', ['test_email' => 'nope'])
            ->assertSessionHasErrors('test_email');
    }
}
