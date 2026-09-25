@extends('admin.layouts.app')
@section('title', $product->name)
@section('crumbs')
    <a href="{{ route('admin.products.index') }}">Products</a> / Edit
@endsection

@section('content')
    @include('admin.products.form', ['action' => route('admin.products.update', $product)])
@endsection
