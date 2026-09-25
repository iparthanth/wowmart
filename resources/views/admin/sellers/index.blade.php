@extends('admin.layouts.app')
@section('title', 'Sellers')
@section('crumbs')
    People
@endsection

@section('content')

@php
    $tones = ['pending' => 'warn', 'approved' => 'ok', 'rejected' => 'bad'];
@endphp

<div class="page-actions">
    <p class="muted" style="margin:0">
        @if ($sellers->total() === 0)
            No sellers{{ $filtered ? ' match these filters' : ' yet' }}.
        @else
            Showing {{ $sellers->firstItem() }}–{{ $sellers->lastItem() }} of {{ number_format($sellers->total()) }} {{ Str::plural('seller', $sellers->total()) }}{{ $filtered ? ' matching the filters' : '' }}.
        @endif
    </p>
</div>

<form method="GET" action="{{ route('admin.sellers.index') }}" class="card filters" role="search">
    <label class="sr-only" for="q">Search</label>
    <input id="q" type="search" name="search" value="{{ $filters['search'] }}" class="input filter-search" placeholder="Shop name, owner name or email">

    <label class="sr-only" for="filter-verification">Verification</label>
    <select id="filter-verification" name="verification" class="select">
        <option value="">Any verification</option>
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $text)
            <option value="{{ $value }}" @selected($filters['verification'] === $value)>{{ $text }}</option>
        @endforeach
    </select>

    <label class="sr-only" for="filter-status">Status</label>
    <select id="filter-status" name="status" class="select">
        <option value="">Active and banned</option>
        <option value="active" @selected($filters['status'] === 'active')>Active</option>
        <option value="banned" @selected($filters['status'] === 'banned')>Banned</option>
    </select>

    <button type="submit" class="btn btn-ghost">Apply</button>
    @if ($filtered)
        <a href="{{ route('admin.sellers.index') }}" class="btn btn-link">Clear filters</a>
    @endif
</form>

<div class="card">
    @if ($sellers->isEmpty())
        <div class="empty">
            @if ($filtered)
                <strong>Nothing matches</strong>
                Try fewer filters, or <a href="{{ route('admin.sellers.index') }}" style="color:var(--tangerine)">see every seller</a>.
            @else
                <strong>No sellers yet</strong>
                Sellers appear here when they sign up on the storefront.
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Shop</th>
                        <th>Owner</th>
                        <th class="wrap">Verification</th>
                        <th class="right">Products</th>
                        <th class="right">Order lines</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($sellers as $seller)
                    @php $shop = $seller->shop; @endphp
                    <tr>
                        <td>
                            <div class="product-cell">
                                @if ($shop?->logoFile)
                                    <img src="{{ $shop->logoFile->url() }}" alt="" class="thumb" loading="lazy">
                                @else
                                    <span class="thumb thumb-missing" title="No logo"></span>
                                @endif
                                <div style="min-width:0">
                                    <a href="{{ route('admin.sellers.show', $seller) }}" class="product-name">{{ $shop?->name ?? $seller->name }}</a>
                                    @if ($shop)
                                        <div class="product-meta mono">{{ $shop->slug }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ $seller->name }}
                            <div class="product-meta">{{ $seller->email ?? '—' }}@if ($seller->phone) · {{ $seller->phone }}@endif</div>
                        </td>
                        <td>
                            @if ($shop)
                                <span class="pill pill-{{ $tones[$shop->verification_status] ?? 'mute' }}">{{ ucfirst($shop->verification_status) }}</span>
                            @else
                                <span class="pill pill-mute">No shop</span>
                            @endif
                        </td>
                        <td class="right num">{{ number_format($seller->products_count) }}</td>
                        <td class="right num">{{ number_format($seller->order_lines_count) }}</td>
                        <td>
                            @if ($seller->banned)
                                <span class="pill pill-bad">Banned</span>
                            @else
                                <span class="pill pill-ok">Active</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if ($sellers->hasPages())
    <div style="margin-top:16px">{{ $sellers->links() }}</div>
@endif

@endsection
