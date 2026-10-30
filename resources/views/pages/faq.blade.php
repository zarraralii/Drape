@extends('layouts.shop')

@section('seo_title', 'FAQs')

@if ($faqs->isNotEmpty())
@push('seo_schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $faqs->map(fn ($faq) => [
        '@type' => 'Question',
        'name' => $faq->question,
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags((string) $faq->answer)],
    ])->values()->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
@endpush
@endif

@section('content')

<x-page-header title="Frequently asked questions" :crumbs="['FAQs']" :image="$banner" />

<section class="section section--flush-top">
    <div class="container prose">
        @if ($faqs->isEmpty())
            <p class="muted">No FAQs yet — <a href="{{ route('contact') }}">ask us anything</a>.</p>
        @else
            <div class="accordion accordion--faq">
                @foreach ($faqs as $faq)
                    <details @if ($loop->first) open @endif>
                        <summary>{{ $faq->question }} <x-icon name="plus" /></summary>
                        <div class="accordion__body">{!! nl2br(e($faq->answer)) !!}</div>
                    </details>
                @endforeach
            </div>
        @endif
        <p class="muted">Still need help? <a href="{{ route('contact') }}">Contact us</a>.</p>
    </div>
</section>

@endsection
