@extends('admin.layouts.app')
@section('title', $country->name)
@section('crumbs')
    <a href="{{ route('admin.countries.index') }}">Shipping Countries</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.countries.update', $country) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit country</h2></div>
    <div class="card-body">
        @include('admin.countries.fields', ['country' => $country])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.countries.index') }}" class="btn btn-link">Back to countries</a>
        </div>
    </div>
</form>

@endsection
