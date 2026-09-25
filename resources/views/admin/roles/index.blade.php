@extends('admin.layouts.app')
@section('title', 'Staff permissions')
@section('crumbs')
    <a href="{{ route('admin.staff.index') }}">Staff</a>
    <span class="crumb-sep">/</span>
    Permissions
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($roles->isEmpty())
                <div class="empty">
                    <strong>No roles yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Opens</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="product-name">{{ $role->name }}</a>
                                    <div class="product-meta">{{ $role->users_count }} {{ Str::plural('member', $role->users_count) }}</div>
                                </td>
                                <td>
                                    @if ($role->permissions->isEmpty())
                                        <span class="pill">Locked - nothing</span>
                                    @else
                                        <span class="pill">{{ $role->permissions->count() }} {{ Str::plural('section', $role->permissions->count()) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                              onsubmit="return confirm(@js('Delete the role "'.$role->name.'"?'))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link btn-sm link-danger">Delete</button>
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

        @if ($roles->hasPages())
            <div style="margin-top:16px">{{ $roles->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.roles.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a role</h2></div>
        <div class="card-body">
            @include('admin.roles.fields', ['role' => $newRole, 'idPrefix' => 'new'])
            <button type="submit" class="btn btn-primary btn-block">Create role</button>
        </div>
    </form>
</div>

@endsection
