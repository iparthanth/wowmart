@extends('admin.layouts.app')
@section('title', 'Edit '.$type->name)
@section('crumbs')
    <a href="{{ route('admin.notifications.types') }}">Notification Types</a> / {{ $type->name }}
@endsection

@section('content')

<form method="POST" action="{{ route('admin.notifications.types.update', $type) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h3>Notification type</h3></div>
    <div class="card-body">
        <x-admin.field name="name" label="Type" :value="old('name', $type->name)" required maxlength="60" />
        <div class="field">
            <span class="field-label">Image (36x36)</span>
            <x-admin.media-picker name="image" :images="$images" :selected="old('image', $type->image)" />
        </div>
        <x-admin.field name="default_text" label="Default text" :value="old('default_text', $type->default_text)" required maxlength="80" />
        <label class="check">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" name="status" value="1" @checked(old('status', $type->status ? '1' : '0') === '1')>
            Active
        </label>
        <div class="row-actions" style="margin-top:16px">
            <button type="submit" class="btn btn-primary">Save type</button>
            <a href="{{ route('admin.notifications.types') }}" class="btn btn-ghost">Back</a>
        </div>
    </div>
</form>

@endsection
