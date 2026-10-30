@extends('layouts.shop')

@php
    $options = $product->options();
    $isVariable = $product->isVariable();
    [$minPrice, $maxPrice] = $product->priceRange();
    $price = $selectedVariant ? $selectedVariant->unitPrice($product) : (float) $product->current_price;
    $compare = $selectedVariant ? $selectedVariant->compareAtPrice($product) : ($product->is_on_sale ? (float) $product->price : null);
    $stock = $selectedVariant ? (int) $selectedVariant->quantity : $product->qty;
    $soldOut = !$product->in_stock || ($stock !== null && $stock <= 0);
    $isWishlisted = \App\Support\Wishlist::has($product->id);
    $brandName = \App\Support\Store::brand()['name'];
    $sizeGuide = \App\Support\Store::info()?->size_guide;
@endphp

@section('seo_title', $product->meta_title ?: $product->name)
@section('seo_description', $product->meta_description ?: ($product->short_description ?: ($product->description ?: '')))
@section('seo_type', 'product')
@if ($product->cover_image)
    @section('seo_image', $product->cover_image)
@endif

@push('seo_schema')
@php
    $schemaDescription = \Illuminate\Support\Str::limit(trim(strip_tags(html_entity_decode((string) ($product->meta_description ?: $product->short_description ?: $product->description)))), 500);
    $availability = $product->in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';
@endphp
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'image' => $images->all() ?: null,
    'description' => $schemaDescription ?: null,
    'sku' => $selectedVariant?->sku ?: (string) $product->id,
    'category' => $product->category?->name,
    'brand' => ['@type' => 'Brand', 'name' => $brandName],
    'offers' => $isVariable && $minPrice !== $maxPrice ? [
        '@type' => 'AggregateOffer',
        'priceCurrency' => \App\Support\Store::currency(),
        'lowPrice' => number_format($minPrice, 2, '.', ''),
        'highPrice' => number_format($maxPrice, 2, '.', ''),
        'offerCount' => $product->variations->count(),
        'availability' => $availability,
    ] : [
        '@type' => 'Offer',
        'url' => route('products.show', $product->slug),
        'priceCurrency' => \App\Support\Store::currency(),
        'price' => number_format($price, 2, '.', ''),
        'availability' => $availability,
        'itemCondition' => 'https://schema.org/NewCondition',
    ],
    'aggregateRating' => $reviewCount > 0 ? [
        '@type' => 'AggregateRating',
        'ratingValue' => (float) $rating,
        'reviewCount' => (int) $reviewCount,
    ] : null,
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => array_values(array_filter([
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        $product->category ? ['@type' => 'ListItem', 'position' => 2, 'name' => $product->category->name, 'item' => route('collections.show', $product->category)] : null,
        ['@type' => 'ListItem', 'position' => $product->category ? 3 : 2, 'name' => $product->name, 'item' => route('products.show', $product->slug)],
    ])),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
@endpush

@section('content')

<div class="container">
    <nav class="breadcrumbs breadcrumbs--pdp" aria-label="Breadcrumb">
        <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            @if ($product->category)
                <li><a href="{{ route('collections.show', $product->category) }}">{{ $product->category->name }}</a></li>
            @else
                <li><a href="{{ route('products.index') }}">Shop</a></li>
            @endif
            <li aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>
</div>

