@extends('admin.layouts.app')
@section('title', 'Referral Users')
@section('crumbs')
    Affiliate System / Referral Users
@endsection

@section('content')

<div class="card">
    <div class="card-head"><h2>Referral users</h2></div>
    @if ($referrals->isEmpty())
        <div class="empty">
            <strong>No referrals yet</strong>
            Customers who sign up through an affiliate's link are listed here with who brought them in.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined</th>
                        <th>Referred by</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($referrals as $referral)
                    <tr>
                        <td class="muted num">{{ $referral->id }}</td>
                        <td>{{ $referral->referredUser?->name ?? 'Deleted user' }}</td>
                        <td class="muted">{{ $referral->referredUser?->email }}</td>
                        <td class="muted">{{ $referral->created_at->format('j M Y') }}</td>
                        <td>
                            {{ $referral->affiliate?->user?->name ?? 'Deleted user' }}
                            <span class="pill pill-mute mono">{{ $referral->affiliate?->code }}</span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if ($referrals->hasPages())
    <div style="margin-top:16px">{{ $referrals->links() }}</div>
@endif

@endsection
