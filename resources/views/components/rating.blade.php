@props(['value' => 0, 'count' => null])
<span {{ $attributes->merge(['class' => 'rating']) }} aria-label="Rated {{ number_format((float) $value, 1) }} out of 5">
    @for ($i = 1; $i <= 5; $i++)
        <x-icon name="star" class="{{ $i <= round((float) $value) ? 'is-filled' : '' }}" />
    @endfor
    @if ($count !== null)<span class="rating__count">({{ $count }})</span>@endif
</span>
