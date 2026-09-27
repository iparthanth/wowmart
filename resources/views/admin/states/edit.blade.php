@extends('admin.layouts.app')
@section('title', $state->name)
@section('crumbs')
    <a href="{{ route('admin.states.index') }}">Shipping States</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.states.update', $state) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit state</h2></div>
    <div class="card-body">
        @include('admin.states.fields', ['state' => $state])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.states.index') }}" class="btn btn-link">Back to states</a>
        </div>
    </div>
</form>

@endsection
