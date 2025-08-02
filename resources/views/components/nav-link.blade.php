@props(['active'])

@php
$classes = ($active ?? false)
            ? "block w-full"
            : "block w-full";
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
