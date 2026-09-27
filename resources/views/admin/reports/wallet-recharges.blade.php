@extends('admin.layouts.app')
@section('title', 'Wallet Recharge History')
@section('crumbs')
    Reports / Wallet Recharge History
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">Customers' offline wallet top-ups; pending requests float to the top.</p>
    <a href="{{ $csvUrl }}" class="btn btn-ghost">Download CSV</a>
</div>

<form method="GET" action="{{ route('admin.reports.wallet-recharges') }}" class="card filters">
    <label class="sr-only" for="filter-approval">Approval</label>
    <select id="filter-approval" name="approval" class="select">
        @foreach (['' => 'All requests'] + App\Models\WalletRecharge::APPROVALS as $value => $text)
            <option value="{{ $value }}" @selected($filters['approval'] === $value)>{{ $value === '' ? $text : ucfirst($text) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-ghost">Apply</button>
    @if ($filters['approval'])
        <a href="{{ route('admin.reports.wallet-recharges') }}" class="btn btn-link">Clear filter</a>
    @endif
</form>

<div class="card">
    @if ($rows->isEmpty())
        <div class="empty">
            <strong>No recharge requests</strong>
            @if ($filters['approval'])
                Nothing is {{ $filters['approval'] }} right now.
            @else
                Customers' offline top-up requests land here.
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th class="right">Amount</th>
                        <th>Payment method</th>
                        <th>Approval</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <div style="min-width:0">
                                    <span class="product-name">{{ $row['customer'] }}</span>
                                    <div class="product-meta muted">{{ $row['email'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="right num">{{ number_format($row['amount'], 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $row['payment_method'])) }}</td>
                        <td>
                            @if ($row['approval'] === 'approved')
                                <span class="pill pill-ok">Approved</span>
                            @elseif ($row['approval'] === 'rejected')
                                <span class="pill pill-danger">Rejected</span>
                            @else
                                <span class="pill pill-info">Pending</span>
                            @endif
                        </td>
                        <td class="muted">{{ \Illuminate\Support\Carbon::parse($row['created_at'])->format('j M Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="report-totals">
                    <tr>
                        <td colspan="1">Total, {{ number_format($totals['requests']) }} {{ Str::plural('request', $totals['requests']) }}</td>
                        <td class="right num">{{ number_format($totals['amount'], 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>

@endsection
