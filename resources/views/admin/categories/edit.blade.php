@extends('admin.layouts.app')
@section('title', $category->name)
@section('crumbs')
    <a href="{{ route('admin.categories.index') }}">Categories</a> / Edit
@endsection

@section('content')
    @include('admin.categories.form', ['action' => route('admin.categories.update', $category)])
@endsection
