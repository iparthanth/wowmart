@extends('admin.layouts.app')
@use('App\Enums\DeliveryStatus')
@use('App\Enums\PaymentStatus')
@section('title', 'Order '.$order->code)
@section('crumbs')
    <a href="{{ route('admin.orders.index') }}">All orders</a> / {{ $order->code }}
@endsection

@section('content')

@php
    $customer = $order->customer;
    $nextStates = $order->delivery_status->next();
@endphp

<div class="page-actions">
    <p class="muted" style="margin:0">
        Placed {{ $order->created_at->format('j M Y, g:i a') }} · {{ $order->details->count() }} {{ Str::plural('item', $order->details->count()) }}
    </p>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">Back to all orders</a>
</div>

<div class="split">
    <div style="display:grid;gap:18px;min-width:0">
        <section class="card">
            <div class="card-head"><h2>Items</h2></div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="tight">#</th>
                            <th>Product</th>
                            <th class="wrap">Delivery type</th>
                            <th class="right">Qty</th>
                            <th class="right">Price</th>
                            <th class="right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($order->details as $i => $line)
                        <tr>
                            <td class="muted num">{{ $i + 1 }}</td>
                            <td>
                                <div class="product-cell">
                                    @if ($line->product?->thumbnail)
                                        <img src="{{ $line->product->thumbnail->url() }}" alt="" class="thumb" loading="lazy">
                                    @else
                                        <span class="thumb thumb-missing" title="No image"></span>
                                    @endif
                                    <div style="min-width:0">
                                        @if ($line->product)
                                            <a href="{{ route('admin.products.edit', $line->product) }}" class="product-name">{{ $line->product->name }}</a>
                                        @else
                                            <span class="product-name muted">Deleted product</span>
                                        @endif
                                        @if ($line->variation !== '' && $line->variation !== null)
                                            <div class="product-meta">{{ $line->variation }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="muted">{{ Str::ucfirst(str_replace('_', ' ', $line->shipping_type)) }}</td>
                            <td class="right num">{{ $line->quantity }}</td>
                            <td class="right num" style="white-space:nowrap">৳{{ number_format($line->price, 2) }}</td>
                            <td class="right num" style="white-space:nowrap">৳{{ number_format($line->lineTotal(), 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <dl class="facts">
                <div><dt>Subtotal</dt><dd class="num">৳{{ number_format($order->subtotal(), 2) }}</dd></div>
                <div><dt>Tax</dt><dd class="num">৳{{ number_format($order->taxTotal(), 2) }}</dd></div>
                <div><dt>Shipping</dt><dd class="num">৳{{ number_format($order->shippingTotal(), 2) }}</dd></div>
                <div>
                    <dt>Coupon discount @if ($order->coupon_code)<span class="mono">({{ $order->coupon_code }})</span>@endif</dt>
                    <dd class="num">−৳{{ number_format($order->coupon_discount, 2) }}</dd>
                </div>
                <div class="order-total"><dt>Grand total</dt><dd class="num">৳{{ number_format($order->grand_total, 2) }}</dd></div>
            </dl>
        </section>
    </div>

    <div style="display:grid;gap:18px;min-width:0">
        <section class="card">
            <div class="card-head"><h2>Status</h2></div>
            <dl class="facts" style="border-top:0">
                <div><dt>Delivery</dt><dd><x-admin.status :status="$order->delivery_status" /></dd></div>
                <div><dt>Payment</dt><dd><x-admin.status :status="$order->payment_status" /></dd></div>
                <div><dt>Payment method</dt><dd>{{ $order->paymentMethodLabel() }}</dd></div>
            </dl>
            <div class="card-body aside-actions">
                @forelse ($nextStates as $next)
                    @php
                        $cancel = $next === DeliveryStatus::Cancelled;
                        $label = match ($next) {
                            DeliveryStatus::Confirmed => 'Confirm order',
                            DeliveryStatus::Cancelled => 'Cancel order',
                            default => 'Mark as '.strtolower($next->label()),
                        };
                    @endphp
                    <form method="POST" action="{{ route('admin.orders.delivery-status', $order) }}"
                          @if ($cancel) onsubmit="return confirm(@js('Cancel order '.$order->code.'? Its items go back into stock. This cannot be undone.'))" @endif>
                        @csrf
                        <input type="hidden" name="status" value="{{ $next->value }}">
                        <button type="submit" class="btn {{ $cancel ? 'btn-danger' : 'btn-primary' }} btn-block">{{ $label }}</button>
                    </form>
                @empty
                    <p class="muted" style="margin:0">{{ $order->delivery_status->label() }} is final; the delivery status cannot change.</p>
                @endforelse

                @if ($order->payment_status === PaymentStatus::Unpaid && $order->delivery_status !== DeliveryStatus::Cancelled)
                    <form method="POST" action="{{ route('admin.orders.payment-status', $order) }}">
                        @csrf
                        <input type="hidden" name="status" value="paid">
                        <button type="submit" class="btn btn-ghost btn-block">Mark as paid</button>
                    </form>
                @elseif ($order->payment_status === PaymentStatus::Paid)
                    <form method="POST" action="{{ route('admin.orders.payment-status', $order) }}"
                          onsubmit="return confirm(@js('Mark order '.$order->code.' as unpaid?'))">
                        @csrf
                        <input type="hidden" name="status" value="unpaid">
                        <button type="submit" class="btn btn-ghost btn-block">Mark as unpaid</button>
                    </form>
                @endif
            </div>
        </section>

        <section class="card">
            <div class="card-head"><h2>Customer</h2></div>
            <dl class="facts" style="border-top:0">
                @if ($customer)
                    <div><dt>Name</dt><dd>{{ $customer->name }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $customer->email }}</dd></div>
                    <div><dt>Phone</dt><dd>{{ $customer->phone ?? '—' }}</dd></div>
                @else
                    <div><dt>Name</dt><dd class="muted">Deleted customer</dd></div>
                @endif
                <div><dt>Seller</dt><dd>{{ $order->seller ? ($order->seller->shop?->name ?? $order->seller->name) : 'In-house' }}</dd></div>
            </dl>
        </section>

        <section class="card">
            <div class="card-head"><h2>Shipping address</h2></div>
            @if (empty($order->shipping_address))
                <div class="card-body muted">No address was recorded.</div>
            @else
                <dl class="facts" style="border-top:0">
                    {{-- The address is a free-form snapshot, so show whatever keys the storefront saved. --}}
                    @foreach ((array) $order->shipping_address as $key => $value)
                        <div>
                            <dt>{{ Str::ucfirst(str_replace('_', ' ', (string) $key)) }}</dt>
                            <dd>{{ is_scalar($value) ? $value : ($value === null ? '—' : json_encode($value)) }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </section>
    </div>
</div>

@endsection
