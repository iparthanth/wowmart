<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') · WowMart Admin</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600&family=JetBrains+Mono:wght@400;500&family=Sora:wght@500;600;700&display=swap">
    {{-- Only the Taka sign: none of the fonts above contain it, so without this every OS draws it differently. --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@500;600&text=%E0%A7%B3&display=swap">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
@php
    // A module appears in the menu only once its routes exist, so the menu
    // never links to a screen that has not been built.
    $menu = [
        [null, [['Dashboard', 'admin.dashboard', ['admin.dashboard']]]],
        ['Products', [
            ['All products', 'admin.products.index', ['admin.products.index', 'admin.products.edit']],
            ['Add new product', 'admin.products.create', ['admin.products.create']],
            ['Categories', 'admin.categories.index', ['admin.categories.*']],
            ['Brands', 'admin.brands.index', ['admin.brands.*']],
        ]],
        ['Sales', [['All orders', 'admin.orders.index', ['admin.orders.*'], $pendingOrders ?? 0]]],
        ['Media', [['Uploaded files', 'admin.uploads.index', ['admin.uploads.*']]]],
        ['Marketing', [
            ['Flash deals', 'admin.flash-deals.index', ['admin.flash-deals.*']],
            ['Coupons', 'admin.coupons.index', ['admin.coupons.*']],
        ]],
    ];
@endphp

<input type="checkbox" id="nav-open" class="sr-only" aria-label="Open menu">

<div class="shell">
    <aside class="side">
        <div class="side-brand">
            <a href="{{ route('admin.dashboard') }}" aria-label="WowMart Admin home">
                <img src="{{ asset('images/brand/wowmart-logo-light.svg') }}" alt="WowMart">
            </a>
        </div>

        <nav class="nav" aria-label="Admin">
            @foreach ($menu as [$group, $items])
                @php $visible = array_filter($items, fn ($i) => Route::has($i[1])); @endphp
                @continue(empty($visible))
                <div class="nav-group">
                    @if ($group)<div class="nav-label">{{ $group }}</div>@endif
                    @foreach ($visible as $item)
                        @php [$label, $name, $patterns] = $item; $count = $item[3] ?? 0; @endphp
                        <a href="{{ route($name) }}" @if (request()->routeIs(...$patterns)) aria-current="page" @endif>
                            <span>{{ $label }}</span>
                            @if ($count > 0)
                                <span class="nav-count" title="{{ $count }} need attention">
                                    <x-admin.spark />{{ $count }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
    </aside>

    <div class="main">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:12px;min-width:0">
                <label for="nav-open" class="btn btn-ghost btn-sm menu-toggle">Menu</label>
                <div style="min-width:0">
                    @hasSection('crumbs')<div class="crumbs">@yield('crumbs')</div>@endif
                    <h1>@yield('title', 'Dashboard')</h1>
                </div>
            </div>
            <div class="who">
                <span>Signed in as <strong>{{ auth()->user()->name }}</strong></span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm">Sign out</button>
                </form>
            </div>
        </header>

        <main class="content">
            @if (session('status'))
                <div class="flash flash-ok" role="status">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="flash flash-bad" role="alert">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
