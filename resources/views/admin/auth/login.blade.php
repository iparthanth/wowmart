<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · WowMart Admin</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600&family=Sora:wght@500;600;700&display=swap">
    {{-- Only the Taka sign: none of the fonts above contain it, so without this every OS draws it differently. --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@500;600&text=%E0%A7%B3&display=swap">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<div class="auth">
    <section class="auth-brand">
        <img src="{{ asset('images/brand/wowmart-logo-light.svg') }}" alt="WowMart">
        <p class="line">Products, orders and sellers, <em>all in one place.</em></p>
        <small>WowMart Admin · Authorised staff only</small>
    </section>

    <section class="auth-form">
        <form method="POST" action="{{ url('/login') }}" novalidate>
            @csrf
            <h1>Sign in</h1>
            <p class="lede">Use your WowMart admin email and password.</p>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" class="input" value="{{ old('email') }}"
                       autocomplete="username" autofocus required
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')<span class="error" id="email-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" class="input"
                       autocomplete="current-password" required
                       @error('password') aria-invalid="true" @enderror>
                @error('password')<span class="error">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign in</button>

            <p class="foot">Customers sign in on the WowMart website, not here.</p>
        </form>
    </section>
</div>
</body>
</html>
