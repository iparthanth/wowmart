@extends('admin.layouts.app')
@section('title', 'In-house sales')
@section('crumbs')
    Reports
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">
        In-house products sold from {{ \Illuminate\Support\Carbon::parse($filters['date_from'])->format('j M Y') }}
        to {{ \Illuminate\Support\Carbon::parse($filters['date_to'])->format('j M Y') }}. Cancelled orders are not counted.
    </p>
    <a href="{{ $csvUrl }}" class="btn btn-ghost">Download CSV</a>
</div>

@include('admin.reports._dates', ['action' => route('admin.reports.in-house-sales')])

<div class="card">
    @if ($rows->isEmpty())
        <div class="empty">
            <strong>No in-house sales in this range</strong>
            Widen the dates to include older orders.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="right">Units sold</th>
                        <th class="right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>
                            <a href="{{ route('admin.products.edit', $row['id']) }}" class="product-name">{{ $row['name'] }}</a>
                            <div class="product-meta mono">{{ $row['sku'] ?? 'No SKU' }}</div>
                        </td>
                        <td class="right num">{{ number_format($row['units']) }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($row['revenue'], 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="report-totals">
                    <tr>
                        <td>Total, {{ number_format($totals['products']) }} {{ Str::plural('product', $totals['products']) }}</td>
                        <td class="right num">{{ number_format($totals['units']) }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($totals['revenue'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>

@endsection
