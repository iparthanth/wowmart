@extends('admin.layouts.app')
@section('title', $color->name)
@section('crumbs')
    <a href="{{ route('admin.colors.index') }}">Colours</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.colors.update', $color) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head">
        <h2>Edit colour</h2>
        <span class="faint mono">{{ $color->code }}</span>
    </div>
    <div class="card-body">
        @include('admin.colors.fields')
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.colors.index') }}" class="btn btn-link">Back to colours</a>
        </div>
    </div>
</form>

@endsection
