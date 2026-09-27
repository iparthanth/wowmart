@extends('admin.layouts.app')
@section('title', $city->name)
@section('crumbs')
    <a href="{{ route('admin.cities.index') }}">Shipping Cities</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.cities.update', $city) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit city</h2></div>
    <div class="card-body">
        @include('admin.cities.fields', ['city' => $city])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.cities.index') }}" class="btn btn-link">Back to cities</a>
        </div>
    </div>
</form>

@endsection
