@extends('admin.layouts.app')
@section('title', 'OTP Configurations')
@section('crumbs')
    OTP System / Configurations
@endsection

@section('content')

<form method="POST" action="{{ route('admin.otp.configuration.save') }}" class="card" style="max-width:860px">
    @csrf
    <div class="card-head"><h2>Activate OTP</h2></div>
    <div class="card-body">
        <div class="field-row">
            <div class="field">
                <label for="otp-activation">OTP system</label>
                <select id="otp-activation" name="otp_activation" class="input">
                    <option value="1" @selected($settings['otp_activation'] === '1')>Enabled</option>
                    <option value="0" @selected($settings['otp_activation'] === '0')>Disabled</option>
                </select>
            </div>
            <x-admin.field name="otp_length" label="Code length" type="number" min="4" max="10" :value="$settings['otp_length']" required />
            <x-admin.field name="otp_lifetime_seconds" label="Code lifetime (seconds)" type="number" min="30" max="3600" :value="$settings['otp_lifetime_seconds']" required />
        </div>
        <div class="field-row">
            <x-admin.field name="otp_max_attempts" label="Max attempts" type="number" min="1" max="10" :value="$settings['otp_max_attempts']" required />
            <x-admin.field name="otp_resend_wait_seconds" label="Resend wait (seconds)" type="number" min="10" max="600" :value="$settings['otp_resend_wait_seconds']" required />
            <div class="field">
                <label for="otp-gateway">SMS gateway</label>
                <select id="otp-gateway" name="otp_gateway" class="input">
                    @foreach (['none' => 'None', 'twilio' => 'Twilio', 'nexmo' => 'Nexmo', 'ssl_wireless' => 'SSL Wireless', 'bulk_sms_bd' => 'Bulk SMS BD'] as $value => $text)
                        <option value="{{ $value }}" @selected($settings['otp_gateway'] === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="field-row">
            <x-admin.field name="otp_gateway_api_key" label="Gateway API key" :value="$settings['otp_gateway_api_key']" maxlength="255" />
            <x-admin.field name="otp_gateway_sender_id" label="Sender ID" :value="$settings['otp_gateway_sender_id']" maxlength="40" />
        </div>
        <button type="submit" class="btn btn-primary">Save configuration</button>
    </div>
</form>

@endsection
