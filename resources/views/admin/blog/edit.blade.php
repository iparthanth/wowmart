@extends('admin.layouts.app')
@section('title', $post->title)
@section('crumbs')
    <a href="{{ route('admin.blog.index') }}">Blog Posts</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.blog.update', $post) }}" class="card" style="max-width:860px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit post</h2></div>
    <div class="card-body">
        @include('admin.blog.fields', ['post' => $post])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.blog.index') }}" class="btn btn-link">Back to posts</a>
        </div>
    </div>
</form>

@endsection
