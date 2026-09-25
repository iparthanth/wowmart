@extends('admin.layouts.app')
@section('title', $seller->shop?->name ?? $seller->name)
@section('crumbs')
    <a href="{{ route('admin.sellers.index') }}">Sellers</a> / {{ $seller->shop?->name ?? $seller->name }}
@endsection

@section('content')

@php
    $shop = $seller->shop;
    $name = $shop?->name ?? $seller->name;
    $tones = ['pending' => 'warn', 'approved' => 'ok', 'rejected' => 'bad'];
@endphp

<div class="page-actions">
    <p class="muted" style="margin:0">
        Joined {{ $seller->created_at?->format('j M Y') ?? '—' }} ·
        {{ number_format($seller->products_count) }} {{ Str::plural('product', $seller->products_count) }} ·
        {{ number_format($seller->order_lines_count) }} order {{ Str::plural('line', $seller->order_lines_count) }}
    </p>
    <a href="{{ route('admin.sellers.index') }}" class="btn btn-ghost">Back to all sellers</a>
</div>

<div class="split">
    <div style="display:grid;gap:18px;min-width:0">
        <section class="card">
            <div class="card-head"><h2>Products</h2></div>
            @if ($products->isEmpty())
                <div class="empty">
                    <strong>No products</strong>
                    This seller has not listed anything yet.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="right">Price</th>
                                <th class="right">Stock</th>
                                <th>Published</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        @if ($product->thumbnail)
                                            <img src="{{ $product->thumbnail->url() }}" alt="" class="thumb" loading="lazy">
                                        @else
                                            <span class="thumb thumb-missing" title="No image"></span>
                                        @endif
                                        <div style="min-width:0">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="product-name">{{ $product->name }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td class="right num" style="white-space:nowrap">৳{{ number_format($product->unit_price, 2) }}</td>
                                <td class="right num">{{ number_format($product->current_stock) }}</td>
                                <td>
                                    @if ($product->published)
                                        <span class="pill pill-ok">Published</span>
                                    @else
                                        <span class="pill pill-mute">Draft</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        @if ($products->hasPages())
            <div>{{ $products->links() }}</div>
        @endif

        <section class="card">
            <div class="card-head"><h2>Recent order lines</h2></div>
            @if ($lines->isEmpty())
                <div class="empty">
                    <strong>No orders yet</strong>
                    Lines appear here once customers buy this seller's products.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Product</th>
                                <th class="right">Qty</th>
                                <th class="right">Total</th>
                                <th class="wrap">Delivery status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($lines as $line)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $line->order) }}" class="mono product-name">{{ $line->order->code }}</a>
                                    <div class="product-meta">{{ $line->created_at?->format('j M Y') }}</div>
                                </td>
                                <td>{{ $line->product?->name ?? 'Deleted product' }}</td>
                                <td class="right num">{{ $line->quantity }}</td>
                                <td class="right num" style="white-space:nowrap">৳{{ number_format($line->lineTotal(), 2) }}</td>
                                <td><x-admin.status :status="$line->delivery_status" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <div style="display:grid;gap:18px;min-width:0">
        <section class="card">
            <div class="card-head"><h2>Verification</h2></div>
            @if ($shop)
                <dl class="facts" style="border-top:0">
                    <div><dt>Status</dt><dd><span class="pill pill-{{ $tones[$shop->verification_status] ?? 'mute' }}">{{ ucfirst($shop->verification_status) }}</span></dd></div>
                </dl>
                <div class="card-body aside-actions">
                    @if ($shop->verification_status !== 'approved')
                        <form method="POST" action="{{ route('admin.sellers.verification', $seller) }}">
                            @csrf
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="btn btn-primary btn-block">Approve seller</button>
                        </form>
                    @endif
                    @if ($shop->verification_status !== 'rejected')
                        <form method="POST" action="{{ route('admin.sellers.verification', $seller) }}"
                              onsubmit="return confirm(@js('Reject '.$name.'? You can approve the shop again later.'))">
                            @csrf
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="btn btn-danger btn-block">Reject seller</button>
                        </form>
                    @endif
                </div>
            @else
                <div class="card-body muted">This seller has not set up a shop, so there is nothing to verify.</div>
            @endif
        </section>

        <section class="card">
            <div class="card-head"><h2>Shop</h2></div>
            <dl class="facts" style="border-top:0">
                <div><dt>Name</dt><dd>{{ $shop?->name ?? '—' }}</dd></div>
                <div><dt>Address</dt><dd>{{ $shop?->address ?: '—' }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $shop?->phone ?: '—' }}</dd></div>
                <div><dt>Rating</dt><dd class="num">{{ $shop ? $shop->rating.' ('.$shop->num_of_reviews.' '.Str::plural('review', $shop->num_of_reviews).')' : '—' }}</dd></div>
            </dl>
        </section>

        <section class="card">
            <div class="card-head"><h2>Owner</h2></div>
            <dl class="facts" style="border-top:0">
                <div><dt>Name</dt><dd>{{ $seller->name }}</dd></div>
                <div><dt>Email</dt><dd>{{ $seller->email ?? '—' }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $seller->phone ?? '—' }}</dd></div>
                <div>
                    <dt>Status</dt>
                    <dd>
                        @if ($seller->banned)
                            <span class="pill pill-bad">Banned</span>
                        @else
                            <span class="pill pill-ok">Active</span>
                        @endif
                    </dd>
                </div>
            </dl>
            <div class="card-body aside-actions">
                @if ($seller->banned)
                    <form method="POST" action="{{ route('admin.sellers.toggle', [$seller, 'banned']) }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-block">Unban seller</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.sellers.toggle', [$seller, 'banned']) }}"
                          onsubmit="return confirm(@js('Ban '.$name.'? The storefront hides its products until you unban it.'))">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-block">Ban seller</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.sellers.destroy', $seller) }}"
                      onsubmit="return confirm(@js('Delete '.$name.' and its shop? This cannot be undone.'))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-link btn-sm link-danger">Delete seller</button>
                </form>
            </div>
        </section>
    </div>
</div>

@endsection
