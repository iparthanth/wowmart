@extends('admin.layouts.app')
@section('title', $role->name)
@section('crumbs')
    <a href="{{ route('admin.roles.index') }}">Permissions</a>
    <span class="crumb-sep">/</span>
    {{ $role->name }}
@endsection

@section('content')

<form method="POST" action="{{ route('admin.roles.update', $role) }}" class="card" style="max-width:720px">
    @csrf
    @method('PUT')
    <div class="card-head">
        <h2>{{ $role->name }}</h2>
        <span class="pill">{{ $role->users_count ?? $role->users()->count() }} {{ Str::plural('member', $role->users()->count()) }}</span>
    </div>
    <div class="card-body">
        @include('admin.roles.fields', ['role' => $role, 'idPrefix' => 'edit'])
        <button type="submit" class="btn btn-primary">Save changes</button>
    </div>
</form>

@endsection
