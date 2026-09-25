@extends('admin.layouts.app')
@section('title', $customer->name)
@section('crumbs')
    <a href="{{ route('admin.customers.index') }}">Customers</a> / {{ $customer->name }}
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">
        Joined {{ $customer->created_at?->format('j M Y') }} · {{ $customer->orders_count }} {{ Str::plural('order', $customer->orders_count) }}
    </p>
    <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-ghost">Edit details</a>
</div>

<div class="split">
    <div style="display:grid;gap:18px;min-width:0">
        <section class="card">
            <div class="card-head"><h2>Recent orders</h2></div>
            @if ($orders->isEmpty())
                <div class="empty">
                    <strong>No orders yet</strong>
                    Orders this customer places on the storefront appear here.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Placed</th>
                                <th class="right">Amount</th>
                                <th class="wrap">Delivery status</th>
                                <th class="wrap">Payment status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order) }}" class="mono product-name">{{ $order->code }}</a></td>
                                <td class="muted" style="white-space:nowrap">{{ $order->created_at->format('j M Y, g:i a') }}</td>
                                <td class="right num" style="white-space:nowrap">৳{{ number_format($order->grand_total, 2) }}</td>
                                <td><x-admin.status :status="$order->delivery_status" /></td>
                                <td><x-admin.status :status="$order->payment_status" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($customer->orders_count > $orders->count())
                    <p class="hint" style="margin:0;padding:12px 18px">Showing the latest {{ $orders->count() }} of {{ $customer->orders_count }} orders.</p>
                @endif
            @endif
        </section>

        <section class="card">
            <div class="card-head"><h2>Addresses</h2></div>
            @if ($customer->addresses->isEmpty())
                <div class="empty">
                    <strong>No saved addresses</strong>
                    The customer adds these at checkout on the storefront.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($customer->addresses as $address)
                            <tr>
                                <td>
                                    {{ $address->name }}
                                    @if ($address->set_default)<span class="pill pill-info">Default</span>@endif
                                </td>
                                <td>{{ $address->address }}, {{ $address->city }}{{ $address->postal_code ? ' '.$address->postal_code : '' }}</td>
                                <td class="mono" style="white-space:nowrap">{{ $address->phone }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <div style="display:grid;gap:18px;min-width:0;align-content:start">
        <section class="card">
            <div class="card-head"><h2>Profile</h2></div>
            <dl class="facts" style="border-top:0">
                <div><dt>Email</dt><dd>{{ $customer->email ?? '—' }}</dd></div>
                <div>
                    <dt>Email verified</dt>
                    <dd>
                        @if ($customer->email_verified_at)
                            <span class="pill pill-ok">Verified {{ $customer->email_verified_at->format('j M Y') }}</span>
                        @else
                            <span class="pill pill-mute">Not verified</span>
                        @endif
                    </dd>
                </div>
                <div><dt>Phone</dt><dd class="mono">{{ $customer->phone ? $customer->country_code.' '.$customer->phone : '—' }}</dd></div>
                <div>
                    <dt>Account</dt>
                    <dd>
                        @if ($customer->banned)
                            <span class="pill pill-bad">Banned</span>
                        @else
                            <span class="pill pill-ok">Active</span>
                        @endif
                    </dd>
                </div>
                <div><dt>Total spent</dt><dd class="num">৳{{ number_format((float) $customer->total_spent, 2) }}</dd></div>
                <div><dt>Wishlist items</dt><dd class="num">{{ number_format($customer->wishlists_count) }}</dd></div>
                <div><dt>Reviews written</dt><dd class="num">{{ number_format($customer->reviews_count) }}</dd></div>
            </dl>
            <p class="hint" style="margin:0;padding:0 18px 14px">Total spent leaves out cancelled orders.</p>
        </section>

        <section class="card">
            <div class="card-head"><h2>Account actions</h2></div>
            <div class="card-body aside-actions">
                @if ($customer->banned)
                    <form method="POST" action="{{ route('admin.customers.toggle', [$customer, 'banned']) }}">
                        @csrf
                        <input type="hidden" name="banned" value="0">
                        <button type="submit" class="btn btn-primary btn-block">Unban account</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.customers.toggle', [$customer, 'banned']) }}"
                          onsubmit="return confirm(@js('Ban '.$customer->name.'? They will not be able to sign in or place orders until unbanned.'))">
                        @csrf
                        <input type="hidden" name="banned" value="1">
                        <button type="submit" class="btn btn-ghost btn-block">Ban account</button>
                    </form>
                @endif

                @if ($customer->orders_count === 0)
                    <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}"
                          onsubmit="return confirm(@js('Delete '.$customer->name.'? Their addresses, cart, wishlist and reviews are deleted too. This cannot be undone.'))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">Delete customer</button>
                    </form>
                @else
                    <p class="hint" style="margin:0">Customers with orders cannot be deleted, so order history stays complete. Ban the account instead.</p>
                @endif
            </div>
        </section>
    </div>
</div>

@endsection
