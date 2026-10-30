@php($url = route('products.show', $item['product']->slug) . ($item['variant'] ? '?variant=' . $item['variant']->id : ''))
<li class="line-item" data-line="{{ $item['key'] }}">
    <a href="{{ $url }}" class="line-item__image">
        @if ($item['image'])<img src="{{ $item['image'] }}" alt="" loading="lazy">@endif
    </a>
    <div class="line-item__info">
        <a href="{{ $url }}" class="line-item__title">{{ $item['product']->name }}</a>
        @if ($item['label'])<p class="line-item__variant">{{ $item['label'] }}</p>@endif
        <x-price :amount="$item['unit_price']" :compare="$item['compare_at']" class="line-item__price" />
        @if ($item['max'] !== null && $item['qty'] > $item['max'])
            <p class="line-item__warn">Only {{ $item['max'] }} left — we'll adjust this at checkout.</p>
        @endif
        <div class="line-item__actions">
            <form method="POST" action="{{ route('cart.update') }}" class="qty qty--sm" data-cart-line-form>
                @csrf
                <input type="hidden" name="key" value="{{ $item['key'] }}">
                <button type="submit" name="qty" value="{{ $item['qty'] - 1 }}" aria-label="Decrease quantity"><x-icon name="minus" /></button>
                <span class="qty__value" aria-live="polite">{{ $item['qty'] }}</span>
                <button type="submit" name="qty" value="{{ $item['qty'] + 1 }}" aria-label="Increase quantity" @disabled($item['max'] !== null && $item['qty'] >= $item['max'])><x-icon name="plus" /></button>
            </form>
            <form method="POST" action="{{ route('cart.remove') }}" data-cart-line-form>
                @csrf
                <input type="hidden" name="key" value="{{ $item['key'] }}">
                <button type="submit" class="link-btn">Remove</button>
            </form>
        </div>
    </div>
    @unless ($compact ?? false)
        <p class="line-item__total">{{ money($item['line_total']) }}</p>
    @endunless
</li>
