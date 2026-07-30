@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-6 pe-4 py-2 border-l-2 border-yellow-300 text-start text-sm font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100 transition'
            : 'block w-full ps-6 pe-4 py-2 border-l-2 border-transparent text-start text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
