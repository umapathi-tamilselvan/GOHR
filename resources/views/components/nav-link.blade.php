@props(['active'])

@php
$classes = ($active ?? false)
            ? "flex items-center px-4 py-2 mt-2 text-white bg-{$roleColor}-500 rounded-md"
            : "flex items-center px-4 py-2 mt-2 text-gray-600 transition-colors duration-200 transform rounded-md hover:bg-gray-200 hover:text-gray-700";
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