<section class="pdp" data-product='@json(['variants' => $product->variantPayload(), 'options' => $options->pluck('name')])'>
    <div class="container pdp__grid">
        <div class="pdp__gallery">
            <div class="gallery" data-gallery>
                <div class="gallery__track" data-gallery-track>
                    @forelse ($images as $image)
                        <figure class="gallery__item">
                            <img src="{{ $image }}" alt="{{ $product->name }}{{ $loop->first ? '' : ' — image ' . $loop->iteration }}" @if ($loop->first) data-gallery-main fetchpriority="high" @else loading="lazy" @endif width="1080" height="1440">
                        </figure>
                    @empty
                        <figure class="gallery__item gallery__item--empty"><span>{{ $product->name }}</span></figure>
                    @endforelse
                </div>
                @if ($images->count() > 1)
                    <div class="gallery__dots" aria-hidden="true">
                        @foreach ($images as $image)
                            <span class="{{ $loop->first ? 'is-active' : '' }}"></span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="pdp__info">
            <div class="pdp__sticky">
                @if ($product->category)
                    <p class="eyebrow"><a href="{{ route('collections.show', $product->category) }}">{{ $product->category->name }}</a></p>
                @endif
                <h1 class="pdp__title">{{ $product->name }}</h1>

                <div class="pdp__price-row">
                    <span data-price><x-price :amount="$price" :compare="$compare" /></span>
                    @if ($reviewCount)
                        <a href="#reviews" class="pdp__rating"><x-rating :value="$rating" :count="$reviewCount" /></a>
                    @endif
                </div>

                @if ($product->short_description)
                    <div class="pdp__lede rich-text">{!! $product->short_description !!}</div>
                @endif

                <form class="pdp__form" data-add-to-cart data-pdp-form>
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variation_id" value="{{ $selectedVariant?->id }}" data-variant-input>

                    @foreach ($options as $option)
                        @php($selectedValue = $selectedVariant?->optionValues()[$option['name']] ?? null)
                        <fieldset class="option" data-option="{{ $option['name'] }}">
                            <legend class="option__legend">
                                <span>{{ $option['name'] }}: <strong data-option-label>{{ $selectedValue }}</strong></span>
                                @if (!$option['is_color'])
                                    <button type="button" class="link-btn" data-dialog-open="size-guide"><x-icon name="ruler" /> Size guide</button>
                                @endif
                            </legend>
                            <div class="{{ $option['is_color'] ? 'option__swatches' : 'option__pills' }}">
                                @foreach ($option['values'] as $value)
                                    <label class="{{ $option['is_color'] ? 'swatch' : 'pill' }}" title="{{ $value['value'] }}">
                                        <input type="radio" name="option[{{ $option['name'] }}]" value="{{ $value['value'] }}" @checked($selectedValue === $value['value'])>
                                        @if ($option['is_color'])
                                            <span class="swatch__dot" style="--swatch: {{ $value['swatch'] }}"></span>
                                            <span class="visually-hidden">{{ $value['value'] }}</span>
                                        @else
                                            <span>{{ $value['value'] }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach

                    <p class="pdp__stock {{ $soldOut ? 'is-out' : ($stock !== null && $stock <= 5 ? 'is-low' : '') }}" data-stock>
                        @if ($soldOut)
                            Sold out{{ $isVariable ? ' in this option' : '' }}
                        @elseif ($stock !== null && $stock <= 5)
                            Only {{ $stock }} left
                        @else
                            In stock — ready to ship
                        @endif
                    </p>

                    <div class="pdp__buy">
                        <div class="qty" data-qty>
                            <button type="button" data-qty-step="-1" aria-label="Decrease quantity"><x-icon name="minus" /></button>
                            <input type="number" name="qty" value="1" min="1" max="{{ $stock ?? 99 }}" inputmode="numeric" aria-label="Quantity">
                            <button type="button" data-qty-step="1" aria-label="Increase quantity"><x-icon name="plus" /></button>
                        </div>
                        <button type="submit" class="btn btn--primary btn--block" data-add-button @disabled($soldOut)>
                            {{ $soldOut ? 'Sold out' : 'Add to bag' }}
                        </button>
                        <button type="button" class="btn btn--icon {{ $isWishlisted ? 'is-active' : '' }}" data-wishlist="{{ $product->id }}" aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}" aria-label="Save to wishlist">
                            <x-icon name="heart" />
                        </button>
                    </div>
                </form>

                <ul class="pdp__perks">
                    <li><x-icon name="truck" /> {{ \App\Support\Store::freeShippingThreshold() ? 'Free delivery on orders over ' . money(\App\Support\Store::freeShippingThreshold()) : 'Delivery in ' . config('store.delivery.estimate') }}</li>
                    <li><x-icon name="cash" /> Cash on delivery available</li>
                    <li><x-icon name="return" /> Easy size exchanges</li>
                </ul>

                <div class="accordion">
                    <details open>
                        <summary>Description <x-icon name="plus" /></summary>
                        <div class="accordion__body rich-text">
                            @if ($product->description)
                                {!! $product->description !!}
                            @else
                                <p>{{ strip_tags((string) $product->short_description) ?: 'Details coming soon.' }}</p>
                            @endif
                        </div>
                    </details>
                    @if ($product->material_care)
                        <details>
                            <summary>Fabric &amp; care <x-icon name="plus" /></summary>
                            <div class="accordion__body rich-text">{!! nl2br(e($product->material_care)) !!}</div>
                        </details>
                    @endif
                    <details>
                        <summary>Delivery &amp; returns <x-icon name="plus" /></summary>
                        <div class="accordion__body">
                            <p>We deliver nationwide in {{ config('store.delivery.estimate') }}. Pay by cash when your parcel arrives.</p>
                            <p>Wrong size? Exchanges are easy — see our <a href="{{ route('returns') }}">refund &amp; exchange policy</a> and <a href="{{ route('shipping') }}">shipping policy</a>.</p>
                        </div>
                    </details>
                    @if (!empty($product->tags))
                        <div class="pdp__tags">
                            @foreach ($product->tags as $tag)
                                <a href="{{ route('search', ['q' => $tag]) }}">{{ $tag }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<dialog class="dialog" data-dialog="size-guide" aria-labelledby="size-guide-title">
    <div class="dialog__head">
        <h2 id="size-guide-title">Size guide</h2>
        <button type="button" class="icon-btn" data-dialog-close aria-label="Close"><x-icon name="close" /></button>
    </div>
    <div class="dialog__body">
        @include('pages.partials.size-guide-content', ['content' => $sizeGuide])
    </div>
</dialog>

<section class="section reviews" id="reviews">
    <div class="container reviews__grid">
        <div class="reviews__summary">
            <h2 class="section-title">Reviews</h2>
            @if ($reviewCount)
                <p class="reviews__score">{{ $rating }}<span>/5</span></p>
                <x-rating :value="$rating" />
                <p class="muted">Based on {{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</p>
            @else
                <p class="muted">No reviews yet.</p>
            @endif
            <p class="muted small">Reviews come from verified customers who received this item.</p>
        </div>

        <div class="reviews__list">
            @foreach ($product->reviews as $review)
                <article class="review">
                    <header class="review__head">
                        <span class="avatar">
                            @if ($review->user?->profile_image)
                                <img src="{{ $review->user->profile_image }}" alt="">
                            @else
                                {{ Str::of($review->name)->trim()->substr(0, 1)->upper() }}
                            @endif
                        </span>
                        <div>
                            <strong>{{ $review->name }}</strong>
                            <x-rating :value="$review->rating" />
                        </div>
                        <time class="muted small" datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('M d, Y') }}</time>
                    </header>
                    <p>{{ $review->review }}</p>
                    @if (!empty($review->media))
                        <div class="review__media">
                            @foreach ($review->media as $attachment)
                                @if ($attachment['type'] === 'video')
                                    <video src="{{ $attachment['url'] }}" controls playsinline preload="metadata"></video>
                                @else
                                    <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener"><img src="{{ $attachment['url'] }}" alt="Photo from {{ $review->name }}" loading="lazy"></a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @if ($review->admin_reply)
                        <div class="review__reply">
                            <strong>Reply from {{ $brandName }}</strong>
                            <p>{{ $review->admin_reply }}</p>
                        </div>
                    @endif
                </article>
            @endforeach

            @if ($reviewableOrder)
                <form method="POST" action="{{ route('reviews.store', $product->slug) }}" enctype="multipart/form-data" class="review-form card">
                    @csrf
                    <h3>Write a review</h3>
                    <p class="muted small">You bought this in order {{ $reviewableOrder->uid }}.</p>
                    <input type="hidden" name="order_id" value="{{ $reviewableOrder->id }}">
                    <div class="form-grid">
                        <div class="field">
                            <label for="reviewName">Name</label>
                            <input type="text" id="reviewName" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                        </div>
                        <div class="field">
                            <label for="reviewEmail">Email</label>
                            <input type="email" id="reviewEmail" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                        </div>
                    </div>
                    <div class="field">
                        <label>Rating</label>
                        <x-star-rating-input :id="'rating-' . $reviewableOrder->id . '-' . $product->id" />
                    </div>
                    <div class="field">
                        <label for="reviewText">Your review</label>
                        <textarea id="reviewText" name="review" rows="4" required>{{ old('review') }}</textarea>
                    </div>
                    <div class="field">
                        <label for="reviewMedia">Photos or videos <span class="muted">(optional)</span></label>
                        <x-review-media-input id="reviewMedia" />
                    </div>
                    @if ($errors->any())
                        <div class="alert alert--error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                    <button type="submit" class="btn btn--primary">Post review</button>
                </form>
            @endif
        </div>
    </div>
</section>

@if ($related->isNotEmpty())
    <section class="section">
        <div class="container">
            <div class="section-head">
                <h2 class="section-title">You may also like</h2>
            </div>
            <div class="product-grid product-grid--rail">
                @foreach ($related as $item)
                    <x-product-card :product="$item" />
                @endforeach
            </div>
        </div>
    </section>
@endif

@endsection
