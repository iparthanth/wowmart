@extends('admin.layouts.app')
@section('title', 'Offline Payment Orders')
@section('crumbs')
    Payment Gateways / Offline Payment Orders
@endsection

@section('content')

<div class="card">
    <div class="card-head" style="flex-wrap:wrap;gap:8px">
        <h2>Offline payment orders</h2>
        <form method="GET" action="{{ route('admin.offline.orders') }}" class="row-actions" style="flex-wrap:wrap;gap:8px">
            <select name="payment_status" class="input" style="width:auto">
                <option value="">Any payment</option>
                @foreach (['unpaid', 'paid'] as $s)
                    <option value="{{ $s }}" @selected(request('payment_status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="delivery_status" class="input" style="width:auto">
                <option value="">Any delivery</option>
                @foreach (['pending', 'confirmed', 'on_delivery', 'delivered', 'cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('delivery_status') === $s)>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                @endforeach
            </select>
            <input type="date" name="date" class="input" style="width:auto" value="{{ request('date') }}">
            <input type="text" name="search" class="input" style="width:180px" placeholder="Order code" value="{{ request('search') }}">
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
        </form>
    </div>
    @if ($orders->isEmpty())
        <div class="empty">
            <strong>No offline orders found</strong>
            Orders paid through a manual payment method land here for verification.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order code</th>
                        <th class="right">Num. of products</th>
                        <th>Customer</th>
                        <th>Seller</th>
                        <th class="right">Amount</th>
                        <th>Delivery status</th>
                        <th>Payment method</th>
                        <th>Payment status</th>
                        <th class="tight"><span class="sr-only">Options</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td><span class="mono">{{ $order->code }}</span></td>
                        <td class="right num">{{ $order->details_count }}</td>
                        <td>{{ $order->customer?->name }}</td>
                        <td>{{ $order->seller?->name ?? 'In house' }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($order->grand_total, 2) }}</td>
                        <td><span class="pill pill-mute">{{ str_replace('_', ' ', $order->delivery_status->value) }}</span></td>
                        <td class="muted">{{ str_replace('_', ' ', $order->payment_type) }}</td>
                        <td>
                            @if ($order->payment_status->value === 'paid')
                                <span class="pill pill-ok">paid</span>
                            @else
                                <span class="pill pill-warn">unpaid</span>
                            @endif
                        </td>
                        <td>
                            @if ($order->payment_status !== 'paid')
                                <form method="POST" action="{{ route('admin.offline.orders.mark-paid', $order) }}"
                                      onsubmit="return confirm(@js('Mark order '.$order->code.' as paid? Verify the bank or wallet transaction first.'))">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">Mark paid</button>
                                </form>
                            @else
                                <span class="faint">—</span>
                            @endif
                        </td>
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
