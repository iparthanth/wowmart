@extends('admin.layouts.app')
@section('title', $language->name)
@section('crumbs')
    <a href="{{ route('admin.languages.index') }}">Languages</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.languages.update', $language) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit language</h2></div>
    <div class="card-body">
        @include('admin.languages.fields', ['language' => $language])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.languages.index') }}" class="btn btn-link">Back to languages</a>
        </div>
    </div>
</form>

@endsection
