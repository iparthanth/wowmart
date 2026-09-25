@extends('admin.layouts.app')
@section('title', 'Add new category')
@section('crumbs')
    <a href="{{ route('admin.categories.index') }}">Categories</a> / New
@endsection

@section('content')
    @include('admin.categories.form', ['action' => route('admin.categories.store')])
@endsection
