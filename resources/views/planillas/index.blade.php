{{-- resources/views/planillas/index.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Planillas de Trabajadores') }}
        </h2>
    </x-slot>

    <div class="pt-6 pb-12">
        <div class="w-full px-6">

            <x-success-error-bagdes />

            <div class="mb-4 flex justify-end items-center gap-3">
                @if (Auth::user()->tipo_usuario == 2 || Auth::user()->area->nombre == 'ADMINISTRACION')
                    <button onclick="window.location.href='{{ route('inventario.branding') }}'"
                        class="bg-red-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center gap-2">
                        <i class="bi bi-box-seam"></i>
                        Firmas y Logos
                    </button>
                @endif


                <!-- Botones de Acción -->
                <div class="flex items-center gap-2">

                    <button id="btnConteoTrabajadores"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm text-white rounded-lg transition shadow"
                        style="background-color: #2563eb !important; color: white !important;"
                        onmouseover="this.style.backgroundColor='#1d4ed8';"
                        onmouseout="this.style.backgroundColor='#2563eb';">
                        <i class="bi bi-table"></i>
                        Conteo Trabajadores
                    </button>

                    <button id="btnVencimientoTrabajadoresModal"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm text-white rounded-lg transition shadow"
                        style="background-color: #f59e0b !important; color: white !important;"
                        onmouseover="this.style.backgroundColor='#d97706';"
                        onmouseout="this.style.backgroundColor='#f59e0b';">
                        <i class="bi bi-calendar-x"></i>
                        Contratos por Vencer
                    </button>

                    <a href="{{ route('planillas.listar') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm text-white rounded-lg shadow"
                        style="background-color: #16a34a !important; color: white !important;"
                        onmouseover="this.style.backgroundColor='#15803d'; this.style.color='white';"
                        onmouseout="this.style.backgroundColor='#16a34a'; this.style.color='white';">
                        Listado General
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg"></div>

            {{-- ══════════════════════════════════════════════════════════════
                 TABLA PRINCIPAL DE EMPRESAS
            ══════════════════════════════════════════════════════════════ --}}
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap" id="myTable-planillas">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-4 border-b">ESTADO RECURSOS</th>
                            <th class="px-4 py-4 border-b">EMPRESA</th>
                            <th class="px-4 py-4 border-b">RUC</th>
                            <th class="px-4 py-4 border-b">RUBRO</th>
                            <th class="px-4 py-4 border-b">GERENTE</th>
                            <!-- <th class="px-4 py-4 border-b text-center">ACTIVOS</th> -->
                            <th class="px-4 py-4 border-b text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $empresa)
                            <tr class="hover:bg-gray-50" id="fila-empresa-{{ $empresa->id_empresas_clientes }}">

                                <td class="px-4 py-4 border-b">
                                    {{ $empresa->estado_recursos ?? '—' }}
                                </td>

                                <td class="px-4 py-4 border-b font-medium">
                                    {{ $empresa->razon_social ?? '—' }}
                                </td>

                                <td class="px-4 py-4 border-b">
                                    {{ $empresa->ruc ?? '—' }}
                                </td>

                                <td class="px-4 py-4 border-b"
                                    style="max-width:200px; white-space:nowrap; overflow-x:auto; overflow-y:hidden;">
                                    <div
                                        style="max-width:200px; overflow-x:auto; overflow-y:hidden; white-space:nowrap;">
                                        {{ $empresa->rubro ?? '—' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 border-b">
                                    {{ $empresa->gerenteActivo?->nombre ?? '—' }}
                                </td>

                                <!-- <td class="px-4 py-4 border-b text-center font-bold text-blue-600 col-activos">
                                    {{ $empresa->activos_count }}
                                </td> -->

                                <td class="px-4 py-4 border-b">
                                    <div class="flex items-center justify-center gap-3">

                                        @if (Auth::user()->area->nombre != 'AUDITORIA')
                                            {{-- Botón: Agregar trabajador --}}
                                            <a role="button"
                                                class="flex items-center rounded-md tooltip btn-agregar-trabajador"
                                                style="color: #28a745;" onmouseover="this.style.color='#34d058';"
                                                onmouseout="this.style.color='#28a745';"
                                                data-id="{{ $empresa->id_empresas_clientes }}"
                                                data-empresa="{{ $empresa->razon_social }}"
                                                data-ruc="{{ $empresa->ruc }}">
                                                <svg style="width:20px;height:20px;" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                                </svg>
                                                <span class="tooltip-text">Agregar Trabajador</span>
                                            </a>
                                        @endif

                                        {{-- Botón: Ver trabajadores --}}
                                        <a role="button"
                                            class="flex items-center rounded-md tooltip btn-ver-trabajadores"
                                            style="color: #2986cc;" onmouseover="this.style.color='#2ba4ff';"
                                            onmouseout="this.style.color='#2986cc';"
                                            data-id="{{ $empresa->id_empresas_clientes }}"
                                            data-empresa="{{ $empresa->razon_social }}"
                                            data-ruc="{{ $empresa->ruc }}">
                                            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                            </svg>
                                            <span class="tooltip-text">Ver Trabajadores</span>
                                        </a>

                                        {{-- Botón: Control de contratos --}}
                                        <a role="button"
                                            class="flex items-center rounded-md tooltip btn-ver-contratos-control"
                                            style="color: #6a1b9a;" onmouseover="this.style.color='#8e24aa';"
                                            onmouseout="this.style.color='#6a1b9a';"
                                            data-id="{{ $empresa->id_empresas_clientes }}"
                                            data-empresa="{{ $empresa->razon_social }}"
                                            data-ruc="{{ $empresa->ruc }}">
                                            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="tooltip-text">Control de Contratos</span>
                                        </a>

                                        {{-- Botón: Eliminar todos --}}
                                        {{-- <a role="button"
                                            class="flex items-center rounded-md tooltip btn-eliminar-todos"
                                            style="color: #cc2929;" onmouseover="this.style.color='#ff4444';"
                                            onmouseout="this.style.color='#cc2929';"
                                            data-id="{{ $empresa->id_empresas_clientes }}"
                                            data-empresa="{{ $empresa->razon_social }}">
                                            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                            <span class="tooltip-text">Eliminar Todos</span>
                                        </a> --}}

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <x-ajax-toast />

    <!-- No saving overlay -->
</x-app-layout>


{{-- =====================================================================
     MODAL: AGREGAR TRABAJADOR
====================================================================== --}}
<x-modal-show id="modalAgregar" title="Agregar Trabajador" static="true">
    <form id="formAgregar" class="space-y-4">
        @csrf
        <input type="hidden" id="agregar_empresa_id" name="id_empresas_clientes">

        {{-- Tarjeta cliente --}}
        <div class="w-full flex items-center gap-4 bg-blue-50 border border-blue-100 rounded-2xl px-5 py-4">
            <div class="flex-shrink-0 w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-widest text-blue-500 leading-none mb-1">Cliente</p>
                <p id="agregar_empresa_nombre_display" class="text-base font-bold text-blue-800 leading-tight truncate">
                </p>
                <p class="text-sm text-gray-500 mt-0.5">RUC: <span id="agregar_empresa_ruc_display"
                        class="font-semibold text-gray-700"></span></p>
            </div>
        </div>
        <input type="hidden" id="agregar_empresa_nombre">
        <input type="hidden" id="agregar_empresa_ruc">

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="agregar_dni" value="DNI" />
                <input type="text" id="agregar_dni" name="dni" maxlength="8" required pattern="\d{8}"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                    placeholder="Ej: 12345678" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
            <div>
                <x-input-label for="agregar_nombre" value="Apellidos y nombres" />
                <input type="text" id="agregar_nombre" name="nombre_trabajador" maxlength="200" required
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                    placeholder="Apellidos y Nombres" oninput="this.value = this.value.toUpperCase()">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="agregar_fecha_ingreso" value="Fecha de Ingreso" />
                <input type="date" id="agregar_fecha_ingreso" name="fecha_ingreso" required
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
            </div>
            <div>
                <x-input-label for="agregar_cargo" value="Cargo" />
                <input type="text" id="agregar_cargo" name="cargo" maxlength="150" required
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                    placeholder="Ej: Contador, Asistente..." oninput="this.value = this.value.toUpperCase()">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="agregar_sueldo" value="Sueldo (S/.)" />
                <input type="number" id="agregar_sueldo" name="sueldo" min="0" step="0.01" required
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                    placeholder="0.00">
            </div>
            <div>
                <x-input-label for="agregar_referido" value="Referido (opcional)" />
                <input type="text" id="agregar_referido" name="referido" maxlength="200"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                    placeholder="Nombre del referido" oninput="this.value = this.value.toUpperCase()">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="agregar_archivo_dni" value="DNI (PDF)" />
                <input type="file" id="agregar_archivo_dni" name="archivo_dni" accept="application/pdf"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
            </div>
            <div>
                <x-input-label for="agregar_firma_digital" value="Firma Digital (Imagen)" />
                <input type="file" id="agregar_firma_digital" name="firma_digital" accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition">
            </div>
        </div>

        {{-- Sección: Datos Iniciales del Contrato --}}
        <div class="border-t border-gray-100 pt-4 mt-2">
            <h4 class="text-sm font-bold text-gray-700 mb-3">Datos del Contrato Inicial</h4>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="agregar_modalidad" value="Modalidad de Contrato" />
                    <input type="text" id="agregar_modalidad" name="modalidad" required maxlength="255"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                        placeholder="Ej: Contrato a Plazo Fijo, Part-time..." oninput="this.value = this.value.toUpperCase()">
                </div>
                <div>
                    <x-input-label for="agregar_fecha_fin" value="Fecha Fin" />
                    <input type="date" id="agregar_fecha_fin" name="fecha_fin" required
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
                </div>
            </div>
            <div class="mt-3">
                <x-input-label for="agregar_detalle_contrato" value="Detalle del Contrato (opcional)" />
                <input type="text" id="agregar_detalle_contrato" name="detalle" maxlength="255"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                    placeholder="Ej: Observaciones de contrato...">
            </div>
        </div>

        <div>
            <x-input-label for="agregar_observaciones" value="Observaciones (opcional)" />
            <textarea id="agregar_observaciones" name="observaciones" rows="3"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                placeholder="Alguna observación adicional..."></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button type="button" onclick="toggleModal('modalAgregar')"
                class="px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-350 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                Cancelar
            </button>
            <button type="submit" id="btnGuardarAgregar"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm text-white rounded-lg transition shadow"
                style="background-color: #16a34a !important; color: white !important;"
                onmouseover="this.style.backgroundColor='#15803d';"
                onmouseout="this.style.backgroundColor='#16a34a';">
                <i id="btnGuardarAgregarIcon" class="bi bi-check-lg"></i>
                <span id="btnGuardarAgregarText">Guardar</span>
            </button>
        </div>
    </form>
</x-modal-show>


{{-- =====================================================================
    MODAL: VER / EDITAR TRABAJADORES (Vista All)
====================================================================== --}}
<x-modal-show id="modalTrabajadores" title="Trabajadores" size="full">
    {{-- Tarjeta cliente --}}
    <div class="w-full flex items-center gap-4 bg-blue-50 border border-blue-100 rounded-2xl px-5 py-4 mb-4">
        <div class="flex-shrink-0 w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-widest text-blue-500 leading-none mb-1">Cliente</p>
            <p id="modal-empresa-nombre" class="text-base font-bold text-blue-800 leading-tight truncate"></p>
            <p class="text-sm text-gray-500 mt-0.5">RUC: <span id="modal-empresa-ruc"
                    class="font-semibold text-gray-700"></span></p>
        </div>
    </div>

    <div class="mb-3 flex items-center justify-between gap-3">
        {{-- Barra de búsqueda --}}
        <div class="relative flex-1 max-w-xs">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z" />
                </svg>
            </span>
            <input type="text" id="buscador-trabajadores" placeholder="Buscar por nombre, DNI o cargo..."
                class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring focus:ring-blue-200 transition"
                autocomplete="off">
        </div>
        {{-- Contador: activos (se actualiza al buscar) + total fijo --}}
        <div class="flex items-center gap-2 text-sm whitespace-nowrap" id="modal-contador-trabajadores">
            <span id="modal-badge-activos"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 hidden">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                <span id="modal-num-activos">0</span> activos
            </span>
            <span id="modal-badge-total"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 hidden">
                <span id="modal-num-total">0</span> total
            </span>
        </div>
    </div>

    <div class="overflow-x-hidden overflow-y-auto max-h-[75vh]">
        <div id="trabajadores-loading" class="text-center py-10 text-gray-500">
            <i class="bi bi-arrow-repeat text-2xl"></i> Cargando...
        </div>
        <table id="tabla-trabajadores" class="w-full text-sm hidden">
            <thead class="bg-gray-100 text-gray-700 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 border-b font-semibold text-left">DNI</th>
                    <th class="px-3 py-2 border-b font-semibold text-left">Nombre</th>
                    <th class="px-3 py-2 border-b font-semibold text-left">Cargo</th>
                    <th class="px-3 py-2 border-b font-semibold text-left">Sueldo</th>
                    <th class="px-3 py-2 border-b font-semibold text-left">F. Ingreso</th>
                    <th class="px-3 py-2 border-b font-semibold text-left">F. Cese</th>
                    <th class="px-3 py-2 border-b font-semibold text-center">DNI (PDF)</th>
                    <th class="px-3 py-2 border-b font-semibold text-center">Firma</th>
                    <th class="px-3 py-2 border-b font-semibold text-left">Referidos</th>
                    <th class="px-3 py-2 border-b font-semibold text-left">Observaciones</th>
                    @if (Auth::user()->area->nombre != 'AUDITORIA')
                        <th class="px-3 py-2 border-b font-semibold text-center">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody id="tbody-trabajadores" class="divide-y divide-gray-100"></tbody>
        </table>
        <div id="trabajadores-sin-datos" class="hidden text-center py-10 text-gray-400 text-sm">
            <i class="bi bi-inbox text-3xl block mb-2"></i> Esta empresa no tiene trabajadores registrados.
        </div>
    </div>
</x-modal-show>


{{-- =====================================================================
     MODAL: EDITAR TRABAJADOR
====================================================================== --}}
<x-modal-show id="modalEditar" title="Editar Trabajador" static="true">
    <form id="formEditar" class="space-y-4">
        @csrf
        <input type="hidden" id="editar_id" name="id_planilla">
        <input type="hidden" id="editar_empresa_id">

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="editar_dni" value="DNI" />
                <input type="text" id="editar_dni" name="dni" maxlength="8" required pattern="\d{8}"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
            <div>
                <x-input-label for="editar_nombre" value="Apellidos y Nombres" />
                <input type="text" id="editar_nombre" name="nombre_trabajador" maxlength="200" required
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                    oninput="this.value = this.value.toUpperCase()">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="editar_fecha_ingreso" value="Fecha de Ingreso" />
                <input type="date" id="editar_fecha_ingreso" name="fecha_ingreso" required
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
            </div>
            <div>
                <x-input-label for="editar_fecha_cese" value="Fecha de Cese (opcional)" />
                <input type="date" id="editar_fecha_cese" name="fecha_cese"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="editar_cargo" value="Cargo" />
                <input type="text" id="editar_cargo" name="cargo" maxlength="150" required
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                    oninput="this.value = this.value.toUpperCase()">
            </div>
            <div>
                <x-input-label for="editar_sueldo" value="Sueldo (S/.)" />
                <input type="number" id="editar_sueldo" name="sueldo" min="0" step="0.01" required
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="editar_referido" value="Referido (opcional)" />
                <input type="text" id="editar_referido" name="referido" maxlength="200"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                    oninput="this.value = this.value.toUpperCase()">
            </div>
            <div class="flex flex-col justify-end">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="editar_archivo_dni" value="DNI (PDF) - Opcional" />
                <input type="file" id="editar_archivo_dni" name="archivo_dni" accept="application/pdf"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                <p id="editar_archivo_dni_actual" class="text-xs text-gray-500 mt-1 italic hidden"></p>
            </div>
            <div>
                <x-input-label for="editar_firma_digital" value="Firma Digital (Imagen) - Opcional" />
                <input type="file" id="editar_firma_digital" name="firma_digital" accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition">
                <p id="editar_firma_digital_actual" class="text-xs text-gray-500 mt-1 italic hidden"></p>
            </div>
        </div>

        <div>
            <x-input-label for="editar_observaciones" value="Observaciones (opcional)" />
            <textarea id="editar_observaciones" name="observaciones" rows="3"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button type="button" id="btnCancelarEditar"
                class="px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-350 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                Cancelar
            </button>
            <button type="submit" id="btnGuardarEditar"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm text-white rounded-lg transition shadow"
                style="background-color: #eab308 !important; color: white !important;"
                onmouseover="this.style.backgroundColor='#ca8a04';"
                onmouseout="this.style.backgroundColor='#eab308';">
                <i id="btnGuardarEditarIcon" class="bi bi-pencil-fill"></i>
                <span id="btnGuardarEditarText">Actualizar</span>
            </button>
        </div>
    </form>
</x-modal-show>

{{-- =====================================================================
     MODAL: CONTROL DE CONTRATOS
====================================================================== --}}
<x-modal-show id="modalControlContratos" title="Control de Contratos" size="full">
    {{-- Tarjeta cliente --}}
    <div class="w-full flex items-center gap-4 bg-purple-50 border border-purple-100 rounded-2xl px-5 py-4 mb-4">
        <div class="flex-shrink-0 w-11 h-11 bg-purple-100 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-widest text-purple-500 leading-none mb-1">Empresa</p>
            <p id="contratos-empresa-nombre" class="text-base font-bold text-purple-800 leading-tight truncate"></p>
            <p class="text-sm text-gray-500 mt-0.5">RUC: <span id="contratos-empresa-ruc" class="font-semibold text-gray-700"></span></p>
        </div>
    </div>

    {{-- Filtro de trabajadores --}}
    <div class="mb-3 flex items-center justify-between gap-3">
        <div class="relative flex-1 max-w-xs">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z" />
                </svg>
            </span>
            <input type="text" id="buscador-contratos-trabajadores" placeholder="Buscar por trabajador o DNI..."
                class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring focus:ring-purple-200 transition"
                autocomplete="off">
        </div>
    </div>

    <div class="overflow-x-auto overflow-y-auto max-h-[70vh] border border-gray-200 rounded-xl shadow-inner">
        <div id="contratos-loading" class="text-center py-10 text-gray-500">
            <i class="bi bi-arrow-repeat text-2xl animate-spin inline-block mr-2"></i> Cargando control de contratos...
        </div>
        <table id="tabla-contratos-control" class="min-w-full divide-y divide-gray-200 text-sm hidden">
            <thead class="bg-gray-950 text-white font-semibold sticky top-0 z-10">
                <tr class="text-left">
                    <th class="px-4 py-3 text-center w-12 border border-gray-800">Nº</th>
                    <th class="px-4 py-3 w-32 border border-gray-800">DNI</th>
                    <th class="px-4 py-3 w-64 border border-gray-800">APELLIDOS | NOMBRES</th>
                    <th class="px-4 py-3 w-40 border border-gray-800">TIPO DOC.</th>
                    <th class="px-4 py-3 w-40 border border-gray-800">CARGO</th>
                    <th class="px-4 py-3 w-36 border border-gray-800">FECHA INICIO</th>
                    <th class="px-4 py-3 w-36 border border-gray-800">FECHA FIN</th>
                    <th class="px-4 py-3 w-32 border border-gray-800">SUELDO</th>
                    <th class="px-4 py-3 border border-gray-800">DETALLE</th>
                    @if (Auth::user()->area->nombre != 'AUDITORIA')
                        <th class="px-4 py-3 w-20 text-center border border-gray-800">ACCIONES</th>
                    @endif
                </tr>
            </thead>
            <tbody id="tbody-contratos-control" class="bg-white divide-y divide-gray-100 font-medium"></tbody>
        </table>
        <div id="contratos-sin-datos" class="hidden text-center py-10 text-gray-400 text-sm">
            <i class="bi bi-folder-x text-3xl block mb-2"></i> No se encontraron trabajadores con contratos registrados.
        </div>
    </div>
</x-modal-show>

{{-- =====================================================================
    MODAL: AGREGAR CONTROL DE CONTRATO
====================================================================== --}}
<x-modal-show id="modalAgregarContratoControl" title="Agregar Control de Contrato">
    <form id="formAgregarContratoControl" class="space-y-4">
        @csrf
        <input type="hidden" id="contrato_id_control" name="id_control">
        <input type="hidden" id="contrato_id_planilla" name="id_planilla">

        {{-- Info trabajador --}}
        <div class="w-full flex items-center gap-4 bg-indigo-50 border border-indigo-100 rounded-2xl px-5 py-4">
            <div class="flex-shrink-0 w-11 h-11 bg-indigo-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-500 leading-none mb-1">Trabajador</p>
                <p id="contrato_trabajador_nombre_display" class="text-base font-bold text-indigo-800 leading-tight truncate"></p>
                <p class="text-sm text-gray-500 mt-0.5">DNI: <span id="contrato_trabajador_dni_display" class="font-semibold text-gray-700"></span></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="contrato_tipo_doc" value="Tipo de Documento *" />
                <input type="text" id="contrato_tipo_doc" name="tipo_doc" required maxlength="50"
                    list="tipo_doc_suggestions"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200"
                    placeholder="Ej: CONTRATO, ADENDA 01..." oninput="this.value = this.value.toUpperCase()">
                <datalist id="tipo_doc_suggestions">
                    <!-- Opciones dinámicas -->
                </datalist>
            </div>
            <div>
                <x-input-label for="contrato_modalidad" value="Modalidad" />
                <input type="text" id="contrato_modalidad" name="modalidad" required maxlength="255"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200"
                    placeholder="Ej: Contrato a Plazo Fijo, Part-time..." oninput="this.value = this.value.toUpperCase()">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="contrato_sueldo" value="Sueldo (S/.) *" />
                <input type="number" step="0.01" id="contrato_sueldo" name="sueldo" required min="0"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200 dark:bg-zinc-800 dark:border-zinc-700"
                    placeholder="0.00">
            </div>
            <div>
                <x-input-label for="contrato_cargo" value="Cargo *" />
                <input type="text" id="contrato_cargo" name="cargo" required maxlength="150"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200"
                    placeholder="Ej: Administrador, Operario..." oninput="this.value = this.value.toUpperCase()">
            </div>
        </div>

        {{-- Checkbox Indeterminado --}}
        <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-3">
            <input type="checkbox" id="contrato_es_indeterminado" name="es_indeterminado" value="1"
                class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring focus:ring-emerald-200 focus:ring-offset-0 cursor-pointer">
            <label for="contrato_es_indeterminado" class="text-sm font-bold text-emerald-800 cursor-pointer select-none">
                ¿Es un contrato INDETERMINADO?
            </label>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="contrato_fecha_inicio" value="Fecha de Inicio" />
                <input type="date" id="contrato_fecha_inicio" name="fecha_inicio" required
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200">
            </div>
            <div>
                <x-input-label for="contrato_fecha_fin" value="Fecha Fin" />
                <input type="date" id="contrato_fecha_fin" name="fecha_fin"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200">
            </div>
        </div>

        <div>
            <x-input-label for="contrato_detalle" value="Detalle / Observaciones (opcional)" />
            <input type="text" id="contrato_detalle" name="detalle" maxlength="255"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200"
                placeholder="Ej: Renovación regular...">
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button type="button" onclick="toggleModal('modalAgregarContratoControl')"
                class="px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-350 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                Cancelar
            </button>
            <button type="submit" id="btnGuardarContratoControl"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm text-white rounded-lg transition shadow"
                style="background-color: #4f46e5 !important; color: white !important;"
                onmouseover="this.style.backgroundColor='#4338ca';"
                onmouseout="this.style.backgroundColor='#4f46e5';">
                <i id="btnGuardarContratoControlIcon" class="bi bi-check-lg"></i>
                <span id="btnGuardarContratoControlText">Guardar Contrato</span>
            </button>
        </div>
    </form>
</x-modal-show>

{{-- =====================================================================
     MODAL: CONTEO DE TRABAJADORES
====================================================================== --}}
<x-modal-show id="modalConteo" title="Conteo de Trabajadores" size="large">

    {{-- Tarjetas resumen --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="rounded-xl bg-blue-50 border border-blue-100 px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="bi bi-building text-blue-500 text-base"></i>
            </div>
            <div>
                <p class="text-xs text-blue-400 font-semibold uppercase tracking-wide leading-none mb-0.5">A1</p>
                <p class="text-xl font-bold text-blue-700 leading-none" id="card-total-a1">—</p>
            </div>
        </div>
        <div class="rounded-xl bg-purple-50 border border-purple-100 px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                <i class="bi bi-building text-purple-500 text-base"></i>
            </div>
            <div>
                <p class="text-xs text-purple-400 font-semibold uppercase tracking-wide leading-none mb-0.5">R1</p>
                <p class="text-xl font-bold text-purple-700 leading-none" id="card-total-r1">—</p>
            </div>
        </div>
        <div class="rounded-xl bg-gray-50 border border-gray-200 px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0">
                <i class="bi bi-people text-gray-500 text-base"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide leading-none mb-0.5">Total</p>
                <p class="text-xl font-bold text-gray-700 leading-none" id="card-total-general">—</p>
            </div>
        </div>
    </div>

    {{-- Filtro select --}}
    <div class="flex items-center gap-2 mb-4">
        <label for="filtro-conteo"
            class="text-xs font-semibold text-gray-400 uppercase tracking-wide whitespace-nowrap">Mostrar:</label>
        <select id="filtro-conteo"
            class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 shadow-sm focus:outline-none focus:ring focus:ring-blue-200 text-gray-700">
            <option value="activos">✅ Activos</option>
            <option value="cesados">🔴 Cesados</option>
            <option value="ambos">🔘 Ambos</option>
        </select>
    </div>

    {{-- Tabla --}}
    <div class="overflow-auto max-h-[55vh] rounded-xl border border-gray-200">
        <div id="conteo-loading" class="text-center py-10 text-gray-400">
            <i class="bi bi-arrow-repeat text-3xl block mb-2 animate-spin"></i>
            <span class="text-sm">Cargando datos...</span>
        </div>
        <table id="tabla-conteo" class="w-full text-sm hidden">
            <thead class="bg-gray-50 text-gray-500 sticky top-0 z-10">
                <tr class="border-b border-gray-200">
                    <th class="px-4 py-3 font-semibold text-left text-xs uppercase tracking-wide">Empresa</th>
                    <th class="px-4 py-3 font-semibold text-center text-xs uppercase tracking-wide">
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>A1
                        </span>
                    </th>
                    <th class="px-4 py-3 font-semibold text-center text-xs uppercase tracking-wide">
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-purple-400 inline-block"></span>R1
                        </span>
                    </th>
                    <th class="px-4 py-3 font-semibold text-center text-xs uppercase tracking-wide">Total</th>
                </tr>
            </thead>
            <tbody id="tbody-conteo" class="divide-y divide-gray-100 bg-white"></tbody>
            <tfoot class="bg-gray-50 border-t-2 border-gray-200 sticky bottom-0">
                <tr class="font-bold text-gray-700">
                    <td class="px-4 py-3 text-sm">Total general</td>
                    <td class="px-4 py-3 text-center text-blue-600" id="conteo-total-a1">0</td>
                    <td class="px-4 py-3 text-center text-purple-600" id="conteo-total-r1">0</td>
                    <td class="px-4 py-3 text-center" id="conteo-total-general">0</td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-modal-show>

{{-- =====================================================================
     MODAL: CONTRATOS POR VENCER CON FILTRO POR MES
====================================================================== --}}
<x-modal-show id="modalVencimientosPlanilla" title="Contratos por Vencer" size="large">
    {{-- Tarjeta Control de Vencimientos --}}
    <div class="w-full flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50 border border-gray-150 rounded-2xl px-5 py-4 mb-4">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-11 h-11 bg-blue-50 dark:bg-blue-950/20 text-blue-500 rounded-xl flex items-center justify-center border border-blue-100 shadow-sm">
                <i class="bi bi-clock-history text-xl"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-800">
                    Control de Vencimientos
                </h3>
                <p class="text-xs text-gray-400">
                    Visualiza los contratos que están próximos a finalizar.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Filtrar por:</span>
            <div class="flex items-center gap-1.5">
                <select id="filtro_mes_vencimientos_mes" class="text-sm border border-gray-300 rounded-lg pl-2.5 pr-8 py-1.5 shadow-sm focus:outline-none focus:ring focus:ring-amber-200 text-gray-700 font-medium bg-white">
                    <option value="01">Enero</option>
                    <option value="02">Febrero</option>
                    <option value="03">Marzo</option>
                    <option value="04">Abril</option>
                    <option value="05">Mayo</option>
                    <option value="06">Junio</option>
                    <option value="07">Julio</option>
                    <option value="08">Agosto</option>
                    <option value="09">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>
                <select id="filtro_mes_vencimientos_anio" class="text-sm border border-gray-300 rounded-lg pl-2.5 pr-8 py-1.5 shadow-sm focus:outline-none focus:ring focus:ring-amber-200 text-gray-700 font-medium bg-white">
                    <!-- Se llenará con JavaScript -->
                </select>
            </div>
        </div>
    </div>

    {{-- Referencia de colores --}}
    <div class="flex items-center gap-2 mb-6 px-1 py-1.5 text-[10px] font-semibold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 flex-wrap">
        <span class="mr-2">Referencia:</span>
        <div class="flex items-center gap-4 flex-wrap">
            <span class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-sm"></span>
                &le; 7 Días o Vencido
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-sm"></span>
                &le; 15 Días
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-sm"></span>
                &gt; 15 Días
            </span>
        </div>
    </div>

    {{-- Contenedor de Tarjetas --}}
    <div class="overflow-y-auto max-h-[60vh] pr-1 space-y-3" id="vencimientos-planilla-lista">
        <div id="vencimientos-planilla-loading" class="text-center py-12 text-gray-400">
            <i class="bi bi-arrow-repeat text-3xl animate-spin inline-block mb-2"></i>
            <p class="text-sm font-medium">Cargando contratos por vencer...</p>
        </div>
        <div id="vencimientos-planilla-vacio" class="hidden text-center py-12 text-gray-400">
            <i class="bi bi-calendar-check text-4xl block mb-2 text-gray-300"></i>
            <p class="text-sm font-medium">No hay contratos que venzan en este mes.</p>
        </div>
        <div id="vencimientos-planilla-content" class="grid grid-cols-1 gap-3"></div>
    </div>
</x-modal-show>

{{-- =====================================================================
     STYLES
====================================================================== --}}
<style>
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

    @keyframes pulseSubtle {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.93; }
    }
    .animate-pulse-subtle {
        animation: pulseSubtle 3s infinite ease-in-out;
    }
    @keyframes bounceSubtle {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-3px); }
    }
    .animate-bounce-subtle {
        animation: bounceSubtle 2s infinite ease-in-out;
    }

    /* Tooltip */
    .tooltip {
        position: relative;
    }

    .tooltip .tooltip-text {
        visibility: hidden;
        background-color: #1f2937;
        color: #fff;
        text-align: center;
        padding: 4px 8px;
        border-radius: 6px;
        position: absolute;
        z-index: 100;
        bottom: 130%;
        left: 50%;
        transform: translateX(-50%);
        white-space: nowrap;
        font-size: 11px;
        opacity: 0;
        transition: opacity .2s;
    }

    .tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }



    /* Fix stacking order for modals opened on top of modalTrabajadores and modalVencimientosPlanilla */
    #modalAgregarContratoControl, #modalControlContratos, #modalEditar {
        z-index: 60 !important;
    }
</style>


{{-- =====================================================================
     SCRIPTS
====================================================================== --}}
<script>
    const userArea = "{{ Auth::user()->area->nombre }}";

    $(document).ready(function() {

        // ─── DATATABLES ──────────────────────────────────────────────────────
        $('#myTable-planillas').DataTable({
            responsive: false,
            paging: true,
            ordering: true,
            pageLength: 100,
            language: {
                url: '/js/datatables/lang/es-ES.json'
            },
            order: [
                [1, 'asc']
            ],
            columnDefs: [{
                orderable: false,
                targets: [5]
            }]
        });

        // ─── TOAST ───────────────────────────────────────────────────────────
        function mostrarToast(mensaje, tipo = 'success') {
            if (typeof showAjaxToast === 'function') {
                showAjaxToast(mensaje, tipo);
            }
        }

        function mostrarGolemCargando(mensaje = 'Guardando información...') {
            // No-op (cargando desactivado por solicitud del usuario)
        }

        function ocultarGolemCargando() {
            // No-op (cargando desactivado por solicitud del usuario)
        }

        function actualizarAlertVencimientos() {
            const periodo = obtenerPeriodoSeleccionado();
            if (periodo) {
                cargarVencimientosPlanilla(periodo);
            }
        }

        // ─── HELPERS MODAL ───────────────────────────────────────────────────
        function abrirModal(id) {
            toggleModal(id);
        }

        function cerrarModal(id) {
            toggleModal(id);
        }

        function formatearFecha(str) {
            if (!str) return '—';
            const [y, m, d] = str.split('-');
            return `${d}/${m}/${y}`;
        }

        // ─── EMPRESA ACTIVA EN VISTA ALL ─────────────────────────────────────
        let empresaActivaId = null;

        // ─── CARGAR TRABAJADORES ─────────────────────────────────────────────
        function cargarTrabajadores(empresaId) {
            empresaActivaId = empresaId;
            $('#trabajadores-loading').removeClass('hidden');
            $('#tabla-trabajadores').addClass('hidden');
            $('#trabajadores-sin-datos').addClass('hidden');
            $('#tbody-trabajadores').empty();

            $.get(`/planillas/trabajadores/${empresaId}`, function(res) {
                $('#trabajadores-loading').addClass('hidden');
                const tbody = $('#tbody-trabajadores');
                const lista = res.trabajadores;

                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);

                const activos = lista.filter(function(t) {
                    if (!t.fecha_cese) return true; // sin fecha de cese = activo
                    const cese = new Date(t.fecha_cese + 'T00:00:00');
                    return cese > hoy; // solo futuro = activo
                });

                $('#modal-num-activos').text(activos.length);
                $('#modal-num-total').removeData('total');
                $('#modal-num-total').text(lista.length);
                $('#modal-badge-activos').removeClass('hidden').data('activos-total', activos.length);
                $('#modal-badge-total').removeClass('hidden');
                // Guardar total real para restaurar tras búsqueda
                $('#modal-num-total').data('total', lista.length);

                // Actualizar contador en la tabla principal
                $(`#fila-empresa-${empresaId} .col-activos`).text(activos.length);

                if (!lista.length) {
                    $('#trabajadores-sin-datos').removeClass('hidden');
                    return;
                }

                lista.sort((a, b) => {
                    const aActivo = !a.fecha_cese;
                    const bActivo = !b.fecha_cese;

                    if (aActivo && !bActivo) return -1;
                    if (!aActivo && bActivo) return 1;

                    return 0;
                });

                lista.forEach(function(t) {
                    const ceseFmt = t.fecha_cese ?
                        `<span class="text-red-500 font-semibold">${formatearFecha(t.fecha_cese)}</span>` :
                        '<span class="text-gray-400">—</span>';

                    const $tr = $('<tr class="hover:bg-gray-50"></tr>').attr('id',
                        `fila-trabajador-${t.id_planilla}`);
                    $tr.append(`<td class="px-3 py-2 border-b">${t.dni}</td>`);
                    $tr.append(
                        `<td class="px-3 py-2 border-b font-medium">${t.nombre_trabajador}</td>`
                    );
                    $tr.append(`<td class="px-3 py-2 border-b">${t.cargo}</td>`);
                    $tr.append(
                        `<td class="px-3 py-2 border-b">S/. ${parseFloat(t.sueldo).toFixed(2)}</td>`
                    );
                    $tr.append(
                        `<td class="px-3 py-2 border-b">${formatearFecha(t.fecha_ingreso)}</td>`
                    );
                    $tr.append(`<td class="px-3 py-2 border-b">${ceseFmt}</td>`);

                    const dniLink = t.archivo_dni ?
                        `<a href="${t.archivo_dni}" target="_blank" class="text-blue-500 hover:text-blue-700 transition flex items-center justify-center gap-1 font-semibold"><i class="bi bi-file-earmark-pdf"></i> Ver</a>` :
                        '<span class="text-gray-400 text-center block">—</span>';
                    const firmaLink = t.firma_digital ?
                        `<a href="${t.firma_digital}" target="_blank" class="text-emerald-500 hover:text-emerald-700 transition flex items-center justify-center gap-1 font-semibold"><i class="bi bi-image"></i> Ver</a>` :
                        '<span class="text-gray-400 text-center block">—</span>';

                    $tr.append(`<td class="px-3 py-2 border-b text-center">${dniLink}</td>`);
                    $tr.append(`<td class="px-3 py-2 border-b text-center">${firmaLink}</td>`);

                    $tr.append(
                        `<td class="px-3 py-2 border-b">${t.referido ? t.referido : '<span class="text-gray-400">—</span>'}</td>`
                    );

                    $tr.append(
                        `<td class="px-3 py-2 border-b max-w-[200px]">${t.observaciones ? `<span class="text-gray-600">${t.observaciones}</span>` : '<span class="text-gray-400">—</span>'}</td>`
                    );

                    // Botón editar — datos via .data() para evitar problemas con comillas
                    const $btnEditar = $(`
                        <a role="button" class="flex items-center rounded-md tooltip btn-editar-trabajador"
                            style="color:#e6a817;" onmouseover="this.style.color='#f5bc42';" onmouseout="this.style.color='#e6a817';">
                            <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                            </svg>
                            <span class="tooltip-text">Editar</span>
                        </a>
                    `);
                    $btnEditar.data('id', t.id_planilla);
                    $btnEditar.data('dni', t.dni);
                    $btnEditar.data('nombre', t.nombre_trabajador);
                    $btnEditar.data('cargo', t.cargo);
                    $btnEditar.data('sueldo', t.sueldo);
                    $btnEditar.data('fechaIngreso', t.fecha_ingreso ?? '');
                    $btnEditar.data('fechaCese', t.fecha_cese ?? '');
                    $btnEditar.data('referido', t.referido ?? '');
                    $btnEditar.data('observaciones', t.observaciones ?? '');
                    $btnEditar.data('archivoDni', t.archivo_dni ?? '');
                    $btnEditar.data('firmaDigital', t.firma_digital ?? '');

                    // Botón eliminar
                    const $btnEliminar = $(`
                        <a role="button" class="flex items-center rounded-md tooltip btn-eliminar-trabajador"
                            style="color:#cc2929;" onmouseover="this.style.color='#ff4444';" onmouseout="this.style.color='#cc2929';">
                            <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                            <span class="tooltip-text">Eliminar</span>
                        </a>
                    `);
                    $btnEliminar.data('id', t.id_planilla);
                    $btnEliminar.data('nombre', t.nombre_trabajador);

                    if (userArea !== 'AUDITORIA') {
                        const $tdAcciones = $(
                            '<td class="px-3 py-2 border-b text-center"></td>');
                        const $divAcciones = $(
                            '<div class="flex items-center justify-center gap-3"></div>');

                        // Botón: Agregar control de contratos
                        const $btnAgregarContrato = $(`
                            <a role="button" class="flex items-center rounded-md tooltip btn-agregar-contrato-modal"
                                style="color:#6366f1;" onmouseover="this.style.color='#4f46e5';" onmouseout="this.style.color='#6366f1';">
                                <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                                <span class="tooltip-text">Agregar Contrato</span>
                            </a>
                        `);
                        $btnAgregarContrato.attr('data-id', t.id_planilla);
                        $btnAgregarContrato.attr('data-nombre', t.nombre_trabajador);
                        $btnAgregarContrato.attr('data-dni', t.dni);
                        $btnAgregarContrato.data('id', t.id_planilla);
                        $btnAgregarContrato.data('nombre', t.nombre_trabajador);
                        $btnAgregarContrato.data('dni', t.dni);

                        $divAcciones.append($btnEditar).append($btnAgregarContrato).append($btnEliminar);
                        $tdAcciones.append($divAcciones);
                        $tr.append($tdAcciones);
                    }

                    tbody.append($tr);
                });

                $('#tabla-trabajadores').removeClass('hidden');
            }).fail(function() {
                $('#trabajadores-loading').addClass('hidden');
                mostrarToast('Error al cargar trabajadores.', 'error');
            });
        }

        // ─── ABRIR MODAL AGREGAR ─────────────────────────────────────────────
        $(document).on('click', '.btn-agregar-trabajador', function() {
            const id = $(this).data('id');
            const empresa = $(this).data('empresa');
            const ruc = $(this).data('ruc');

            $('#formAgregar')[0].reset();
            $('#agregar_empresa_id').val(id);
            $('#agregar_empresa_nombre').val(empresa);
            $('#agregar_empresa_ruc').val(ruc);
            $('#agregar_empresa_nombre_display').text(empresa);
            $('#agregar_empresa_ruc_display').text(ruc || '—');
            abrirModal('modalAgregar');
        });

        // ─── GUARDAR NUEVO TRABAJADOR ────────────────────────────────────────
        $('#formAgregar').on('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnGuardarAgregar');
            btn.disabled = true;
            $('#btnGuardarAgregarIcon').attr('class', 'bi bi-arrow-repeat');
            $('#btnGuardarAgregarText').text('Guardando...');
            mostrarGolemCargando('Guardando trabajador...');

            const formData = new FormData(this);

            $.ajax({
                url: '/planillas',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    cerrarModal('modalAgregar');
                    mostrarToast(res.message || 'Trabajador agregado.', 'success');
                    actualizarAlertVencimientos();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.errors ?
                        Object.values(xhr.responseJSON.errors).flat().join(' | ') :
                        (xhr.responseJSON?.message || 'Error al guardar.');
                    mostrarToast(msg, 'error');
                },
                complete: function() {
                    btn.disabled = false;
                    $('#btnGuardarAgregarIcon').attr('class', 'bi bi-check-lg');
                    $('#btnGuardarAgregarText').text('Guardar');
                    ocultarGolemCargando();
                }
            });
        });

        // ─── ABRIR MODAL VER TRABAJADORES ───────────────────────────────────
        $(document).on('click', '.btn-ver-trabajadores', function() {
            const id = $(this).data('id');
            const empresa = $(this).data('empresa');
            const ruc = $(this).data('ruc');

            $('#modal-empresa-nombre').text(empresa);
            $('#modal-empresa-ruc').text(ruc || '—');
            abrirModal('modalTrabajadores');
            cargarTrabajadores(id);
        });

        // ─── EDITAR TRABAJADOR (delegado desde tbody) ─────────────────────────
        $('#tbody-trabajadores').on('click', '.btn-editar-trabajador', function() {
            const d = $(this).data();

            $('#editar_id').val(d.id);
            $('#editar_empresa_id').val(empresaActivaId);
            $('#editar_dni').val(d.dni);
            $('#editar_nombre').val(d.nombre);
            $('#editar_cargo').val(d.cargo);
            $('#editar_sueldo').val(d.sueldo);
            $('#editar_fecha_ingreso').val(d.fechaIngreso);
            $('#editar_fecha_cese').val(d.fechaCese);
            $('#editar_referido').val(d.referido);
            $('#editar_observaciones').val(d.observaciones);

            if (d.archivoDni) {
                $('#editar_archivo_dni_actual').text('Ya tiene PDF cargado').removeClass('hidden');
            } else {
                $('#editar_archivo_dni_actual').addClass('hidden');
            }

            if (d.firmaDigital) {
                $('#editar_firma_digital_actual').text('Ya tiene firma cargada').removeClass('hidden');
            } else {
                $('#editar_firma_digital_actual').addClass('hidden');
            }

            abrirModal('modalEditar');
        });

        // ─── CERRAR MODAL EDITAR ─────────────────────────────────────────────
        $('#btnCancelarEditar').on('click', function() {
            cerrarModal('modalEditar');
        });

        // ─── GUARDAR EDICIÓN ─────────────────────────────────────────────────
        $('#formEditar').on('submit', function(e) {
            e.preventDefault();
            const id = $('#editar_id').val();
            const btn = document.getElementById('btnGuardarEditar');
            btn.disabled = true;
            $('#btnGuardarEditarIcon').attr('class', 'bi bi-arrow-repeat');
            $('#btnGuardarEditarText').text('Actualizando...');
            mostrarGolemCargando('Actualizando trabajador...');

            const formData = new FormData(this);

            $.ajax({
                url: `/planillas/${id}/actualizar`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    cerrarModal('modalEditar');
                    mostrarToast(res.message || 'Trabajador actualizado.', 'success');
                    if (empresaActivaId) cargarTrabajadores(empresaActivaId);
                    actualizarAlertVencimientos();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.errors ?
                        Object.values(xhr.responseJSON.errors).flat().join(' | ') :
                        (xhr.responseJSON?.message || 'Error al actualizar.');
                    mostrarToast(msg, 'error');
                },
                complete: function() {
                    btn.disabled = false;
                    $('#btnGuardarEditarIcon').attr('class', 'bi bi-pencil-fill');
                    $('#btnGuardarEditarText').text('Actualizar');
                    ocultarGolemCargando();
                }
            });
        });

        // ─── ELIMINAR UN TRABAJADOR (delegado desde tbody) ───────────────────
        $('#tbody-trabajadores').on('click', '.btn-eliminar-trabajador', function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');

            if (!confirm(`¿Estás seguro de eliminar al trabajador "${nombre}"?`)) return;

            $.ajax({
                url: `/planillas/${id}/eliminar`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    $(`#fila-trabajador-${id}`).remove();
                    mostrarToast(res.message || 'Trabajador eliminado.', 'success');

                    const total = $('#tbody-trabajadores tr[id^="fila-trabajador-"]')
                        .length;

                    // Recalcular activos
                    const hoy = new Date();
                    hoy.setHours(0, 0, 0, 0);
                    let activos = 0;
                    $('#tbody-trabajadores tr[id^="fila-trabajador-"]').each(function() {
                        const ceseTexto = $(this).find('td:nth-child(6)').text()
                            .trim();
                        const esActivo = ceseTexto === '—' || (function() {
                            const parts = ceseTexto.split('/');
                            if (parts.length !== 3) return false;
                            const cese = new Date(
                                `${parts[2]}-${parts[1]}-${parts[0]}T00:00:00`
                            );
                            return cese > hoy;
                        })();
                        if (esActivo) activos++;
                    });

                    // Actualizar badges
                    $('#modal-num-activos').text(activos);
                    $('#modal-num-total').text(total).data('total', total);

                    // Actualizar contador en la tabla principal
                    $(`#fila-empresa-${empresaActivaId} .col-activos`).text(activos);

                    if (!total) {
                        $('#tabla-trabajadores').addClass('hidden');
                        $('#trabajadores-sin-datos').removeClass('hidden');
                    }
                    actualizarAlertVencimientos();
                },
                error: function() {
                    mostrarToast('Error al eliminar el trabajador.', 'error');
                }
            });
        });

        // ─── BUSCADOR EN MODAL TRABAJADORES ─────────────────────────────────
        $('#buscador-trabajadores').on('input', function() {
            const termino = $(this).val().toLowerCase().trim();
            let visibles = 0;

            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            let activosVisibles = 0;

            $('#tbody-trabajadores tr[id^="fila-trabajador-"]').each(function() {
                const dni = $(this).find('td:nth-child(1)').text().toLowerCase();
                const nombre = $(this).find('td:nth-child(2)').text().toLowerCase();
                const cargo = $(this).find('td:nth-child(3)').text().toLowerCase();
                const ceseTexto = $(this).find('td:nth-child(6)').text().trim();

                const coincide = !termino || dni.includes(termino) || nombre.includes(
                    termino) || cargo.includes(termino);
                $(this).toggle(coincide);

                if (coincide) {
                    visibles++;
                    // Contar activos: sin fecha de cese o fecha futura
                    const esActivo = ceseTexto === '—' || (function() {
                        const parts = ceseTexto.split('/');
                        if (parts.length !== 3) return false;
                        const cese = new Date(
                            `${parts[2]}-${parts[1]}-${parts[0]}T00:00:00`);
                        return cese > hoy;
                    })();
                    if (esActivo) activosVisibles++;
                }
            });

            // Actualizar badge activos con los visibles filtrados
            $('#modal-num-activos').text(activosVisibles);

            // Actualizar badge total: si hay búsqueda mostrar "X de Y", si no, el total real
            let totalReal = $('#modal-num-total').data('total');

            if (typeof totalReal === 'undefined') {
                totalReal = $('#tbody-trabajadores tr').length; // fallback 🔥
            }
            if (termino) {
                $('#modal-badge-total').text(`${visibles} de ${totalReal} resultados`);
            } else {
                $('#modal-badge-total').text(`${totalReal} total`);
            }

            // Mensaje sin resultados
            $('#trabajadores-sin-busqueda').remove();
            if (termino && visibles === 0) {
                $('#tbody-trabajadores').append(
                    `<tr id="trabajadores-sin-busqueda"><td colspan="9" class="text-center py-6 text-gray-400 text-sm"><i class="bi bi-search block text-2xl mb-1"></i>Sin resultados para "${$('<span>').text(termino).html()}"</td></tr>`
                );
            }
        });

        // Limpiar buscador al abrir modal y guardar totales reales
        $(document).on('click', '.btn-ver-trabajadores', function() {
            $('#buscador-trabajadores').val('');
            $('#trabajadores-sin-busqueda').remove();
            // Reset badges para que no persistan entre empresas
            $('#modal-num-activos').text('0');
            $('#modal-num-total').text('0').removeData('total');
            $('#modal-badge-activos').addClass('hidden');
            $('#modal-badge-total').addClass('hidden');
        });

        // ─── ELIMINAR TODOS (doble confirmación) ─────────────────────────────
        $(document).on('click', '.btn-eliminar-todos', function() {
            const id = $(this).data('id');
            const empresa = $(this).data('empresa');

            if (!confirm(
                    `¿Deseas eliminar TODOS los trabajadores de "${empresa}"?\n\nEsta acción no se puede deshacer.`
                )) return;
            if (!confirm(
                    `⚠️ CONFIRMA de nuevo:\n¿Seguro que deseas eliminar TODOS los trabajadores de "${empresa}"?`
                )) return;

            $.ajax({
                url: `/planillas/empresa/${id}/eliminar-todos`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    mostrarToast(res.message || 'Todos los trabajadores eliminados.',
                        'success');
                    // Actualizar contador en la tabla principal
                    $(`#fila-empresa-${id} .col-activos`).text(0);
                },
                error: function() {
                    mostrarToast('Error al eliminar los trabajadores.', 'error');
                }
            });
        });

        // ─── CONTEO TRABAJADORES ─────────────────────────────────────────────
        let datosConteo = []; // cache de datos cargados

        function estaActivo(t) {
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            if (!t.fecha_cese) return true;
            const cese = new Date(t.fecha_cese + 'T00:00:00');
            return cese > hoy;
        }

        function renderizarConteo(filtro) {
            const tbody = $('#tbody-conteo');
            tbody.empty();
            let totalA1 = 0,
                totalR1 = 0;

            datosConteo.forEach(function(empresa) {
                let trabajadores = empresa.trabajadores;

                if (filtro === 'activos') trabajadores = trabajadores.filter(t => estaActivo(t));
                if (filtro === 'cesados') trabajadores = trabajadores.filter(t => !estaActivo(t));

                const total = trabajadores.length;
                if (!total) return;

                totalA1 += empresa.estado === 'A1' ? total : 0;
                totalR1 += empresa.estado === 'R1' ? total : 0;

                const badgeEstado = empresa.estado === 'A1' ?
                    `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-600">${empresa.estado}</span>` :
                    `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-600">${empresa.estado}</span>`;

                tbody.append(`
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                ${badgeEstado}
                                <span class="text-gray-700">${empresa.razon_social}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2.5 text-center font-semibold text-blue-600">${empresa.estado === 'A1' ? total : '<span class="text-gray-300">—</span>'}</td>
                        <td class="px-4 py-2.5 text-center font-semibold text-purple-600">${empresa.estado === 'R1' ? total : '<span class="text-gray-300">—</span>'}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-700 font-bold text-xs">${total}</span>
                        </td>
                    </tr>
                `);
            });

            $('#conteo-total-a1').text(totalA1);
            $('#conteo-total-r1').text(totalR1);
            $('#conteo-total-general').text(totalA1 + totalR1);

            // Actualizar tarjetas resumen
            $('#card-total-a1').text(totalA1);
            $('#card-total-r1').text(totalR1);
            $('#card-total-general').text(totalA1 + totalR1);
        }



        $('#btnConteoTrabajadores').on('click', function() {
            abrirModal('modalConteo');

            $('#conteo-loading').removeClass('hidden');
            $('#tabla-conteo').addClass('hidden');

            $.get('/planillas/conteo', function(res) {
                datosConteo = res.empresas;
                $('#conteo-loading').addClass('hidden');
                $('#tabla-conteo').removeClass('hidden');
                const filtroActivo = $('#filtro-conteo').val() || 'activos';
                renderizarConteo(filtroActivo);
            }).fail(function() {
                $('#conteo-loading').addClass('hidden');
                mostrarToast('Error al cargar el conteo.', 'error');
            });
        });

        $('#filtro-conteo').on('change', function() {
            renderizarConteo($(this).val());
        });

        // ─── CONTRATOS POR VENCER (FILTRO POR MES) ───────────────────────────
        const isAuditoria = userArea === 'AUDITORIA';

        function inicializarFiltrosAnioMes() {
            const selectAnio = $('#filtro_mes_vencimientos_anio');
            const selectMes = $('#filtro_mes_vencimientos_mes');
            selectAnio.empty();

            const hoy = new Date();
            const añoActual = hoy.getFullYear();
            const mesActual = String(hoy.getMonth() + 1).padStart(2, '0'); // '01'-'12'

            // Llenar años: desde añoActual - 3 hasta añoActual + 5
            for (let y = añoActual - 3; y <= añoActual + 5; y++) {
                const option = $('<option></option>').val(y).text(y);
                if (y === añoActual) {
                    option.attr('selected', 'selected');
                }
                selectAnio.append(option);
            }

            // Seleccionar mes actual
            selectMes.val(mesActual);
        }

        function obtenerPeriodoSeleccionado() {
            const mes = $('#filtro_mes_vencimientos_mes').val();
            const anio = $('#filtro_mes_vencimientos_anio').val();
            return `${anio}-${mes}`;
        }

        function cargarVencimientosPlanilla(periodo) {
            $('#vencimientos-planilla-loading').removeClass('hidden');
            $('#vencimientos-planilla-content').addClass('hidden');
            $('#vencimientos-planilla-vacio').addClass('hidden');

            $.get(`/planillas/vencimientos-data`, { periodo: periodo }, function(res) {
                $('#vencimientos-planilla-loading').addClass('hidden');
                const vencimientos = res.vencimientos;

                if (vencimientos.length === 0) {
                    $('#vencimientos-planilla-vacio').removeClass('hidden');
                    mostrarToast('No hay contratos por vencer en el periodo seleccionado.', 'info');
                    return;
                }

                $('#vencimientos-planilla-content').empty().removeClass('hidden');
                mostrarToast(`Se encontraron ${vencimientos.length} contratos por vencer.`, 'success');
                
                vencimientos.forEach(function(v) {
                    const iconColorClass = v.color === 'red' ? 'text-red-500 animate-pulse' : (v.color === 'amber' ? 'text-amber-500' : 'text-blue-500');
                    const iconClass = v.color === 'red' ? 'bi-exclamation-triangle-fill' : (v.color === 'amber' ? 'bi-exclamation-circle-fill' : 'bi-info-circle-fill');
                    
                    const borderLeftColor = v.color === 'red' ? 'border-l-red-500' : (v.color === 'amber' ? 'border-l-amber-500' : 'border-l-blue-500');
                    const badgeBgColor = v.color === 'red' ? 'bg-red-500' : (v.color === 'amber' ? 'bg-amber-500' : 'bg-blue-600');

                    const cardHtml = `
                        <div class="relative flex flex-col md:flex-row md:items-center justify-between p-4 bg-white hover:bg-gray-50 border border-gray-150 rounded-2xl shadow-sm transition-all duration-200 border-l-[6px] ${borderLeftColor} gap-4">
                            <div class="flex items-start gap-4">
                                <!-- Icon container -->
                                <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center flex-shrink-0 border border-gray-100 shadow-sm mt-0.5">
                                    <i class="bi ${iconClass} ${iconColorClass} text-xl"></i>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-gray-800 tracking-wide uppercase truncate max-w-[300px] md:max-w-[400px]" title="${v.planilla.nombre_trabajador}">
                                        ${v.planilla.nombre_trabajador}
                                    </h4>
                                    <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider mt-1">
                                        ORIGEN: <span class="text-gray-600">${v.planilla.empresa_nombre}</span>
                                    </p>
                                    
                                    <!-- Botones de Acción -->
                                    <div class="flex items-center gap-2 mt-3 flex-wrap">
                                        <!-- Botón: Control de Contratos -->
                                        <button type="button" class="btn-ver-contrato-alerta inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg shadow-sm transition-all"
                                                data-id="${v.planilla.id_empresa}"
                                                data-worker-name="${v.planilla.nombre_trabajador}"
                                                data-worker-dni="${v.planilla.dni}">
                                            <i class="bi bi-folder2-open text-xs"></i>
                                            <span>Control de Contratos</span>
                                        </button>
                                        
                                        <!-- Botón: Agregar Contrato -->
                                        ${!isAuditoria ? `
                                        <button type="button" class="btn-agregar-contrato-modal inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-250 rounded-lg shadow-sm transition-all"
                                                data-id="${v.planilla.id_planilla}"
                                                data-nombre="${v.planilla.nombre_trabajador}"
                                                data-dni="${v.planilla.dni}">
                                            <i class="bi bi-file-earmark-plus text-xs"></i>
                                            <span>Agregar Contrato</span>
                                        </button>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center gap-2 border-t border-gray-100 pt-3 md:pt-0 md:border-none">
                                <!-- Vence badge -->
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider text-white ${badgeBgColor}">
                                    ${v.texto_vence}
                                </span>
                                
                                <!-- Date -->
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 bg-gray-50 border border-gray-200 px-2.5 py-1 rounded-lg">
                                    <i class="bi bi-calendar3 text-gray-400"></i>
                                    ${v.fecha_fin}
                                </span>
                            </div>
                        </div>
                    `;
                    $('#vencimientos-planilla-content').append(cardHtml);
                });
            }).fail(function() {
                $('#vencimientos-planilla-loading').addClass('hidden');
                mostrarToast('Error al cargar contratos por vencer.', 'error');
            });
        }

        // Inicializar los selects
        inicializarFiltrosAnioMes();

        $('#btnVencimientoTrabajadoresModal').on('click', function() {
            abrirModal('modalVencimientosPlanilla');
            const periodo = obtenerPeriodoSeleccionado();
            cargarVencimientosPlanilla(periodo);
        });

        $('#filtro_mes_vencimientos_mes, #filtro_mes_vencimientos_anio').on('change', function() {
            const periodo = obtenerPeriodoSeleccionado();
            cargarVencimientosPlanilla(periodo);
        });

        // ─── VER CONTROL DE CONTRATOS DE UNA EMPRESA ─────────────────────────
        let empresaContratosId = null;

        function cargarContratosControl(empresaId) {
            empresaContratosId = empresaId;
            $('#contratos-loading').removeClass('hidden');
            $('#tabla-contratos-control').addClass('hidden');
            $('#contratos-sin-datos').addClass('hidden');
            $('#tbody-contratos-control').empty();

            $.get(`/contratos-control/empresa/${empresaId}`, function(res) {
                $('#contratos-loading').addClass('hidden');
                
                $('#contratos-empresa-nombre').text(res.empresa.razon_social);
                $('#contratos-empresa-ruc').text(res.empresa.ruc || '—');

                const tbody = $('#tbody-contratos-control');
                const trabajadores = res.trabajadores;

                if (!trabajadores.length) {
                    $('#contratos-sin-datos').removeClass('hidden');
                    return;
                }

                let num = 1;
                trabajadores.forEach(function(t) {
                    const contratos = t.contratos;
                    const N = contratos.length;

                    // Si no tiene contratos control
                    if (N === 0) {
                        const $tr = $('<tr class="hover:bg-gray-50 border-b border-gray-100"></tr>');
                        $tr.append(`<td class="px-4 py-3 text-center border border-gray-200">${num++}</td>`);
                        $tr.append(`<td class="px-4 py-3 border border-gray-200">${t.dni}</td>`);
                        $tr.append(`<td class="px-4 py-3 border border-gray-200 font-bold">${t.nombre_trabajador}</td>`);
                        
                        if (userArea !== 'AUDITORIA') {
                            $tr.append(`<td colspan="6" class="px-4 py-3 text-center text-gray-400 italic border border-gray-200">Sin contratos control registrados</td>`);
                            
                            if (!t.fecha_cese) {
                                const $btnAgregar = $(`
                                    <button type="button" class="text-indigo-600 hover:text-indigo-850 btn-agregar-contrato-modal transition"
                                        data-id="${t.id_planilla}"
                                        data-nombre="${t.nombre_trabajador.replace(/"/g, '&quot;')}"
                                        data-dni="${t.dni}"
                                        title="Agregar Contrato/Adenda">
                                        <i class="bi bi-file-earmark-plus-fill text-lg"></i>
                                    </button>
                                `);
                                const $tdAccion = $('<td class="px-4 py-3 text-center border border-gray-200 align-middle"></td>').append($btnAgregar);
                                $tr.append($tdAccion);
                            } else {
                                $tr.append('<td class="px-4 py-3 text-center border border-gray-200 align-middle text-gray-300">—</td>');
                            }
                        } else {
                            $tr.append(`<td colspan="6" class="px-4 py-3 text-center text-gray-400 italic border border-gray-200">Sin contratos control registrados</td>`);
                        }
                        tbody.append($tr);
                        return;
                    }

                    // Renderizar contratos con rowspan
                    contratos.forEach(function(c, index) {
                        const $tr = $('<tr class="hover:bg-gray-50 border-b border-gray-100"></tr>');
                        
                        // Añadir celdas de trabajador con rowspan para la primera fila
                        if (index === 0) {
                            $tr.append(`<td rowspan="${N}" class="px-4 py-3 text-center align-middle bg-gray-50 border border-gray-200 font-bold text-gray-700">${num++}</td>`);
                            $tr.append(`<td rowspan="${N}" class="px-4 py-3 align-middle bg-gray-50 border border-gray-200 font-semibold text-gray-600">${t.dni}</td>`);
                            $tr.append(`<td rowspan="${N}" class="px-4 py-3 align-middle bg-gray-50 border border-gray-200 font-bold text-gray-800">${t.nombre_trabajador}</td>`);
                        }

                        $tr.append(`<td class="px-4 py-3 border border-gray-200 font-bold text-gray-600">${c.tipo_doc}</td>`);
                        $tr.append(`<td class="px-4 py-3 border border-gray-200 text-gray-600">${c.cargo || '—'}</td>`);
                        $tr.append(`<td class="px-4 py-3 border border-gray-200">${formatearFecha(c.fecha_inicio)}</td>`);

                        // Resaltado amarillo para fecha fin modificada por cese
                        const finEstilo = c.fecha_fin_modificada_cese 
                            ? 'style="background-color: #fef08a;" class="px-4 py-3 border border-gray-200 font-bold text-amber-800"' 
                            : 'class="px-4 py-3 border border-gray-200 text-gray-600"';
                        let finFmt = c.fecha_fin ? formatearFecha(c.fecha_fin) : '<span class="text-gray-400">—</span>';
                        if (c.es_indeterminado) {
                            if (c.estado === 'ACTIVO') {
                                finFmt = '<span class="text-emerald-600 font-bold" style="color: #059669;">INDETERMINADO</span>';
                            } else {
                                finFmt = c.fecha_fin ? formatearFecha(c.fecha_fin) : '<span class="text-emerald-600 font-bold" style="color: #059669;">INDETERMINADO</span>';
                            }
                        }
                        $tr.append(`<td ${finEstilo}>${finFmt}</td>`);
                        $tr.append(`<td class="px-4 py-3 border border-gray-200 font-semibold text-gray-700 dark:text-gray-300">S/. ${parseFloat(c.sueldo || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>`);
                        $tr.append(`<td class="px-4 py-3 border border-gray-200 text-gray-600 max-w-xs break-words">${c.detalle ? c.detalle : '—'}</td>`);

                        if (userArea !== 'AUDITORIA') {
                            if (index === N - 1 && !t.fecha_cese) {
                                const showAgregar = !c.es_indeterminado;
                                const $btnAgregar = showAgregar ? $(`
                                    <button type="button" class="text-indigo-600 hover:text-indigo-850 btn-agregar-contrato-modal transition"
                                        data-id="${t.id_planilla}"
                                        data-nombre="${t.nombre_trabajador.replace(/"/g, '&quot;')}"
                                        data-dni="${t.dni}"
                                        title="Agregar Contrato/Adenda">
                                        <i class="bi bi-file-earmark-plus-fill text-lg"></i>
                                    </button>
                                `) : '';

                                const $btnEdit = $(`
                                    <button type="button" class="text-yellow-600 hover:text-yellow-800 transition" 
                                        onclick="abrirEditarContratoControl(this)"
                                        data-id="${c.id_control}" 
                                        data-planilla-id="${t.id_planilla}"
                                        data-nombre="${t.nombre_trabajador.replace(/"/g, '&quot;')}" 
                                        data-dni="${t.dni}" 
                                        data-tipo="${c.tipo_doc}" 
                                        data-modalidad="${(c.modalidad || '').replace(/"/g, '&quot;')}" 
                                        data-fecha-inicio="${c.fecha_inicio}" 
                                        data-fecha-fin="${c.fecha_fin || ''}" 
                                        data-sueldo="${c.sueldo || ''}"
                                        data-cargo="${(c.cargo || '').replace(/"/g, '&quot;')}"
                                        data-es-indeterminado="${c.es_indeterminado ? '1' : '0'}"
                                        data-detalle="${(c.detalle || '').replace(/"/g, '&quot;')}"
                                        title="Editar Contrato/Adenda">
                                        <i class="bi bi-pencil-fill text-lg" style="pointer-events: none;"></i>
                                    </button>
                                `);

                                const $btnDelete = $(`
                                    <button type="button" class="text-red-500 hover:text-red-700 btn-eliminar-contrato transition" 
                                        data-id="${c.id_control}" 
                                        data-tipo="${c.tipo_doc}" 
                                        data-trabajador="${t.nombre_trabajador.replace(/"/g, '&quot;')}" 
                                        title="Eliminar Contrato">
                                        <i class="bi bi-trash-fill text-lg" style="pointer-events: none;"></i>
                                    </button>
                                `);

                                const $div = $('<div class="flex items-center justify-center gap-3"></div>')
                                    .append($btnAgregar)
                                    .append($btnEdit)
                                    .append($btnDelete);

                                const $tdAccion = $('<td class="px-4 py-3 text-center border border-gray-200 align-middle"></td>').append($div);
                                $tr.append($tdAccion);
                            } else {
                                $tr.append('<td class="px-4 py-3 text-center border border-gray-200 align-middle text-gray-300">—</td>');
                            }
                        }

                        tbody.append($tr);
                    });
                });

                $('#tabla-contratos-control').removeClass('hidden');
                
                // Ejecutar el buscador si tiene algún valor preestablecido (ej. al venir desde una alerta)
                if ($('#buscador-contratos-trabajadores').val()) {
                    $('#buscador-contratos-trabajadores').trigger('input');
                }
            }).fail(function() {
                $('#contratos-loading').addClass('hidden');
                mostrarToast('Error al cargar contratos control.', 'error');
            });
        }

        $(document).on('click', '.btn-ver-contratos-control', function() {
            const id = $(this).data('id');
            $('#buscador-contratos-trabajadores').val(''); // Limpiar buscador
            abrirModal('modalControlContratos');
            cargarContratosControl(id);
        });

        // Abrir control de contratos pre-filtrado desde la tarjeta de alerta
        const abrirVerContratoAlerta = function() {
            const id = $(this).attr('data-id') || $(this).data('id');
            const workerName = $(this).attr('data-worker-name') || $(this).data('worker-name');
            const workerDni = $(this).attr('data-worker-dni') || $(this).data('worker-dni');

            abrirModal('modalControlContratos');
            
            // Preferir el DNI para realizar una búsqueda exacta
            const searchTerm = workerDni || workerName;
            $('#buscador-contratos-trabajadores').val(searchTerm);
            cargarContratosControl(id);
        };
        $(document).on('click', '.btn-ver-contrato-alerta', abrirVerContratoAlerta);
        $('#vencimientos-planilla-content').on('click', '.btn-ver-contrato-alerta', abrirVerContratoAlerta);

        // Buscador dentro de modalControlContratos
        $('#buscador-contratos-trabajadores').on('input', function() {
            const query = $(this).val().toLowerCase().trim();
            
            if (!query) {
                $('#tbody-contratos-control tr').show();
                return;
            }

            let showingGroup = false;
            let groupRows = [];

            $('#tbody-contratos-control tr').each(function() {
                const $tr = $(this);
                const hasRowspan = $tr.find('td[rowspan]').length > 0;

                if (hasRowspan) {
                    if (groupRows.length > 0) {
                        groupRows.forEach(row => row.toggle(showingGroup));
                    }
                    groupRows = [];
                    showingGroup = false;

                    const dni = $tr.find('td:nth-child(2)').text().toLowerCase();
                    const nombre = $tr.find('td:nth-child(3)').text().toLowerCase();
                    if (dni.includes(query) || nombre.includes(query)) {
                        showingGroup = true;
                    }
                    groupRows.push($tr);
                } else {
                    groupRows.push($tr);
                }
            });
            if (groupRows.length > 0) {
                groupRows.forEach(row => row.toggle(showingGroup));
            }
        });

        // ─── ABRIR AGREGAR CONTROL DE CONTRATO ────────────────────────────────
        const abrirAgregarContrato = function() {
            const id = $(this).attr('data-id') || $(this).data('id');
            const nombre = $(this).attr('data-nombre') || $(this).data('nombre');
            const dni = $(this).attr('data-dni') || $(this).data('dni');

            $('#formAgregarContratoControl')[0].reset();
            $('#contrato_id_control').val(''); // Modo guardar
            $('#contrato_id_planilla').val(id);
            $('#contrato_trabajador_nombre_display').text(nombre);
            $('#contrato_trabajador_dni_display').text(dni);

            $.get(`/contratos-control/siguiente/${id}`, function(res) {
                if (!res.puede_agregar) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No se puede agregar contrato',
                        text: res.mensaje,
                        confirmButtonColor: '#3b82f6'
                    });
                    return;
                }

                // Limpiar y poblar sugerencias en el datalist
                const datalist = $('#tipo_doc_suggestions');
                datalist.empty();

                if (!res.sugerencia_adenda) {
                    // Es el primer contrato del trabajador
                    $('#contrato_tipo_doc').val('CONTRATO');
                } else {
                    datalist.append(`<option value="${res.sugerencia_adenda}">`);
                    datalist.append(`<option value="${res.sugerencia_contrato}">`);

                    // Establecer por defecto la sugerencia del sistema
                    $('#contrato_tipo_doc').val(res.siguiente_tipo_doc);
                }

                $('#contrato_fecha_inicio').val(res.fecha_sugerida_inicio);
                $('#contrato_fecha_inicio').removeAttr('min');
                if (res.fecha_sugerida_inicio) {
                    $('#contrato_fecha_fin').attr('min', res.fecha_sugerida_inicio);
                } else {
                    $('#contrato_fecha_fin').removeAttr('min');
                }

                // Cargar sueldo sugerido
                $('#contrato_sueldo').val(res.sueldo_sugerido || '');

                // Cargar cargo sugerido
                $('#contrato_cargo').val(res.trabajador.cargo || '');

                // Inicializar checkbox y disparar cambio
                $('#contrato_es_indeterminado').prop('checked', false).trigger('change');

                abrirModal('modalAgregarContratoControl');
            }).fail(function() {
                mostrarToast('Error al obtener sugerencia de contrato.', 'error');
            });
        };
        $(document).on('click', '.btn-agregar-contrato-modal', abrirAgregarContrato);
        $('#tbody-trabajadores').on('click', '.btn-agregar-contrato-modal', abrirAgregarContrato);
        $('#vencimientos-planilla-content').on('click', '.btn-agregar-contrato-modal', abrirAgregarContrato);
        $('#tbody-contratos-control').on('click', '.btn-agregar-contrato-modal', abrirAgregarContrato);

        // ─── ABRIR EDITAR CONTROL DE CONTRATO ────────────────────────────────
        window.abrirEditarContratoControl = function(btn) {
            const $btn = $(btn);
            const idControl = $btn.attr('data-id');
            const idPlanilla = $btn.attr('data-planilla-id');
            const nombre = $btn.attr('data-nombre');
            const dni = $btn.attr('data-dni');
            const tipo = $btn.attr('data-tipo');
            const modalidad = $btn.attr('data-modalidad');
            const fechaInicio = $btn.attr('data-fecha-inicio');
            const fechaFin = $btn.attr('data-fecha-fin');
            const sueldo = $btn.attr('data-sueldo');
            const cargo = $btn.attr('data-cargo');
            const esIndeterminado = $btn.attr('data-es-indeterminado') === '1';
            const detalle = $btn.attr('data-detalle');

            $('#formAgregarContratoControl')[0].reset();
            $('#contrato_id_control').val(idControl); // Modo editar
            $('#contrato_id_planilla').val(idPlanilla);
            $('#contrato_trabajador_nombre_display').text(nombre);
            $('#contrato_trabajador_dni_display').text(dni);
            
            // Limpiar sugerencias al editar para evitar confusión
            $('#tipo_doc_suggestions').empty();
            
            // Establecer valores
            $('#contrato_tipo_doc').val(tipo);
            $('#contrato_modalidad').val(modalidad);
            $('#contrato_fecha_inicio').val(fechaInicio);
            $('#contrato_fecha_fin').val(fechaFin);
            $('#contrato_sueldo').val(sueldo);
            $('#contrato_cargo').val(cargo);
            $('#contrato_detalle').val(detalle);
            
            // Checkbox indeterminado
            $('#contrato_es_indeterminado').prop('checked', esIndeterminado).trigger('change');
            
            $('#contrato_fecha_inicio').removeAttr('min');
            $('#contrato_fecha_fin').removeAttr('min');

            abrirModal('modalAgregarContratoControl');
        };

        // Cambiar obligatoriedad de fecha fin según el checkbox de indeterminado
        $(document).on('change', '#contrato_es_indeterminado', function() {
            const isChecked = $(this).is(':checked');
            if (isChecked) {
                $('#contrato_fecha_fin').prop('required', false);
                $('label[for="contrato_fecha_fin"]').html('Fecha Fin <span class="text-xs text-emerald-600 font-bold">(Opcional para Indeterminado)</span>');
            } else {
                $('#contrato_fecha_fin').prop('required', true);
                $('label[for="contrato_fecha_fin"]').html('Fecha Fin <span class="text-red-500 font-bold">*</span>');
            }
        });

        // Actualizar dinámicamente la fecha mínima de fin según la de inicio elegida
        $('#contrato_fecha_inicio').on('change', function() {
            const val = $(this).val();
            if (val) {
                $('#contrato_fecha_fin').attr('min', val);
            }
        });

        // Guardar nuevo o editar contrato control
        $('#formAgregarContratoControl').on('submit', function(e) {
            e.preventDefault();
            const idControl = $('#contrato_id_control').val();
            const isEdit = idControl !== '';
            
            const btn = document.getElementById('btnGuardarContratoControl');
            btn.disabled = true;
            $('#btnGuardarContratoControlIcon').attr('class', 'bi bi-arrow-repeat animate-spin');
            $('#btnGuardarContratoControlText').text('Guardando...');
            mostrarGolemCargando('Guardando contrato...');

            const formData = $(this).serialize();
            const ajaxUrl = isEdit ? `/contratos-control/${idControl}` : '/contratos-control';
            const ajaxMethod = isEdit ? 'PUT' : 'POST';

            $.ajax({
                url: ajaxUrl,
                method: ajaxMethod,
                data: formData,
                success: function(res) {
                    cerrarModal('modalAgregarContratoControl');
                    mostrarToast(res.message || 'Contrato guardado correctamente.', 'success');
                    if (empresaContratosId) {
                        cargarContratosControl(empresaContratosId);
                    }
                    if (empresaActivaId) {
                        cargarTrabajadores(empresaActivaId);
                    }
                    actualizarAlertVencimientos();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.errors ?
                        Object.values(xhr.responseJSON.errors).flat().join(' | ') :
                        (xhr.responseJSON?.message || 'Error al guardar el contrato.');
                    mostrarToast(msg, 'error');
                },
                complete: function() {
                    btn.disabled = false;
                    $('#btnGuardarContratoControlIcon').attr('class', 'bi bi-check-lg');
                    $('#btnGuardarContratoControlText').text('Guardar Contrato');
                    ocultarGolemCargando();
                }
            });
        });

        // Eliminar contrato control
        $('#tbody-contratos-control').on('click', '.btn-eliminar-contrato', function() {
            const id = $(this).attr('data-id');
            const tipo = $(this).attr('data-tipo');
            const trabajador = $(this).attr('data-trabajador');

            if (!confirm(`¿Estás seguro de eliminar el registro "${tipo}" del trabajador "${trabajador}"?`)) {
                return;
            }

            $.ajax({
                url: `/contratos-control/${id}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    mostrarToast(res.message || 'Registro eliminado.', 'success');
                    if (empresaContratosId) {
                        cargarContratosControl(empresaContratosId);
                    }
                    actualizarAlertVencimientos();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Error al eliminar el registro.';
                    mostrarToast(msg, 'error');
                }
            });
        });

        // Notificar al cargar la página si existen contratos vencidos o por vencer este mes
        (function() {
            const periodoActual = `${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}`;
            $.get('/planillas/vencimientos-data', { periodo: periodoActual }, function(res) {
                const count = res.vencimientos.length;
                if (count > 0) {
                    mostrarToast(`Atención: Tienes ${count} contrato(s) por vencer o vencidos este mes.`, 'warning');
                }
            });
        })();

    });
</script>
