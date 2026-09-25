{{-- The customer form, used by create and edit. --}}
@extends('admin.layouts.app')
@section('title', $customer->exists ? 'Edit '.$customer->name : 'Add customer')
@section('crumbs')
    <a href="{{ route('admin.customers.index') }}">Customers</a> / {{ $customer->exists ? 'Edit' : 'New' }}
@endsection

@section('content')
@php
    // After a failed save show what was typed; otherwise show what is stored.
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<form method="POST" action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}" class="form-layout">
    @csrf
    @if ($customer->exists)
        @method('PUT')
    @endif

    <div class="form-main">
        @if ($errors->any())
            <div class="flash flash-bad" role="alert">
                {{ $errors->count() === 1 ? 'One field needs' : $errors->count().' fields need' }} fixing before this can be saved.
            </div>
        @endif

        <section class="card form-section" aria-labelledby="profile-h">
            <div class="card-head"><h2 id="profile-h">Profile</h2></div>
            <div class="card-body">
                <x-admin.field name="name" label="Full name" :value="$v('name', $customer->name)" required maxlength="120" autocomplete="off" />

                <x-admin.field name="email" label="Email" type="email" :value="$v('email', $customer->email)" maxlength="190" autocomplete="off"
                               :hint="$customer->exists ? null : 'Adding a customer does not verify their email; they confirm it from the storefront.'" />

                <div class="grid-3">
                    <x-admin.field name="country_code" label="Country code" :value="$v('country_code', $customer->country_code ?? '+880')" maxlength="5" class="mono" />
                    <div style="grid-column:span 2">
                        <x-admin.field name="phone" label="Phone" type="tel" :value="$v('phone', $customer->phone)" maxlength="32" autocomplete="off"
                                       hint="Digits only; spaces and dashes are removed. Enter an email, a phone, or both." />
                    </div>
                </div>
            </div>
        </section>

        <section class="card form-section" aria-labelledby="password-h">
            <div class="card-head"><h2 id="password-h">Password</h2></div>
            <div class="card-body">
                <div class="grid-2">
                    <x-admin.field name="password" label="{{ $customer->exists ? 'New password' : 'Password' }}" type="password" autocomplete="new-password"
                                   :required="! $customer->exists" minlength="8"
                                   :hint="$customer->exists ? 'Leave blank to keep the current password.' : 'At least 8 characters. Share it with the customer privately.'" />
                    <x-admin.field name="password_confirmation" label="Type it again" type="password" autocomplete="new-password"
                                   :required="! $customer->exists" minlength="8" />
                </div>
            </div>
        </section>
    </div>

    <aside class="form-aside">
        <div class="card">
            <div class="card-body aside-actions">
                <button type="submit" class="btn btn-primary btn-block">{{ $customer->exists ? 'Save changes' : 'Add customer' }}</button>
                <a href="{{ $customer->exists ? route('admin.customers.show', $customer) : route('admin.customers.index') }}" class="btn btn-ghost btn-block">Cancel</a>
            </div>
            @if ($customer->exists)
                <dl class="facts">
                    <div><dt>Joined</dt><dd>{{ $customer->created_at?->format('j M Y') }}</dd></div>
                    <div>
                        <dt>Account</dt>
                        <dd>
                            @if ($customer->banned)
                                <span class="pill pill-bad">Banned</span>
                            @else
                                <span class="pill pill-ok">Active</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            @endif
        </div>
    </aside>
</form>

@endsection
