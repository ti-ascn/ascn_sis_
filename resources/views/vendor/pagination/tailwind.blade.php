@if ($paginator->hasPages())

    <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-center mt-6">
        <ul class="flex items-center space-x-2 select-none">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="px-3 py-1 rounded-full bg-gray-200 text-gray-400 cursor-not-allowed">
                    ‹
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}"
                        class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-300 transition">
                        ‹
                    </a>
                </li>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="px-3 py-1 rounded-full bg-gray-200 text-gray-500">…</li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="px-4 py-1 rounded-full bg-yellow-600 text-white font-semibold shadow">
                                {{ $page }}
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}"
                                    class="px-4 py-1 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-300 transition">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}"
                        class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-300 transition">
                        ›
                    </a>
                </li>
            @else
                <li class="px-3 py-1 rounded-full bg-gray-200 text-gray-400 cursor-not-allowed">
                    ›
                </li>
            @endif

        </ul>
    </nav>

    {{-- Texto debajo --}}
    <div class="text-sm text-gray-600 text-center mt-2">
        Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }}
        de {{ $paginator->total() }} resultados
    </div>

@endif
