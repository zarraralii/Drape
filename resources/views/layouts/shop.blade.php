<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#fbfaf7">

@include('layouts.partials.seo')

<link rel="icon" href="{{ asset_ver('assets/drape/favicon.svg') }}" type="image/svg+xml">
<link rel="icon" href="{{ asset_ver('assets/drape/favicon-32.png') }}" type="image/png" sizes="32x32">
<link rel="apple-touch-icon" href="{{ asset_ver('assets/drape/favicon-180.png') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
<link href="{{ asset_ver('assets/drape/drape.css') }}" rel="stylesheet">
@stack('styles')
</head>
<body class="@yield('body_class')">
@include('layouts.partials.icons')
<a class="skip-link" href="#main">Skip to content</a>

@if ($siteInfo?->announcement_text)
    <div class="announcement">
        <p>{{ $siteInfo->announcement_text }}</p>
    </div>
@endif

<header class="site-header" data-header>
    <div class="container site-header__inner">
        <div class="site-header__left">
            <button type="button" class="icon-btn site-header__menu-btn" data-drawer-open="menu" aria-label="Open menu">
                <x-icon name="menu" />
            </button>
            <nav class="main-nav" aria-label="Main">
                <ul>
                    <li><a href="{{ route('products.index', ['sort' => 'newest']) }}" @class(['is-active' => request()->routeIs('products.index') && request('sort') === 'newest'])>New In</a></li>
                    <li class="main-nav__has-menu">
                        <a href="{{ route('collections.index') }}" @class(['is-active' => request()->routeIs('collections.*')]) aria-haspopup="true">Shop <x-icon name="chevron-down" /></a>
                        <div class="main-nav__menu">
                            <ul>
                                <li><a href="{{ route('products.index') }}">Shop all</a></li>
                                @foreach ($navCollections as $navCollection)
                                    <li><a href="{{ route('collections.show', $navCollection) }}">{{ $navCollection->name }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                    <li><a href="{{ route('products.index', ['sale' => 1]) }}" class="main-nav__sale">Sale</a></li>
                    <li><a href="{{ route('story') }}" @class(['is-active' => request()->routeIs('story')])>Our Story</a></li>
                </ul>
            </nav>
        </div>

        <a href="{{ route('home') }}" class="site-logo" aria-label="{{ $brand['name'] }} home">
            @if ($siteInfo?->logo)
                <img src="{{ $siteInfo->logo }}" alt="{{ $brand['name'] }}">
            @else
                <span class="site-logo__text">{{ $brand['name'] }}</span>
            @endif
        </a>

        <div class="site-header__right">
            <button type="button" class="icon-btn" data-search-open aria-label="Search">
                <x-icon name="search" />
            </button>
            <a href="{{ auth()->check() ? route('account.orders') : route('login') }}" class="icon-btn site-header__account" aria-label="Account">
                <x-icon name="user" />
            </a>
            <a href="{{ route('wishlist.index') }}" class="icon-btn site-header__wishlist" aria-label="Wishlist">
                <x-icon name="heart" />
                <span class="icon-btn__badge" data-wishlist-count @if (!$wishlistCount) hidden @endif>{{ $wishlistCount }}</span>
            </a>
            <a href="{{ route('cart.index') }}" class="icon-btn" data-drawer-open="cart" aria-label="Bag">
                <x-icon name="bag" />
                <span class="icon-btn__badge" data-cart-count @if (!$cartCount) hidden @endif>{{ $cartCount }}</span>
            </a>
        </div>
    </div>

    <div class="search-panel" data-search-panel hidden>
        <div class="container">
            <form action="{{ route('search') }}" method="GET" class="search-panel__form" role="search">
                <x-icon name="search" />
                <input type="search" name="q" placeholder="Search for dresses, shirts, kurtas…" autocomplete="off" aria-label="Search" data-search-input data-suggest-url="{{ route('search.suggest') }}">
                <button type="button" class="icon-btn" data-search-close aria-label="Close search"><x-icon name="close" /></button>
            </form>
            <div class="search-panel__results" data-search-results></div>
        </div>
    </div>
</header>

{{-- Mobile menu --}}
<div class="drawer drawer--left" data-drawer="menu" aria-hidden="true">
    <div class="drawer__backdrop" data-drawer-close></div>
    <div class="drawer__panel" role="dialog" aria-modal="true" aria-label="Menu">
        <div class="drawer__head">
            <span class="drawer__title">Menu</span>
            <button type="button" class="icon-btn" data-drawer-close aria-label="Close menu"><x-icon name="close" /></button>
        </div>
        <div class="drawer__body mobile-menu">
            <ul class="mobile-menu__list">
                <li><a href="{{ route('products.index', ['sort' => 'newest']) }}">New In</a></li>
                <li><a href="{{ route('products.index') }}">Shop All</a></li>
                @foreach ($navCollections as $navCollection)
                    <li><a href="{{ route('collections.show', $navCollection) }}">{{ $navCollection->name }}</a></li>
                @endforeach
                <li><a href="{{ route('products.index', ['sale' => 1]) }}" class="main-nav__sale">Sale</a></li>
                <li><a href="{{ route('story') }}">Our Story</a></li>
            </ul>
            <ul class="mobile-menu__secondary">
                @auth
                    <li><a href="{{ route('account.orders') }}"><x-icon name="user" /> My account</a></li>
                @else
                    <li><a href="{{ route('login') }}"><x-icon name="user" /> Log in</a></li>
                @endauth
                <li><a href="{{ route('wishlist.index') }}"><x-icon name="heart" /> Wishlist</a></li>
                <li><a href="{{ route('order.track') }}"><x-icon name="truck" /> Track an order</a></li>
                <li><a href="{{ route('contact') }}"><x-icon name="mail" /> Contact us</a></li>
            </ul>
        </div>
    </div>
</div>

{{-- Cart drawer: filled from /cart/drawer --}}
<div class="drawer drawer--right" data-drawer="cart" aria-hidden="true">
    <div class="drawer__backdrop" data-drawer-close></div>
    <div class="drawer__panel" role="dialog" aria-modal="true" aria-label="Your bag" data-cart-drawer data-url="{{ route('cart.drawer') }}">
        <div class="drawer__head">
            <span class="drawer__title">Your bag</span>
            <button type="button" class="icon-btn" data-drawer-close aria-label="Close bag"><x-icon name="close" /></button>
        </div>
        <div class="drawer__loading">Loading…</div>
    </div>
</div>

<main id="main">
    @if (session('success') || session('fail') || session('throttled'))
        <div class="container flash-wrap">
            @if (session('success'))<div class="alert alert--success" role="status">{{ session('success') }}</div>@endif
            @if (session('fail'))<div class="alert alert--error" role="alert">{{ session('fail') }}</div>@endif
            @if (session('throttled'))<div class="alert alert--error" role="alert">{{ session('throttled') }}</div>@endif
        </div>
    @endif

    @yield('content')
</main>

<footer class="site-footer">
    <div class="container">
        <div class="site-footer__top">
            <div class="site-footer__brand">
                <a href="{{ route('home') }}" class="site-logo site-logo--footer">
                    <span class="site-logo__text">{{ $brand['name'] }}</span>
                </a>
                @if ($brand['tagline'])<p>{{ $brand['tagline'] }}</p>@endif
                <ul class="social">
                    @if ($siteInfo?->instagram_link)<li><a href="{{ $siteInfo->instagram_link }}" target="_blank" rel="noopener" aria-label="Instagram"><x-icon name="instagram" /></a></li>@endif
                    @if ($siteInfo?->facebook_link)<li><a href="{{ $siteInfo->facebook_link }}" target="_blank" rel="noopener" aria-label="Facebook"><x-icon name="facebook" /></a></li>@endif
                    @if ($siteInfo?->tiktok_link)<li><a href="{{ $siteInfo->tiktok_link }}" target="_blank" rel="noopener" aria-label="TikTok"><x-icon name="tiktok" /></a></li>@endif
                    @if ($siteInfo?->youtube_link)<li><a href="{{ $siteInfo->youtube_link }}" target="_blank" rel="noopener" aria-label="YouTube"><x-icon name="youtube" /></a></li>@endif
                </ul>
            </div>

            <div class="site-footer__col">
                <h3>Shop</h3>
                <ul>
                    <li><a href="{{ route('products.index', ['sort' => 'newest']) }}">New In</a></li>
                    @foreach ($navCollections->take(5) as $navCollection)
                        <li><a href="{{ route('collections.show', $navCollection) }}">{{ $navCollection->name }}</a></li>
                    @endforeach
                    <li><a href="{{ route('products.index', ['sale' => 1]) }}">Sale</a></li>
                </ul>
            </div>

            <div class="site-footer__col">
                <h3>Help</h3>
                <ul>
                    <li><a href="{{ route('order.track') }}">Track your order</a></li>
                    <li><a href="{{ route('size-guide') }}">Size guide</a></li>
                    <li><a href="{{ route('shipping') }}">Shipping</a></li>
                    <li><a href="{{ route('returns') }}">Returns &amp; exchanges</a></li>
                    <li><a href="{{ route('faq') }}">FAQs</a></li>
                    <li><a href="{{ route('contact') }}">Contact us</a></li>
                </ul>
            </div>

            <div class="site-footer__newsletter">
                <h3>Join the list</h3>
                <p>New arrivals, restocks and members-only offers. No spam.</p>
                <form method="POST" action="{{ route('newsletter.store') }}" class="inline-form">
                    @csrf
                    <label class="visually-hidden" for="footer-newsletter">Email address</label>
                    <input type="email" id="footer-newsletter" name="email" placeholder="Email address" required>
                    <button type="submit" class="inline-form__btn" aria-label="Subscribe"><x-icon name="arrow-right" /></button>
                </form>
                @if ($siteInfo?->phone || $siteInfo?->email)
                    <ul class="site-footer__contact">
                        @if ($siteInfo->phone)<li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteInfo->phone) }}"><x-icon name="phone" /> {{ $siteInfo->phone }}</a></li>@endif
                        @if ($siteInfo->email)<li><a href="mailto:{{ $siteInfo->email }}"><x-icon name="mail" /> {{ $siteInfo->email }}</a></li>@endif
                    </ul>
                @endif
            </div>
        </div>

        <div class="site-footer__bottom">
            <p>&copy; {{ date('Y') }} {{ $brand['name'] }}. All rights reserved.</p>
            <ul>
                <li><a href="{{ route('privacy') }}">Privacy</a></li>
                <li><a href="{{ route('terms') }}">Terms</a></li>
                <li><a href="{{ route('returns') }}">Refunds</a></li>
            </ul>
        </div>
    </div>
</footer>

<div class="toast-stack" data-toasts aria-live="polite"></div>

<script>
    window.Drape = {
        csrf: @json(csrf_token()),
        routes: {
            cartAdd: @json(route('cart.add')),
            cartUpdate: @json(route('cart.update')),
            cartRemove: @json(route('cart.remove')),
            couponApply: @json(route('cart.coupon.apply')),
            couponRemove: @json(route('cart.coupon.remove')),
            wishlistToggle: @json(route('wishlist.toggle')),
        },
    };
</script>
<script src="{{ asset_ver('assets/drape/drape.js') }}" defer></script>
@stack('scripts')
</body>
</html>
