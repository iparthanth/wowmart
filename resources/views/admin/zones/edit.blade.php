@extends('admin.layouts.app')
@section('title', $zone->name)
@section('crumbs')
    <a href="{{ route('admin.zones.index') }}">Shipping Zones</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.zones.update', $zone) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit zone</h2></div>
    <div class="card-body">
        @include('admin.zones.fields', ['zone' => $zone])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.zones.index') }}" class="btn btn-link">Back to zones</a>
        </div>
    </div>
</form>

@endsection
