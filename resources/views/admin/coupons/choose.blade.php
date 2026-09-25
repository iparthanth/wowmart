@extends('admin.layouts.app')
@section('title', 'Add new coupon')
@section('crumbs')
    <a href="{{ route('admin.coupons.index') }}">Coupons</a> / New
@endsection

@section('content')
@php
    $blurbs = [
        'product_base' => 'Takes money off chosen products, wherever they are in the cart.',
        'cart_base' => 'Takes money off the whole cart once it reaches a minimum spend.',
        'welcome_base' => "A new customer's first order, for a set number of days after sign-up.",
    ];
@endphp

<div class="card">
    <div class="card-head"><h2>What should the coupon discount?</h2></div>
    <div class="card-body">
        <p class="muted" style="margin-top:0">The type cannot change later, so pick the one that fits.</p>
        <div class="choices">
            @foreach (\App\Enums\CouponType::cases() as $type)
                <a href="{{ route('admin.coupons.create', ['type' => $type->value]) }}" class="choice">
                    <strong>{{ $type->label() }}</strong>
                    <span class="muted">{{ $blurbs[$type->value] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

@endsection
