@extends('layouts.shop')

@section('seo_title', 'Collections')

@section('content')

<x-page-header title="Collections" :crumbs="['Collections']" />

<section class="section section--flush-top">
    <div class="container">
        @if ($collections->isEmpty())
            <div class="empty-state">
                <p>Collections are coming soon.</p>
                <a href="{{ route('products.index') }}" class="btn btn--primary">Shop all products</a>
            </div>
        @else
            <div class="collection-grid collection-grid--3">
                <a href="{{ route('products.index') }}" class="collection-tile">
                    <div class="collection-tile__media"><span class="collection-tile__monogram" aria-hidden="true">All</span></div>
                    <div class="collection-tile__label"><span>Shop all</span><x-icon name="arrow-right" /></div>
                </a>
                @foreach ($collections as $collection)
                    <a href="{{ route('collections.show', $collection) }}" class="collection-tile">
                        <div class="collection-tile__media">
                            @if ($collection->cover_image)
                                <img src="{{ $collection->cover_image }}" alt="" loading="lazy">
                            @else
                                <span class="collection-tile__monogram" aria-hidden="true">{{ mb_substr($collection->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="collection-tile__label">
                            <span>{{ $collection->name }} <small>{{ $collection->products_count }} {{ Str::plural('item', $collection->products_count) }}</small></span>
                            <x-icon name="arrow-right" />
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

@endsection
