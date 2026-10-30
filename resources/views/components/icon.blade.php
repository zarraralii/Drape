@props(['name'])
<svg {{ $attributes->merge(['class' => 'icon icon-' . $name]) }} aria-hidden="true" focusable="false"><use href="#i-{{ $name }}"></use></svg>
