@props(['active', 'icon', 'label'])

@php
    $isActive = $active ?? false;
@endphp

<div x-data="{ open: @json($isActive) }" class="w-full">
    <button @click="open = !open"
        class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl transition-all group {{ $isActive ? 'text-amber-600 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30' : 'text-gray-500 dark:text-gray-200 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
        <div class="flex items-center gap-3">
            @if ($icon)
                <i class="bi {{ $icon }} text-lg group-hover:scale-110 transition-transform"></i>
            @endif
            <span class="font-medium truncate">{{ $label }}</span>
        </div>
        <i class="bi bi-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        class="mt-1 ml-4 pl-4 border-l border-gray-100 dark:border-gray-800 space-y-1">
        {{ $slot }}
    </div>
</div>
