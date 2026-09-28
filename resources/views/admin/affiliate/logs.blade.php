@extends('admin.layouts.app')
@section('title', 'Affiliate Logs')
@section('crumbs')
    Affiliate System / Logs
@endsection

@section('content')

<div class="card">
    <div class="card-head"><h2>Affiliate logs</h2></div>
    @if ($logs->isEmpty())
        <div class="empty">
            <strong>No affiliate activity yet</strong>
            Every commission earned and every withdrawal paid out is recorded here.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Referred by</th>
                        <th>Referral user</th>
                        <th class="right">Amount</th>
                        <th>Order</th>
                        <th>Referral type</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td class="muted num">{{ $log->id }}</td>
                        <td>{{ $log->affiliate?->user?->name ?? 'Deleted user' }} <span class="pill pill-mute mono">{{ $log->affiliate?->code }}</span></td>
                        <td>{{ $log->referredUser?->name }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($log->amount, 2) }}</td>
                        <td>
                            @if ($log->order)
                                <span class="mono">{{ $log->order->code }}</span>
                            @else
                                <span class="faint">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="pill {{ ['purchase' => 'pill-ok', 'registration' => 'pill-info', 'withdrawal' => 'pill-warn'][$log->type] ?? 'pill-mute' }}">{{ $log->type }}</span>
                        </td>
                        <td class="muted">{{ $log->created_at->format('j M Y, g:i a') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if ($logs->hasPages())
    <div style="margin-top:16px">{{ $logs->links() }}</div>
@endif

@endsection
