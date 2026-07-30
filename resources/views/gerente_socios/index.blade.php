<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            GERENTES Y SOCIOS DE LAS EMPRESAS A1 </span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
            @endif
        </div>
        <div class="w-full px-4">
            <x-success-error-bagdes />
            <div class="bg-white shadow rounded-lg p-6 overflow-x-auto">
                <table id="myTable-gerentesocios" class="min-w-full table-auto border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 align-middle text-center">Empresa</th>
                            <th class="px-4 py-1 align-middle">RUC</th>
                            <th class="px-4 py-2 align-middle">Gerente</th>
                            <th class="px-4 py-2 align-middle">Fecha Acta</th>
                            <th class="px-4 py-2 align-middle">Fecha Sunat</th>
                            <th class="px-4 py-2 align-middle">Fecha Cambios</th>
                            <th class="px-4 py-2 align-middle">Socios</th>
                            <th class="px-4 py-2 align-middle">Capital Socio</th>
                            <th class="px-4 py-2 align-middle">Fecha Cambio Socios</th>
                            <th class="px-4 py-2 align-middle">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $empresa)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $empresa->razon_social }}</td>
                                <td class="px-4 py-2">{{ $empresa->ruc }}</td>
                                <td class="px-3 py-2 text-sm text-left">
                                    @php
                                        $obsGerente = $empresa->gerenteActivo->observacion_gerente ?? '';
                                        $tieneGerente = !empty($empresa->gerenteActivo);
                                    @endphp
                                    @if ($tieneGerente)
                                        <button type="button"
                                            class="inline-flex items-center w-full text-left tooltip relative {{ $obsGerente ? 'text-blue-600 hover:underline' : 'text-gray-500 hover:underline' }}"
                                            data-tipo="gerente"
                                            data-observacion="{{ htmlspecialchars($obsGerente, ENT_QUOTES) }}"
                                            data-empresa="{{ $empresa->id_empresas_clientes }}">
                                            <span class="truncate w-full">
                                                {{ $empresa->gerenteActivo->nombre }}
                                            </span>
                                            <span class="tooltip-text z-50 pointer-events-none">
                                                {{ $obsGerente ? 'Ver Observación' : 'Agregar Observación' }}
                                            </span>
                                        </button>
                                    @else
                                        <span class="text-gray-400 italic cursor-not-allowed"> —
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $empresa->gerenteActivo?->fecha_acta
                                        ? \Carbon\Carbon::parse($empresa->gerenteActivo->fecha_acta)->format('d/m/Y')
                                        : '—' }}
                                </td>
                                <td>
                                    {{ $empresa->gerenteActivo?->fecha_sunat
                                        ? \Carbon\Carbon::parse($empresa->gerenteActivo->fecha_sunat)->format('d/m/Y')
                                        : '—' }}
                                </td>
                                <td>
                                    {{ $empresa->gerenteActivo?->fecha_cambio
                                        ? \Carbon\Carbon::parse($empresa->gerenteActivo->fecha_cambio)->format('d/m/Y')
                                        : '—' }}
                                </td>
                                <td class="px-3 py-2 text-sm text-left">
                                    @php
                                        $obsSocios = $empresa->sociosActivos->first()->observacion ?? '';
                                        $tieneSocios = $empresa->sociosActivos->isNotEmpty();
                                    @endphp

                                    @if ($tieneSocios)
                                        <button type="button"
                                            class="inline-flex items-center w-full text-left tooltip relative {{ $obsSocios ? 'text-blue-600 hover:underline' : 'text-gray-500 hover:underline' }}"
                                            data-tipo="socios"
                                            data-observacion="{{ htmlspecialchars($obsSocios, ENT_QUOTES) }}"
                                            data-empresa="{{ $empresa->id_empresas_clientes }}">
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach ($empresa->sociosActivos as $socio)
                                                    <li class="truncate">{{ $socio->nombre }}</li>
                                                @endforeach
                                            </ul>
                                            <span class="tooltip-text z-50 pointer-events-none">
                                                {{ $obsSocios ? 'Ver Observación' : 'Agregar Observación' }}
                                            </span>
                                        </button>
                                    @else
                                        <span class="text-gray-400 italic cursor-not-allowed">
                                            —
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-2">
                                    <ul class="space-y-1 text-sm text-center">
                                        @forelse ($empresa->sociosActivos as $socio)
                                            <li>{{ $socio->porcentaje ?? '—' }}%</li>
                                        @empty
                                            <li>—</li>
                                        @endforelse
                                    </ul>
                                </td>
                                <td class="px-4 py-2">
                                    <ul class="space-y-1 text-sm text-center">
                                        @forelse ($empresa->sociosActivos as $socio)
                                            <li>
                                                {{ $socio->fecha_cambio ? \Carbon\Carbon::parse($socio->fecha_cambio)->format('d/m/Y') : '—' }}
                                            </li>
                                        @empty
                                            <li>—</li>
                                        @endforelse
                                    </ul>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center justify-center gap-2">
                                        @php
                                            $tieneGerente = !empty($empresa->gerenteActivo);
                                            $tieneSocios = $empresa->sociosActivos->isNotEmpty();
                                        @endphp
                                        @if (Auth::user()->area->nombre == 'LEGAL')
                                            @if ($tieneGerente || $tieneSocios)
                                                <a href="{{ route('gerente_socios.edit', $empresa->id_empresas_clientes) }}"
                                                    class="flex items-center p-2 rounded-md tooltip text-yellow-500"
                                                    style="transition: color 0.3s;"
                                                    onmouseover="this.style.color='#ca8a04';"
                                                    onmouseout="this.style.color='#eab308';">
                                                    <span class="tooltip-text">Editar</span>
                                                    <x-edit-icon />
                                                </a>
                                            @endif
                                            <a href="{{ route('gerente_socios.create', $empresa->id_empresas_clientes) }}"
                                                class="flex items-center p-2 rounded-md tooltip text-green-500"
                                                style="transition: color 0.3s;"
                                                onmouseover="this.style.color='#059669';"
                                                onmouseout="this.style.color='#10b981';">
                                                <span class="tooltip-text">Agregar</span>
                                                <x-add-people-icon />
                                            </a>
                                        @endif

                                        <button type="button"
                                            class="flex items-center p-2 rounded-md tooltip text-purple-500"
                                            style="transition: color 0.3s;" onmouseover="this.style.color='#7c3aed';"
                                            onmouseout="this.style.color='#8b5cf6';"
                                            onclick="abrirModalGerentes({{ $empresa->id_empresas_clientes }}, '{{ addslashes($empresa->razon_social) }}')">
                                            <span class="tooltip-text">Ver Gerentes</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </button>

                                        <a href="{{ route('gerente_socios.listar', $empresa->id_empresas_clientes) }}"
                                            class="flex items-center p-2 rounded-md tooltip text-blue-500"
                                            style="transition: color 0.3s;" onmouseover="this.style.color='#2563eb';"
                                            onmouseout="this.style.color='#3b82f6';">
                                            <span class="tooltip-text">Mostrar</span>
                                            <x-people-icon />
                                        </a>
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

