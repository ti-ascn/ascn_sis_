{{-- resources/views/contratos_recepcion/index.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Contratos de Recepción') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-6">

            <x-success-error-bagdes />

            {{-- ✅ BOTÓN GLOBAL --}}
            <div class="flex justify-end mb-4">
                <button type="button" id="btnVerTodosContratos"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow">
                    <i class="bi bi-archive"></i> Ver todos los contratos
                </button>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap" id="myTable-contratos">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-4 border-b">RUC</th>
                            <th class="px-4 py-4 border-b">N° COTIZACIÓN</th>
                            <th class="px-4 py-4 border-b">EMPRESA</th>
                            <th class="px-4 py-4 border-b">CORREO</th>
                            <th class="px-4 py-4 border-b">VENCIMIENTO</th>
                            <th class="px-4 py-4 border-b">FECHA INICIO</th>
                            <th class="px-4 py-4 border-b">FECHA FIN</th>
                            <th class="px-4 py-4 border-b text-center">COTIZACIÓN</th>
                            <th class="px-4 py-4 border-b text-center">CONTRATO</th>
                            <th class="px-4 py-4 border-b">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cotizaciones as $cotizacion)
                            @php
                                $contrato = $cotizacion->contratoPrincipal;
                                $hoy = \Carbon\Carbon::today();

                                if ($contrato) {
                                    $fechaFin = \Carbon\Carbon::parse($contrato->fecha_fin);
                                    $diff = $hoy->diffInDays($fechaFin, false);
                                    if ($diff > 6) {
                                        $vText = $diff . ' días restantes';
                                        $vColor = 'text-green-600 font-semibold';
                                    } elseif ($diff > 0) {
                                        $vText = $diff . ' días restantes';
                                        $vColor = 'text-orange-500 font-semibold';
                                    } elseif ($diff == 0) {
                                        $vText = 'Vence hoy';
                                        $vColor = 'text-red-600 font-bold';
                                    } else {
                                        $vText = 'Venció hace ' . abs($diff) . ' días';
                                        $vColor = 'text-red-500 font-semibold';
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-gray-50" id="fila-cotizacion-{{ $cotizacion->id_cotizacion }}">

                                <td class="px-4 py-4 border-b">{{ $cotizacion->empresaCliente->ruc ?? '—' }}</td>
                                <td class="px-4 py-4 border-b font-medium">{{ $cotizacion->nro_cotizacion ?? '—' }}</td>
                                <td class="px-4 py-4 border-b font-medium">
                                    {{ $cotizacion->empresaCliente->razon_social ?? '—' }}</td>
                                <td class="px-4 py-4 border-b font-medium">
                                    @if (!empty($cotizacion->empresaCliente->correo))
                                        <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode($cotizacion->empresaCliente->correo) }}"
                                            target="_blank" class="text-blue-600 hover:underline">
                                            {{ $cotizacion->empresaCliente->correo }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-4 border-b vencimiento-{{ $cotizacion->id_cotizacion }}">
                                    @if ($contrato)
                                        <span class="{{ $vColor }}">{{ $vText }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 border-b fecha-inicio-{{ $cotizacion->id_cotizacion }}">
                                    {{ $contrato ? $contrato->fecha_inicio->format('d/m/Y') : '—' }}
                                </td>

                                <td class="px-4 py-4 border-b fecha-fin-{{ $cotizacion->id_cotizacion }}">
                                    {{ $contrato ? $contrato->fecha_fin->format('d/m/Y') : '—' }}
                                </td>

                                {{-- COTIZACIÓN: solo icono PDF + tooltip --}}
                                <td
                                    class="px-4 py-4 border-b text-center archivo-cotizacion-{{ $cotizacion->id_cotizacion }}">
                                    @if ($contrato && $contrato->archivo_cotizacion)
                                        <a href="{{ asset($contrato->archivo_cotizacion) }}" target="_blank"
                                            class="tooltip" style="color: #e53e3e;"
                                            onmouseover="this.style.color='#c53030';"
                                            onmouseout="this.style.color='#e53e3e';">

                                            <i class="bi bi-file-earmark-pdf text-lg"></i>
                                            <span class="tooltip-text">Ver cotización</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- CONTRATO: solo icono PDF + tooltip --}}
                                <td
                                    class="px-4 py-4 border-b text-center archivo-contrato-{{ $cotizacion->id_cotizacion }}">
                                    @if ($contrato && $contrato->archivo_contrato)
                                        <a href="{{ asset($contrato->archivo_contrato) }}" target="_blank"
                                            class="tooltip" style="color: #e53e3e;"
                                            onmouseover="this.style.color='#c53030';"
                                            onmouseout="this.style.color='#e53e3e';">

                                            <i class="bi bi-file-earmark-pdf text-lg"></i>
                                            <span class="tooltip-text">Ver contrato</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 border-b">
                                    <div
                                        class="flex items-center gap-2 acciones-cell-{{ $cotizacion->id_cotizacion }}">
                                        @if ($contrato)
                                            @if (Auth::user()->area->nombre != 'LEGAL')
                                                <a role="button"
                                                    class="flex items-center rounded-md tooltip btn-editar-contrato"
                                                    style="color: #e6a817;" onmouseover="this.style.color='#f5bc42';"
                                                    onmouseout="this.style.color='#e6a817';"
                                                    data-id="{{ $contrato->id_contrato_recepcion }}"
                                                    data-cotizacion="{{ $cotizacion->id_cotizacion }}"
                                                    data-fecha-inicio="{{ $contrato->fecha_inicio->format('Y-m-d') }}"
                                                    data-fecha-fin="{{ $contrato->fecha_fin->format('Y-m-d') }}"
                                                    data-empresa="{{ $cotizacion->empresaCliente->razon_social ?? '' }}"
                                                    data-ruc="{{ $cotizacion->empresaCliente->ruc ?? '' }}"
                                                    data-archivo-cotizacion="{{ $contrato->archivo_cotizacion ?? '' }}"
                                                    data-archivo-contrato="{{ $contrato->archivo_contrato ?? '' }}">
                                                    <svg style="width:20px;height:20px;" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                    </svg>
                                                    <span class="tooltip-text">Editar</span>
                                                </a>
                                            @endif
                                            <a role="button"
                                                class="flex items-center rounded-md tooltip btn-ver-historial"
                                                style="color: #2986cc;" onmouseover="this.style.color='#2ba4ff';"
                                                onmouseout="this.style.color='#2986cc';"
                                                data-cotizacion="{{ $cotizacion->id_cotizacion }}"
                                                data-empresa="{{ $cotizacion->empresaCliente->razon_social ?? '' }}"
                                                data-ruc="{{ $cotizacion->empresaCliente->ruc ?? '' }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
                                                </svg>
                                                <span class="tooltip-text">Historial</span>
                                            </a>
                                            @if (Auth::user()->area->nombre != 'LEGAL')
                                                <a role="button"
                                                    class="flex items-center rounded-md tooltip btn-eliminar-contrato"
                                                    style="color: #cc2929;" onmouseover="this.style.color='#ff4444';"
                                                    onmouseout="this.style.color='#cc2929';"
                                                    data-id="{{ $contrato->id_contrato_recepcion }}"
                                                    data-cotizacion="{{ $cotizacion->id_cotizacion }}">
                                                    <svg style="width:20px;height:20px;" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                    <span class="tooltip-text">Eliminar</span>
                                                </a>
                                                {{-- Crear Contrato siempre visible --}}
                                                <a role="button"
                                                    class="flex items-center rounded-md tooltip btn-crear-contrato"
                                                    style="color: #28a745;" onmouseover="this.style.color='#34d058';"
                                                    onmouseout="this.style.color='#28a745';"
                                                    data-cotizacion="{{ $cotizacion->id_cotizacion }}"
                                                    data-empresa="{{ $cotizacion->empresaCliente->razon_social ?? '' }}"
                                                    data-ruc="{{ $cotizacion->empresaCliente->ruc ?? '' }}">
                                                    <svg style="width:20px;height:20px;" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                                                    </svg>
                                                    <span class="tooltip-text">Crear Contrato</span>
                                                </a>
                                            @endif
                                        @else
                                            @if (Auth::user()->area->nombre != 'LEGAL')
                                                <a role="button"
                                                    class="flex items-center rounded-md tooltip btn-crear-contrato"
                                                    style="color: #28a745;" onmouseover="this.style.color='#34d058';"
                                                    onmouseout="this.style.color='#28a745';"
                                                    data-cotizacion="{{ $cotizacion->id_cotizacion }}"
                                                    data-empresa="{{ $cotizacion->empresaCliente->razon_social ?? '' }}"
                                                    data-ruc="{{ $cotizacion->empresaCliente->ruc ?? '' }}">
                                                    <svg style="width:20px;height:20px;" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                                                    </svg>
                                                    <span class="tooltip-text">Crear Contrato</span>
                                                </a>
                                            @endif
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
</x-app-layout>

{{-- =====================================================================
     MODAL: CREAR / EDITAR CONTRATO
====================================================================== --}}
<x-modal-show id="modalContrato" title="Crear Contrato">
    <form id="formContrato" class="space-y-4" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="contrato_id" name="contrato_id">
        <input type="hidden" id="contrato_cotizacion_id" name="id_cotizacion">
        <input type="hidden" id="contrato_desde_historial" value="0">
        <div>
            <x-input-label for="contrato_empresa" value="Empresa" />
            <input type="text" id="contrato_empresa"
                class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
        </div>
        <div>
            <x-input-label for="contrato_ruc" value="RUC" />
            <input type="text" id="contrato_ruc"
                class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
        </div>
        <div>
            <x-input-label for="contrato_fecha_inicio" value="Fecha Inicio" />
            <input type="date" id="contrato_fecha_inicio" name="fecha_inicio" required
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
        </div>
        <div>
            <x-input-label for="contrato_fecha_fin" value="Fecha Fin" />
            <input type="date" id="contrato_fecha_fin" name="fecha_fin" required
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
        </div>
        <div>
            <x-input-label for="contrato_archivo_cotizacion" value="Archivo Cotización (PDF)" />
            <div id="cotizacion_archivo_actual"
                class="hidden mb-2 flex items-center gap-2 text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-md px-3 py-2">
                <i class="bi bi-file-earmark-pdf text-red-500"></i>
                <span id="cotizacion_archivo_nombre" class="truncate max-w-xs text-xs"></span>
                <a id="cotizacion_archivo_ver" href="#" target="_blank"
                    class="ml-auto text-red-500 hover:text-red-700 text-xs font-medium flex items-center gap-1 flex-shrink-0">
                    <i class="bi bi-eye"></i> Ver
                </a>
            </div>
            <input type="file" id="contrato_archivo_cotizacion" name="archivo_cotizacion"
                accept="application/pdf"
                class="block w-full border border-gray-300 rounded-md shadow-sm text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-400 mt-1">Máximo 10 MB. Solo archivos PDF.</p>
        </div>
        <div>
            <x-input-label for="contrato_archivo_contrato" value="Archivo Contrato (PDF)" />
            <div id="contrato_archivo_actual"
                class="hidden mb-2 flex items-center gap-2 text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-md px-3 py-2">
                <i class="bi bi-file-earmark-pdf text-red-500"></i>
                <span id="contrato_archivo_nombre" class="truncate max-w-xs text-xs"></span>
                <a id="contrato_archivo_ver" href="#" target="_blank"
                    class="ml-auto text-red-500 hover:text-red-700 text-xs font-medium flex items-center gap-1 flex-shrink-0">
                    <i class="bi bi-eye"></i> Ver
                </a>
            </div>
            <input type="file" id="contrato_archivo_contrato" name="archivo_contrato" accept="application/pdf"
                class="block w-full border border-gray-300 rounded-md shadow-sm text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-400 mt-1">Máximo 10 MB. Solo archivos PDF.</p>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" onclick="toggleModal('modalContrato')"
                class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition text-white">
                Cancelar
            </button>
            <x-primary-button type="button" id="btnGuardarContrato" class="px-6 py-2 flex items-center gap-2">
                <i class="bi bi-check-lg" id="btnGuardarContratoIcon"></i>
                <span id="btnGuardarContratoText">Guardar</span>
            </x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- =====================================================================
     MODAL: HISTORIAL DE CONTRATOS (por cotización)
====================================================================== --}}
<x-modal-show id="modalHistorial" title="Historial de Contratos" size="large">

    {{-- Cabecera de empresa --}}
    <div class="flex items-center gap-3 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 mb-5 -mt-1">
        <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex-shrink-0">
            <i class="bi bi-building text-base"></i>
        </div>
        <div>
            <p class="text-xs text-blue-500 font-medium uppercase tracking-wide">Cliente</p>
            <h3 class="text-sm font-bold text-blue-800 leading-tight">
                <span id="historial-empresa"></span>
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">
                RUC: <span id="historial-ruc" class="font-semibold text-gray-700"></span>
            </p>
        </div>
    </div>

    {{-- Tabla historial --}}
    <div class="overflow-auto max-h-[55vh] rounded-xl border border-gray-200 shadow-sm">
        {{-- Loading --}}
        <div id="historial-loading" class="flex flex-col items-center justify-center py-14 text-gray-400">
            <svg class="animate-spin h-7 w-7 text-blue-400 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <span class="text-sm">Cargando historial...</span>
        </div>

        <table id="tabla-historial" class="w-full text-sm hidden">
            <thead class="bg-gray-50 text-gray-600 sticky top-0 z-10 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 font-semibold text-left text-xs uppercase tracking-wide w-[11%]">Estado</th>
                    <th class="px-4 py-3 font-semibold text-left text-xs uppercase tracking-wide w-[14%]">Fecha Inicio
                    </th>
                    <th class="px-4 py-3 font-semibold text-left text-xs uppercase tracking-wide w-[14%]">Fecha Fin
                    </th>
                    <th class="px-4 py-3 font-semibold text-left text-xs uppercase tracking-wide w-[25%]">Vencimiento
                    </th>
                    <th class="px-4 py-3 font-semibold text-center text-xs uppercase tracking-wide w-[10%]">Cotización
                    </th>
                    <th class="px-4 py-3 font-semibold text-center text-xs uppercase tracking-wide w-[10%]">Contrato
                    </th>
                    <th class="px-4 py-3 font-semibold text-center text-xs uppercase tracking-wide w-[16%]">Acciones
                    </th>
                </tr>
            </thead>
            <tbody id="tbody-historial" class="divide-y divide-gray-100"></tbody>
        </table>

        {{-- Sin datos --}}
        <div id="historial-sin-datos" class="hidden flex flex-col items-center justify-center py-14 text-gray-400">
            <i class="bi bi-inbox text-4xl mb-2 block"></i>
            <p class="text-sm">Sin contratos registrados para esta cotización.</p>
        </div>
    </div>

    {{-- Footer del modal --}}
    <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
        <p class="text-xs text-gray-400" id="historial-contador"></p>
        @if (Auth::user()->area->nombre != 'LEGAL')
            <button type="button" id="btnNuevoDesdeHistorial"
                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition shadow-sm">
                <i class="bi bi-plus-circle"></i> Agregar Contrato
            </button>
        @endif
    </div>

