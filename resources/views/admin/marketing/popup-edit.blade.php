@extends('admin.layouts.app')
@section('title', $popup->title)
@section('crumbs')
    <a href="{{ route('admin.marketing.popups') }}">Dynamic Pop-up</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.marketing.popups.update', $popup) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit pop-up</h2></div>
    <div class="card-body">
        @include('admin.marketing.popup-fields', ['popup' => $popup])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.marketing.popups') }}" class="btn btn-link">Back to pop-ups</a>
        </div>
    </div>
</form>

@endsection
