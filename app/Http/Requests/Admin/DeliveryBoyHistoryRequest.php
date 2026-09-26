<?php

namespace App\Http\Requests\Admin;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/** A payout to a delivery boy, or cash collected back from them. */
class DeliveryBoyHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'delivery_boy_id' => [
                'required', 'integer',
                Rule::exists('users', 'id')->where('user_type', 'delivery_boy'),
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999'],
            'note' => ['nullable', 'string', 'max:200'],
            'paid_at' => ['nullable', 'date', 'before_or_equal:today'],
            'collected_at' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    /** @return array{delivery_boy_id: int, amount: string, note: string|null, paid_at: CarbonImmutable|null, collected_at: CarbonImmutable|null} */
    public function payload(): array
    {
        return [
            'delivery_boy_id' => (int) $this->validated('delivery_boy_id'),
            'amount' => $this->validated('amount'),
            'note' => $this->validated('note'),
            'paid_at' => $this->recordedDate('paid_at'),
            'collected_at' => $this->recordedDate('collected_at'),
        ];
    }

    /** The timestamp defaults to now when left blank. Named to avoid Request::date(). */
    private function recordedDate(string $key): ?CarbonImmutable
    {
        $raw = $this->input($key);

        return is_string($raw) && trim($raw) !== ''
            ? Carbon::parse($raw)->toImmutable()
            : null;
    }
}
