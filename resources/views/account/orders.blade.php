@extends('layouts.shop')

@section('seo_title', 'My account')
@section('seo_noindex', '1')

@section('content')

@include('account.partials.header', ['active' => 'orders'])

<section class="section section--flush-top">
    <div class="container account">
        @if ($orders->isEmpty())
            <div class="empty-state">
                <x-icon name="package" />
                <p>You haven't placed any orders yet.</p>
                <a href="{{ route('products.index') }}" class="btn btn--primary">Start shopping</a>
            </div>
        @else
            <div class="stack">
                @foreach ($orders as $order)
                    <x-order-status-card :order="$order" :allow-reviews="true" />
                @endforeach
            </div>
        @endif
    </div>
</section>

@endsection
