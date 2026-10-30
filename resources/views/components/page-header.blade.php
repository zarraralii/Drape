@props(['title', 'eyebrow' => null, 'crumbs' => [], 'image' => null])
<section {{ $attributes->merge(['class' => 'page-header' . ($image ? ' page-header--image' : '')]) }} @if ($image) style="--page-header-image: url('{{ $image }}')" @endif>
    <div class="container">
        @if ($crumbs)
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <ol>
                    <li><a href="{{ route('home') }}">Home</a></li>
                    @foreach ($crumbs as $crumb)
                        @if (is_array($crumb))
                            <li><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
                        @else
                            <li aria-current="page">{{ $crumb }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
        @if ($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
        <h1 class="page-header__title">{{ $title }}</h1>
        @if (trim($slot))<div class="page-header__text">{{ $slot }}</div>@endif
    </div>
</section>
