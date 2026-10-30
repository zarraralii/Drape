@extends('layouts.shop')

@section('seo_title', 'Track your order')
@section('seo_noindex', '1')

@section('content')

<x-page-header title="Track your order" :crumbs="['Track order']">
    Enter your order number and the mobile number you used at checkout.
</x-page-header>

<section class="section section--flush-top">
    <div class="container account--narrow">
        <form method="GET" action="{{ route('order.track') }}" class="card track-form">
            <div class="form-grid">
                <div class="field">
                    <label for="order_uid">Order number</label>
                    <input type="text" id="order_uid" name="order_uid" value="{{ $orderUid }}" placeholder="e.g. DR-7KQ2M9XA" required autocomplete="off">
                </div>
                <div class="field">
                    <label for="phone">Mobile number</label>
                    <input type="tel" id="phone" name="phone" value="{{ $phone }}" required autocomplete="tel">
                </div>
            </div>
            <button type="submit" class="btn btn--primary">Track order</button>
        </form>

        @if ($searched)
            @if ($order)
                <x-order-status-card :order="$order" />
            @else
                <div class="alert alert--error" role="alert">We couldn't find an order with those details. Check the order number and the mobile number used at checkout.</div>
            @endif
        @endif
    </div>
</section>

@endsection
