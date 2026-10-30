@extends('layouts.shop')

@section('seo_title', 'Size guide')

@section('content')

<x-page-header title="Size guide" :crumbs="['Size guide']" />

<section class="section section--flush-top">
    <div class="container prose">
        @include('pages.partials.size-guide-content', ['content' => $content])
    </div>
</section>

@endsection
