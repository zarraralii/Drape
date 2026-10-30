@extends('layouts.shop')

@section('seo_title', 'Wishlist')
@section('seo_noindex', '1')

@section('content')

<x-page-header title="Wishlist" :crumbs="['Wishlist']" />

<section class="section section--flush-top">
    <div class="container">
        @if ($products->isEmpty())
            <div class="empty-state">
                <x-icon name="heart" />
                <p>Nothing saved yet. Tap the heart on anything you love.</p>
                <a href="{{ route('products.index') }}" class="btn btn--primary">Browse the shop</a>
            </div>
        @else
            <div class="product-grid">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </div>
</section>

@endsection
