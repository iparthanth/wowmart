@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')

<section aria-labelledby="attention-heading">
    <h2 id="attention-heading" class="sr-only">Needs attention</h2>
    <div class="attention">
        @foreach ($queue as $q)
            @php
                $hot = $q['count'] > 0;
                $href = Route::has($q['route']) ? route($q['route'], $q['params']) : null;
                $tag = $href ? 'a' : 'div';
            @endphp
            <{{ $tag }} @if ($href) href="{{ $href }}" @endif class="queue {{ $hot ? 'is-hot' : 'is-clear' }}">
                <span class="label">
                    @if ($hot)<x-admin.spark />@endif
                    {{ $q['label'] }}
                </span>
                <span class="count">{{ number_format($q['count']) }}</span>
                <span class="sub">{{ $hot ? $q['hint'] : 'Nothing to do' }}</span>
            </{{ $tag }}>
        @endforeach
    </div>
</section>

<dl class="card totals">
    <div>
        <dt>Products</dt>
        <dd>{{ number_format($totals['products']) }}</dd>
        <span class="faint" style="font-size:12px">{{ $totals['inHouse'] }} in-house · {{ $totals['products'] - $totals['inHouse'] }} from sellers</span>
    </div>
    <div><dt>Customers</dt><dd>{{ number_format($totals['customers']) }}</dd></div>
    <div><dt>Sellers</dt><dd>{{ number_format($totals['sellers']) }}</dd></div>
    <div>
        <dt>Order value</dt>
        <dd>৳{{ number_format($totals['orderValue'], 2) }}</dd>
        <span class="faint" style="font-size:12px">excluding cancelled</span>
    </div>
</dl>

<div class="split">
    <section class="card">
        <div class="card-head">
            <h2>Order value, last 14 days</h2>
            <span class="faint" style="font-size:12px">৳ per day · excluding cancelled</span>
        </div>
        <div class="card-body">
            <x-admin.bar-chart :points="$revenue" label="Order value" />
        </div>
    </section>

    <section class="card">
        <div class="card-head">
            <h2>Latest orders</h2>
            @if (Route::has('admin.orders.index'))
                <a href="{{ route('admin.orders.index') }}">All orders</a>
            @endif
        </div>
        @if ($recentOrders->isEmpty())
            <div class="empty"><strong>No orders yet</strong>New orders appear here as customers check out.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr><th>Order</th><th>Customer</th><th class="right">Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($recentOrders as $order)
                        <tr>
                            <td class="mono tight">
                                @if (Route::has('admin.orders.show'))
                                    <a href="{{ route('admin.orders.show', $order) }}">{{ $order->code }}</a>
                                @else
                                    {{ $order->code }}
                                @endif
                            </td>
                            <td>{{ $order->customer?->name ?? 'Deleted customer' }}</td>
                            <td class="right num">৳{{ number_format($order->grand_total, 2) }}</td>
                            <td><x-admin.status :status="$order->delivery_status" /></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
