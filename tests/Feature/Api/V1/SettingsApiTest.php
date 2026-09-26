<?php

namespace Tests\Feature\Api\V1;

use App\Mail\TestEmail;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SettingsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_a_screen_reads_back_its_fields_with_values(): void
    {
        $this->getJson('/api/v1/settings/general')
            ->assertOk()
            ->assertJsonPath('data.screen', 'general')
            ->assertJsonPath('data.label', 'General')
            ->assertJsonPath('data.fields.site_name.value', 'WowMart')
            ->assertJsonPath('data.fields.site_name.type', 'text')
            ->assertJsonPath('data.fields.maintenance_mode.type', 'boolean');
    }

    public function test_an_unknown_screen_is_404(): void
    {
        $this->getJson('/api/v1/settings/not-a-screen')->assertNotFound();
        $this->putJson('/api/v1/settings/not-a-screen', ['values' => []])->assertNotFound();
    }

    public function test_an_update_saves_declared_keys_and_ignores_the_rest(): void
    {
        $this->putJson('/api/v1/settings/general', [
            'values' => ['site_phone' => '+1 555 0199', 'evil_key' => 'nope'],
        ])->assertOk()->assertJsonPath('data.saved', 1);

        $this->assertSame('+1 555 0199', BusinessSetting::get('site_phone'));
        $this->assertNull(BusinessSetting::where('type', 'evil_key')->first());
    }

    public function test_bad_values_are_422(): void
    {
        // Errors are keyed by the setting key itself, whatever the screen.
        $this->putJson('/api/v1/settings/general', ['values' => ['site_email' => 'nope']])
            ->assertUnprocessable()->assertJsonValidationErrors('site_email');

        $this->putJson('/api/v1/settings/smtp', ['values' => ['mail_port' => 'not-a-port']])
            ->assertUnprocessable();
    }

    public function test_a_secret_is_masked_on_read_and_blank_means_keep_on_write(): void
    {
        $this->putJson('/api/v1/settings/smtp', ['values' => ['mail_password' => 'app-secret-123']])
            ->assertOk();

        $stored = BusinessSetting::where('type', 'mail_password')->value('value');
        $this->assertNotSame('app-secret-123', $stored);
        $this->assertSame('app-secret-123', Crypt::decryptString($stored));

        $this->getJson('/api/v1/settings/smtp')
            ->assertOk()
            ->assertJsonPath('data.fields.mail_password.value', null)
            ->assertJsonPath('data.fields.mail_password.is_set', true);

        $this->putJson('/api/v1/settings/smtp', ['values' => ['mail_password' => '']])->assertOk();
        $this->assertSame($stored, BusinessSetting::where('type', 'mail_password')->value('value'));
    }

    public function test_the_test_email_endpoint_sends(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/settings/test-mail', ['test_email' => 'owner@wowmart.test'])
            ->assertOk()->assertJsonPath('message', 'Test email sent to owner@wowmart.test.');

        Mail::assertSent(TestEmail::class, fn (TestEmail $mail) => $mail->hasTo('owner@wowmart.test'));
    }
}
