{{-- resources/views/cotizaciones/plantillas/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase tracking-tight">
            Gestión de Plantillas Excel
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ view: '{{ request('tab', 'emisores') }}' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-success-error-bagdes />

            {{-- Selector de Vista (Intercambio entre tablas) --}}
            <div class="flex items-center justify-center">
                <div class="inline-flex rounded-md shadow-sm" role="group">
                    <button type="button" @click="view = 'emisores'"
                        :class="view === 'emisores' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                        class="px-6 py-2.5 text-sm font-black uppercase tracking-widest border border-gray-200 rounded-l-lg transition-all duration-200">
                        <i class="bi bi-send-fill mr-2"></i> Emisores
                    </button>
                    <button type="button" @click="view = 'receptores'"
                        :class="view === 'receptores' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                        class="px-6 py-2.5 text-sm font-black uppercase tracking-widest border border-l-0 border-gray-200 rounded-r-lg transition-all duration-200">
                        <i class="bi bi-box-arrow-in-right mr-2"></i> Receptores
                    </button>
                </div>
            </div>

            {{-- ── TABLA: EMISORES ── --}}
            <div x-show="view === 'emisores'" x-cloak class="space-y-4">
                <div
                    class="bg-white dark:bg-[#2a2d35] shadow-lg rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 p-4">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 dt-tabular"
                        id="tablaEmisores">
                        <thead class="bg-gray-50 dark:bg-[#1a1c21]">
                            <tr
                                class="text-left text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                <th class="px-6 py-3">Empresa Emisora</th>
                                <th class="px-6 py-3">RUC</th>
                                <th class="px-6 py-3 text-center">Plantilla Actual</th>
                                <th class="px-6 py-3 text-center">Estado</th>
                                <th class="px-6 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($empresas as $empresa)
                                @php $p = $plantillas->get($empresa->id_empresas_clientes); @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-[#2d3039] transition-colors">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $empresa->razon_social }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $empresa->ruc }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($p)
                                            <a href="{{ route('cotizaciones.plantillas.descargar', $empresa) }}"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/40 transition group border border-green-200 dark:border-green-800">
                                                <i
                                                    class="bi bi-file-earmark-excel-fill text-green-600 group-hover:scale-110 transition"></i>
                                                <span
                                                    class="text-xs font-bold truncate max-w-[150px] text-left">{{ $p->nombre_original }}</span>
                                                <i
                                                    class="bi bi-download text-[10px] opacity-70 group-hover:opacity-100"></i>
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400 italic">No configurada</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($p)
                                            <span
                                                class="px-2.5 py-1 text-[9px] font-black rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 uppercase tracking-tighter shadow-sm border border-green-200 dark:border-green-800">Configurada</span>
                                        @else
                                            <span
                                                class="px-2.5 py-1 text-[9px] font-black rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 uppercase tracking-tighter border border-gray-200 dark:border-gray-700">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-center items-center gap-1.5">
                                            <a href="{{ route('cotizaciones.plantillas.editar', $empresa) }}"
                                                class="w-9 h-9 flex items-center justify-center text-amber-600 hover:bg-amber-100 dark:hover:bg-amber-900/30 rounded-full transition-all border border-transparent hover:border-amber-200"
                                                title="Configurar">
                                                <i class="bi bi-pencil-square text-lg"></i>
                                            </a>
                                            @if ($p)
                                                <form action="{{ route('cotizaciones.plantillas.eliminar', $empresa) }}"
                                                    method="POST" onsubmit="return confirm('¿Eliminar plantilla?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="w-9 h-9 flex items-center justify-center text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-full transition-all border border-transparent hover:border-red-200"
                                                        title="Eliminar">
                                                        <i class="bi bi-trash text-lg"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── TABLA: RECEPTORES (UNIFICADA) ── --}}
            <div x-show="view === 'receptores'" x-cloak class="space-y-4">
                <div
                    class="bg-white dark:bg-[#2a2d35] shadow-lg rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 p-4">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 dt-tabular"
                        id="tablaReceptores">
                        <thead class="bg-gray-50 dark:bg-[#1a1c21]">
                            <tr
                                class="text-left text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                <th class="px-6 py-3">Cliente / Empresa Receptor</th>
                                <th class="px-6 py-3">RUC</th>
                                <th class="px-6 py-3 text-center">Tipo</th>
                                <th class="px-6 py-3 text-center">Formato Solicitud</th>
                                <th class="px-6 py-3 text-center">Formato OC</th>
                                <th class="px-6 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($receptores as $r)
                                @php
                                    $key = $r->tipo_receptor . '_' . $r->id_display;
                                    $p = $plantillasReceptorData->get($key);
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-[#2d3039] transition-colors">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800 dark:text-gray-200">
                                        {{ $r->razon_social }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $r->ruc }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($r->tipo_receptor === 'cliente')
                                            <span
                                                class="px-2 py-0.5 text-[8px] font-black rounded border border-indigo-200 bg-indigo-50 text-indigo-600 uppercase tracking-tighter shadow-sm">AUDITORÍA</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 text-[8px] font-black rounded border border-gray-200 bg-gray-50 text-gray-500 uppercase tracking-tighter shadow-sm">EMPRESA</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($p && $p->archivo_solicitud)
                                            <a href="{{ route('cotizaciones.plantillas.receptor.descargar', [$r->id_display, 'solicitud']) }}?tipo={{ $r->tipo_receptor }}"
                                                class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800 hover:bg-green-100 transition shadow-sm">
                                                <i class="bi bi-download text-xs"></i> <span
                                                    class="text-[9px] font-black uppercase tracking-tighter">XLSX</span>
                                            </a>
                                        @else
                                            <span
                                                class="text-[10px] text-gray-300 dark:text-gray-600 font-bold italic tracking-widest">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($p && $p->archivo_orden_compra)
                                            <a href="{{ route('cotizaciones.plantillas.receptor.descargar', [$r->id_display, 'orden']) }}?tipo={{ $r->tipo_receptor }}"
                                                class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800 hover:bg-green-100 transition shadow-sm">
                                                <i class="bi bi-download text-xs"></i> <span
                                                    class="text-[9px] font-black uppercase tracking-tighter">XLSX</span>
                                            </a>
                                        @else
                                            <span
                                                class="text-[10px] text-gray-300 dark:text-gray-600 font-bold italic tracking-widest">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-center items-center gap-1.5">
                                            <a href="{{ route('cotizaciones.plantillas.receptor.editar', $r->id_display) }}?tipo={{ $r->tipo_receptor }}"
                                                class="w-9 h-9 flex items-center justify-center text-indigo-600 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 rounded-full transition-all border border-transparent hover:border-indigo-200"
                                                title="Configurar">
                                                <i class="bi bi-pencil-square text-lg"></i>
                                            </a>
                                            @if ($p)
                                                <form
                                                    action="{{ route('cotizaciones.plantillas.receptor.eliminar', $r->id_display) }}?tipo={{ $r->tipo_receptor }}"
                                                    method="POST" onsubmit="return confirm('¿Eliminar plantillas?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="w-9 h-9 flex items-center justify-center text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-full transition-all border border-transparent hover:border-red-200"
                                                        title="Eliminar">
                                                        <i class="bi bi-trash text-lg"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Inicialización de DataTables --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.dt-tabular').DataTable({
                pageLength: 25,
                responsive: true,
                language: {
                    url: "/js/datatables/lang/es-ES.json"
                },
                order: [
                    [0, 'asc']
                ]
            });
        });
    </script>
</x-app-layout>
