<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, follow">
@php
    $siteInfo = \App\Support\Store::info();
    $brand = \App\Support\Store::brand();
@endphp
<title>@yield('title', 'Checkout') | {{ $brand['name'] }}</title>
<link rel="icon" href="{{ asset_ver('assets/drape/favicon.svg') }}" type="image/svg+xml">
<link rel="icon" href="{{ asset_ver('assets/drape/favicon-32.png') }}" type="image/png" sizes="32x32">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
<link href="{{ asset_ver('assets/drape/drape.css') }}" rel="stylesheet">
</head>
<body class="page-checkout">
@include('layouts.partials.icons')

<header class="checkout-header">
    <div class="container checkout-header__inner">
        <a href="{{ route('home') }}" class="site-logo" aria-label="{{ $brand['name'] }} home">
            @if ($siteInfo?->logo)
                <img src="{{ $siteInfo->logo }}" alt="{{ $brand['name'] }}">
            @else
                <span class="site-logo__text">{{ $brand['name'] }}</span>
            @endif
        </a>
        <a href="{{ route('cart.index') }}" class="icon-btn" aria-label="Back to bag"><x-icon name="bag" /></a>
    </div>
</header>

<main id="main">
    @yield('content')
</main>

<footer class="checkout-footer container">
    <a href="{{ route('returns') }}">Refund policy</a>
    <a href="{{ route('shipping') }}">Shipping</a>
    <a href="{{ route('privacy') }}">Privacy</a>
    <a href="{{ route('terms') }}">Terms</a>
</footer>

<div class="toast-stack" data-toasts aria-live="polite"></div>
<script>
    window.Drape = {
        csrf: @json(csrf_token()),
        routes: {
            couponApply: @json(route('cart.coupon.apply')),
            couponRemove: @json(route('cart.coupon.remove')),
            validateAgent: @json(route('checkout.validate-agent')),
        },
    };
</script>
<script src="{{ asset_ver('assets/drape/drape.js') }}" defer></script>
@stack('scripts')
</body>
</html>