<!-- Modal Editar Observaciones -->
<x-modal-show id="cuentaModal" title="Editar Observaciones" class="max-w-lg">
    <div class="px-4 py-4">
        <form id="form-observaciones" action="" method="POST" class="space-y-4">
            @csrf
            <div id="grupo-gerente" style="display: none;">
                <x-input-label for="observacion_gerente_edit" value="Observación del Gerente" />
                <textarea id="observacion_gerente_edit" name="observacion_gerente" rows="6"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm resize-none {{ Auth::user()->area->nombre != 'LEGAL' ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                    {{ Auth::user()->area->nombre != 'LEGAL' ? 'disabled' : '' }}>
                </textarea>
            </div>

            <div id="grupo-socios" style="display: none;">
                <x-input-label for="observaciones_modal_edit" value="Observación de los Socios" />
                <textarea id="observaciones_modal_edit" name="observaciones" rows="6"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm resize-none {{ Auth::user()->area->nombre != 'LEGAL' ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                    {{ Auth::user()->area->nombre != 'LEGAL' ? 'disabled' : '' }}>
                </textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t">
                <button type="button" onclick="toggleModal('cuentaModal')"
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition text-gray-700">
                    Cancelar
                </button>
                @if (Auth::user()->area->nombre == 'LEGAL')
                    <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                        Guardar
                    </button>
                @endif
            </div>
        </form>
    </div>
</x-modal-show>


<!-- Modal Historial de Gerentes -->
<x-modal-show id="modalGerentes" title="Historial de Gerentes" size="large">
    <div class="px-6 py-4">

        {{-- Nombre de la empresa debajo del título --}}
        <p id="modalGerentes-empresa" class="text-sm text-gray-500 -mt-2 mb-4"></p>

        {{-- Spinner de carga --}}
        <div id="modalGerentes-loading" class="flex flex-col items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-purple-500 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 22 12h-4a8 8 0 01-8 8v-4z"></path>
            </svg>
            <span class="text-gray-500 text-sm">Cargando gerentes...</span>
        </div>

        {{-- Contenido --}}
        <div id="modalGerentes-content" style="display: none;">

            {{-- Gerente Activo --}}
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-green-700 uppercase tracking-wide mb-2 flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                    Gerente Activo
                </h4>
                <div id="tabla-gerente-activo" class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full text-sm">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Nombre</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700">Fecha Acta</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700">Fecha Sunat</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700">Fecha Cambio</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700">Archivo Firma</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700">Archivo DNI</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="body-gerente-activo"></tbody>
                    </table>
                </div>
            </div>

            {{-- Gerentes Inactivos --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-gray-400"></span>
                    Gerentes Inactivos
                </h4>
                <div id="tabla-gerentes-inactivos" class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Nombre</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700">Fecha Acta</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700">Fecha Sunat</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700">Fecha Cambio</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700">Archivo Firma</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700">Archivo DNI</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="body-gerentes-inactivos"></tbody>
                    </table>
                </div>
                <p id="sin-inactivos" class="text-gray-400 italic text-sm text-center py-4" style="display:none;">
                    No hay gerentes inactivos registrados.
                </p>
            </div>
        </div>

        {{-- Sin datos --}}
        <div id="modalGerentes-empty" class="text-center text-gray-400 italic py-10" style="display: none;">
            No se encontraron gerentes para esta empresa.
        </div>

        {{-- Pie --}}
        <div class="flex justify-end pt-4 border-t mt-4">
            <button type="button" onclick="cerrarModalGerentes()"
                class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition text-gray-700 text-sm">
                Cerrar
            </button>
        </div>

    </div>
</x-modal-show>


<!-- Modal Subir Archivos -->
<x-modal-show id="modalSubirArchivos" title="Subir Documentos del Gerente" size="large">
    <div class="px-6 py-4 space-y-5">

        <form id="form-subir-archivos" action="" method="POST" enctype="multipart/form-data"
            class="space-y-5">
            @csrf
            <p id="modalArchivos-nombre" class="text-sm text-gray-500 -mt-2 mb-1"></p>
            <input type="hidden" id="archivos-gerente-id" name="gerente_id" value="">

            <!-- Archivo Firma -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Archivo Firma
                    <span class="text-gray-400 font-normal text-xs">(JPG, PNG)</span>
                </label>

                <div id="aviso-firma"
                    class="hidden items-center gap-2 mb-2 px-3 py-2 bg-yellow-50 border border-yellow-300 rounded-md text-xs text-yellow-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 text-yellow-500"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    Ya existe una firma guardada. Al guardar, el archivo anterior será reemplazado.
                </div>

                <div id="drop-firma"
                    class="relative border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-purple-400 transition cursor-pointer bg-gray-50"
                    ondragover="event.preventDefault(); this.classList.add('border-purple-500','bg-purple-50');"
                    ondragleave="this.classList.remove('border-purple-500','bg-purple-50');"
                    ondrop="handleDrop(event, 'firma')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-gray-400 mb-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <p class="text-sm text-gray-500">Arrastra aquí o <span class="text-purple-600 font-medium">haz
                            clic para seleccionar</span></p>
                    <p id="nombre-firma" class="text-xs text-green-600 mt-1 font-medium" style="display:none;"></p>
                    <input type="file" id="input-firma" name="archivo_firma" accept=".jpg,.jpeg,.png,.webp"
                        class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                        onchange="mostrarNombreArchivo('firma', this)">
                </div>
            </div>

            <!-- Archivo DNI -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Archivo DNI
                    <span class="text-gray-400 font-normal text-xs">(PDF)</span>
                </label>

                <div id="aviso-dni"
                    class="hidden items-center gap-2 mb-2 px-3 py-2 bg-yellow-50 border border-yellow-300 rounded-md text-xs text-yellow-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 text-yellow-500"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    Ya existe un DNI guardado. Al guardar, el archivo anterior será reemplazado.
                </div>

                <div id="drop-dni"
                    class="relative border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-purple-400 transition cursor-pointer bg-gray-50"
                    ondragover="event.preventDefault(); this.classList.add('border-purple-500','bg-purple-50');"
                    ondragleave="this.classList.remove('border-purple-500','bg-purple-50');"
                    ondrop="handleDrop(event, 'dni')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-gray-400 mb-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c0 1.3 2 2 2 2" />
                    </svg>
                    <p class="text-sm text-gray-500">Arrastra aquí o <span class="text-purple-600 font-medium">haz
                            clic para seleccionar</span></p>
                    <p id="nombre-dni" class="text-xs text-green-600 mt-1 font-medium" style="display:none;"></p>
                    <input type="file" id="input-dni" name="archivo_dni" accept=".pdf"
                        class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                        onchange="mostrarNombreArchivo('dni', this)">
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3 pt-2 border-t">
                <button type="button" onclick="cerrarModalArchivos()"
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition text-gray-700 text-sm">
                    Cancelar
                </button>
                <button type="submit" id="btn-guardar-archivos"
                    class="px-5 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition text-sm font-medium flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Guardar Archivos
                </button>
            </div>
        </form>
    </div>
</x-modal-show>

@php $puedeSubirArchivos = in_array(optional(Auth::user()->area)->nombre, ['LEGAL', 'ADMINISTRACION']); @endphp
<script>
    const puedeSubirArchivos = {{ $puedeSubirArchivos ? 'true' : 'false' }};
</script>

<script>
    document.querySelectorAll('button[data-tipo]').forEach(btn => {
        btn.addEventListener('click', function() {
            const tipo = this.dataset.tipo;
            const observacion = this.dataset.observacion || '';
            const empresaId = this.dataset.empresa;
            abrirModalObservaciones(empresaId, tipo, observacion);
        });
    });

    function abrirModalObservaciones(empresaId, tipo, observacion = '') {
        const form = document.getElementById('form-observaciones');
        form.action = `/gerente_socios/${empresaId}/update_observaciones`;
        if (tipo === 'gerente') {
            document.getElementById('grupo-gerente').style.display = 'block';
            document.getElementById('grupo-socios').style.display = 'none';
            document.getElementById('observacion_gerente_edit').value = observacion;
        } else if (tipo === 'socios') {
            document.getElementById('grupo-gerente').style.display = 'none';
            document.getElementById('grupo-socios').style.display = 'block';
            document.getElementById('observaciones_modal_edit').value = observacion;
        }
        toggleModal('cuentaModal');
    }
</script>

<script>
    const textarea = document.getElementById('observaciones_modal_edit');
    textarea.addEventListener('focus', () => {
        textarea.setSelectionRange(0, 0);
        textarea.scrollTop = 0;
    });
</script>

<script>
    let empresaIdActual = null;

    function abrirModalGerentes(empresaId, razonSocial) {
        empresaIdActual = empresaId;
        document.getElementById('modalGerentes-empresa').textContent = razonSocial;

        const modal = document.getElementById('modalGerentes');
        modal.style.removeProperty('display');
        modal.style.display = 'flex';

        document.getElementById('modalGerentes-loading').style.display = 'flex';
        document.getElementById('modalGerentes-content').style.display = 'none';
        document.getElementById('modalGerentes-empty').style.display = 'none';

        // Abrir el modal SIN toggle — solo mostrar
        const modalEl = document.getElementById('modalGerentes');
        if (modalEl && modalEl.classList.contains('hidden')) {
            toggleModal('modalGerentes');
        }

        fetch(`/gerente_socios/${empresaId}/gerentes`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                renderGerentes(data);
            })
            .catch(err => {
                console.error(err);
                document.getElementById('modalGerentes-loading').style.display = 'none';
                document.getElementById('modalGerentes-empty').style.display = 'block';
                document.getElementById('modalGerentes-empty').textContent =
                    'Error al cargar los datos. Intente nuevamente.';
            });
    }

    function cerrarModalGerentes() {
        const modal = document.getElementById('modalGerentes');
        modal.style.display = 'none';
        empresaIdActual = null;
    }

    function renderGerentes(data) {
        document.getElementById('modalGerentes-loading').style.display = 'none';

        const {
            activo,
            inactivos
        } = data;

        if (!activo && (!inactivos || inactivos.length === 0)) {
            document.getElementById('modalGerentes-empty').style.display = 'block';
            return;
        }

        document.getElementById('modalGerentes-content').style.display = 'block';

        // --- Gerente Activo ---
        const bodyActivo = document.getElementById('body-gerente-activo');
        bodyActivo.innerHTML = '';
        if (activo) {
            bodyActivo.innerHTML = `
                <tr class="border-t hover:bg-green-50 transition">
                    <td class="px-4 py-2 font-medium text-gray-800">${activo.nombre ?? '—'}</td>
                    <td class="px-4 py-2 text-center text-gray-600">${formatFecha(activo.fecha_acta)}</td>
                    <td class="px-4 py-2 text-center text-gray-600">${formatFecha(activo.fecha_sunat)}</td>
                    <td class="px-4 py-2 text-center text-gray-600">${formatFecha(activo.fecha_cambio)}</td>
                    <td class="px-4 py-2 text-center">${renderArchivoLink(activo.archivo_firma, 'firma')}</td>
                    <td class="px-4 py-2 text-center">${renderArchivoLink(activo.archivo_dni, 'DNI')}</td>
                    <td class="px-4 py-2 text-center">
                        <div class="inline-flex items-center gap-1">
                            ${puedeSubirArchivos ? `
                            <button type="button"
                                onclick="abrirModalArchivos(${activo.id_gerente}, '${escapeAttr(activo.nombre)}', ${activo.archivo_firma ? 'true' : 'false'}, ${activo.archivo_dni ? 'true' : 'false'})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-100 text-purple-700 rounded-md text-xs font-medium hover:bg-purple-200 transition">
                                Subir Archivos
                            </button>` : '—'}
                        </div>
                    </td>
                </tr>`;
        } else {
            bodyActivo.innerHTML =
                `<tr><td colspan="7" class="px-4 py-3 text-center text-gray-400 italic">No hay gerente activo.</td></tr>`;
        }

        // --- Gerentes Inactivos ---
        const bodyInactivos = document.getElementById('body-gerentes-inactivos');
        const sinInactivos = document.getElementById('sin-inactivos');
        bodyInactivos.innerHTML = '';

        if (inactivos && inactivos.length > 0) {
            sinInactivos.style.display = 'none';
            inactivos.forEach(g => {
                const tr = document.createElement('tr');
                tr.className = 'border-t hover:bg-gray-50 transition';
                tr.innerHTML = `
                    <td class="px-4 py-2 text-gray-700">${g.nombre ?? '—'}</td>
                    <td class="px-4 py-2 text-center text-gray-500">${formatFecha(g.fecha_acta)}</td>
                    <td class="px-4 py-2 text-center text-gray-500">${formatFecha(g.fecha_sunat)}</td>
                    <td class="px-4 py-2 text-center text-gray-500">${formatFecha(g.fecha_cambio)}</td>
                    <td class="px-4 py-2 text-center">${renderArchivoLink(g.archivo_firma, 'firma')}</td>
                    <td class="px-4 py-2 text-center">${renderArchivoLink(g.archivo_dni, 'DNI')}</td>
                    <td class="px-4 py-2 text-center">
                        ${puedeSubirArchivos ? `
                        <button type="button"
                            onclick="abrirModalArchivos(${g.id_gerente}, '${escapeAttr(g.nombre)}', ${g.archivo_firma ? 'true' : 'false'}, ${g.archivo_dni ? 'true' : 'false'})"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-100 text-purple-700 rounded-md text-xs font-medium hover:bg-purple-200 transition">
                            Subir Archivos
                        </button>` : '—'}
                    </td>`;
                bodyInactivos.appendChild(tr);
            });
        } else {
            sinInactivos.style.display = 'block';
        }
    }

    function renderArchivoLink(url, tipo) {
        if (!url) {
            return `<span class="text-gray-300 italic text-xs">Sin archivo</span>`;
        }
        return `<a href="${url}" target="_blank"
            class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-600 rounded text-xs hover:bg-blue-100 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 010 18z" />
            </svg>
            Ver ${tipo}
        </a>`;
    }

    function eliminarArchivo(gerenteId, campo) {
        if (!confirm('¿Estás seguro de que deseas eliminar este archivo?')) return;

        fetch(`/gerente_socios/${gerenteId}/eliminar_archivo`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({
                    campo
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion(data.message || 'Archivo eliminado correctamente.', 'success');
                    if (empresaIdActual) {
                        abrirModalGerentes(empresaIdActual, document.getElementById('modalGerentes-empresa')
                            .textContent);
                    }
                } else {
                    mostrarNotificacion(data.message || 'Error al eliminar el archivo.', 'error');
                }
            })
            .catch(() => mostrarNotificacion('Error de conexión. Intente nuevamente.', 'error'));
    }

    function formatFecha(fechaStr) {
        if (!fechaStr) return '—';
        const d = new Date(fechaStr);
        if (isNaN(d)) return fechaStr;
        return d.toLocaleDateString('es-PE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    function escapeAttr(str) {
        return (str ?? '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    document.getElementById('modalGerentes').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalGerentes();
    });


    // ========== Modal Subir Archivos ==========

    // Recibe los booleanos de si ya existe firma/dni para mostrar los avisos
    function abrirModalArchivos(gerenteId, nombreGerente, tieneArchivoFirma, tieneArchivoDni) {
        document.getElementById('archivos-gerente-id').value = gerenteId;
        document.getElementById('modalArchivos-nombre').textContent = nombreGerente;

        // Limpiar formulario y nombres mostrados
        document.getElementById('form-subir-archivos').reset();
        document.getElementById('nombre-firma').style.display = 'none';
        document.getElementById('nombre-dni').style.display = 'none';

        // Mostrar u ocultar avisos de reemplazo según si ya hay archivo
        const avisoFirma = document.getElementById('aviso-firma');
        const avisoDni = document.getElementById('aviso-dni');
        avisoFirma.style.display = tieneArchivoFirma ? 'flex' : 'none';
        avisoDni.style.display = tieneArchivoDni ? 'flex' : 'none';

        document.getElementById('form-subir-archivos').action = `/gerente_socios/${gerenteId}/subir_archivos`;

        // Abrir solo si está cerrado
        const modalEl = document.getElementById('modalSubirArchivos');
        if (modalEl.classList.contains('hidden')) {
            toggleModal('modalSubirArchivos');
        }

        const modal = document.getElementById('modalSubirArchivos');
        modal.style.removeProperty('display');
        modal.style.display = 'flex';
    }

    function cerrarModalArchivos() {
        document.getElementById('modalSubirArchivos').style.display = 'none';
    }

    function mostrarNombreArchivo(tipo, input) {
        const label = document.getElementById(`nombre-${tipo}`);
        if (input.files && input.files[0]) {
            label.textContent = '✓ ' + input.files[0].name;
            label.style.display = 'block';
        } else {
            label.style.display = 'none';
        }
    }

    function handleDrop(event, tipo) {
        event.preventDefault();
        const dropZone = event.currentTarget;
        dropZone.classList.remove('border-purple-500', 'bg-purple-50');

        const files = event.dataTransfer.files;
        if (files.length > 0) {
            const input = document.getElementById(`input-${tipo}`);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            input.files = dataTransfer.files;
            mostrarNombreArchivo(tipo, input);
        }
    }

    function submitArchivoAjax() {
        const form = document.getElementById('form-subir-archivos');
        const formData = new FormData(form);
        const action = form.getAttribute('action');

        const btnGuardar = document.getElementById('btn-guardar-archivos');
        if (btnGuardar) {
            btnGuardar.disabled = true;
            btnGuardar.textContent = 'Subiendo...';
        }

        fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cerrarModalArchivos();
                    if (empresaIdActual) {
                        abrirModalGerentes(empresaIdActual, document.getElementById('modalGerentes-empresa')
                            .textContent);
                    }
                    mostrarNotificacion(data.message || 'Archivos subidos correctamente.', 'success');
                } else {
                    mostrarNotificacion(data.message || 'Error al subir los archivos.', 'error');
                }
            })
            .catch(() => {
                mostrarNotificacion('Error de conexión. Intente nuevamente.', 'error');
            })
            .finally(() => {
                if (btnGuardar) {
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Guardar Archivos`;
                }
            });
    }

    function mostrarNotificacion(mensaje, tipo) {
        if (typeof showAjaxToast === 'function') {
            showAjaxToast(mensaje, tipo);
        } else {
            const colores = tipo === 'success' ?
                'bg-green-100 text-green-800 border-green-300' :
                'bg-red-100 text-red-800 border-red-300';
            const notif = document.createElement('div');
            notif.className =
                `fixed top-5 right-5 z-[9999] border px-5 py-3 rounded-lg shadow-lg text-sm font-medium transition-all ${colores}`;
            notif.textContent = mensaje;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3500);
        }
    }

    document.getElementById('form-subir-archivos').addEventListener('submit', function(e) {
        e.preventDefault();
        submitArchivoAjax();
    });

    document.getElementById('modalSubirArchivos').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalArchivos();
    });
</script>

<script>
    $(document).ready(function() {
        $('#myTable-gerentesocios').DataTable({
            paging: true,
            searching: true,
            pageLength: 200,
            ordering: true,
            responsive: false,
            excelfilter: false,
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            }
        });
    });
</script>
