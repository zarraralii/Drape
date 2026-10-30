<div class="order-summary">
    <ul class="order-summary__items">
        @foreach ($items as $item)
            <li>
                <span class="order-summary__thumb">
                    @if ($item['image'])<img src="{{ $item['image'] }}" alt="" loading="lazy">@endif
                    <span class="order-summary__qty">{{ $item['qty'] }}</span>
                </span>
                <span class="order-summary__name">
                    {{ $item['product']->name }}
                    @if ($item['label'])<small>{{ $item['label'] }}</small>@endif
                </span>
                <span>{{ money($item['line_total']) }}</span>
            </li>
        @endforeach
    </ul>

    @if ($coupon)
        <form method="POST" action="{{ route('cart.coupon.remove') }}" class="applied-code">
            @csrf
            <span><x-icon name="check" /> <code>{{ $coupon->code }}</code></span>
            <button type="submit" class="link-btn">Remove</button>
        </form>
    @else
        <form method="POST" action="{{ route('cart.coupon.apply') }}" class="inline-form inline-form--boxed">
            @csrf
            <label class="visually-hidden">Discount code</label>
            <input type="text" name="code" placeholder="Discount code" autocomplete="off" required>
            <button type="submit" class="btn btn--outline btn--sm">Apply</button>
        </form>
    @endif

    <dl class="summary__rows">
        <div><dt>Subtotal</dt><dd>{{ money($subtotal) }}</dd></div>
        @if ($discount > 0)<div class="is-discount"><dt>Discount</dt><dd>−{{ money($discount) }}</dd></div>@endif
        <div><dt>Delivery</dt><dd>{{ $delivery > 0 ? money($delivery) : 'Free' }}</dd></div>
        <div class="summary__total"><dt>Total</dt><dd><small>{{ \App\Support\Store::currency() }}</small> {{ number_format($total, floor($total) == $total ? 0 : 2) }}</dd></div>
    </dl>
</div>
