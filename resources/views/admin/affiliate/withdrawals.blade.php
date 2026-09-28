@extends('admin.layouts.app')
@section('title', 'Affiliate Withdraw Requests')
@section('crumbs')
    Affiliate System / Withdraw Requests
@endsection

@section('content')

<div class="card">
    <div class="card-head" style="flex-wrap:wrap;gap:8px">
        <h2>Withdraw requests</h2>
        <div class="row-actions">
            @foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $text)
                <a href="{{ route('admin.affiliate.withdrawals', $value === '' ? [] : ['status' => $value]) }}"
                   class="btn btn-sm {{ request('status', '') === $value ? 'btn-primary' : 'btn-ghost' }}">{{ $text }}</a>
            @endforeach
        </div>
    </div>
    @if ($withdrawals->isEmpty())
        <div class="empty">
            <strong>No requests {{ request('status') ? 'are '.request('status') : 'yet' }}</strong>
            Affiliates ask to cash out their balance here; approving pays them and logs the payout.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th class="right">Amount</th>
                        <th>Status</th>
                        <th>Note</th>
                        @if (request('status', 'pending') !== 'approved' || $withdrawals->contains(fn ($w) => $w->status === 'pending'))
                            <th class="tight"><span class="sr-only">Options</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @foreach ($withdrawals as $withdrawal)
                    <tr>
                        <td class="muted num">{{ $withdrawal->id }}</td>
                        <td class="muted">{{ $withdrawal->created_at->format('j M Y') }}</td>
                        <td>{{ $withdrawal->affiliate?->user?->name ?? 'Deleted user' }}</td>
                        <td class="muted">{{ $withdrawal->affiliate?->user?->email }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($withdrawal->amount, 2) }}</td>
                        <td>
                            <span class="pill {{ ['pending' => 'pill-warn', 'approved' => 'pill-ok', 'rejected' => 'pill-bad'][$withdrawal->status] }}">{{ $withdrawal->status }}</span>
                        </td>
                        <td class="muted">{{ $withdrawal->note }}</td>
                        <td>
                            @if ($withdrawal->status === 'pending')
                                <div class="row-actions">
                                    <form method="POST" action="{{ route('admin.affiliate.withdrawals.decision', $withdrawal) }}"
                                          onsubmit="return confirm(@js('Approve this withdrawal of ৳'.number_format($withdrawal->amount, 2).'? The amount leaves the affiliate balance.'))">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                    </form>
                                    <details>
                                        <summary class="btn btn-link btn-sm link-danger">Reject</summary>
                                        <form method="POST" action="{{ route('admin.affiliate.withdrawals.decision', $withdrawal) }}" style="margin-top:8px">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <input type="text" name="note" class="input" placeholder="Reason (optional)" maxlength="500">
                                            <button type="submit" class="btn btn-ghost btn-sm">Confirm rejection</button>
                                        </form>
                                    </details>
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

@if ($withdrawals->hasPages())
    <div style="margin-top:16px">{{ $withdrawals->links() }}</div>
@endif

@endsection
