@extends('admin.layouts.app')
@section('title', 'Seller sales')
@section('crumbs')
    Reports
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">
        Seller sales from {{ \Illuminate\Support\Carbon::parse($filters['date_from'])->format('j M Y') }}
        to {{ \Illuminate\Support\Carbon::parse($filters['date_to'])->format('j M Y') }}. Cancelled orders are not counted.
    </p>
    <a href="{{ $csvUrl }}" class="btn btn-ghost">Download CSV</a>
</div>

@include('admin.reports._dates', ['action' => route('admin.reports.seller-sales')])

<div class="card">
    @if ($rows->isEmpty())
        <div class="empty">
            <strong>No seller shops yet</strong>
            Each shop gets a row here once a seller signs up.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Shop</th>
                        <th class="right">Orders</th>
                        <th class="right">Units sold</th>
                        <th class="right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>
                            <span class="product-name">{{ $row['shop'] }}</span>
                            <div class="product-meta">{{ $row['seller'] }}</div>
                        </td>
                        <td class="right num">{{ number_format($row['orders']) }}</td>
                        <td class="right num">{{ number_format($row['units']) }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($row['revenue'], 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="report-totals">
                    <tr>
                        {{-- An order with items from two shops counts once here. --}}
                        <td>Total, {{ number_format($totals['shops']) }} {{ Str::plural('shop', $totals['shops']) }}</td>
                        <td class="right num">{{ number_format($totals['orders']) }}</td>
                        <td class="right num">{{ number_format($totals['units']) }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($totals['revenue'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>

@endsection
