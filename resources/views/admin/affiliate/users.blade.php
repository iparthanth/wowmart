@extends('admin.layouts.app')
@section('title', 'Affiliate Users')
@section('crumbs')
    Affiliate System / Users
@endsection

@section('content')

<div class="card">
    <div class="card-head" style="flex-wrap:wrap;gap:8px">
        <h2>Affiliate users</h2>
        <div class="row-actions">
            @foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $text)
                <a href="{{ route('admin.affiliate.users', $value === '' ? [] : ['status' => $value]) }}"
                   class="btn btn-sm {{ request('status', '') === $value ? 'btn-primary' : 'btn-ghost' }}">{{ $text }}</a>
            @endforeach
        </div>
    </div>
    @if ($affiliates->isEmpty())
        <div class="empty">
            <strong>No affiliates {{ request('status') ? 'are '.request('status') : 'yet' }}</strong>
            Customers who join the programme from the storefront appear here for approval.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Referral code</th>
                        <th class="right">Referred users</th>
                        <th class="right">Due amount</th>
                        <th>Approval</th>
                        <th class="tight"><span class="sr-only">Options</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($affiliates as $affiliate)
                    <tr>
                        <td>{{ $affiliate->user?->name ?? 'Deleted user' }}</td>
                        <td class="muted">{{ $affiliate->user?->email }}</td>
                        <td><span class="pill pill-mute mono">{{ $affiliate->code }}</span></td>
                        <td class="right num">{{ $affiliate->referrals_count }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($affiliate->balance, 2) }}</td>
                        <td>
                            <span class="pill {{ ['pending' => 'pill-warn', 'approved' => 'pill-ok', 'rejected' => 'pill-bad'][$affiliate->status] }}">{{ $affiliate->status }}</span>
                        </td>
                        <td>
                            @if ($affiliate->status !== 'approved')
                                <form method="POST" action="{{ route('admin.affiliate.users.decision', $affiliate) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                </form>
                            @endif
                            @if ($affiliate->status !== 'rejected')
                                <form method="POST" action="{{ route('admin.affiliate.users.decision', $affiliate) }}" style="display:inline"
                                      onsubmit="return confirm(@js('Reject this affiliate? They keep their balance until a withdrawal is decided.'))">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-link btn-sm link-danger">Reject</button>
                                </form>
                            @endif
                            @if ($affiliate->status === 'pending')
                                <form method="POST" action="{{ route('admin.affiliate.users.decision', $affiliate) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="status" value="pending">
                                    <button type="submit" class="btn btn-ghost btn-sm">Reset to pending</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if ($affiliates->hasPages())
    <div style="margin-top:16px">{{ $affiliates->links() }}</div>
@endif

@endsection
