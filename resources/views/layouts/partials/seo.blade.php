{{--
    Site-wide SEO head tags. Pages override the defaults with:
      @section('seo_title', 'Page name')        – brand name is appended
      @section('seo_description', '…')
      @section('seo_image', 'https://…')
      @section('seo_type', 'product')           – Open Graph type
      @section('seo_noindex', '1')              – keep private pages out of Google
      @push('seo_schema') <script type="application/ld+json">…</script> @endpush
--}}
@php
    $seoBrand = $siteInfo?->brand_name ?: ($brand['name'] ?? config('store.brand.name'));

    $seoPageTitle = html_entity_decode(trim($__env->yieldContent('seo_title')), ENT_QUOTES) ?: ($title ?? null);
    $seoTitle = $seoPageTitle
        ? $seoPageTitle . ' | ' . $seoBrand
        : ($siteInfo?->meta_title ?: $seoBrand . ($siteInfo?->tagline ? ' – ' . $siteInfo->tagline : ''));

    $seoDescription = trim($__env->yieldContent('seo_description'))
        ?: ($siteInfo?->meta_description ?: ($siteInfo?->hero_description ?: $siteInfo?->about))
        ?: 'Shop ' . $seoBrand . ' clothing online — everyday essentials, dresses, shirts and more, delivered to your door.';
    $seoDescription = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode((string) $seoDescription)))), 160);

    $seoImage = html_entity_decode(trim($__env->yieldContent('seo_image')), ENT_QUOTES) ?: ($siteInfo?->home_banner ?: $siteInfo?->logo);
    $seoType = trim($__env->yieldContent('seo_type')) ?: 'website';

    // Canonical: the clean URL (no filters/sort/search), keeping ?page=N.
    $seoPage = (int) request()->query('page', 1);
    $seoCanonical = url()->current() . ($seoPage > 1 ? '?page=' . $seoPage : '');

    // Filtered / sorted listing URLs are near-duplicates of the main one.
    $seoFiltered = collect(request()->query())->except('page')->isNotEmpty();
    $seoNoindex = trim($__env->yieldContent('seo_noindex')) !== '' || $seoFiltered;

    $seoVerification = $siteInfo?->google_site_verification;
    if ($seoVerification && preg_match('/content=["\']([^"\']+)["\']/i', $seoVerification, $m)) {
        $seoVerification = $m[1];
    }
@endphp
<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="{{ $seoNoindex ? 'noindex, follow' : 'index, follow, max-image-preview:large' }}">
<link rel="canonical" href="{{ $seoCanonical }}">
@if ($seoVerification)
<meta name="google-site-verification" content="{{ $seoVerification }}">
@endif

<meta property="og:site_name" content="{{ $seoBrand }}">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoCanonical }}">
<meta property="og:locale" content="en_US">
@if ($seoImage)
<meta property="og:image" content="{{ $seoImage }}">
@endif

<meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
@if ($seoImage)
<meta name="twitter:image" content="{{ $seoImage }}">
@endif

@php
    $seoSameAs = array_values(array_filter([
        $siteInfo?->facebook_link, $siteInfo?->instagram_link, $siteInfo?->tiktok_link, $siteInfo?->youtube_link,
    ]));
@endphp
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $seoBrand,
    'url' => route('home'),
    'logo' => $siteInfo?->logo,
    'email' => $siteInfo?->email,
    'telephone' => $siteInfo?->phone,
    'sameAs' => $seoSameAs ?: null,
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
@if (request()->routeIs('home'))
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => $seoBrand,
    'url' => route('home'),
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => route('search') . '?q={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
@endif
@stack('seo_schema')
