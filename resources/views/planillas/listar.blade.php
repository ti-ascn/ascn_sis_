{{-- resources/views/planillas/listar.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listado General de Trabajadores') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-6">

            <x-success-error-bagdes />

            {{-- ── Tarjetas resumen ───────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

                {{-- Total trabajadores --}}
                <div class="rounded-xl bg-blue-50 border border-blue-100 px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-people-fill text-blue-500 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-blue-400 leading-none mb-1">
                            Total</p>
                        <p class="text-2xl font-bold text-blue-700 leading-none">{{ $trabajadores->count() }}</p>
                    </div>
                </div>

                {{-- Activos --}}
                <div class="rounded-xl bg-green-50 border border-green-100 px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-person-check-fill text-green-500 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-green-400 leading-none mb-1">
                            Activos</p>
                        <p class="text-2xl font-bold text-green-700 leading-none">
                            {{ $trabajadores->whereNull('fecha_cese')->count() }}
                        </p>
                    </div>
                </div>

                {{-- Inactivos --}}
                <div class="rounded-xl bg-red-50 border border-red-100 px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-person-x-fill text-red-400 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-red-400 leading-none mb-1">
                            Inactivos</p>
                        <p class="text-2xl font-bold text-red-600 leading-none">
                            {{ $trabajadores->whereNotNull('fecha_cese')->count() }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- ── Tabla principal ───────────────────────────────────────────── --}}
            <div class="overflow-x-auto bg-white shadow-lg rounded-xl">
                <table class="min-w-full table-auto display nowrap" id="tabla-listado-trabajadores">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-3 border-b text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Empresa</th>
                            <th class="px-4 py-3 border-b text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Estado</th>
                            <th class="px-4 py-3 border-b text-xs font-semibold uppercase tracking-wide text-gray-500">
                                DNI</th>
                            <th class="px-4 py-3 border-b text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Nombre</th>
                            <th class="px-4 py-3 border-b text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Ingreso</th>
                            <th class="px-4 py-3 border-b text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Cese</th>
                            <th class="px-4 py-3 border-b text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Cargo</th>
                            <th class="px-4 py-3 border-b text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Sueldo</th>
                            <th class="px-4 py-3 border-b text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Referido</th>
                            <th class="px-4 py-3 border-b text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trabajadores as $t)
                            <tr class="hover:bg-gray-50 transition">

                                {{-- Empresa --}}
                                <td class="px-4 py-3 border-b font-medium text-gray-800 whitespace-nowrap">
                                    {{ $t->empresa ?? '—' }}
                                </td>

                                {{-- Estado --}}
                                <td class="px-4 py-3 border-b whitespace-nowrap">
                                    @if ($t->fecha_cese)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400 inline-block"></span>
                                            Inactivo
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                            Activo
                                        </span>
                                    @endif
                                </td>

                                {{-- DNI --}}
                                <td class="px-4 py-3 border-b text-gray-700">
                                    {{ $t->dni ?? '—' }}
                                </td>

                                {{-- Nombre --}}
                                <td class="px-4 py-3 border-b font-medium text-gray-800 whitespace-nowrap">
                                    {{ $t->nombre_trabajador ?? '—' }}
                                </td>

                                {{-- Fecha Ingreso --}}
                                <td class="px-4 py-3 border-b text-gray-600 whitespace-nowrap">
                                    {{ $t->fecha_ingreso ? \Carbon\Carbon::parse($t->fecha_ingreso)->format('d/m/Y') : '—' }}
                                </td>

                                {{-- Fecha Cese --}}
                                <td class="px-4 py-3 border-b whitespace-nowrap">
                                    @if ($t->fecha_cese)
                                        <span class="text-red-500 font-semibold">
                                            {{ \Carbon\Carbon::parse($t->salida)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- Cargo --}}
                                <td class="px-4 py-3 border-b text-gray-700 whitespace-nowrap">
                                    {{ $t->cargo ?? '—' }}
                                </td>

                                {{-- Sueldo --}}
                                <td class="px-4 py-3 border-b text-gray-700 whitespace-nowrap">
                                    @if ($t->sueldo !== null)
                                        S/. {{ number_format($t->sueldo, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Referido --}}
                                <td class="px-4 py-3 border-b text-gray-600">
                                    {{ $t->referido ?? '—' }}
                                </td>

                                {{-- Observaciones --}}
                                <td class="px-4 py-3 border-b text-gray-600 max-w-xs">
                                    <div class="max-w-xs truncate" title="{{ $t->observaciones }}">
                                        {{ $t->observaciones ?? '—' }}
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>


{{-- =====================================================================
     STYLES
====================================================================== --}}
<style>
    .overflow-x-auto::-webkit-scrollbar {
        height: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: transparent;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background-color: #9ca3af;
        border-radius: 999px;
    }
</style>


{{-- =====================================================================
     SCRIPTS
====================================================================== --}}
<script>
    $(document).ready(function() {
        $('#tabla-listado-trabajadores').DataTable({
            responsive: false,
            paging: true,
            ordering: true,
            pageLength: 25,
            language: {
                url: '/js/datatables/lang/es-ES.json'
            },
            order: [
                [0, 'asc'],
                [3, 'asc']
            ],
            columnDefs: [{
                // Columna Observaciones: no ordenable
                orderable: false,
                targets: [9]
            }]
        });
    });
</script>
