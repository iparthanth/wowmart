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

        ['label' => 'POS System', 'children' => [
            ['label' => 'POS Manager'],
            ['label' => 'POS Configuration'],
        ]],

        ['label' => 'Products', 'children' => [
            ['label' => 'Add New Product', 'route' => 'admin.products.create', 'patterns' => ['admin.products.create']],
            ['label' => 'All Products', 'route' => 'admin.products.index', 'patterns' => ['admin.products.index', 'admin.products.edit']],
            ['label' => 'In House Products', 'route' => 'admin.products.index', 'params' => ['added_by' => 'admin']],
            ['label' => 'Seller Products', 'children' => [
                ['label' => 'Physical Products'],
                ['label' => 'Digital Products'],
            ]],
            ['label' => 'Bulk Import'],
            ['label' => 'Bulk Export'],
            ['label' => 'Categories', 'route' => 'admin.categories.index', 'patterns' => ['admin.categories.*']],
            ['label' => 'Category Based Discount'],
            ['label' => 'Brands', 'children' => [
                ['label' => 'All Brands', 'route' => 'admin.brands.index', 'patterns' => ['admin.brands.*']],
                ['label' => 'Brand Bulk Import'],
            ]],
            ['label' => 'Attributes', 'route' => 'admin.attributes.index', 'patterns' => ['admin.attributes.*']],
            ['label' => 'Colors', 'route' => 'admin.colors.index', 'patterns' => ['admin.colors.*']],
            ['label' => 'Size Guide', 'children' => [
                ['label' => 'Size Charts'],
                ['label' => 'Measurement Points'],
            ]],
            ['label' => 'Warranties', 'route' => 'admin.warranties.index', 'patterns' => ['admin.warranties.*']],
            ['label' => 'Product Reviews', 'route' => 'admin.reviews.index', 'patterns' => ['admin.reviews.*']],
            ['label' => 'Notes', 'children' => [
                ['label' => 'Add New Note'],
                ['label' => 'Note List', 'route' => 'admin.notes.index', 'patterns' => ['admin.notes.*']],
            ]],
            ['label' => 'Wholesale Products', 'children' => [
                ['label' => 'Add New Wholesale Product'],
                ['label' => 'All Wholesale Products'],
                ['label' => 'In House Wholesale Products'],
                ['label' => 'Seller Wholesale Products'],
            ]],
        ]],

        ['label' => 'Sales', 'children' => [
            ['label' => 'All Orders', 'route' => 'admin.orders.index', 'patterns' => ['admin.orders.*'], 'count' => $pendingOrders ?? 0],
            ['label' => 'Inhouse Orders', 'route' => 'admin.orders.inhouse', 'patterns' => ['admin.orders.*']],
            ['label' => 'Seller Orders', 'route' => 'admin.orders.seller', 'patterns' => ['admin.orders.*']],
            ['label' => 'Pickup Point Orders', 'route' => 'admin.orders.pickup', 'patterns' => ['admin.orders.*']],
            ['label' => 'Unpaid Orders', 'route' => 'admin.orders.unpaid', 'patterns' => ['admin.orders.*']],
            ['label' => 'Delivery Boy', 'children' => [
                ['label' => 'All Delivery Boys', 'route' => 'admin.delivery-boys.index', 'patterns' => ['admin.delivery-boys*']],
                ['label' => 'Payment Histories', 'route' => 'admin.delivery-boys.payments', 'patterns' => ['admin.delivery-boys.payments*']],
                ['label' => 'Collected Histories', 'route' => 'admin.delivery-boys.collections', 'patterns' => ['admin.delivery-boys.collections*']],
                ['label' => 'Cancel Requests', 'route' => 'admin.delivery-boys.cancel-requests', 'patterns' => ['admin.delivery-boys.cancel-requests*']],
                ['label' => 'Configuration', 'route' => 'admin.settings.show', 'params' => ['screen' => 'delivery-boy'], 'patterns' => ['admin.settings.*']],
            ]],
            ['label' => 'Refunds', 'children' => [
                ['label' => 'Refund Requests', 'route' => 'admin.refunds.index', 'patterns' => ['admin.refunds.*'], 'count' => $pendingRefunds ?? 0],
                ['label' => 'Approved Refunds', 'route' => 'admin.refunds.index', 'params' => ['status' => 'approved'], 'patterns' => ['admin.refunds.*']],
                ['label' => 'Rejected Refunds', 'route' => 'admin.refunds.index', 'params' => ['status' => 'rejected'], 'patterns' => ['admin.refunds.*']],
                ['label' => 'Refund Configuration', 'route' => 'admin.refunds.settings', 'patterns' => ['admin.refunds.settings*']],
            ]],
        ]],

        ['label' => 'Customers', 'children' => [
            ['label' => 'Customer List', 'route' => 'admin.customers.index', 'patterns' => ['admin.customers.*']],
            ['label' => 'Classified Products'],
            ['label' => 'Classified Packages'],
        ]],

        ['label' => 'Sellers', 'children' => [
            ['label' => 'All Sellers', 'route' => 'admin.sellers.index', 'patterns' => ['admin.sellers.*'], 'count' => $pendingSellers ?? 0],
            ['label' => 'Seller Rating & Followers'],
            ['label' => 'Payouts'],
            ['label' => 'Payout Requests'],
            ['label' => 'Seller Commission'],
            ['label' => 'Seller Based Commission'],
            ['label' => 'Category Based Commission'],
            ['label' => 'Seller Packages'],
            ['label' => 'Seller Verification Form'],
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

        ['label' => 'Blog System', 'children' => [
            ['label' => 'All Posts', 'route' => 'admin.blog.index', 'patterns' => ['admin.blog.index', 'admin.blog.edit', 'admin.blog.update', 'admin.blog.destroy', 'admin.blog.store']],
            ['label' => 'Categories', 'route' => 'admin.blog-categories.index', 'patterns' => ['admin.blog-categories.*']],
        ]],

        ['label' => 'Marketing', 'children' => [
            ['label' => 'Flash Deals', 'route' => 'admin.flash-deals.index', 'patterns' => ['admin.flash-deals.*']],
            ['label' => 'Dynamic Pop-up', 'route' => 'admin.marketing.popups', 'patterns' => ['admin.marketing.popups*']],
            ['label' => 'Custom Alert', 'route' => 'admin.marketing.alerts', 'patterns' => ['admin.marketing.alerts*']],
            ['label' => 'Email Templates', 'children' => [
                ['label' => 'Admin Templates', 'route' => 'admin.marketing.templates', 'params' => ['receiver' => 'admin'], 'patterns' => ['admin.marketing.templates*']],
                ['label' => 'Seller Templates', 'route' => 'admin.marketing.templates', 'params' => ['receiver' => 'seller'], 'patterns' => ['admin.marketing.templates*']],
                ['label' => 'Customer Templates', 'route' => 'admin.marketing.templates', 'params' => ['receiver' => 'customer'], 'patterns' => ['admin.marketing.templates*']],
                ['label' => 'Common Templates', 'route' => 'admin.marketing.templates', 'params' => ['receiver' => 'common'], 'patterns' => ['admin.marketing.templates*']],
            ]],
            ['label' => 'Newsletters', 'route' => 'admin.marketing.newsletters', 'patterns' => ['admin.marketing.newsletters*']],
            ['label' => 'Notification', 'children' => [
                ['label' => 'Settings'],
                ['label' => 'Notification Types'],
                ['label' => 'Custom Notification'],
                ['label' => 'Custom Notification History'],
            ]],
            ['label' => 'Bulk SMS'],
            ['label' => 'Subscribers', 'route' => 'admin.marketing.subscribers', 'patterns' => ['admin.marketing.subscribers*']],
            ['label' => 'Coupons', 'route' => 'admin.coupons.index', 'patterns' => ['admin.coupons.*']],
        ]],

        ['label' => 'Support', 'children' => [
            ['label' => 'Tickets'],
            ['label' => 'Product Conversations'],
            ['label' => 'Product Queries'],
            ['label' => 'Contacts'],
        ]],

        ['label' => 'Affiliate System', 'children' => [
            ['label' => 'Affiliate Registration Form'],
            ['label' => 'Affiliate Configurations'],
            ['label' => 'Affiliate Users'],
            ['label' => 'Referral Users'],
            ['label' => 'Affiliate Withdraw Requests'],
            ['label' => 'Affiliate Logs'],
        ]],

        ['label' => 'OTP System', 'children' => [
            ['label' => 'OTP Login Configuration'],
            ['label' => 'OTP Configurations'],
            ['label' => 'SMS Templates'],
        ]],

        ['label' => 'Payment Gateways', 'children' => [
            ['label' => 'Payment Methods'],
            ['label' => 'Offline Payment System', 'children' => [
                ['label' => 'Manual Payment Methods'],
                ['label' => 'Offline Payment Orders'],
                ['label' => 'Offline Wallet Recharge'],
                ['label' => 'Offline Customer Package Payments'],
                ['label' => 'Offline Seller Package Payments'],
            ]],
        ]],

        ['label' => 'Website Setup', 'children' => [
            ['label' => 'Select Homepage', 'route' => 'admin.settings.show', 'params' => ['screen' => 'homepage-select'], 'patterns' => ['admin.settings.*']],
            ['label' => 'Homepage Settings', 'route' => 'admin.settings.show', 'params' => ['screen' => 'homepage'], 'patterns' => ['admin.settings.*']],
            ['label' => 'Authentication Layout & Settings', 'route' => 'admin.settings.show', 'params' => ['screen' => 'authentication'], 'patterns' => ['admin.settings.*']],
            ['label' => 'Header', 'route' => 'admin.settings.show', 'params' => ['screen' => 'header'], 'patterns' => ['admin.settings.*']],
            ['label' => 'Footer', 'route' => 'admin.settings.show', 'params' => ['screen' => 'footer'], 'patterns' => ['admin.settings.*']],
            ['label' => 'Pages', 'route' => 'admin.pages.index', 'patterns' => ['admin.pages.*']],
            ['label' => 'Appearance', 'route' => 'admin.settings.show', 'params' => ['screen' => 'appearance'], 'patterns' => ['admin.settings.*']],
        ]],

        ['label' => 'Setup & Configurations', 'children' => [
            ['label' => 'Features Activation', 'route' => 'admin.settings.show', 'params' => ['screen' => 'features'], 'patterns' => ['admin.settings.*']],
            ['label' => 'Languages', 'route' => 'admin.languages.index', 'patterns' => ['admin.languages.*']],
            ['label' => 'Currency', 'route' => 'admin.currencies.index', 'patterns' => ['admin.currencies.*']],
            ['label' => 'Vat & Tax', 'route' => 'admin.taxes.index', 'patterns' => ['admin.taxes.*']],
            ['label' => 'Pickup Points', 'route' => 'admin.pickup-points.index', 'patterns' => ['admin.pickup-points.*']],
            ['label' => 'SMTP Settings', 'route' => 'admin.settings.show', 'params' => ['screen' => 'smtp'], 'patterns' => ['admin.settings.*']],
            ['label' => 'Order Configuration', 'route' => 'admin.settings.show', 'params' => ['screen' => 'orders'], 'patterns' => ['admin.settings.*']],
            ['label' => 'File System & Cache Configuration', 'route' => 'admin.settings.show', 'params' => ['screen' => 'file-system'], 'patterns' => ['admin.settings.*']],
            ['label' => 'Social Media Logins', 'route' => 'admin.settings.show', 'params' => ['screen' => 'social-login'], 'patterns' => ['admin.settings.*'], 'children' => [
                ['label' => 'Facebook', 'route' => 'admin.settings.show', 'params' => ['screen' => 'facebook'], 'patterns' => ['admin.settings.*'], 'children' => [
                    ['label' => 'WhatsApp Chat', 'route' => 'admin.settings.show', 'params' => ['screen' => 'whatsapp'], 'patterns' => ['admin.settings.*']],
                    ['label' => 'Facebook Comment', 'route' => 'admin.settings.show', 'params' => ['screen' => 'facebook'], 'patterns' => ['admin.settings.*']],
                ]],
                ['label' => 'Google', 'route' => 'admin.settings.show', 'params' => ['screen' => 'google'], 'patterns' => ['admin.settings.*'], 'children' => [
                    ['label' => 'Analytics Tools', 'route' => 'admin.settings.show', 'params' => ['screen' => 'google'], 'patterns' => ['admin.settings.*']],
                    ['label' => 'Google reCAPTCHA', 'route' => 'admin.settings.show', 'params' => ['screen' => 'google'], 'patterns' => ['admin.settings.*']],
                    ['label' => 'Google Map', 'route' => 'admin.settings.show', 'params' => ['screen' => 'google'], 'patterns' => ['admin.settings.*']],
                    ['label' => 'Google Firebase', 'route' => 'admin.settings.show', 'params' => ['screen' => 'google'], 'patterns' => ['admin.settings.*']],
                ]],
            ]],
            ['label' => 'Shipping', 'children' => [
                ['label' => 'Shipping Configuration', 'route' => 'admin.settings.show', 'params' => ['screen' => 'shipping'], 'patterns' => ['admin.settings.*']],
                ['label' => 'Shipping Countries', 'route' => 'admin.countries.index', 'patterns' => ['admin.countries.*']],
                ['label' => 'Shipping States', 'route' => 'admin.states.index', 'patterns' => ['admin.states.*']],
                ['label' => 'Shipping Cities', 'route' => 'admin.cities.index', 'patterns' => ['admin.cities.*']],
                ['label' => 'Shipping Zones', 'route' => 'admin.zones.index', 'patterns' => ['admin.zones.*']],
                ['label' => 'Shipping Carriers', 'route' => 'admin.carriers.index', 'patterns' => ['admin.carriers.*']],
            ]],
        ]],

        ['label' => 'Staffs', 'children' => [
            ['label' => 'All Staffs', 'route' => 'admin.staff.index', 'patterns' => ['admin.staff.*']],
            ['label' => 'Staff Permissions', 'route' => 'admin.roles.index', 'patterns' => ['admin.roles.*']],
        ]],

        ['label' => 'System', 'children' => [
            ['label' => 'Update'],
            ['label' => 'Server Status'],
            ['label' => 'Sitemap Generator'],
            ['label' => 'Addon Manager'],
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
