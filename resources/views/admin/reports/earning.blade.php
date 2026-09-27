@extends('admin.layouts.app')
@section('title', 'Earning Report')
@section('crumbs')
    Reports / Earning Report
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">What each shop earned, and what the marketplace earned from it, over paid seller orders.</p>
    <a href="{{ $csvUrl }}" class="btn btn-ghost">Download CSV</a>
</div>

@include('admin.reports._dates', ['action' => route('admin.reports.earning')])

<div class="card">
    @if ($rows->isEmpty())
        <div class="empty">
            <strong>No shops yet</strong>
            Seller earnings appear once shops and paid seller orders exist.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Shop</th>
                        <th>Seller</th>
                        <th class="right">Paid orders</th>
                        <th class="right">Admin commission</th>
                        <th class="right">Seller earning</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td><span class="product-name">{{ $row['shop'] }}</span></td>
                        <td class="muted">{{ $row['seller'] }}</td>
                        <td class="right num">{{ number_format($row['orders']) }}</td>
                        <td class="right num">{{ number_format($row['admin_commission'], 2) }}</td>
                        <td class="right num">{{ number_format($row['seller_earning'], 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="report-totals">
                    <tr>
                        <td colspan="2">Total, {{ number_format($totals['sellers']) }} {{ Str::plural('shop', $totals['sellers']) }}, {{ number_format($totals['orders']) }} paid {{ Str::plural('order', $totals['orders']) }}</td>
                        <td></td>
                        <td class="right num">{{ number_format($totals['admin_commission'], 2) }}</td>
                        <td class="right num">{{ number_format($totals['seller_earning'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>

@endsection
