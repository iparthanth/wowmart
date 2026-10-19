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
    /*
     * The reference panel's sidebar, section for section (see
     * docs/reference/admin_menu.json). A node with a 'route' links once that
     * route exists; the rest render as muted "not built yet" placeholders, so
     * the structure matches the reference without ever linking to a missing
     * screen. Rendering is recursive: resources/views/components/admin/nav-node.
     */
    $menu = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'patterns' => ['admin.dashboard']],

        ['label' => 'Products', 'children' => [
            ['label' => 'Add New Product', 'route' => 'admin.products.create', 'patterns' => ['admin.products.create']],
            ['label' => 'All Products', 'route' => 'admin.products.index', 'patterns' => ['admin.products.index', 'admin.products.edit']],
            ['label' => 'In House Products', 'route' => 'admin.products.index', 'params' => ['added_by' => 'admin']],
            ['label' => 'Categories', 'route' => 'admin.categories.index', 'patterns' => ['admin.categories.*']],
            ['label' => 'Brands', 'route' => 'admin.brands.index', 'patterns' => ['admin.brands.*']],
            ['label' => 'Attributes', 'route' => 'admin.attributes.index', 'patterns' => ['admin.attributes.*']],
            ['label' => 'Colors', 'route' => 'admin.colors.index', 'patterns' => ['admin.colors.*']],
            ['label' => 'Warranties', 'route' => 'admin.warranties.index', 'patterns' => ['admin.warranties.*']],
            ['label' => 'Product Reviews', 'route' => 'admin.reviews.index', 'patterns' => ['admin.reviews.*']],
            ['label' => 'Notes', 'children' => [
                ['label' => 'Add New Note', 'route' => 'admin.notes.create', 'patterns' => ['admin.notes.create']],
                ['label' => 'Note List', 'route' => 'admin.notes.index', 'patterns' => ['admin.notes.*']],
            ]],
        ]],

        ['label' => 'Sales', 'children' => [
            ['label' => 'All Orders', 'route' => 'admin.orders.index', 'patterns' => ['admin.orders.*'], 'count' => $pendingOrders ?? 0],
            ['label' => 'Inhouse Orders', 'route' => 'admin.orders.inhouse', 'patterns' => ['admin.orders.*']],
            ['label' => 'Seller Orders', 'route' => 'admin.orders.seller', 'patterns' => ['admin.orders.*']],
            ['label' => 'Pickup Point Orders', 'route' => 'admin.orders.pickup', 'patterns' => ['admin.orders.*']],
            ['label' => 'Unpaid Orders', 'route' => 'admin.orders.unpaid', 'patterns' => ['admin.orders.*']],
        ]],

        ['label' => 'Media', 'children' => [
            ['label' => 'Uploaded Files', 'route' => 'admin.uploads.index', 'patterns' => ['admin.uploads.*']],
        ]],

        ['label' => 'Reports', 'children' => [
            ['label' => 'Earning Report', 'route' => 'admin.reports.earning', 'patterns' => ['admin.reports.earning']],
            ['label' => 'In House Product Sale', 'route' => 'admin.reports.in-house-sales', 'patterns' => ['admin.reports.in-house-sales']],
            ['label' => 'Seller Products Sale', 'route' => 'admin.reports.seller-sales', 'patterns' => ['admin.reports.seller-sales']],
            ['label' => 'Products Stock', 'route' => 'admin.reports.stock', 'patterns' => ['admin.reports.stock']],
            ['label' => 'Products Wishlist', 'route' => 'admin.reports.wishlist', 'patterns' => ['admin.reports.wishlist']],
            ['label' => 'User Searches', 'route' => 'admin.reports.user-searches', 'patterns' => ['admin.reports.user-searches']],
            ['label' => 'Commission History', 'route' => 'admin.reports.commission-history', 'patterns' => ['admin.reports.commission-history']],
            ['label' => 'Wallet Recharge History', 'route' => 'admin.reports.wallet-recharges', 'patterns' => ['admin.reports.wallet-recharges']],
        ]],

        ['label' => 'Support', 'children' => [
            ['label' => 'Tickets', 'route' => 'admin.support.tickets', 'patterns' => ['admin.support.tickets*']],
            ['label' => 'Product Conversations', 'route' => 'admin.support.conversations', 'patterns' => ['admin.support.conversations*']],
            ['label' => 'Product Queries', 'route' => 'admin.support.queries', 'patterns' => ['admin.support.queries*']],
            ['label' => 'Contacts', 'route' => 'admin.support.contacts', 'patterns' => ['admin.support.contacts*']],
        ]],

        ['label' => 'Staffs', 'children' => [
            ['label' => 'All Staffs', 'route' => 'admin.staff.index', 'patterns' => ['admin.staff.*']],
            ['label' => 'Staff Permissions', 'route' => 'admin.roles.index', 'patterns' => ['admin.roles.*']],
        ]],
    ];

    // Staff only see the sections their role opens; admins get the whole menu.
    $menu = app(\App\Services\PermissionService::class)->filterMenu($menu, auth()->user());
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
            @foreach ($menu as $node)
                <x-admin.nav-node :node="$node" />
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
                <span class="who-text">Signed in as <strong>{{ auth()->user()->name }}</strong></span>
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

    {{-- Tapping the dark area closes the mobile menu: this label toggles #nav-open. --}}
    <label for="nav-open" class="menu-scrim" aria-hidden="true"></label>
</div>
</body>
</html>
