@extends('admin.layouts.app')
@section('title', 'All products')
@section('crumbs')
    Products
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">
        @if ($products->total() === 0)
            No products{{ $filtered ? ' match these filters' : ' yet' }}.
        @else
            Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} {{ Str::plural('product', $products->total()) }}{{ $filtered ? ' matching the filters' : '' }}.
        @endif
    </p>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add new product</a>
</div>

<form method="GET" action="{{ route('admin.products.index') }}" class="card filters" role="search">
    <label class="sr-only" for="q">Search</label>
    <input id="q" type="search" name="search" value="{{ $filters['search'] }}" class="input filter-search" placeholder="Name or SKU">

    @php
        $selects = [
            'added_by' => ['Owner', ['' => 'All owners', 'admin' => 'In-house', 'seller' => 'Sellers']],
            'stock' => ['Stock', ['' => 'Any stock', 'out' => 'Sold out', 'low' => 'Low on stock']],
            'published' => ['Status', ['' => 'Any status', '1' => 'Published', '0' => 'Unpublished']],
            'image' => ['Image', ['' => 'Any image', 'missing' => 'Missing image']],
            'sort' => ['Sort', ['newest' => 'Newest first', 'best_selling' => 'Best selling', 'price_asc' => 'Price, low to high', 'price_desc' => 'Price, high to low', 'stock_asc' => 'Stock, lowest first']],
        ];
    @endphp

    <label class="sr-only" for="filter-category">Category</label>
    <select id="filter-category" name="category" class="select">
        <option value="">All categories</option>
        @foreach ($categories as $c)
            <option value="{{ $c->id }}" @selected($filters['category'] === $c->id)>{{ str_repeat('— ', $c->level) }}{{ $c->name }}</option>
        @endforeach
    </select>

    @foreach ($selects as $name => [$label, $options])
        <label class="sr-only" for="filter-{{ $name }}">{{ $label }}</label>
        <select id="filter-{{ $name }}" name="{{ $name }}" class="select">
            @foreach ($options as $value => $text)
                <option value="{{ $value }}" @selected((string) $filters[$name] === (string) $value)>{{ $text }}</option>
            @endforeach
        </select>
    @endforeach

    <button type="submit" class="btn btn-ghost">Apply</button>
    @if ($filtered)
        <a href="{{ route('admin.products.index') }}" class="btn btn-link">Clear filters</a>
    @endif
</form>

<div class="card">
    @if ($products->isEmpty())
        <div class="empty">
            @if ($filtered)
                <strong>Nothing matches</strong>
                Try fewer filters, or <a href="{{ route('admin.products.index') }}" style="color:var(--tangerine)">see every product</a>.
            @else
                <strong>No products yet</strong>
                <a href="{{ route('admin.products.create') }}" style="color:var(--tangerine)">Add the first one</a>.
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table class="table products">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="right">Price</th>
                        <th class="right">Stock</th>
                        <th class="wrap">Published</th>
                        <th class="wrap">Featured</th>
                        <th class="wrap">Today's deal</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($products as $p)
                    <tr>
                        <td>
                            <div class="product-cell">
                                @if ($p->thumbnail)
                                    <img src="{{ $p->thumbnail->url() }}" alt="" class="thumb" loading="lazy">
                                @else
                                    <span class="thumb thumb-missing" title="No image"></span>
                                @endif
                                <div style="min-width:0">
                                    <a href="{{ route('admin.products.edit', $p) }}" class="product-name">{{ $p->name }}</a>
                                    <div class="product-meta">
                                        @if ($p->sku)<span class="mono">{{ $p->sku }}</span> · @endif
                                        {{ $p->added_by === 'admin' ? 'In-house' : ($p->owner?->shop?->name ?? $p->owner?->name ?? 'Unknown seller') }}
                                        @unless ($p->approved)
                                            · <span class="pill pill-warn">Awaiting approval</span>
                                        @endunless
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="muted">{{ $p->category?->name ?? '—' }}</td>
                        <td class="right num" style="white-space:nowrap">
                            @if ($p->hasActiveDiscount())
                                ৳{{ number_format($p->discountedPrice(), 2) }}
                                <div class="faint" style="font-size:12px"><s>৳{{ number_format($p->unit_price, 2) }}</s></div>
                            @else
                                ৳{{ number_format($p->unit_price, 2) }}
                            @endif
                        </td>
                        <td class="right num" style="white-space:nowrap">
                            @if ($p->current_stock <= 0)
                                <span class="pill pill-bad">Sold out</span>
                            @elseif ($p->isLowStock())
                                <span class="pill pill-warn">{{ $p->current_stock }} left</span>
                            @else
                                {{ number_format($p->current_stock) }}
                            @endif
                        </td>
                        <td><x-admin.switch :action="route('admin.products.toggle', [$p, 'published'])" :on="$p->published" :label="'Published: '.$p->name" /></td>
                        <td><x-admin.switch :action="route('admin.products.toggle', [$p, 'featured'])" :on="$p->featured" :label="'Featured: '.$p->name" /></td>
                        <td><x-admin.switch :action="route('admin.products.toggle', [$p, 'todays_deal'])" :on="$p->todays_deal" :label="'Today\'s deal: '.$p->name" /></td>
                        <td>
                            <div class="row-actions">
                                @unless ($p->approved)
                                    <form method="POST" action="{{ route('admin.products.toggle', [$p, 'approved']) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost btn-sm">Approve</button>
                                    </form>
                                @endunless
                                <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-ghost btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.products.duplicate', $p) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm">Duplicate</button>
                                </form>
                                <form method="POST" action="{{ route('admin.products.destroy', $p) }}"
                                      onsubmit="return confirm(@js('Delete "'.$p->name.'"? This cannot be undone.'))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm link-danger">Delete</button>
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

@if ($products->hasPages())
    <div style="margin-top:16px">{{ $products->links() }}</div>
@endif

@endsection
