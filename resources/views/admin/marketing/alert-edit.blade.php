@extends('admin.layouts.app')
@section('title', $alert->title)
@section('crumbs')
    <a href="{{ route('admin.marketing.alerts') }}">Custom Alert</a> / Edit
@endsection

@section('content')

<form method="POST" action="{{ route('admin.marketing.alerts.update', $alert) }}" class="card" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="card-head"><h2>Edit alert</h2></div>
    <div class="card-body">
        @include('admin.marketing.alert-fields', ['alert' => $alert])
        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.marketing.alerts') }}" class="btn btn-link">Back to alerts</a>
        </div>
    </div>
</form>

@endsection
