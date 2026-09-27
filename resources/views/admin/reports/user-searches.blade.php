@extends('admin.layouts.app')
@section('title', 'User Searches')
@section('crumbs')
    Reports / User Searches
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">What visitors typed into the storefront search box, most searched first.</p>
    <a href="{{ $csvUrl }}" class="btn btn-ghost">Download CSV</a>
</div>

<form method="GET" action="{{ route('admin.reports.user-searches') }}" class="card filters" role="search">
    <label class="sr-only" for="q">Search</label>
    <input id="q" type="search" name="search" value="{{ $filters['search'] }}" class="input filter-search" placeholder="Search term">
    <button type="submit" class="btn btn-ghost">Apply</button>
    @if ($filters['search'] !== '')
        <a href="{{ route('admin.reports.user-searches') }}" class="btn btn-link">Clear filter</a>
    @endif
</form>

<div class="card">
    @if ($rows->isEmpty())
        <div class="empty">
            @if ($filters['search'] !== '')
                <strong>Nothing matches</strong>
                Try another term, or <a href="{{ route('admin.reports.user-searches') }}" style="color:var(--tangerine)">see every search</a>.
            @else
                <strong>No searches yet</strong>
                Terms appear here as visitors use the storefront search box.
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Search term</th>
                        <th class="right">Searches</th>
                        <th>Last searched</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row['query'] }}</td>
                        <td class="right num">{{ number_format($row['searches']) }}</td>
                        <td class="muted">{{ \Illuminate\Support\Carbon::parse($row['last_searched'])->format('j M Y, g:i a') }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="report-totals">
                    <tr>
                        <td>Total, {{ number_format($totals['terms']) }} {{ Str::plural('term', $totals['terms']) }}</td>
                        <td class="right num">{{ number_format($totals['searches']) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>

@endsection
