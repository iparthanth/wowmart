@extends('admin.layouts.app')
@section('title', $member->name)
@section('crumbs')
    <a href="{{ route('admin.staff.index') }}">Staff</a>
    <span class="crumb-sep">/</span>
    {{ $member->name }}
@endsection

@section('content')
<div class="split">
    <form method="POST" action="{{ route('admin.staff.update', $member) }}" class="card">
        @csrf
        @method('PUT')
        <div class="card-head">
            <h2>{{ $member->name }}</h2>
            <span class="pill {{ $member->isAdmin() ? 'pill-warn' : '' }}">{{ $member->user_type->label() }}</span>
        </div>
        <div class="card-body">
            @include('admin.staff.fields', ['member' => $member, 'idPrefix' => 'edit'])
            <div class="form-foot">
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-head"><h2>Account facts</h2></div>
        <div class="card-body">
            <div class="facts">
                <div class="fact">
                    <span class="fact-label">Added</span>
                    <span class="fact-value">{{ $member->created_at->format('d M Y') }}</span>
                </div>
                <div class="fact">
                    <span class="fact-label">Role</span>
                    <span class="fact-value">{{ $member->role?->name ?? ($member->isAdmin() ? 'Full access' : 'None') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
