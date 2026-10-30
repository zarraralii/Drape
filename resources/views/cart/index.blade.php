@extends('layouts.shop')

@section('seo_title', 'Your bag')
@section('seo_noindex', '1')

@section('content')

<x-page-header title="Your bag" />

<section class="section section--flush-top">
    <div class="container">
        @if ($items->isEmpty())
            <div class="empty-state">
                <x-icon name="bag" />
                <p>Your bag is empty.</p>
                <a href="{{ route('products.index') }}" class="btn btn--primary">Continue shopping</a>
            </div>
        @else
            <div class="cart-layout">
                <div>
                    <ul class="line-items line-items--page">
                        @foreach ($items as $item)
                            @include('cart.partials.line', ['item' => $item])
                        @endforeach
                    </ul>
                    <a href="{{ route('products.index') }}" class="link-arrow">Continue shopping <x-icon name="arrow-right" /></a>
                </div>

                <aside class="summary card">
                    <h2 class="summary__title">Order summary</h2>
                    @include('cart.partials.shipping-meter')

                    @if ($coupon)
                        <form method="POST" action="{{ route('cart.coupon.remove') }}" class="applied-code">
                            @csrf
                            <span><x-icon name="check" /> <code>{{ $coupon->code }}</code> applied</span>
                            <button type="submit" class="link-btn">Remove</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('cart.coupon.apply') }}" class="inline-form inline-form--boxed">
                            @csrf
                            <label for="cart-code" class="visually-hidden">Discount code</label>
                            <input type="text" id="cart-code" name="code" placeholder="Discount code" autocomplete="off" required>
                            <button type="submit" class="btn btn--outline btn--sm">Apply</button>
                        </form>
                    @endif

                    <dl class="summary__rows">
                        <div><dt>Subtotal</dt><dd>{{ money($subtotal) }}</dd></div>
                        @if ($discount > 0)<div class="is-discount"><dt>Discount</dt><dd>−{{ money($discount) }}</dd></div>@endif
                        <div><dt>Delivery</dt><dd>{{ $delivery > 0 ? money($delivery) : 'Free' }}</dd></div>
                        <div class="summary__total"><dt>Total</dt><dd>{{ money($total) }}</dd></div>
                    </dl>
                    <a href="{{ route('checkout.index') }}" class="btn btn--primary btn--block">Checkout</a>
                    <p class="summary__note"><x-icon name="cash" /> Cash on delivery · {{ config('store.delivery.estimate') }}</p>
                </aside>
            </div>
        @endif
    </div>
</section>

@endsection
