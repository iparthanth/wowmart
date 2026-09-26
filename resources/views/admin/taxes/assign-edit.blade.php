@extends('admin.layouts.app')
@section('title', 'Edit product tax')
@section('crumbs')
    <a href="{{ route('admin.taxes.index') }}">Vat & Tax</a> / Edit product tax
@endsection

@section('content')

<form method="POST" action="{{ route('admin.taxes.product-taxes.update', $assignment) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit product tax</h2></div>
    <div class="card-body">
        @include('admin.taxes.assign-fields', ['assignment' => $assignment, 'taxes' => $taxes])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.taxes.index') }}" class="btn btn-link">Back to taxes</a>
        </div>
    </div>
</form>

@endsection
