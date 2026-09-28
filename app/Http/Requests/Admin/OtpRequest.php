<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** One request for the three OTP screens: login toggles, OTP parameters, SMS templates. */
class OtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return match ($this->route()?->getName()) {
            'admin.otp.login.save', 'api.v1.otp.login.update' => [
                'otp_login_customer' => ['required', 'in:0,1'],
                'otp_registration_customer' => ['required', 'in:0,1'],
                'otp_login_seller' => ['required', 'in:0,1'],
                'otp_registration_seller' => ['required', 'in:0,1'],
                'otp_login_delivery_boy' => ['required', 'in:0,1'],
                'otp_registration_delivery_boy' => ['required', 'in:0,1'],
                'otp_login_admin' => ['required', 'in:0,1'],
            ],
            'admin.otp.configuration.save', 'api.v1.otp.configuration.update' => [
                'otp_activation' => ['required', 'in:0,1'],
                'otp_length' => ['required', 'integer', 'min:4', 'max:10'],
                'otp_lifetime_seconds' => ['required', 'integer', 'min:30', 'max:3600'],
                'otp_max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
                'otp_resend_wait_seconds' => ['required', 'integer', 'min:10', 'max:600'],
                'otp_gateway' => ['required', Rule::in(['none', 'twilio', 'nexmo', 'ssl_wireless', 'bulk_sms_bd'])],
                'otp_gateway_api_key' => ['nullable', 'string', 'max:255'],
                'otp_gateway_sender_id' => ['nullable', 'string', 'max:40'],
            ],
            'admin.otp.templates.update', 'api.v1.otp.templates.update' => [
                'body' => ['required', 'string', 'max:1000'],
                'template_id' => ['nullable', 'string', 'max:60'],
                // Absent (unchecked checkbox) keeps the row's stored status.
                'status' => ['nullable', 'boolean'],
            ],
            default => [],
        };
    }
}
