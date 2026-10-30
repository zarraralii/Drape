@extends('layouts.checkout')

@section('title', 'Order confirmed')

@section('content')

<div class="container confirm">
    <div class="confirm__head">
        <span class="confirm__tick"><x-icon name="check" /></span>
        <div>
            <p class="muted">Order {{ $order->uid }}</p>
            <h1>Thank you{{ $order->contact_name ? ', ' . explode(' ', trim($order->contact_name))[0] : '' }}!</h1>
        </div>
    </div>

    <div class="confirm__grid">
        <div class="card">
            <h2>Your order is confirmed</h2>
            <p>We'll call or message <strong>{{ $order->contact_phone }}</strong> to confirm before dispatch. Delivery takes {{ config('store.delivery.estimate') }}, and you'll pay <strong>{{ money($order->total_amount) }}</strong> in cash when it arrives.</p>
            <p class="muted small">Keep your order number — with your phone number it lets you <a href="{{ route('order.track', ['order_uid' => $order->uid]) }}">track this order</a> at any time.</p>

            <div class="confirm__details">
                <div>
                    <h3>Delivery address</h3>
                    <p>{{ $order->contact_name }}<br>{{ $order->delivery?->full_address }}<br>{{ collect([$order->delivery?->city, $order->delivery?->state, $order->delivery?->country])->filter()->implode(', ') }}</p>
                </div>
                <div>
                    <h3>Payment</h3>
                    <p>{{ $order->payment_method }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Summary</h2>
            <ul class="order-lines">
                @foreach ($order->items as $item)
                    <li>
                        <span>{{ $item->display_name }} @if ($item->variant_label)<small class="muted">{{ $item->variant_label }}</small>@endif × {{ $item->qty }}</span>
                        <span>{{ money($item->item_total) }}</span>
                    </li>
                @endforeach
            </ul>
            <dl class="summary__rows">
                <div><dt>Subtotal</dt><dd>{{ money($order->subtotal) }}</dd></div>
                @if ($order->discount_amount > 0)<div class="is-discount"><dt>Discount @if ($order->coupon_code)<code>{{ $order->coupon_code }}</code>@endif</dt><dd>−{{ money($order->discount_amount) }}</dd></div>@endif
                <div><dt>Delivery</dt><dd>{{ $order->delivery_charges > 0 ? money($order->delivery_charges) : 'Free' }}</dd></div>
                <div class="summary__total"><dt>Total</dt><dd>{{ money($order->total_amount) }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="confirm__actions">
        <a href="{{ route('products.index') }}" class="btn btn--primary">Continue shopping</a>
        @auth
            <a href="{{ route('account.orders') }}" class="btn btn--outline">View my orders</a>
        @else
            <a href="{{ route('register') }}" class="btn btn--outline">Create an account</a>
        @endauth
    </div>
</div>

@endsection
