<?php

namespace App\Http\Requests\Admin;

use App\Models\Affiliate;
use App\Models\AffiliateWithdrawal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * One request for the affiliate screens:
 * - configurations (commission blocks, category commissions, validation days)
 * - registration form builder (enabled + custom field rows)
 * - affiliate approval decisions and withdrawal decisions
 */
class AffiliateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return match ($this->route()?->getName()) {
            'admin.affiliate.configurations', 'api.v1.affiliate.settings.update' => [
                'affiliate_first_purchase_status' => ['required', 'in:0,1'],
                'affiliate_first_purchase_type' => ['required', 'in:percent,amount'],
                'affiliate_first_purchase_value' => ['required', 'numeric', 'min:0', 'max:1000000'],
                'affiliate_sharing_status' => ['required', 'in:0,1'],
                'affiliate_sharing_type' => ['required', 'in:percent,amount'],
                'affiliate_sharing_value' => ['required', 'numeric', 'min:0', 'max:1000000'],
                'affiliate_category_status' => ['required', 'in:0,1'],
                'affiliate_category_commissions' => ['nullable', 'array'],
                'affiliate_category_commissions.*.category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
                'affiliate_category_commissions.*.type' => ['required', 'in:percent,amount'],
                'affiliate_category_commissions.*.value' => ['required', 'numeric', 'min:0', 'max:1000000'],
                'affiliate_validation_days' => ['required', 'integer', 'min:1', 'max:3650'],
            ],
            'admin.affiliate.registration.save', 'api.v1.affiliate.registration.update' => [
                'affiliate_registration_enabled' => ['required', 'in:0,1'],
                'fields' => ['nullable', 'array', 'max:15'],
                'fields.*.label' => ['required_with:fields', 'string', 'max:120'],
                'fields.*.type' => ['required_with:fields', 'in:text,number,email,tel,textarea'],
            ],
            'admin.affiliate.users.decision' => [
                'status' => ['required', Rule::in(Affiliate::STATUSES)],
            ],
            'admin.affiliate.withdrawals.decision' => [
                'status' => ['required', Rule::in(['approved', 'rejected'])],
                'note' => ['nullable', 'string', 'max:500'],
            ],
            'api.v1.affiliate.users.update' => [
                'status' => ['sometimes', Rule::in(Affiliate::STATUSES)],
            ],
            'api.v1.affiliate.withdrawals.update' => [
                'status' => ['sometimes', Rule::in(AffiliateWithdrawal::STATUSES)],
                'note' => ['nullable', 'string', 'max:500'],
            ],
            default => [],
        };
    }
}