</x-modal-show>

{{-- =====================================================================
     MODAL: TODOS LOS CONTRATOS
====================================================================== --}}
<x-modal-show id="modalTodosContratos" title="Todos los Contratos" size="large">

    {{-- Buscador --}}
    <div class="mb-4">
        <div class="relative">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" id="buscadorTodosContratos" placeholder="Buscar por empresa, N° cotización..."
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring focus:ring-indigo-200 focus:border-indigo-400 outline-none">
        </div>
    </div>

    <div class="overflow-auto max-h-[70vh] rounded-lg border border-gray-200">
        <div id="todos-loading" class="text-center py-10 text-gray-500">
            <i class="bi bi-arrow-repeat text-2xl"></i> Cargando...
        </div>
        <table id="tabla-todos-contratos" class="w-full text-sm hidden">
            <thead class="bg-gray-100 text-gray-700 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 border-b font-semibold">Empresa</th>
                    <th class="px-3 py-2 border-b font-semibold">N° Cotización</th>
                    <th class="px-3 py-2 border-b font-semibold">Fecha Inicio</th>
                    <th class="px-3 py-2 border-b font-semibold">Fecha Fin</th>
                    <th class="px-3 py-2 border-b font-semibold">Vencimiento</th>
                    <th class="px-3 py-2 border-b font-semibold text-center">Cotización</th>
                    <th class="px-3 py-2 border-b font-semibold text-center">Contrato</th>
                    <th class="px-3 py-2 border-b font-semibold text-center">Estado</th>
                </tr>
            </thead>
            <tbody id="tbody-todos-contratos" class="divide-y divide-gray-100"></tbody>
        </table>
        <div id="todos-sin-datos" class="hidden text-center py-10 text-gray-400 text-sm">
            <i class="bi bi-inbox text-3xl block mb-2"></i> No se encontraron contratos.
        </div>
    </div>

    {{-- Contador --}}
    <div class="mt-3 text-xs text-gray-400 text-right" id="todos-contador"></div>

