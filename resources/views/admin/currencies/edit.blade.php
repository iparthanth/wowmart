@extends('admin.layouts.app')
@section('title', $currency->name)
@section('crumbs')
    <a href="{{ route('admin.currencies.index') }}">Currency</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.currencies.update', $currency) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head">
        <h2>Edit currency</h2>
        <span class="faint mono">{{ $currency->code }}</span>
    </div>
    <div class="card-body">
        @include('admin.currencies.fields')
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.currencies.index') }}" class="btn btn-link">Back to currencies</a>
        </div>
    </div>
</form>

@endsection
