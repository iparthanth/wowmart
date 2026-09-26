@extends('admin.layouts.app')
@use('App\Enums\DeliveryStatus')
@use('App\Enums\PaymentStatus')
@php
    // Sales lists pass their own label and base route; "All orders" is the default.
    $listLabel ??= 'All orders';
    $listRoute ??= 'admin.orders.index';
@endphp
@section('title', $listLabel)
@section('crumbs')
    Sales / {{ $listLabel }}
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">
        @if ($orders->total() === 0)
            No orders{{ $filtered ? ' match these filters' : ' yet' }}.
        @else
            Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ number_format($orders->total()) }} {{ Str::plural('order', $orders->total()) }}{{ $filtered ? ' matching the filters' : '' }}.
        @endif
    </p>
</div>

<form method="GET" action="{{ route($listRoute) }}" class="card filters" role="search">
    <label class="sr-only" for="q">Search</label>
    <input id="q" type="search" name="search" value="{{ $filters['search'] }}" class="input filter-search" placeholder="Order code, customer name, email or phone">

    <label class="sr-only" for="filter-delivery_status">Delivery status</label>
    <select id="filter-delivery_status" name="delivery_status" class="select">
        <option value="">Any delivery status</option>
        @foreach (DeliveryStatus::cases() as $s)
            <option value="{{ $s->value }}" @selected($filters['delivery_status'] === $s)>{{ $s->label() }}</option>
        @endforeach
    </select>

    <label class="sr-only" for="filter-payment_status">Payment status</label>
    <select id="filter-payment_status" name="payment_status" class="select">
        <option value="">Any payment status</option>
        @foreach (PaymentStatus::cases() as $s)
            <option value="{{ $s->value }}" @selected($filters['payment_status'] === $s)>{{ $s->label() }}</option>
        @endforeach
    </select>

    <label class="filter-date">
        <span class="muted">From</span>
        <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="input">
    </label>
    <label class="filter-date">
        <span class="muted">To</span>
        <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="input">
    </label>

    <label class="sr-only" for="filter-sort">Sort</label>
    <select id="filter-sort" name="sort" class="select">
        @foreach (['newest' => 'Newest first', 'oldest' => 'Oldest first', 'amount' => 'Largest amount'] as $value => $text)
            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $text }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-ghost">Apply</button>
    @if ($filtered)
        <a href="{{ route($listRoute) }}" class="btn btn-link">Clear filters</a>
    @endif
</form>

<div class="card">
    @if ($orders->isEmpty())
        <div class="empty">
            @if ($filtered)
                <strong>Nothing matches</strong>
                Try fewer filters, or <a href="{{ route($listRoute) }}" style="color:var(--tangerine)">clear the filters</a>.
            @else
                <strong>No orders yet</strong>
                New orders appear here as customers check out.
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th class="right">Items</th>
                        <th>Customer</th>
                        <th>Seller</th>
                        <th class="right">Amount</th>
                        <th class="wrap">Delivery status</th>
                        <th class="wrap">Payment method</th>
                        <th class="wrap">Payment status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="mono product-name">{{ $order->code }}</a>
                            <div class="product-meta">{{ $order->created_at->format('j M Y, g:i a') }}</div>
                        </td>
                        <td class="right num">{{ $order->details_count }}</td>
                        <td>{{ $order->customer?->name ?? 'Deleted customer' }}</td>
                        <td class="muted">{{ $order->seller ? ($order->seller->shop?->name ?? $order->seller->name) : 'In-house' }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($order->grand_total, 2) }}</td>
                        <td><x-admin.status :status="$order->delivery_status" /></td>
                        <td class="muted">{{ $order->paymentMethodLabel() }}</td>
                        <td><x-admin.status :status="$order->payment_status" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if ($orders->hasPages())
    <div style="margin-top:16px">{{ $orders->links() }}</div>
@endif

@endsection
