@extends('layouts.shop')

@section('body_class', 'page-home')

@section('content')

@php($siteInfo = \App\Support\Store::info())

{{-- Hero: admin banners as slides, or a typographic hero until there are any --}}
@if ($banners->isNotEmpty())
    <section class="hero" data-slider aria-roledescription="carousel" aria-label="Featured">
        <div class="hero__track">
            @foreach ($banners as $banner)
                @php($bg = $banner->background_image ?: $banner->image)
                <div class="hero__slide {{ $loop->first ? 'is-active' : '' }}" data-slide aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $banners->count() }}">
                    @if ($bg)
                        <img class="hero__bg" src="{{ $bg }}" alt="" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                    @endif
                    <div class="container hero__content">
                        @if ($banner->eyebrow)<p class="eyebrow eyebrow--light">{{ $banner->eyebrow }}</p>@endif
                        @if ($loop->first)
                            <h1 class="hero__title">{{ $banner->headline }}</h1>
                        @else
                            <h2 class="hero__title">{{ $banner->headline }}</h2>
                        @endif
                        @if ($banner->description)<p class="hero__text">{{ $banner->description }}</p>@endif
                        <a href="{{ safe_link($banner->cta_link, route('products.index')) }}" class="btn btn--light">{{ $banner->cta_label ?: $hero['ctaLabel'] }}</a>
                    </div>
                </div>
            @endforeach
        </div>
        @if ($banners->count() > 1)
            <div class="hero__controls container">
                <div class="hero__dots">
                    @foreach ($banners as $banner)
                        <button type="button" data-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'is-active' : '' }}" aria-label="Show slide {{ $loop->iteration }}"></button>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@else
    <section class="hero hero--plain">
        <div class="hero__drape" aria-hidden="true"></div>
        <div class="container hero__content">
            <p class="eyebrow">{{ $hero['eyebrow'] }}</p>
            <h1 class="hero__title">{{ $hero['headline'] }}</h1>
            <p class="hero__text">{{ $hero['description'] }}</p>
            <div class="hero__actions">
                <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="btn btn--primary">{{ $hero['ctaLabel'] }}</a>
                <a href="{{ route('collections.index') }}" class="btn btn--outline">Browse collections</a>
            </div>
        </div>
    </section>
@endif

{{-- Shop by collection --}}
@if ($collections->isNotEmpty())
    <section class="section">
        <div class="container">
            <div class="section-head">
                <h2 class="section-title">Shop by collection</h2>
                <a href="{{ route('collections.index') }}" class="link-arrow">View all <x-icon name="arrow-right" /></a>
            </div>
            <div class="collection-grid collection-grid--{{ min(4, $collections->count()) }}">
                @foreach ($collections->take(4) as $collection)
                    <a href="{{ route('collections.show', $collection) }}" class="collection-tile">
                        <div class="collection-tile__media">
                            @if ($collection->cover_image)
                                <img src="{{ $collection->cover_image }}" alt="" loading="lazy">
                            @else
                                <span class="collection-tile__monogram" aria-hidden="true">{{ mb_substr($collection->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="collection-tile__label">
                            <span>{{ $collection->name }}</span>
                            <x-icon name="arrow-right" />
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- New arrivals --}}
@if ($newArrivals->isNotEmpty())
    <section class="section">
        <div class="container">
            <div class="section-head">
                <h2 class="section-title">New arrivals</h2>
                <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="link-arrow">Shop new in <x-icon name="arrow-right" /></a>
            </div>
            <div class="product-grid product-grid--rail">
                @foreach ($newArrivals as $product)
                    <x-product-card :product="$product" :eager="$loop->index < 2" />
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Promo banners (Site Info → promo banners) --}}
@if ($siteInfo?->promo_banner_left || $siteInfo?->promo_banner_right)
    <section class="section section--tight">
        <div class="container promo-split">
            @foreach (['left', 'right'] as $side)
                @if ($siteInfo->{'promo_banner_' . $side})
                    <a href="{{ safe_link($siteInfo->{'promo_banner_' . $side . '_link'}, route('products.index')) }}" class="promo-card">
                        <img src="{{ $siteInfo->{'promo_banner_' . $side} }}" alt="" loading="lazy">
                        @if ($siteInfo->{'promo_banner_' . $side . '_label'})
                            <span class="promo-card__label">{{ $siteInfo->{'promo_banner_' . $side . '_label'} }} <x-icon name="arrow-right" /></span>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    </section>
