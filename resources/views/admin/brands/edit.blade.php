@extends('admin.layouts.app')
@section('title', $brand->name)
@section('crumbs')
    <a href="{{ route('admin.brands.index') }}">Brands</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.brands.update', $brand) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head">
        <h2>Edit brand</h2>
        <span class="faint mono">/{{ $brand->slug }}</span>
    </div>
    <div class="card-body">
        @include('admin.brands.fields')
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.brands.index') }}" class="btn btn-link">Back to brands</a>
        </div>
    </div>
</form>

@endsection
