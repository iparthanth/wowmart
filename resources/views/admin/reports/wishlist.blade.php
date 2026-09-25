@extends('admin.layouts.app')
@section('title', 'Wishlist report')
@section('crumbs')
    Reports
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">Products customers have saved to a wishlist, most wanted first.</p>
    <a href="{{ $csvUrl }}" class="btn btn-ghost">Download CSV</a>
</div>

<form method="GET" action="{{ route('admin.reports.wishlist') }}" class="card filters">
    <label class="sr-only" for="filter-category">Category</label>
    <select id="filter-category" name="category" class="select">
        <option value="">Any category</option>
        @foreach ($categories as $c)
            <option value="{{ $c->id }}" @selected($filters['category'] === $c->id)>{{ str_repeat('— ', $c->level) }}{{ $c->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-ghost">Apply</button>
    @if ($filters['category'])
        <a href="{{ route('admin.reports.wishlist') }}" class="btn btn-link">Clear filter</a>
    @endif
</form>

<div class="card">
    @if ($rows->isEmpty())
        <div class="empty">
            @if ($filters['category'])
                <strong>No wishlisted products in this category</strong>
                Pick another category, or <a href="{{ route('admin.reports.wishlist') }}" style="color:var(--tangerine)">see every category</a>.
            @else
                <strong>No wishlists yet</strong>
                Products appear here once customers save them from the storefront.
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="right">Wishlists</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>
                            <a href="{{ route('admin.products.edit', $row['id']) }}" class="product-name">{{ $row['name'] }}</a>
                            <div class="product-meta mono">{{ $row['sku'] ?? 'No SKU' }}</div>
                        </td>
                        <td class="muted">{{ $row['category'] ?? '—' }}</td>
                        <td class="right num">{{ number_format($row['wishlists']) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="report-totals">
                    <tr>
                        <td colspan="2">Total, {{ number_format($totals['products']) }} {{ Str::plural('product', $totals['products']) }}</td>
                        <td class="right num">{{ number_format($totals['wishlists']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>

@endsection