@endif

{{-- Featured / bestsellers --}}
@if ($featured->isNotEmpty() && $featured->pluck('id')->diff($newArrivals->pluck('id'))->isNotEmpty())
    <section class="section">
        <div class="container">
            <div class="section-head">
                <h2 class="section-title">Most loved</h2>
                <a href="{{ route('products.index', ['sort' => 'best_selling']) }}" class="link-arrow">Shop bestsellers <x-icon name="arrow-right" /></a>
            </div>
            <div class="product-grid">
                @foreach ($featured->take(4) as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Editorial / story --}}
<section class="section">
    <div class="container editorial">
        <div class="editorial__media">
            @if ($siteInfo?->promo_banner_full)
                <img src="{{ $siteInfo->promo_banner_full }}" alt="" loading="lazy">
            @elseif ($siteInfo?->home_banner)
                <img src="{{ $siteInfo->home_banner }}" alt="" loading="lazy">
            @else
                <div class="editorial__art" aria-hidden="true"></div>
            @endif
        </div>
        <div class="editorial__copy">
            <p class="eyebrow">{{ $siteInfo?->story_eyebrow ?: 'The Drape way' }}</p>
            <h2 class="display">{{ $siteInfo?->story_headline ?: 'Fewer, better clothes' }}</h2>
            <p>{{ $siteInfo?->story_description ?: 'We design in small runs from breathable natural fabrics — cotton, linen and lawn — cut with a little extra room so every piece falls the way it should. Made to be worn, washed and worn again.' }}</p>
            <a href="{{ $siteInfo?->promo_banner_full_link ? safe_link($siteInfo->promo_banner_full_link, route('story')) : route('story') }}" class="btn btn--outline">{{ $siteInfo?->promo_banner_full_label ?: 'Read our story' }}</a>
        </div>
    </div>
</section>

{{-- On sale --}}
@if ($onSale->isNotEmpty())
    <section class="section">
        <div class="container">
            <div class="section-head">
                <h2 class="section-title">On sale now</h2>
                <a href="{{ route('products.index', ['sale' => 1]) }}" class="link-arrow">Shop the sale <x-icon name="arrow-right" /></a>
            </div>
            <div class="product-grid">
                @foreach ($onSale as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Lookbook (Site Info → gallery) --}}
@if ($gallery->isNotEmpty())
    <section class="section">
        <div class="container">
            <div class="section-head section-head--center">
                <p class="eyebrow">{{ $siteInfo?->gallery_quote ?: '#WearDrape' }}</p>
                <h2 class="section-title">Styled by you</h2>
            </div>
            <div class="lookbook">
                @foreach ($gallery as $item)
                    @php($tag = $item->cta_link ? 'a' : 'div')
                    <{{ $tag }} @if ($item->cta_link) href="{{ safe_link($item->cta_link, route('products.index')) }}" @endif class="lookbook__item">
                        @if ($item->isVideo())
                            <video src="{{ $item->image }}" muted loop playsinline autoplay preload="metadata"></video>
                        @else
                            <img src="{{ $item->image }}" alt="" loading="lazy">
                        @endif
                    </{{ $tag }}>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Service promises --}}
<section class="section section--tight">
    <div class="container">
        <ul class="perks">
            <li>
                <x-icon name="truck" />
                <div>
                    <strong>{{ \App\Support\Store::freeShippingThreshold() ? 'Free delivery over ' . money(\App\Support\Store::freeShippingThreshold()) : 'Nationwide delivery' }}</strong>
                    <span>Delivered in {{ config('store.delivery.estimate') }}</span>
                </div>
            </li>
            <li>
                <x-icon name="return" />
                <div>
                    <strong>Easy exchanges</strong>
                    <span><a href="{{ route('returns') }}">See our returns policy</a></span>
                </div>
            </li>
            <li>
                <x-icon name="cash" />
                <div>
                    <strong>Cash on delivery</strong>
                    <span>Pay when your order arrives</span>
                </div>
            </li>
            <li>
                <x-icon name="leaf" />
                <div>
                    <strong>Natural fabrics</strong>
                    <span>Breathable, made to last</span>
                </div>
            </li>
        </ul>
    </div>
</section>

@endsection
