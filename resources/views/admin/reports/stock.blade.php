@extends('admin.layouts.app')
@use('App\Services\ReportService')
@section('title', 'Stock report')
@section('crumbs')
    Reports
@endsection

@section('content')

@php
    $filtered = $filters['search'] !== '' || $filters['category'] || $filters['state'];
    $tone = ['out' => 'bad', 'low' => 'warn', 'ok' => 'ok'];
@endphp

<div class="page-actions">
    <p class="muted" style="margin:0">
        @if ($rows->total() === 0)
            No products{{ $filtered ? ' match these filters' : ' yet' }}.
        @else
            Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ number_format($rows->total()) }} {{ Str::plural('product', $rows->total()) }}, lowest stock first.
        @endif
    </p>
    <a href="{{ $csvUrl }}" class="btn btn-ghost">Download CSV</a>
</div>

<form method="GET" action="{{ route('admin.reports.stock') }}" class="card filters" role="search">
    <label class="sr-only" for="q">Search</label>
    <input id="q" type="search" name="search" value="{{ $filters['search'] }}" class="input filter-search" placeholder="Product name or SKU">

    <label class="sr-only" for="filter-category">Category</label>
    <select id="filter-category" name="category" class="select">
        <option value="">Any category</option>
        @foreach ($categories as $c)
            <option value="{{ $c->id }}" @selected($filters['category'] === $c->id)>{{ str_repeat('— ', $c->level) }}{{ $c->name }}</option>
        @endforeach
    </select>

    <label class="sr-only" for="filter-state">Stock state</label>
    <select id="filter-state" name="state" class="select">
        <option value="">Any stock state</option>
        @foreach (ReportService::STATES as $value => $label)
            <option value="{{ $value }}" @selected($filters['state'] === $value)>{{ $label }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-ghost">Apply</button>
    @if ($filtered)
        <a href="{{ route('admin.reports.stock') }}" class="btn btn-link">Clear filters</a>
    @endif
</form>

<div class="card">
    @if ($rows->isEmpty())
        <div class="empty">
            @if ($filtered)
                <strong>Nothing matches</strong>
                Try fewer filters, or <a href="{{ route('admin.reports.stock') }}" style="color:var(--tangerine)">see every product</a>.
            @else
                <strong>No products yet</strong>
                Stock levels appear here once products are added.
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="right">Current stock</th>
                        <th class="right">Low-stock level</th>
                        <th>State</th>
                        <th class="right">Stock value</th>
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
                        <td class="right num">{{ number_format($row['current_stock']) }}</td>
                        <td class="right num muted">{{ number_format($row['low_stock_quantity']) }}</td>
                        <td><span class="pill pill-{{ $tone[$row['state']] }}">{{ $row['state_label'] }}</span></td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($row['stock_value'], 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="report-totals">
                    <tr>
                        <td colspan="2">Total, {{ number_format($totals['products']) }} {{ Str::plural('product', $totals['products']) }}</td>
                        <td class="right num">{{ number_format($totals['units']) }}</td>
                        <td colspan="2"></td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($totals['stock_value'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>

@if ($rows->hasPages())
    <div style="margin-top:16px">{{ $rows->links() }}</div>
@endif

@endsection
