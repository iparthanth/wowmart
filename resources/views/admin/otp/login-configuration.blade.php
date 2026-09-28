@extends('admin.layouts.app')
@section('title', 'OTP Login Configuration')
@section('crumbs')
    OTP System / Login Configuration
@endsection

@section('content')

<form method="POST" action="{{ route('admin.otp.login.save') }}" class="card" style="max-width:760px">
    @csrf
    <div class="card-head"><h2>OTP login</h2></div>
    <div class="card-body">
        <p class="muted">Choose who can sign in (or sign up) with a one-time password instead of a password.</p>
        @foreach ($targets as $key => $label)
            <div class="field" style="flex-direction:row;align-items:center;gap:10px;margin-bottom:8px">
                <input type="hidden" name="{{ $key }}" value="0">
                <input id="{{ $key }}" type="checkbox" name="{{ $key }}" value="1" class="switch" @checked($settings[$key] === '1')>
                <label for="{{ $key }}" style="margin:0">{{ $label }}</label>
            </div>
        @endforeach
        <button type="submit" class="btn btn-primary" style="margin-top:12px">Save login settings</button>
    </div>
</form>

@endsection
