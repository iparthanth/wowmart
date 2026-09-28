<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateWithdrawal;
use App\Models\BusinessSetting;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Every affiliate write: configuration screens, approvals, withdrawal decisions. */
class AffiliateService
{
    /** Commission blocks on the configurations screen, as stored settings. */
    public const DEFAULTS = [
        'affiliate_first_purchase_status' => '1',
        'affiliate_first_purchase_type' => 'percent',
        'affiliate_first_purchase_value' => '5',
        'affiliate_sharing_status' => '1',
        'affiliate_sharing_type' => 'percent',
        'affiliate_sharing_value' => '3',
        'affiliate_category_status' => '0',
        'affiliate_category_commissions' => '[]',
        'affiliate_validation_days' => '30',
        'affiliate_registration_enabled' => '1',
        'affiliate_registration_fields' => '[]',
    ];

    /** @return array<string, string> every affiliate setting with defaults filled in */
    public static function settings(): array
    {
        $settings = [];
        foreach (self::DEFAULTS as $key => $default) {
            $settings[$key] = (string) BusinessSetting::get($key, $default);
        }

        return $settings;
    }

    public function saveConfigurations(array $data): void
    {
        if (($data['affiliate_sharing_status'] ?? '0') === '1' && ($data['affiliate_category_status'] ?? '0') === '1') {
            throw new DomainException('Product sharing affiliate and category wise affiliate cannot be enabled at the same time.');
        }

        foreach ($data as $key => $value) {
            if (array_key_exists($key, self::DEFAULTS)) {
                BusinessSetting::put($key, is_array($value) ? json_encode($value) : (string) $value);
            }
        }
    }

    public function saveRegistrationForm(array $data): void
    {
        BusinessSetting::put('affiliate_registration_enabled', (string) $data['affiliate_registration_enabled']);
        BusinessSetting::put('affiliate_registration_fields', json_encode(array_values($data['fields'] ?? [])));
    }

    /** Approve or reject a pending affiliate; anything else is refused. */
    public function decideAffiliate(Affiliate $affiliate, string $status): Affiliate
    {
        if ($affiliate->status === $status) {
            throw new DomainException("This affiliate is already {$status}.");
        }

        return DB::transaction(fn () => tap($affiliate)->update(['status' => $status]));
    }

    /** Approving pays the balance out; rejecting keeps the money on the account. */
    public function decideWithdrawal(AffiliateWithdrawal $withdrawal, string $status, ?string $note = null): AffiliateWithdrawal
    {
        if ($withdrawal->status !== 'pending') {
            throw new DomainException('This withdrawal request has already been decided.');
        }
        if ($status === 'approved' && $withdrawal->amount > (float) $withdrawal->affiliate->balance) {
            throw new DomainException('The affiliate balance no longer covers this request.');
        }

        return DB::transaction(function () use ($withdrawal, $status, $note): AffiliateWithdrawal {
            tap($withdrawal)->update(['status' => $status, 'note' => $note]);

            if ($status === 'approved') {
                $withdrawal->affiliate->decrement('balance', $withdrawal->amount);
                $withdrawal->affiliate->logs()->create([
                    'type' => 'withdrawal',
                    'amount' => $withdrawal->amount,
                    'description' => "Withdrawal paid out (request #{$withdrawal->id})",
                ]);
            }

            return $withdrawal;
        });
    }

    /** Total earned across commission logs, for the users screen. */
    public static function earnedFor(Affiliate $affiliate): float
    {
        return (float) $affiliate->logs()->whereIn('type', ['purchase', 'registration'])->sum('amount');
    }
}
