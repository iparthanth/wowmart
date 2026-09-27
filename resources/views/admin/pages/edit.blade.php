@extends('admin.layouts.app')
@section('title', $page->title)
@section('crumbs')
    <a href="{{ route('admin.pages.index') }}">Pages</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.pages.update', $page) }}" class="card" style="max-width:860px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit page</h2></div>
    <div class="card-body">
        @include('admin.pages.fields', ['page' => $page])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-link">Back to pages</a>
        </div>
    </div>
</form>

@endsection
