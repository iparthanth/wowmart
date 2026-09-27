@extends('admin.layouts.app')
@section('title', 'Commission History')
@section('crumbs')
    Reports / Commission History
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">The marketplace's cut of every paid seller order, newest first.</p>
    <a href="{{ $csvUrl }}" class="btn btn-ghost">Download CSV</a>
</div>

@include('admin.reports._dates', ['action' => route('admin.reports.commission-history')])

<div class="card">
    @if ($rows->isEmpty())
        <div class="empty">
            <strong>No commission in this range</strong>
            Rows appear when a seller order is paid.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Shop</th>
                        <th class="right">Admin commission</th>
                        <th class="right">Seller earning</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td class="mono">{{ $row['order_code'] }}</td>
                        <td>{{ $row['shop'] ?? '—' }}</td>
                        <td class="right num">{{ number_format($row['admin_commission'], 2) }}</td>
                        <td class="right num">{{ number_format($row['seller_earning'], 2) }}</td>
                        <td class="muted">{{ \Illuminate\Support\Carbon::parse($row['created_at'])->format('j M Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="report-totals">
                    <tr>
                        <td colspan="2">Total, {{ number_format($totals['orders']) }} paid {{ Str::plural('order', $totals['orders']) }}</td>
                        <td class="right num">{{ number_format($totals['admin_commission'], 2) }}</td>
                        <td class="right num">{{ number_format($totals['seller_earning'], 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>

@endsection
