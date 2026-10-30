@extends('layouts.shop')

@section('seo_title', $search !== '' ? 'Search: ' . $search : 'Search')
@section('seo_noindex', '1')

@section('content')

<x-page-header title="Search" />

<section class="section section--flush-top">
    <div class="container">
        <form method="GET" action="{{ route('search') }}" class="search-page__form" role="search">
            <label for="search-page-q" class="visually-hidden">Search</label>
            <input type="search" id="search-page-q" name="q" value="{{ $search }}" placeholder="Search products" autofocus>
            <button type="submit" class="btn btn--primary">Search</button>
        </form>

        @if ($search !== '')
            <p class="toolbar__count">{{ $products->total() }} {{ Str::plural('result', $products->total()) }} for “{{ $search }}”</p>
        @else
            <h2 class="section-title">New arrivals</h2>
        @endif

        @if ($products->isEmpty())
            <div class="empty-state">
                <p>No products found. Try a different word, or browse everything.</p>
                <a href="{{ route('products.index') }}" class="btn btn--outline">Shop all</a>
            </div>
        @else
            <div class="product-grid">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            @if ($search !== '')
                {{ $products->links('vendor.pagination.drape') }}
            @endif
        @endif
    </div>
</section>

@endsection
