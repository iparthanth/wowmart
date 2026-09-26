@extends('admin.layouts.app')
@section('title', $tax->name)
@section('crumbs')
    <a href="{{ route('admin.taxes.index') }}">Vat & Tax</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.taxes.update', $tax) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head">
        <h2>Edit tax</h2>
        <span class="faint">{{ $tax->productTaxes()->count() }} product(s) charged</span>
    </div>
    <div class="card-body">
        @include('admin.taxes.fields')
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.taxes.index') }}" class="btn btn-link">Back to taxes</a>
        </div>
    </div>
</form>

@endsection
