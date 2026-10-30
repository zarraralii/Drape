@extends('layouts.shop')

@section('seo_title', 'Page not found')
@section('seo_noindex', '1')

@section('content')

<section class="section">
    <div class="container empty-state empty-state--page">
        <p class="eyebrow">404</p>
        <h1 class="display">This page has slipped off the rail</h1>
        <p class="muted">The link may be old, or the item may no longer be available.</p>
        <div class="hero__actions">
            <a href="{{ route('products.index') }}" class="btn btn--primary">Shop all</a>
            <a href="{{ route('home') }}" class="btn btn--outline">Go home</a>
        </div>
    </div>
</section>

@endsection
