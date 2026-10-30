@extends('layouts.shop')

@section('seo_title', 'Our Story')
@section('seo_description', $story['description'])

@section('content')

<section class="story-hero">
    @if ($banner)<img src="{{ $banner }}" alt="" class="story-hero__bg">@endif
    <div class="container story-hero__content">
        <p class="eyebrow">{{ $story['eyebrow'] }}</p>
        <h1 class="display display--xl">{{ $story['headline'] }}</h1>
        <p class="story-hero__text">{{ $story['description'] }}</p>
        <a href="{{ safe_link($story['ctaLink'], route('products.index')) }}" class="btn btn--primary">{{ $story['ctaLabel'] }}</a>
    </div>
</section>

@if ($about)
    <section class="section">
        <div class="container prose prose--center rich-text">{!! $about !!}</div>
    </section>
@endif

@foreach ($sections as $section)
    <section class="section">
        <div class="container story-block {{ $loop->odd ? '' : 'story-block--reverse' }}">
            <div class="story-block__media">
                @if ($section->image)
                    <img src="{{ $section->image }}" alt="" loading="lazy">
                @else
                    <div class="editorial__art" aria-hidden="true"></div>
                @endif
            </div>
            <div class="story-block__copy">
                <h2 class="display">{{ $section->title }}</h2>
                <div class="rich-text">{!! $section->description !!}</div>
                @if ($section->cta_label && $section->cta_link)
                    <a href="{{ safe_link($section->cta_link, route('products.index')) }}" class="btn btn--outline">{{ $section->cta_label }}</a>
                @endif
            </div>
        </div>
    </section>
@endforeach

@endsection
