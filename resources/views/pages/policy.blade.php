@extends('layouts.shop')

@section('seo_title', $title)

@section('content')

<x-page-header :title="$title" :crumbs="[$title]" :image="$banner" />

<section class="section section--flush-top">
    <div class="container prose">
        @if ($content)
            {!! nl2br(e($content)) !!}
        @else
            <p class="muted">This policy will be published soon. If you have a question in the meantime, please <a href="{{ route('contact') }}">contact us</a>.</p>
        @endif
    </div>
</section>

@endsection
