@extends('admin.layouts.app')
@section('title', $point->name)
@section('crumbs')
    <a href="{{ route('admin.pickup-points.index') }}">Pickup Points</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.pickup-points.update', $point) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit pickup point</h2></div>
    <div class="card-body">
        @include('admin.pickup-points.fields', ['point' => $point])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.pickup-points.index') }}" class="btn btn-link">Back to pickup points</a>
        </div>
    </div>
</form>

@endsection
