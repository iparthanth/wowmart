@extends('admin.layouts.app')
@section('title', 'Add new product')
@section('crumbs')
    <a href="{{ route('admin.products.index') }}">Products</a> / New
@endsection

@section('content')
    @include('admin.products.form', ['action' => route('admin.products.store')])
@endsection
