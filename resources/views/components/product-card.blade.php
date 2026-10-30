@props(['product', 'eager' => false])
@php
    [$minPrice, $maxPrice] = $product->priceRange();
    $compare = !$product->isVariable() && $product->is_on_sale ? (float) $product->price : null;
    if ($product->isVariable() && $product->is_on_sale && $product->variations->every(fn ($v) => $v->price === null)) {
        $compare = (float) $product->price;
    }
    $swatches = $product->colorSwatches();
    $isWishlisted = \App\Support\Wishlist::has($product->id);
    $url = route('products.show', $product->slug);
    $hover = $product->getRawOriginal('hover_image') ? $product->hover_image : null;
@endphp
<article {{ $attributes->merge(['class' => 'product-card']) }}>
    <div class="product-card__media">
        <a href="{{ $url }}" class="product-card__image" tabindex="-1" aria-hidden="true">
            @if ($product->cover_image)
                <img src="{{ $product->cover_image }}" alt="" loading="{{ $eager ? 'eager' : 'lazy' }}" width="540" height="720">
                @if ($hover)
                    <img src="{{ $hover }}" alt="" loading="lazy" class="product-card__hover" width="540" height="720">
                @endif
            @else
                <span class="product-card__placeholder">{{ $product->name }}</span>
            @endif
        </a>

        <div class="product-card__badges">
            @if (!$product->in_stock)
                <span class="badge badge--muted">Sold out</span>
            @elseif ($compare)
                <span class="badge badge--sale">Sale</span>
            @elseif ($product->is_new)
                <span class="badge">New</span>
            @endif
        </div>

        <button type="button" class="product-card__wish {{ $isWishlisted ? 'is-active' : '' }}" data-wishlist="{{ $product->id }}" aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}" aria-label="Save {{ $product->name }} to wishlist">
            <x-icon name="heart" />
        </button>

        @if ($product->in_stock && !$product->isVariable())
            <form class="product-card__quick" data-add-to-cart>
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" class="btn btn--light btn--sm">Quick add</button>
            </form>
        @elseif ($product->in_stock)
            <a href="{{ $url }}" class="product-card__quick btn btn--light btn--sm">Choose options</a>
        @endif
    </div>

    <div class="product-card__info">
        <h3 class="product-card__title"><a href="{{ $url }}">{{ $product->name }}</a></h3>
        <x-price :amount="$minPrice" :compare="$compare" :from="$minPrice !== $maxPrice" />
        @if (count($swatches) > 1)
            <ul class="swatch-dots" aria-label="{{ count($swatches) }} colours">
                @foreach (array_slice($swatches, 0, 5) as $swatch)
                    <li style="--swatch: {{ $swatch['swatch'] }}" title="{{ $swatch['value'] }}"></li>
                @endforeach
                @if (count($swatches) > 5)<li class="swatch-dots__more">+{{ count($swatches) - 5 }}</li>@endif
            </ul>
        @endif
    </div>
</article>
