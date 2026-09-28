<?php

namespace App\Http\Requests\Admin;

use App\Models\OfflinePackagePayment;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

/**
 * One request for the payment gateway screens: per-gateway credential cards,
 * manual payment methods, and the offline approval decisions.
 */
class PaymentGatewayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return match ($this->route()?->getName()) {
            'admin.payment-methods.save', 'api.v1.payment-methods.update' => $this->gatewayRules(),
            'admin.manual-methods.store', 'api.v1.manual-payment-methods.store' => $this->manualRules(),
            'admin.manual-methods.update', 'api.v1.manual-payment-methods.update' => $this->manualRules(),
            'admin.offline.wallet.decision', 'api.v1.offline.wallet.decision' => $this->approvalRules(),
            'admin.offline.packages.decision', 'api.v1.offline.packages.decision' => $this->approvalRules(),
            'admin.offline.orders.mark-paid', 'api.v1.offline.orders.mark-paid' => [],
            default => [],
        };
    }

    /** @return array<string, array<int, mixed>> */
    private function gatewayRules(): array
    {
        $gateway = (string) $this->route('gateway');
        $card = PaymentGatewayService::GATEWAYS[$gateway] ?? null;
        if ($card === null) {
            return []; // the controller turns an unknown gateway into a 404
        }
        [$fields, $hasSandbox] = [$card[1], $card[2]];

        $rules = [];
        foreach (array_keys($fields) as $field) {
            $rules[$field] = ['nullable', 'string', 'max:255'];
        }
        if ($hasSandbox) {
            $rules['sandbox'] = ['nullable', 'in:0,1'];
        }
        $rules['types'] = ['nullable', 'array'];
        $rules['types.*'] = [new In(PaymentGatewayService::TYPES)];

        return $rules;
    }

    /** @return array<string, array<int, mixed>> */
    private function manualRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'logo' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'in:0,1'],
        ];
    }

    /** @return array<string, array<int, mixed>> */
    private function approvalRules(): array
    {
        return [
            'approval' => ['required', Rule::in(OfflinePackagePayment::APPROVALS)],
        ];
    }
}
