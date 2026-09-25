@extends('admin.layouts.app')
@section('title', 'Staff')
@section('crumbs')
    Staff
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($members->isEmpty())
                <div class="empty">
                    <strong>No members yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($members as $member)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.staff.edit', $member) }}" class="product-name">{{ $member->name }}</a>
                                    @if ($member->isAdmin())
                                        <span class="pill pill-warn">Admin</span>
                                    @endif
                                </td>
                                <td class="mono">{{ $member->email }}</td>
                                <td>{{ $member->role?->name ?? ($member->isAdmin() ? 'Full access' : '-') }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.staff.edit', $member) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.staff.destroy', $member) }}"
                                              onsubmit="return confirm(@js('Remove '.$member->name.' from the panel? This cannot be undone.'))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link btn-sm link-danger">Remove</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($members->hasPages())
            <div style="margin-top:16px">{{ $members->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.staff.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a member</h2></div>
        <div class="card-body">
            @include('admin.staff.fields', ['member' => $newMember, 'roles' => $roles, 'idPrefix' => 'new'])
            <button type="submit" class="btn btn-primary btn-block">Add member</button>
        </div>
    </form>
</div>

@endsection
