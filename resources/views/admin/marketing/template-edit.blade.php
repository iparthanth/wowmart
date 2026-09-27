@extends('admin.layouts.app')
@section('title', $template->identifier)
@section('crumbs')
    <a href="{{ route('admin.marketing.templates') }}">Email Templates</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.marketing.templates.update', $template) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit template</h2></div>
    <div class="card-body">
        @include('admin.marketing.template-fields', ['template' => $template, 'receivers' => $receivers])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.marketing.templates') }}" class="btn btn-link">Back to templates</a>
        </div>
    </div>
</form>

@endsection
