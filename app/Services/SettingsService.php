<?php

namespace App\Services;

use App\Mail\TestEmail;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Reads and writes the panel's settings through the registry in
 * config/settings.php.
 *
 * Two invariants live here on purpose:
 * - only keys the screen declares are ever written, so a crafted form cannot
 *   touch anything else;
 * - secret-typed values are stored encrypted and are never handed back once
 *   saved - readers only learn whether one is set.
 */
class SettingsService
{
    /** @return array<string, string> screen key => label */
    public function screens(): array
    {
        return array_map(fn (array $s) => $s['label'], config('settings.screens', []));
    }

    public function screenExists(string $screen): bool
    {
        return array_key_exists($screen, config('settings.screens', []));
    }

    public function screenLabel(string $screen): string
    {
        return config("settings.screens.{$screen}.label", ucfirst($screen));
    }

    /** @return array<string, array> field key => definition */
    public function fields(string $screen): array
    {
        return config("settings.screens.{$screen}.fields", []);
    }

    /**
     * Current values of a screen, what a form should show. Secret fields are
     * always null - a saved secret never comes back.
     *
     * @return array<string, string|null>
     */
    public function values(string $screen): array
    {
        $values = [];

        foreach ($this->fields($screen) as $key => $field) {
            $values[$key] = $field['type'] === 'secret' ? null : BusinessSetting::get($key);
        }

        return $values;
    }

    /** @return array<string, bool> per field, whether a value is stored (secrets included) */
    public function isSet(string $screen): array
    {
        $map = [];

        foreach ($this->fields($screen) as $key => $field) {
            $map[$key] = BusinessSetting::where('type', $key)->where('lang', '')->exists();
        }

        return $map;
    }

    /**
     * Save a screen. Undeclared keys in $input are ignored; a secret left blank
     * keeps the stored one.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException on the screen's own validation rules
     */
    public function update(string $screen, array $input): int
    {
        $fields = $this->fields($screen);

        $rules = [];
        foreach ($fields as $key => $field) {
            if (array_key_exists($key, $input)) {
                $rules[$key] = $field['validation'];
            }
        }

        Validator::make($input, $rules)->validate();

        $saved = 0;

        foreach ($fields as $key => $field) {
            if (! array_key_exists($key, $input)) {
                continue;
            }

            $value = $input[$key];

            if ($field['type'] === 'boolean') {
                $value = filter_var($value, FILTER_VALIDATE_BOOL) ? '1' : '0';
            } elseif ($field['type'] === 'secret') {
                if (! is_string($value) || trim($value) === '') {
                    continue; // blank means keep the stored secret
                }
                $value = Crypt::encryptString($value);
            }

            BusinessSetting::put($key, $value);
            $saved++;
        }

        return $saved;
    }

    /**
     * A secret's decrypted value, for the code that uses it (mail, S3, ...).
     * Never reaches a response.
     */
    public function decrypted(string $key): ?string
    {
        $value = BusinessSetting::get($key);

        return $value === null ? null : Crypt::decryptString($value);
    }

    /**
     * Send a test email with the SMTP screen's stored values, whatever the
     * application's env is configured with.
     *
     * @throws \Throwable when the mailer rejects the send
     */
    public function sendTestEmail(string $to): void
    {
        $encryption = BusinessSetting::get('mail_encryption', 'tls');

        config([
            'mail.default' => BusinessSetting::get('mail_mailer', 'log'),
            'mail.mailers.smtp.host' => BusinessSetting::get('mail_host', ''),
            'mail.mailers.smtp.port' => (int) BusinessSetting::get('mail_port', 587),
            'mail.mailers.smtp.username' => BusinessSetting::get('mail_username', ''),
            'mail.mailers.smtp.password' => $this->decrypted('mail_password') ?? '',
            'mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption,
        ]);

        Mail::to($to)->send(new TestEmail('This is a test email from the WowMart admin SMTP settings screen.'));
    }
}
