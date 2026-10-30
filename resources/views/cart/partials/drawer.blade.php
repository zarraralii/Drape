{{-- Cart drawer contents — rendered by CartController and swapped in by drape.js. --}}
<div class="drawer__head">
    <span class="drawer__title">Your bag @if ($count)<span class="muted">({{ $count }})</span>@endif</span>
    <button type="button" class="icon-btn" data-drawer-close aria-label="Close bag"><x-icon name="close" /></button>
</div>

@if ($items->isEmpty())
    <div class="drawer__body drawer__empty">
        <x-icon name="bag" />
        <p>Your bag is empty.</p>
        <a href="{{ route('products.index') }}" class="btn btn--primary">Start shopping</a>
    </div>
@else
    @include('cart.partials.shipping-meter')

    <div class="drawer__body">
        <ul class="line-items">
            @foreach ($items as $item)
                @include('cart.partials.line', ['item' => $item, 'compact' => true])
            @endforeach
        </ul>
    </div>

    <div class="drawer__foot">
        @if ($coupon)
            <p class="drawer__row"><span>Discount <code>{{ $coupon->code }}</code></span><span>−{{ money($discount) }}</span></p>
        @endif
        <p class="drawer__row drawer__row--total"><span>Subtotal</span><span>{{ money($subtotal - $discount) }}</span></p>
        <p class="muted small">Delivery calculated at checkout.</p>
        <a href="{{ route('checkout.index') }}" class="btn btn--primary btn--block">Checkout</a>
        <a href="{{ route('cart.index') }}" class="btn btn--ghost btn--block">View bag</a>
    </div>
@endif
