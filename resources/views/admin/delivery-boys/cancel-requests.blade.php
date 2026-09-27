@extends('admin.layouts.app')
@section('title', 'Cancel Requests')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Delivery Boys / Cancel Requests
@endsection

@section('content')

<div class="card">
    <div class="card-head">
        <h2>Orders delivery boys asked to cancel</h2>
        <a href="{{ route('admin.delivery-boys.index') }}" class="btn btn-link btn-sm">All delivery boys</a>
    </div>
    @if ($orders->isEmpty())
        <div class="empty">
            <strong>No cancel requests</strong>
            When a delivery boy asks to cancel an assigned order, it lands here for you to decide.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Delivery boy</th>
                        <th class="right">Total</th>
                        <th>Requested</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="product-name mono">{{ $order->code }}</a></td>
                        <td>{{ $order->customer?->name ?? '—' }}</td>
                        <td>{{ $order->deliveryBoy?->name ?? '—' }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($order->grand_total, 2) }}</td>
                        <td class="muted">{{ $order->cancel_requested_at->format('j M Y, g:i a') }}</td>
                        <td>
                            <div class="row-actions">
                                <form method="POST" action="{{ route('admin.delivery-boys.cancel-requests.approve', $order) }}"
                                      onsubmit="return confirm(@js('Approve the cancel request for order '.$order->code.'? The order is cancelled and its items go back into stock.'))">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">Approve &amp; cancel</button>
                                </form>
                                <form method="POST" action="{{ route('admin.delivery-boys.cancel-requests.decline', $order) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-sm">Decline</button>
                                </form>
                            </div>
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