</x-modal-show>

<style>
    /* ── Scrollbars delgados ─────────────────────────────────── */
    .overflow-x-auto::-webkit-scrollbar,
    .overflow-auto::-webkit-scrollbar {
        height: 4px;
        width: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-track,
    .overflow-auto::-webkit-scrollbar-track {
        background: transparent;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb,
    .overflow-auto::-webkit-scrollbar-thumb {
        background-color: #9ca3af;
        border-radius: 999px;
    }
</style>

{{-- =====================================================================
     SCRIPTS
====================================================================== --}}
<script>
    const esAreaLegal = {{ Auth::user()->area->nombre == 'LEGAL' ? 'true' : 'false' }};
    {{-- 👈 agregar aquí --}}

    $(document).ready(function() {

        // ─── DATATABLES ──────────────────────────────────────────────────────────
        $('#myTable-contratos').DataTable({
            responsive: false,
            paging: true,
            ordering: true,
            pageLength: 100,
            language: {
                url: '/js/datatables/lang/es-ES.json'
            },
            order: [
                [2, 'asc']
            ],
            columnDefs: [{
                orderable: false,
                targets: [3, 6, 7, 8]
            }]
        });

        // ─── TOAST ───────────────────────────────────────────────────────────────
        function mostrarToast(mensaje, tipo = 'success') {
            if (typeof showAjaxToast === 'function') {
                showAjaxToast(mensaje, tipo);
            }
        }


        // ─── HELPERS ─────────────────────────────────────────────────────────────
        function calcularVencimiento(fechaFinStr) {
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            const soloFecha = fechaFinStr ? fechaFinStr.split('T')[0] : '';
            const fechaFin = new Date(soloFecha + 'T00:00:00');
            const diff = Math.round((fechaFin - hoy) / (1000 * 60 * 60 * 24));
            if (diff > 6) return {
                texto: diff + ' días restantes',
                clase: 'text-green-600 font-semibold'
            };
            if (diff > 0) return {
                texto: diff + ' días restantes',
                clase: 'text-orange-500 font-semibold'
            };
            if (diff === 0) return {
                texto: 'Vence hoy',
                clase: 'text-red-600 font-bold'
            };
            return {
                texto: 'Venció hace ' + Math.abs(diff) + ' días',
                clase: 'text-red-500 font-semibold'
            };
        }

        /**
         * pdfIconBtn — genera un link solo con icono + tooltip (igual que columna acciones).
         * @param {string|null} path  Ruta relativa del archivo (sin "/" inicial).
         * @param {string}      label Texto del tooltip: 'COTIZACIÓN' | 'CONTRATO'.
         */
        function pdfIconBtn(path, label) {
            if (!path) return '<span class="text-gray-400">—</span>';

            return `
                <a href="/${path}" target="_blank"
                    class="tooltip"
                    style="color:#e53e3e;"
                    onmouseover="this.style.color='#c53030';"
                    onmouseout="this.style.color='#e53e3e';">
                    
                    <i class="bi bi-file-earmark-pdf text-lg"></i>
                    <span class="tooltip-text">Ver ${label.toLowerCase()}</span>
                </a>
            `;
        }

        function formatearFecha(fecha) {
            if (!fecha) return '—';
            const solo = fecha.split('T')[0];
            const p = solo.split('-');
            if (p.length !== 3) return fecha;
            return p[2] + '/' + p[1] + '/' + p[0];
        }

        // ─── TODOS LOS CONTRATOS ─────────────────────────────────────────────────
        let todosContratosData = [];

        function renderTodosContratos(filtro = '') {
            const tbody = document.getElementById('tbody-todos-contratos');
            const tabla = document.getElementById('tabla-todos-contratos');
            const sinDatos = document.getElementById('todos-sin-datos');
            const contador = document.getElementById('todos-contador');

            const termino = filtro.toLowerCase().trim();
            const datos = termino ?
                todosContratosData.filter(c =>
                    c.empresa.toLowerCase().includes(termino) ||
                    c.nro_cotizacion.toLowerCase().includes(termino)
                ) :
                todosContratosData;

            if (!datos.length) {
                tabla.classList.add('hidden');
                sinDatos.classList.remove('hidden');
                contador.textContent = '';
                return;
            }

            let empresaActual = null;
            tbody.innerHTML = datos.map(c => {
                const esNuevaEmpresa = c.empresa !== empresaActual;
                empresaActual = c.empresa;

                const separador = esNuevaEmpresa ?
                    `<tr class="bg-indigo-50">
                           <td colspan="8" class="px-3 py-1.5 text-xs font-bold text-indigo-700 uppercase tracking-wide">
                               <i class="bi bi-building me-1"></i>${c.empresa}
                           </td>
                       </tr>` :
                    '';

                const estadoBadge = c.es_principal ?
                    `<span class="inline-block bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">Principal</span>` :
                    `<span class="inline-block bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">Inactivo</span>`;

                return `${separador}
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-3 py-2.5 text-sm text-gray-700">${c.empresa}</td>
                    <td class="px-3 py-2.5 text-sm text-gray-700 whitespace-nowrap">${c.nro_cotizacion}</td>
                    <td class="px-3 py-2.5 text-sm text-gray-700 whitespace-nowrap">${formatearFecha(c.fecha_inicio)}</td>
                    <td class="px-3 py-2.5 text-sm text-gray-700 whitespace-nowrap">${formatearFecha(c.fecha_fin)}</td>
                    <td class="px-3 py-2.5 whitespace-nowrap"><span class="${c.vencimiento.clase}">${c.vencimiento.texto}</span></td>
                    <td class="px-3 py-2.5 text-center">${pdfIconBtn(c.archivo_cotizacion, 'COTIZACIÓN')}</td>
                    <td class="px-3 py-2.5 text-center">${pdfIconBtn(c.archivo_contrato, 'CONTRATO')}</td>
                    <td class="px-3 py-2.5 text-center">${estadoBadge}</td>
                </tr>`;
            }).join('');

            tabla.classList.remove('hidden');
            sinDatos.classList.add('hidden');
            contador.textContent = `Mostrando ${datos.length} contrato${datos.length !== 1 ? 's' : ''}`;
        }

        // ─── ABRIR MODAL TODOS LOS CONTRATOS ────────────────────────────────────
        $('#btnVerTodosContratos').on('click', function() {
            toggleModal('modalTodosContratos');
            $('#buscadorTodosContratos').val('');

            const loading = document.getElementById('todos-loading');
            const tabla = document.getElementById('tabla-todos-contratos');
            const sinDatos = document.getElementById('todos-sin-datos');

            loading.classList.remove('hidden');
            tabla.classList.add('hidden');
            sinDatos.classList.add('hidden');

            $.get('/contratos_recepcion/todos', function(res) {
                loading.classList.add('hidden');
                todosContratosData = res.contratos;
                renderTodosContratos();
            }).fail(function() {
                loading.classList.add('hidden');
                sinDatos.classList.remove('hidden');
                mostrarToast('Error al cargar los contratos.', 'error');
            });
        });

        // ─── BUSCADOR EN TIEMPO REAL ─────────────────────────────────────────────
        $('#buscadorTodosContratos').on('input', function() {
            renderTodosContratos($(this).val());
        });

        // ─── CARGAR HISTORIAL (por cotización) ───────────────────────────────────
        function cargarHistorial(cotizacionId) {
            const loading = document.getElementById('historial-loading');
            const tabla = document.getElementById('tabla-historial');
            const sinDatos = document.getElementById('historial-sin-datos');
            const tbody = document.getElementById('tbody-historial');
            const contador = document.getElementById('historial-contador');

            loading.classList.remove('hidden');
            tabla.classList.add('hidden');
            sinDatos.classList.add('hidden');
            if (contador) contador.textContent = '';

            $.get(`/contratos_recepcion/${cotizacionId}/historial`, function(res) {
                loading.classList.add('hidden');

                if (!res.contratos || !res.contratos.length) {
                    sinDatos.classList.remove('hidden');
                    return;
                }

                tbody.innerHTML = res.contratos.map(c => {
                    const esPrincipal = c.es_principal;

                    const estadoBadge = esPrincipal ?
                        `<span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                               <i class="bi bi-check-circle-fill text-green-500"></i> Principal
                           </span>` :
                        `<span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-xs px-2.5 py-1 rounded-full">
                               <i class="bi bi-clock-history"></i> Inactivo
                           </span>`;

                    const venc = calcularVencimiento(c.fecha_fin);
                    const vencHtml = esPrincipal ?
                        `<span class="${venc.clase} text-xs">${venc.texto}</span>` :
                        `<span class="text-gray-400 text-xs">—</span>`;

                    const acciones = `
                    <div class="flex items-center justify-center gap-1">
                        ${!esAreaLegal ? `
                        <a role="button"
                            class="btn-eliminar-historial flex items-center rounded-md tooltip"
                            style="color: #cc2929;" onmouseover="this.style.color='#ff4444';" onmouseout="this.style.color='#cc2929';"
                            data-id="${c.id_contrato_recepcion}"
                            data-cotizacion="${c.id_cotizacion}"
                            data-es-principal="${c.es_principal ? '1' : '0'}">
                            <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                            <span class="tooltip-text">Eliminar</span>
                        </a>` : ''}
                    </div>`;

                    return `
                    <tr class="hover:bg-gray-50 transition ${esPrincipal ? 'bg-green-50/40' : ''}" id="fila-historial-${c.id_contrato_recepcion}">
                        <td class="px-4 py-3">${estadoBadge}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">${formatearFecha(c.fecha_inicio)}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">${formatearFecha(c.fecha_fin)}</td>
                        <td class="px-4 py-3 whitespace-nowrap">${vencHtml}</td>
                        <td class="px-4 py-3 text-center">${pdfIconBtn(c.archivo_cotizacion, 'COTIZACIÓN')}</td>
                        <td class="px-4 py-3 text-center">${pdfIconBtn(c.archivo_contrato, 'CONTRATO')}</td>
                        <td class="px-4 py-3">${acciones}</td>
                    </tr>`;
                }).join('');

                tabla.classList.remove('hidden');
                if (contador) {
                    const total = res.contratos.length;
                    contador.textContent =
                        `${total} contrato${total !== 1 ? 's' : ''} registrado${total !== 1 ? 's' : ''}`;
                }

                bindAccionesHistorial(cotizacionId);
            }).fail(function() {
                loading.classList.add('hidden');
                sinDatos.classList.remove('hidden');
                mostrarToast('Error al cargar el historial.', 'error');
            });
        }

        // ─── BIND ACCIONES HISTORIAL ─────────────────────────────────────────────
        function bindAccionesHistorial(cotizacionId) {
            document.querySelectorAll('.btn-eliminar-historial').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!confirm(
                            '¿Eliminar este contrato? Si hay otros, el más reciente pasará a ser el principal.'
                        )) return;
                    const id = this.dataset.id;
                    $.ajax({
                        url: `/contratos_recepcion/${id}/eliminar`,
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.principal) {
                                actualizarFilaPrincipal(cotizacionId, res
                                    .principal);
                            } else {
                                const fila = $(`#fila-cotizacion-${cotizacionId}`);
                                const empresa = fila.find('.btn-editar-contrato')
                                    .attr('data-empresa') ?? '';
                                const ruc = fila.find('.btn-editar-contrato').attr(
                                    'data-ruc') ?? '';
                                fila.find(`.fecha-inicio-${cotizacionId}`).text(
                                    '—');
                                fila.find(`.fecha-fin-${cotizacionId}`).text('—');
                                fila.find(`.vencimiento-${cotizacionId}`).html(
                                    '<span class="text-gray-400">—</span>');
                                fila.find(`.archivo-cotizacion-${cotizacionId}`)
                                    .html('<span class="text-gray-400">—</span>');
                                fila.find(`.archivo-contrato-${cotizacionId}`).html(
                                    '<span class="text-gray-400">—</span>');
                                fila.find(`.acciones-cell-${cotizacionId}`).html(`
                                    <a role="button" class="flex items-center rounded-md tooltip btn-crear-contrato"
                                        style="color:#28a745;" onmouseover="this.style.color='#34d058';" onmouseout="this.style.color='#28a745';"
                                        data-cotizacion="${cotizacionId}" data-empresa="${empresa}" data-ruc="${ruc}">
                                        <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                                        <span class="tooltip-text">Crear Contrato</span>
                                    </a>`);
                            }
                            cargarHistorial(cotizacionId);
                            mostrarToast(res.message || 'Contrato eliminado.',
                                'error');
                        },
                        error: function() {
                            mostrarToast('Error al eliminar.', 'error');
                        }
                    });
                });
            });
        }

        // ─── ACTUALIZAR FILA PRINCIPAL EN LA TABLA ───────────────────────────────
        function actualizarFilaPrincipal(cotizacionId, contrato) {
            const fila = $(`#fila-cotizacion-${cotizacionId}`);
            fila.find(`.fecha-inicio-${cotizacionId}`).text(formatearFecha(contrato.fecha_inicio));
            fila.find(`.fecha-fin-${cotizacionId}`).text(formatearFecha(contrato.fecha_fin));
            fila.find(`.archivo-cotizacion-${cotizacionId}`).html(pdfIconBtn(contrato.archivo_cotizacion,
                'COTIZACIÓN'));
            fila.find(`.archivo-contrato-${cotizacionId}`).html(pdfIconBtn(contrato.archivo_contrato,
                'CONTRATO'));
            const v = calcularVencimiento(contrato.fecha_fin);
            fila.find(`.vencimiento-${cotizacionId}`).html(`<span class="${v.clase}">${v.texto}</span>`);
            fila.find('.btn-editar-contrato')
                .attr('data-id', contrato.id_contrato_recepcion)
                .attr('data-cotizacion', cotizacionId)
                .attr('data-fecha-inicio', fechaInicioNorm)
                .attr('data-fecha-fin', fechaFinNorm)
                .attr('data-archivo-cotizacion', contrato.archivo_cotizacion ?? '')
                .attr('data-archivo-contrato', contrato.archivo_contrato ?? '');
            fila.find('.btn-eliminar-contrato')
                .attr('data-id', contrato.id_contrato_recepcion)
                .attr('data-cotizacion', cotizacionId);
            fila.find('.btn-ver-historial').attr('data-cotizacion', cotizacionId);
        }

        // ─── ABRIR MODAL HISTORIAL ───────────────────────────────────────────────
        $(document).on('click', '.btn-ver-historial', function() {
            const cotizacionId = $(this).data('cotizacion');
            $('#historial-empresa').text($(this).data('empresa'));
            $('#historial-ruc').text($(this).data('ruc'));
            $('#btnNuevoDesdeHistorial')
                .data('cotizacion', cotizacionId)
                .data('empresa', $(this).data('empresa'))
                .data('ruc', $(this).data('ruc'));
            cargarHistorial(cotizacionId);
            toggleModal('modalHistorial');
        });

        // ─── BOTÓN AGREGAR DESDE HISTORIAL ──────────────────────────────────────
        $('#btnNuevoDesdeHistorial').on('click', function() {
            const cotizacionId = $(this).data('cotizacion');
            const empresa = $(this).data('empresa');
            const ruc = $(this).data('ruc');
            toggleModal('modalHistorial');
            setTimeout(() => {
                $('#contrato_id').val('');
                $('#contrato_cotizacion_id').val(cotizacionId);
                $('#contrato_empresa').val(empresa);
                $('#contrato_ruc').val(ruc);
                $('#contrato_fecha_inicio').val('');
                $('#contrato_fecha_fin').val('');
                $('#contrato_archivo_cotizacion').val('');
                $('#contrato_archivo_contrato').val('');
                $('#cotizacion_archivo_actual').addClass('hidden');
                $('#contrato_archivo_actual').addClass('hidden');
                $('#contrato_desde_historial').val('1');
                toggleModal('modalContrato');
            }, 300);
        });

        // ─── ABRIR MODAL CREAR (desde tabla) ────────────────────────────────────
        $(document).on('click', '.btn-crear-contrato', function() {
            $('#contrato_id').val('');
            $('#contrato_cotizacion_id').val($(this).data('cotizacion'));
            $('#contrato_empresa').val($(this).data('empresa'));
            $('#contrato_ruc').val($(this).data('ruc'));
            $('#contrato_fecha_inicio').val('');
            $('#contrato_fecha_fin').val('');
            $('#contrato_archivo_cotizacion').val('');
            $('#contrato_archivo_contrato').val('');
            $('#cotizacion_archivo_actual').addClass('hidden');
            $('#contrato_archivo_actual').addClass('hidden');
            $('#contrato_desde_historial').val('0');
            toggleModal('modalContrato');
        });

        // ─── ABRIR MODAL EDITAR ──────────────────────────────────────────────────
        $(document).on('click', '.btn-editar-contrato', function() {
            $('#contrato_id').val($(this).attr('data-id'));
            $('#contrato_cotizacion_id').val($(this).attr('data-cotizacion'));
            $('#contrato_empresa').val($(this).attr('data-empresa'));
            $('#contrato_ruc').val($(this).attr('data-ruc'));
            $('#contrato_fecha_inicio').val($(this).attr('data-fecha-inicio'));
            $('#contrato_fecha_fin').val($(this).attr('data-fecha-fin'));
            $('#contrato_archivo_cotizacion').val('');
            $('#contrato_archivo_contrato').val('');
            $('#contrato_desde_historial').val('0');

            const archCot = $(this).attr('data-archivo-cotizacion');
            if (archCot) {
                $('#cotizacion_archivo_nombre').text(archCot.split('/').pop());
                $('#cotizacion_archivo_ver').attr('href', '/' + archCot);
                $('#cotizacion_archivo_actual').removeClass('hidden');
            } else {
                $('#cotizacion_archivo_actual').addClass('hidden');
            }

            const archCon = $(this).attr('data-archivo-contrato');
            if (archCon) {
                $('#contrato_archivo_nombre').text(archCon.split('/').pop());
                $('#contrato_archivo_ver').attr('href', '/' + archCon);
                $('#contrato_archivo_actual').removeClass('hidden');
            } else {
                $('#contrato_archivo_actual').addClass('hidden');
            }

            toggleModal('modalContrato');
        });

        // ─── GUARDAR CONTRATO ────────────────────────────────────────────────────
        $('#btnGuardarContrato').on('click', function() {
            const id = $('#contrato_id').val();
            const url = id ? `/contratos_recepcion/${id}` : `/contratos_recepcion`;

            const btn = document.getElementById('btnGuardarContrato');
            btn.disabled = true;
            $('#btnGuardarContratoIcon').attr('class', 'bi bi-arrow-repeat');
            $('#btnGuardarContratoText').text('Guardando...');

            const cotizacionId = $('#contrato_cotizacion_id').val();
            const desdeHistorial = $('#contrato_desde_historial').val() === '1';
            const fechaInicio = $('#contrato_fecha_inicio').val();
            const fechaFin = $('#contrato_fecha_fin').val();
            const empresa = $('#contrato_empresa').val();
            const ruc = $('#contrato_ruc').val();
            const fila = $(`#fila-cotizacion-${cotizacionId}`);
            const archCotPrevio = fila.find('.btn-editar-contrato').attr('data-archivo-cotizacion') ??
                '';
            const archConPrevio = fila.find('.btn-editar-contrato').attr('data-archivo-contrato') ?? '';

            const formData = new FormData($('#formContrato')[0]);
            formData.set('_token', $('meta[name="csrf-token"]').attr('content'));
            if (id) formData.append('_method', 'PUT');

            $.ajax({
                url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    toggleModal('modalContrato');

                    const idContrato = res.contrato.id_contrato_recepcion;
                    const archCot = res.contrato?.archivo_cotizacion || archCotPrevio;
                    const archCon = res.contrato?.archivo_contrato || archConPrevio;

                    if (res.contrato.es_principal) {
                        fila.find(`.fecha-inicio-${cotizacionId}`).text(formatearFecha(
                            fechaInicio));
                        fila.find(`.fecha-fin-${cotizacionId}`).text(formatearFecha(
                            fechaFin));
                        fila.find(`.archivo-cotizacion-${cotizacionId}`).html(pdfIconBtn(
                            archCot, 'COTIZACIÓN'));
                        fila.find(`.archivo-contrato-${cotizacionId}`).html(pdfIconBtn(
                            archCon, 'CONTRATO'));
                        const v = calcularVencimiento(fechaFin);
                        fila.find(`.vencimiento-${cotizacionId}`).html(
                            `<span class="${v.clase}">${v.texto}</span>`);
                    }

                    if (!id) {
                        // Creación nueva: agregar botones Editar, Historial, Eliminar y mantener Crear Contrato( para los flojos que no quieen entrar )
                        fila.find(`.acciones-cell-${cotizacionId}`).html(`
                        <a role="button" class="flex items-center rounded-md tooltip btn-editar-contrato"
                            style="color:#e6a817;" onmouseover="this.style.color='#f5bc42';" onmouseout="this.style.color='#e6a817';"
                            data-id="${idContrato}" data-cotizacion="${cotizacionId}"
                            data-fecha-inicio="${fechaInicio}" data-fecha-fin="${fechaFin}"
                            data-empresa="${empresa}" data-ruc="${ruc}"
                            data-archivo-cotizacion="${archCot}" data-archivo-contrato="${archCon}">
                            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                            <span class="tooltip-text">Editar</span>
                        </a>
                        <a role="button" class="flex items-center rounded-md tooltip btn-ver-historial"
                            style="color:#2986cc;" onmouseover="this.style.color='#2ba4ff';" onmouseout="this.style.color='#2986cc';"
                            data-cotizacion="${cotizacionId}" data-empresa="${empresa}" data-ruc="${ruc}">
                            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/></svg>
                            <span class="tooltip-text">Historial</span>
                        </a>
                        <a role="button" class="flex items-center rounded-md tooltip btn-eliminar-contrato"
                            style="color:#cc2929;" onmouseover="this.style.color='#ff4444';" onmouseout="this.style.color='#cc2929';"
                            data-id="${idContrato}" data-cotizacion="${cotizacionId}">
                            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            <span class="tooltip-text">Eliminar</span>
                        </a>
                        <a role="button" class="flex items-center rounded-md tooltip btn-crear-contrato"
                            style="color:#28a745;" onmouseover="this.style.color='#34d058';" onmouseout="this.style.color='#28a745';"
                            data-cotizacion="${cotizacionId}" data-empresa="${empresa}" data-ruc="${ruc}">
                            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                            <span class="tooltip-text">Crear Contrato</span>
                        </a>`);
                    } else {
                        // Edición: actualizar data-attributes del botón editar
                        fila.find('.btn-editar-contrato')
                            .attr('data-id', idContrato)
                            .attr('data-cotizacion', cotizacionId)
                            .attr('data-fecha-inicio', fechaInicio)
                            .attr('data-fecha-fin', fechaFin)
                            .attr('data-empresa', empresa)
                            .attr('data-ruc', ruc)
                            .attr('data-archivo-cotizacion', archCot)
                            .attr('data-archivo-contrato', archCon);
                        fila.find('.btn-eliminar-contrato').attr('data-id', idContrato);
                    }

                    mostrarToast(res.message || (id ? 'Contrato actualizado.' :
                        'Contrato creado.'), 'success');

                    if (desdeHistorial) {
                        setTimeout(() => {
                            toggleModal('modalHistorial');
                            cargarHistorial(cotizacionId);
                        }, 350);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.errors ?
                        Object.values(xhr.responseJSON.errors).flat().join(' | ') :
                        (xhr.responseJSON?.message || 'Error al guardar.');
                    mostrarToast(msg, 'error');
                },
                complete: function() {
                    btn.disabled = false;
                    $('#btnGuardarContratoIcon').attr('class', 'bi bi-check-lg');
                    $('#btnGuardarContratoText').text('Guardar');
                }
            });
        });

        // ─── ELIMINAR CONTRATO (desde tabla principal) ───────────────────────────
        $(document).on('click', '.btn-eliminar-contrato', function() {
            if (!confirm(
                    '¿Eliminar este contrato principal? Si hay otros contratos, el más reciente pasará a ser el principal.'
                )) return;

            const id = $(this).attr('data-id');
            const cotizacionId = $(this).data('cotizacion');

            $.ajax({
                url: `/contratos_recepcion/${id}/eliminar`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    const fila = $(`#fila-cotizacion-${cotizacionId}`);
                    const empresa = fila.find('.btn-editar-contrato').attr(
                            'data-empresa') ??
                        fila.find('.btn-crear-contrato').data('empresa') ??
                        '';
                    const ruc = fila.find('.btn-editar-contrato').attr('data-ruc') ??
                        fila.find('.btn-crear-contrato').data('ruc') ??
                        '';

                    if (res.principal) {
                        actualizarFilaPrincipal(cotizacionId, res.principal);
                    } else {
                        fila.find(`.fecha-inicio-${cotizacionId}`).text('—');
                        fila.find(`.fecha-fin-${cotizacionId}`).text('—');
                        fila.find(`.vencimiento-${cotizacionId}`).html(
                            '<span class="text-gray-400">—</span>');
                        fila.find(`.archivo-cotizacion-${cotizacionId}`).html(
                            '<span class="text-gray-400">—</span>');
                        fila.find(`.archivo-contrato-${cotizacionId}`).html(
                            '<span class="text-gray-400">—</span>');
                        fila.find(`.acciones-cell-${cotizacionId}`).html(`
                            <a role="button" class="flex items-center rounded-md tooltip btn-crear-contrato"
                                style="color:#28a745;" onmouseover="this.style.color='#34d058';" onmouseout="this.style.color='#28a745';"
                                data-cotizacion="${cotizacionId}" data-empresa="${empresa}" data-ruc="${ruc}">
                                <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                                <span class="tooltip-text">Crear Contrato</span>
                            </a>`);
                    }
                    mostrarToast(res.message || 'Contrato eliminado.', 'error');
                },
                error: function() {
                    mostrarToast('Error al eliminar.', 'error');
                }
            });
        });

    });
</script>
