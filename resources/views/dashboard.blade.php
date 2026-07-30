<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Menú de inicio') }}
        </h2>
    </x-slot>

    <div class="py-1">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#2a2d35] overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __('Bienvenido,') }} {{ Auth::user()->name }}

                    {{-- ACTUALIZACIONES --}}
                    {{-- <div class="mt-6 bg-yellow-100 border-l-4 border-yellow-500 p-4">
                        <p class="font-semibold">📢 Novedades del sistema:</p>
                        <ul class="list-disc list-inside mt-2 text-sm">
                            <li><strong>Nuevo atajo:</strong> Presiona <kbd>Alt</kbd> + <kbd>F</kbd> para buscar más
                                rápido en tablas.</li>
                        </ul>
                    </div> --}}
                    @if (Auth()->user()->id == '1')
                        <div x-data="{ html: null }" x-init="setInterval(() => {
                            fetch('{{ route('usuarios.online.partial') }}')
                                .then(res => res.text())
                                .then(text => html = text);
                        }, 10000);">
                            <div x-show="!html">
                                <x-usuarios-online />
                            </div>
                            <div x-show="html" x-html="html"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
