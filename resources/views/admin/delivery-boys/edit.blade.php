@extends('admin.layouts.app')
@section('title', $boy->name)
@section('crumbs')
    <a href="{{ route('admin.delivery-boys.index') }}">Delivery Boys</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.delivery-boys.update', $boy) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit delivery boy</h2></div>
    <div class="card-body">
        @include('admin.delivery-boys.fields', ['boy' => $boy])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.delivery-boys.index') }}" class="btn btn-link">Back to delivery boys</a>
        </div>
    </div>
</form>

@endsection
