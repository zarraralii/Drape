@props(['amount', 'compare' => null, 'from' => false])
<span {{ $attributes->merge(['class' => 'price' . ($compare ? ' price--sale' : '')]) }}>
    @if ($from)<span class="price__from">From</span>@endif
    <span class="price__current">{{ money($amount) }}</span>
    @if ($compare)
        <s class="price__compare"><span class="visually-hidden">Regular price</span>{{ money($compare) }}</s>
    @endif
</span>
