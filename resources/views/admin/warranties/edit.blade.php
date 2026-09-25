@extends('admin.layouts.app')
@section('title', $warranty->text)
@section('crumbs')
    <a href="{{ route('admin.warranties.index') }}">Warranties</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.warranties.update', $warranty) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit warranty</h2></div>
    <div class="card-body">
        <x-admin.field name="text" label="Warranty" :value="session()->hasOldInput() ? old('text') : $warranty->text" required maxlength="100" />
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.warranties.index') }}" class="btn btn-link">Back to warranties</a>
        </div>
    </div>
</form>

@endsection
