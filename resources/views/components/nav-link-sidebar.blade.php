@props(['active', 'icon'])

@php
    $classes =
        $active ?? false
            ? 'flex items-center gap-3 px-4 py-3 rounded-xl text-amber-600 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400 font-bold border border-amber-100 dark:border-amber-900/30 transition-all'
            : 'flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 dark:text-gray-200 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all group';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <i class="bi {{ $icon }} text-lg group-hover:scale-110 transition-transform"></i>
    @endif
    <span class="truncate">{{ $slot }}</span>
</a>
