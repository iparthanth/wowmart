@extends('admin.layouts.app')
@section('title', 'Customers')
@section('crumbs')
    Customers
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">
        @if ($customers->total() === 0)
            No customers{{ $filtered ? ' match these filters' : ' yet' }}.
        @else
            Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ number_format($customers->total()) }} {{ Str::plural('customer', $customers->total()) }}{{ $filtered ? ' matching the filters' : '' }}.
        @endif
    </p>
    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">Add customer</a>
</div>

<form method="GET" action="{{ route('admin.customers.index') }}" class="card filters" role="search">
    <label class="sr-only" for="q">Search</label>
    <input id="q" type="search" name="search" value="{{ $filters['search'] }}" class="input filter-search" placeholder="Name, email or phone">

    <label class="sr-only" for="filter-status">Account status</label>
    <select id="filter-status" name="status" class="select">
        <option value="">Active and banned</option>
        <option value="active" @selected($filters['status'] === 'active')>Active only</option>
        <option value="banned" @selected($filters['status'] === 'banned')>Banned only</option>
    </select>

    <label class="sr-only" for="filter-verification">Email verification</label>
    <select id="filter-verification" name="verification" class="select">
        <option value="">Verified or not</option>
        <option value="verified" @selected($filters['verification'] === 'verified')>Email verified</option>
        <option value="unverified" @selected($filters['verification'] === 'unverified')>Email not verified</option>
    </select>

    <label class="sr-only" for="filter-sort">Sort</label>
    <select id="filter-sort" name="sort" class="select">
        @foreach (['newest' => 'Newest first', 'oldest' => 'Oldest first', 'spent' => 'Spent the most'] as $value => $text)
            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $text }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-ghost">Apply</button>
    @if ($filtered)
        <a href="{{ route('admin.customers.index') }}" class="btn btn-link">Clear filters</a>
    @endif
</form>

<div class="card">
    @if ($customers->isEmpty())
        <div class="empty">
            @if ($filtered)
                <strong>Nothing matches</strong>
                Try fewer filters, or <a href="{{ route('admin.customers.index') }}" style="color:var(--tangerine)">see every customer</a>.
            @else
                <strong>No customers yet</strong>
                Customers appear here when they sign up on the storefront, or add one yourself.
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th class="right">Orders</th>
                        <th class="right wrap">Total spent</th>
                        <th>Joined</th>
                        <th>Account</th>
                        <th>Email</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($customers as $c)
                    <tr>
                        <td>
                            <a href="{{ route('admin.customers.show', $c) }}" class="product-name">{{ $c->name }}</a>
                            <div class="product-meta">{{ $c->email ?? 'No email' }}</div>
                        </td>
                        <td class="mono" style="white-space:nowrap">{{ $c->phone ? $c->country_code.' '.$c->phone : '—' }}</td>
                        <td class="right num">{{ number_format($c->orders_count) }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format((float) $c->total_spent, 2) }}</td>
                        <td class="muted" style="white-space:nowrap">{{ $c->created_at?->format('j M Y') }}</td>
                        <td>
                            @if ($c->banned)
                                <span class="pill pill-bad">Banned</span>
                            @else
                                <span class="pill pill-ok">Active</span>
                            @endif
                        </td>
                        <td>
                            @if ($c->email_verified_at)
                                <span class="pill pill-ok">Verified</span>
                            @else
                                <span class="pill pill-mute">Not verified</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('admin.customers.show', $c) }}" class="btn btn-ghost btn-sm">View</a>
                                <a href="{{ route('admin.customers.edit', $c) }}" class="btn btn-link btn-sm">Edit</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if ($customers->hasPages())
    <div style="margin-top:16px">{{ $customers->links() }}</div>
@endif

@endsection
