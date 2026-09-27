@extends('admin.layouts.app')
@section('title', $category->name)
@section('crumbs')
    <a href="{{ route('admin.blog-categories.index') }}">Blog Categories</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.blog-categories.update', $category) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit category</h2></div>
    <div class="card-body">
        @include('admin.blog.category-fields', ['category' => $category])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-link">Back to categories</a>
        </div>
    </div>
</form>

@endsection
