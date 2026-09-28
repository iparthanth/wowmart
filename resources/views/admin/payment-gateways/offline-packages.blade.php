@extends('admin.layouts.app')
@section('title', ucfirst($type).' Package Payments')
@section('crumbs')
    Payment Gateways / Offline {{ ucfirst($type) }} Package Payments
@endsection

@section('content')

<div class="card">
    <div class="card-head" style="flex-wrap:wrap;gap:8px">
        <h2>Offline {{ $type }} package payment requests</h2>
        <div class="row-actions">
            @foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $text)
                <a href="{{ route('admin.offline.packages.'.$type, $value === '' ? [] : ['approval' => $value]) }}"
                   class="btn btn-sm {{ request('approval', '') === $value ? 'btn-primary' : 'btn-ghost' }}">{{ $text }}</a>
            @endforeach
        </div>
    </div>
    @if ($payments->isEmpty())
        <div class="empty">
            <strong>No requests {{ request('approval') ? 'are '.request('approval') : 'yet' }}</strong>
            {{ ucfirst($type) }}s who paid for a package by hand submit their receipt here for verification.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Package</th>
                        <th class="right">Amount</th>
                        <th>Method</th>
                        <th>TXN ID</th>
                        <th>Receipt</th>
                        <th>Approval</th>
                        <th>Date</th>
                        <th class="tight"><span class="sr-only">Options</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($payments as $payment)
                    <tr>
                        <td class="muted num">{{ $payment->id }}</td>
                        <td>
                            {{ $payment->user?->name ?? 'Deleted user' }}
                            <div class="product-meta muted">{{ $payment->user?->email }}</div>
                        </td>
                        <td>{{ $payment->package_name }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($payment->amount, 2) }}</td>
                        <td class="muted">{{ $payment->payment_method }}</td>
                        <td class="mono">{{ $payment->transaction_id ?? '—' }}</td>
                        <td>
                            @if ($payment->receipt)
                                <a href="{{ $payment->receipt->url() }}" target="_blank" rel="noopener">
                                    <img src="{{ $payment->receipt->url() }}" alt="receipt" style="max-height:32px">
                                </a>
                            @else
                                <span class="faint">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="pill {{ ['pending' => 'pill-warn', 'approved' => 'pill-ok', 'rejected' => 'pill-bad'][$payment->approval] }}">{{ $payment->approval }}</span>
                        </td>
                        <td class="muted">{{ $payment->created_at->format('j M Y') }}</td>
                        <td>
                            @if ($payment->approval === 'pending')
                                <div class="row-actions">
                                    <form method="POST" action="{{ route('admin.offline.packages.decision', $payment) }}">
                                        @csrf
                                        <input type="hidden" name="approval" value="approved">
                                        <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.offline.packages.decision', $payment) }}">
                                        @csrf
                                        <input type="hidden" name="approval" value="rejected">
                                        <button type="submit" class="btn btn-link btn-sm link-danger">Reject</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if ($payments->hasPages())
    <div style="margin-top:16px">{{ $payments->links() }}</div>
@endif

@endsection
