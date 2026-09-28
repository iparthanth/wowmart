@extends('admin.layouts.app')
@section('title', 'Offline Wallet Recharge')
@section('crumbs')
    Payment Gateways / Offline Wallet Recharge
@endsection

@section('content')

<div class="card">
    <div class="card-head" style="flex-wrap:wrap;gap:8px">
        <h2>Offline wallet recharge requests</h2>
        <div class="row-actions">
            @foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $text)
                <a href="{{ route('admin.offline.wallet', $value === '' ? [] : ['approval' => $value]) }}"
                   class="btn btn-sm {{ request('approval', '') === $value ? 'btn-primary' : 'btn-ghost' }}">{{ $text }}</a>
            @endforeach
        </div>
    </div>
    @if ($recharges->isEmpty())
        <div class="empty">
            <strong>No requests {{ request('approval') ? 'are '.request('approval') : 'yet' }}</strong>
            Customers who sent a manual top-up submit their transaction here; approving credits their wallet.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th class="right">Amount</th>
                        <th>Method</th>
                        <th>TXN ID</th>
                        <th>Photo</th>
                        <th>Approval</th>
                        <th>Date</th>
                        <th class="tight"><span class="sr-only">Options</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($recharges as $recharge)
                    <tr>
                        <td class="muted num">{{ $recharge->id }}</td>
                        <td>
                            {{ $recharge->customer?->name ?? 'Deleted user' }}
                            <div class="product-meta muted">{{ $recharge->customer?->email }}</div>
                        </td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($recharge->amount, 2) }}</td>
                        <td class="muted">{{ $recharge->payment_method }}</td>
                        <td class="mono">{{ $recharge->transaction_id ?? '—' }}</td>
                        <td>
                            @if ($recharge->receipt)
                                <a href="{{ $recharge->receipt->url() }}" target="_blank" rel="noopener">
                                    <img src="{{ $recharge->receipt->url() }}" alt="receipt" style="max-height:32px">
                                </a>
                            @else
                                <span class="faint">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="pill {{ ['pending' => 'pill-warn', 'approved' => 'pill-ok', 'rejected' => 'pill-bad'][$recharge->approval] }}">{{ $recharge->approval }}</span>
                        </td>
                        <td class="muted">{{ $recharge->created_at->format('j M Y') }}</td>
                        <td>
                            @if ($recharge->approval === 'pending')
                                <div class="row-actions">
                                    <form method="POST" action="{{ route('admin.offline.wallet.decision', $recharge) }}"
                                          onsubmit="return confirm(@js('Approve the ৳'.number_format($recharge->amount, 2).' top-up? The wallet is credited immediately.'))">
                                        @csrf
                                        <input type="hidden" name="approval" value="approved">
                                        <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.offline.wallet.decision', $recharge) }}">
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

@if ($recharges->hasPages())
    <div style="margin-top:16px">{{ $recharges->links() }}</div>
@endif

@endsection
