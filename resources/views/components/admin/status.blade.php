@props(['status'])

@php
    // Tone is presentation, so it lives here rather than on the enums.
    $tone = match ($status) {
        \App\Enums\DeliveryStatus::Pending => 'warn',
        \App\Enums\DeliveryStatus::Confirmed,
        \App\Enums\DeliveryStatus::PickedUp,
        \App\Enums\DeliveryStatus::OnTheWay => 'info',
        \App\Enums\DeliveryStatus::Delivered,
        \App\Enums\PaymentStatus::Paid => 'ok',
        \App\Enums\DeliveryStatus::Cancelled => 'bad',
        default => 'mute',
    };
@endphp

<span {{ $attributes->merge(['class' => "pill pill-{$tone}"]) }}>{{ $status->label() }}</span>
