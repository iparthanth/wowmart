@extends('admin.layouts.app')
@section('title', 'Payment Methods')
@section('crumbs')
    Payment Gateways / Payment Methods
@endsection

@section('content')

<div class="card">
    <div class="card-head">
        <h2>Payment methods</h2>
        <span class="muted">Each card holds the credentials of one gateway and what it may be used for.</span>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;align-items:start">
    @foreach ($settings as $gateway => $gatewaySettings)
        @php
            [$label, $fields, $hasSandbox] = \App\Services\PaymentGatewayService::GATEWAYS[$gateway];
        @endphp
        <form method="POST" action="{{ route('admin.payment-methods.save', $gateway) }}" class="card" style="padding:16px">
            @csrf
            <div class="card-head"><h3>{{ $label }}</h3></div>
            <div class="card-body">
                @foreach ($fields as $field => $fieldLabel)
                    <div class="field" style="margin-bottom:10px">
                        <label for="{{ $gateway }}-{{ $field }}">{{ $fieldLabel }}</label>
                        <input id="{{ $gateway }}-{{ $field }}" type="text" name="{{ $field }}" class="input"
                               value="{{ old($field, $gatewaySettings[$field]) }}" maxlength="255">
                    </div>
                @endforeach
                @if ($hasSandbox)
                    <label class="check" style="margin-bottom:10px">
                        <input type="hidden" name="sandbox" value="0">
                        <input type="checkbox" name="sandbox" value="1" @checked(old('sandbox', $gatewaySettings['sandbox']) === '1')>
                        Sandbox mode
                    </label>
                @endif
                <div class="field" style="margin-bottom:10px">
                    <span class="field-label">Used for</span>
                    <div class="row-actions" style="flex-wrap:wrap;gap:12px">
                        @foreach (\App\Services\PaymentGatewayService::TYPES as $type)
                            <label class="check">
                                <input type="checkbox" name="types[]" value="{{ $type }}" @checked(in_array($type, $gatewaySettings['types']))>
                                {{ ucwords(str_replace('_', ' ', $type)) }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </div>
        </form>
    @endforeach
</div>

@endsection
