@extends('layouts.shop')

@section('seo_title', $title)
@if ($category?->description)
    @section('seo_description', $category->description)
@endif

@php
    $listUrl = $category ? route('collections.show', $category) : route('products.index');
    $keep = array_filter(['q' => $search ?: null, 'sort' => request('sort')]);
@endphp

@section('content')

<x-page-header :title="$title" :crumbs="$category ? [['label' => 'Collections', 'url' => route('collections.index')], $category->name] : [$title]" :image="$category?->banner_image">
    @if ($category?->description){{ $category->description }}@elseif ($search !== '')Showing results for “{{ $search }}”@endif
</x-page-header>

<section class="section section--flush-top">
    <div class="container">
        <div class="listing" data-listing>
            <aside class="drawer drawer--left drawer--sidebar" data-drawer="filters" aria-hidden="true">
                <div class="drawer__backdrop" data-drawer-close></div>
                <div class="drawer__panel" role="dialog" aria-label="Filters">
                    <div class="drawer__head">
                        <span class="drawer__title">Filter</span>
                        <button type="button" class="icon-btn" data-drawer-close aria-label="Close filters"><x-icon name="close" /></button>
                    </div>
                    <form method="GET" action="{{ $listUrl }}" class="drawer__body filters" data-filter-form>
                        @foreach ($keep as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach

                        <details class="filter" open>
                            <summary>Availability</summary>
                            <label class="check"><input type="checkbox" name="in_stock" value="1" @checked($inStock)> <span>In stock only</span></label>
                            <label class="check"><input type="checkbox" name="sale" value="1" @checked($onSale)> <span>On sale</span></label>
                        </details>

                        @if ($categories->isNotEmpty())
                            <details class="filter" open>
                                <summary>Collection</summary>
                                @foreach ($categories as $cat)
                                    <label class="check"><input type="checkbox" name="category[]" value="{{ $cat->id }}" @checked(in_array($cat->id, $categoryIds))> <span>{{ $cat->name }}</span></label>
                                @endforeach
                            </details>
                        @endif

                        @foreach ($optionFilters as $optionName => $values)
                            @php($isColor = \App\Support\Swatches::isColorOption($optionName))
                            <details class="filter" open>
                                <summary>{{ $optionName }}</summary>
                                <div class="{{ $isColor ? 'filter__swatches' : 'filter__pills' }}">
                                    @foreach ($values as $value)
                                        @php($checked = in_array($value, $selectedOptions[$optionName] ?? [], true))
                                        <label class="{{ $isColor ? 'swatch-check' : 'pill-check' }}" title="{{ $value }}">
                                            <input type="checkbox" name="filter[{{ $optionName }}][]" value="{{ $value }}" @checked($checked)>
                                            @if ($isColor)
                                                <span class="swatch-check__dot" style="--swatch: {{ \App\Support\Swatches::color($optionName, $value) }}"></span>
                                                <span class="swatch-check__label">{{ $value }}</span>
                                            @else
                                                <span>{{ $value }}</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach

                        @if ($priceCeil > $priceFloor)
                            <details class="filter" open>
                                <summary>Price</summary>
                                <div class="filter__price">
                                    <label>
                                        <span class="visually-hidden">Minimum price</span>
                                        <input type="number" name="min_price" min="0" step="1" placeholder="{{ (int) $priceFloor }}" value="{{ $minPrice !== null ? (int) $minPrice : '' }}" inputmode="numeric">
                                    </label>
                                    <span>to</span>
                                    <label>
                                        <span class="visually-hidden">Maximum price</span>
                                        <input type="number" name="max_price" min="0" step="1" placeholder="{{ (int) $priceCeil }}" value="{{ $maxPrice !== null ? (int) $maxPrice : '' }}" inputmode="numeric">
                                    </label>
                                </div>
                                <small class="muted">{{ \App\Support\Store::currency() }}</small>
                            </details>
                        @endif

                        <div class="filters__actions">
                            <button type="submit" class="btn btn--primary btn--block">Apply filters</button>
                            @if ($activeFilterCount)
                                <a href="{{ $listUrl . ($keep ? '?' . http_build_query($keep) : '') }}" class="btn btn--ghost btn--block">Clear all</a>
                            @endif
                        </div>
                    </form>
                </div>
            </aside>

            <div class="listing__main">
                <div class="toolbar">
                    <button type="button" class="toolbar__filter-btn" data-drawer-open="filters">
                        <x-icon name="filter" /> Filter @if ($activeFilterCount)<span class="count-pill">{{ $activeFilterCount }}</span>@endif
                    </button>
                    <p class="toolbar__count">{{ $products->total() }} {{ Str::plural('product', $products->total()) }}</p>
                    <form method="GET" action="{{ $listUrl }}" class="toolbar__sort">
                        {{-- Keep every current filter (nested ones too) when re-sorting. --}}
                        @foreach (array_filter(explode('&', http_build_query(request()->except(['sort', 'page'])))) as $pair)
                            @php([$pairName, $pairValue] = array_pad(explode('=', $pair, 2), 2, ''))
                            <input type="hidden" name="{{ urldecode($pairName) }}" value="{{ urldecode($pairValue) }}">
                        @endforeach
                        <label for="sort" class="visually-hidden">Sort by</label>
                        <div class="select">
                            <select id="sort" name="sort" data-autosubmit>
                                @foreach ($sorts as $value => $label)
                                    <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-icon name="chevron-down" />
                        </div>
                    </form>
                </div>

                @if ($activeFilterCount)
                    <ul class="active-filters">
                        @foreach ($selectedOptions as $optionName => $values)
                            @foreach ($values as $value)
                                <li><a href="{{ request()->fullUrlWithQuery(['filter' => collect($selectedOptions)->map(fn ($v, $k) => $k === $optionName ? array_values(array_diff($v, [$value])) : $v)->filter()->all(), 'page' => null]) }}">{{ $value }} <x-icon name="close" /></a></li>
                            @endforeach
                        @endforeach
                        @if ($inStock)<li><a href="{{ request()->fullUrlWithQuery(['in_stock' => null, 'page' => null]) }}">In stock <x-icon name="close" /></a></li>@endif
                        @if ($onSale)<li><a href="{{ request()->fullUrlWithQuery(['sale' => null, 'page' => null]) }}">On sale <x-icon name="close" /></a></li>@endif
                        @if ($minPrice !== null || $maxPrice !== null)<li><a href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null, 'page' => null]) }}">{{ money($minPrice ?? $priceFloor) }} – {{ money($maxPrice ?? $priceCeil) }} <x-icon name="close" /></a></li>@endif
                    </ul>
                @endif

                @if ($products->isEmpty())
                    <div class="empty-state">
                        <p>No products match these filters.</p>
                        <a href="{{ $listUrl }}" class="btn btn--outline">Clear filters</a>
                    </div>
                @else
                    <div class="product-grid product-grid--listing">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" :eager="$loop->index < 4" />
                        @endforeach
                    </div>

                    {{ $products->links('vendor.pagination.drape') }}
                @endif
            </div>
        </div>
    </div>
</section>

@endsection
