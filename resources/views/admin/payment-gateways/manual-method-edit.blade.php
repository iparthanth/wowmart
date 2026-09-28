@extends('admin.layouts.app')
@section('title', 'Edit '.$method->name)
@section('crumbs')
    <a href="{{ route('admin.manual-methods.index') }}">Manual Payment Methods</a> / {{ $method->name }}
@endsection

@section('content')

<form method="POST" action="{{ route('admin.manual-methods.update', $method) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h3>Manual payment method</h3></div>
    <div class="card-body">
        <x-admin.field name="name" label="Heading" :value="old('name', $method->name)" required maxlength="80" />
        <div class="field">
            <span class="field-label">Logo</span>
            <x-admin.media-picker name="logo" :images="$images" :selected="old('logo', $method->logo)" />
        </div>
        <div class="field">
            <label for="m-instructions">Payment instructions</label>
            <textarea id="m-instructions" name="instructions" rows="5" class="input" maxlength="5000">{{ old('instructions', $method->instructions) }}</textarea>
        </div>
        <label class="check">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" name="status" value="1" @checked(old('status', $method->status ? '1' : '0') === '1')>
            Active
        </label>
        <div class="row-actions" style="margin-top:16px">
            <button type="submit" class="btn btn-primary">Save method</button>
            <a href="{{ route('admin.manual-methods.index') }}" class="btn btn-ghost">Back</a>
        </div>
    </div>
</form>

@endsection
