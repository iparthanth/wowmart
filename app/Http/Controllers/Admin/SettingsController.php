<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * One controller for every settings screen: the registry in
 * config/settings.php decides which keys a screen owns, this controller only
 * routes between them.
 */
class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function show(string $screen): View
    {
        $this->assertScreen($screen);

        return view('admin.settings.show', [
            'screen' => $screen,
            'screenLabel' => $this->settings->screenLabel($screen),
            'fields' => $this->settings->fields($screen),
            'values' => $this->settings->values($screen),
            'isSet' => $this->settings->isSet($screen),
        ]);
    }

    public function update(Request $request, string $screen): RedirectResponse
    {
        $this->assertScreen($screen);

        $this->settings->update($screen, $request->except(['_token', '_method']));

        return redirect()->route('admin.settings.show', $screen)->with('status', 'Settings saved.');
    }

    public function sendTestEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'string', 'email', 'max:255'],
        ]);

        try {
            $this->settings->sendTestEmail($validated['test_email']);
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'The test email could not be sent: '.$e->getMessage());
        }

        return back()->with('status', "Test email sent to {$validated['test_email']}.");
    }

    private function assertScreen(string $screen): void
    {
        abort_unless($this->settings->screenExists($screen), 404);
    }
}
