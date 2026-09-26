<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/** The same settings screens as the panel, over the API. Secrets read back as is_set only. */
class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function show(string $screen): JsonResponse
    {
        $this->assertScreen($screen);

        $values = $this->settings->values($screen);
        $isSet = $this->settings->isSet($screen);

        $fields = [];
        foreach ($this->settings->fields($screen) as $key => $field) {
            $fields[$key] = [
                'type' => $field['type'],
                'label' => $field['label'],
                'value' => $values[$key],
                'is_set' => $isSet[$key],
            ];
        }

        return response()->json([
            'data' => [
                'screen' => $screen,
                'label' => $this->settings->screenLabel($screen),
                'fields' => $fields,
            ],
        ]);
    }

    public function update(Request $request, string $screen): JsonResponse
    {
        $this->assertScreen($screen);

        $saved = $this->settings->update($screen, (array) $request->input('values', []));

        return response()->json(['data' => ['screen' => $screen, 'saved' => $saved]]);
    }

    public function sendTestEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'string', 'email', 'max:255'],
        ]);

        try {
            $this->settings->sendTestEmail($validated['test_email']);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'The test email could not be sent: '.$e->getMessage()], 409);
        }

        return response()->json(['message' => "Test email sent to {$validated['test_email']}."]);
    }

    private function assertScreen(string $screen): void
    {
        abort_unless($this->settings->screenExists($screen), 404);
    }
}
