<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Direcciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <div class="relative w-full md:w-auto">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center justify-center gap-2 bg-green-500 px-4 py-2 rounded-md 
                                hover:bg-green-600 transition duration-300 text-white w-full md:w-auto">
                                <i class="bi bi-geo-alt-fill"></i> Crear Dirección
                                <i class="bi bi-chevron-down text-sm"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="absolute right-0 z-50 w-48 bg-white shadow-lg rounded-md border p-2">
                                <x-dropdown-link :href="route('direcciones.create_empresa')">
                                    <i class="bi bi-building me-2"></i> Empresa
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('direcciones.create_cliente')">
                                    <i class="bi bi-person me-2"></i> Cliente
                                </x-dropdown-link>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>
                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->area->nombre == 'GERENCIA')
                    <a href="{{ route('alquiler.index') }}"
                        class="flex items-center justify-center gap-2 bg-indigo-500 px-4 py-2 rounded-md hover:bg-indigo-600 transition text-white">
                        <i class="bi bi-receipt"></i> Ver alquileres
                    </a>
                @endif

                <a href="{{ route('empresas_clientes_auditoria.all') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white w-full md:w-auto">
                    <i class="bi bi-building"></i> Empresas
                </a>

                <a href="{{ route('clientes.index') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white w-full md:w-auto">
                    <i class="bi bi-people-fill"></i> Clientes
                </a>

                <a href="{{ route('choferes.index') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white w-full md:w-auto">
                    <i class="bi bi-truck-front-fill"></i> Choferes
                </a>
            </div>


            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive nowrap" id="myTable-direcciones">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                            <th class="px-4 py-2 border-b">Empresa/Cliente</th>
                            <th class="px-4 py-2 border-b">Tipo</th>
                            <th class="px-4 py-2 border-b">Nombre y Provincia</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($direcciones as $direccion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b relative">
                                    <div class="flex space-x-2 justify-center">
                                        <a href="javascript:void(0)"
                                            class="flex items-center py-1 rounded-md tooltip btn-detalles-direccion"
                                            style="color: #6B7280; transition: color 0.3s;"
                                            onmouseover="this.style.color='#4B5563';"
                                            onmouseout="this.style.color='#6B7280';"
                                            data-id="{{ $direccion->id_direccion }}"
                                            data-nombre="{{ $direccion->nombre }}"
                                            data-tipo="{{ $direccion->tipo_domicilio }}"
                                            data-distrito="{{ $direccion->distrito }}"
                                            data-provincia="{{ $direccion->provincia }}"
                                            data-region="{{ $direccion->region }}"
                                            data-estado="{{ $direccion->estado ? 'Activo' : 'Inactivo' }}"
                                            data-creador="{{ $direccion->creador->name ?? 'Sistema' }}"
                                            data-editor="{{ $direccion->editor->name ?? 'Sistema' }}"
                                            data-fecha_baja="{{ $direccion->fecha_baja ? \Carbon\Carbon::parse($direccion->fecha_baja)->format('d/m/Y') : '—' }}"
                                            {{-- d/m/Y H:i --}}
                                            data-entidad="{{ $direccion->empresa->razon_social ?? ($direccion->cliente->razon_social ?? '-') }}"
                                            data-ruc="{{ $direccion->empresa->ruc ?? ($direccion->cliente->ruc ?? '-') }}">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon />
                                        </a>
                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->area->nombre == 'GERENCIA')
                                            @php
                                                $ultimoAlquiler = $direccion
                                                    ->alquileres()
                                                    ->orderBy('created_at', 'desc')
                                                    ->first();
                                            @endphp

                                            @if ($ultimoAlquiler)
                                                <a href="{{ route('alquiler.show', $ultimoAlquiler->id_alquiler) }}"
                                                    class="flex items-center py-1 rounded-md tooltip"
                                                    style="color:#3B82F6;" onmouseover="this.style.color='#2563EB';"
                                                    onmouseout="this.style.color='#3B82F6';">

                                                    <x-house-icon />
                                                    <span class="tooltip-text">Últ. Alquiler</span>
                                                </a>
                                            @else
                                                <button type="button"
                                                    class="btn-open-alquiler flex items-center py-1 rounded-md tooltip"
                                                    style="color:#6366F1;" onmouseover="this.style.color='#4F46E5';"
                                                    onmouseout="this.style.color='#6366F1';"
                                                    data-id-direccion="{{ $direccion->id_direccion }}"
                                                    data-direccion-nombre="{{ $direccion->nombre }}"
                                                    data-tipo="{{ $direccion->tipo_domicilio }}">

                                                    <x-house-icon />
                                                    <span class="tooltip-text">+Alquiler</span>
                                                </button>
                                            @endif
                                        @endif
                                        {{-- Botón Editar --}}
                                        <a href="{{ route('direcciones.edit', $direccion->id_direccion) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #F59E0B; transition: color 0.3s;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </a>
                                        {{-- Botón Eliminar --}}
                                        {{-- <form action="{{ route('direcciones.destroy', $direccion->id_direccion) }}"
                                                method="POST" style="display:inline;"
                                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta dirección?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';">
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form> --}}
                                        {{-- Botón Activar/Desactivar --}}
                                        @if ($direccion->estado)
                                            <form
                                                action="{{ route('direcciones.desactivar', $direccion->id_direccion) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626;" onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';">
                                                    <x-deactivate-icon />
                                                    <span class="tooltip-text">Desactivar</span>
                                                </button>
                                            </form>
                                        @else
                                            <form
                                                action="{{ route('direcciones.reactivar', $direccion->id_direccion) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #16a34a;" onmouseover="this.style.color='#15803d';"
                                                    onmouseout="this.style.color='#16a34a';">
                                                    <x-activate-icon />
                                                    <span class="tooltip-text">Activar</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @if ($direccion->empresa)
                                        {{ $direccion->empresa->razon_social }} – {{ $direccion->empresa->ruc }}
                                    @elseif ($direccion->cliente)
                                        {{ $direccion->cliente->razon_social }} – {{ $direccion->cliente->ruc }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">{{ $direccion->tipo_domicilio }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ $direccion->nombre . ', ' . $direccion->provincia ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($direccion->estado)
                                        <span
                                            class="inline-block px-3 py-1 text-xs font-semibold text-white bg-green-500 rounded-full">
                                            Activo
                                        </span>
                                    @else
                                        <span
                                            class="inline-block px-3 py-1 text-xs font-semibold text-white bg-red-500 rounded-full">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
<x-modal-show id="modalAlquiler" title="Registrar alquiler">

    <form method="POST" action="{{ route('alquiler.store') }}" class="space-y-6">
        @csrf

        {{-- ID DIRECCIÓN --}}
        <input type="hidden" name="id_direccion" id="alq-id-direccion">

        {{-- Dirección (solo lectura) --}}
        <div>
            <x-input-label value="Dirección" />
            <x-text-input id="alq-direccion-nombre" type="text" class="mt-1 block w-full" disabled />
        </div>

        {{-- Arrendador --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label value="Nombre arrendador" />
                <x-text-input name="nombre_arrendador" class="mt-1 block w-full" required />
            </div>

            <div>
                <x-input-label value="DNI arrendador" />
                <x-text-input name="dni_arrendador" class="mt-1 block w-full" required />
            </div>
        </div>

        {{-- Fechas --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label value="Inicio contrato" />
                <input type="date" name="inicio_contrato"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div>
                <x-input-label value="Fin contrato" />
                <input type="date" name="fin_contrato"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>
        </div>

        {{-- Pago --}}
        <div>
            <x-input-label value="Pago mensual (S/)" />
            <input type="number" step="0.01" name="pago_mensual"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
        </div>

        {{-- Observación --}}
        <div>
            <x-input-label value="Observación" />
            <textarea name="observacion" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                placeholder="Opcional"></textarea>
        </div>

        {{-- ACCIONES --}}
        <div class="flex justify-end pt-4">
            <x-primary-button class="px-6 py-2">
                Guardar alquiler
            </x-primary-button>
        </div>

    </form>

</x-modal-show>
<script>
    document.addEventListener('click', function(e) {

        const btn = e.target.closest('.btn-open-alquiler');
        if (!btn) return;

        // Hidden
        document.getElementById('alq-id-direccion').value =
            btn.dataset.idDireccion;

        // Texto visible
        document.getElementById('alq-direccion-nombre').value =
            `${btn.dataset.direccionNombre} (${btn.dataset.tipo})`;

        toggleModal('modalAlquiler');
    });
</script>


<x-modal-show id="direccionModal" title="Detalles de la Dirección">
    <div class="space-y-4 text-sm text-gray-800">
        <div class="grid grid-cols-2 gap-2">
            <p><strong>ID:</strong> <span id="direccionId"></span></p>
            <p><strong>Estado:</strong> <span id="direccionEstado"></span></p>
            <p><strong>Nombre:</strong> <span id="direccionNombre"></span></p>
            <p><strong>Tipo Domicilio:</strong> <span id="direccionTipo"></span></p>
            <p><strong>Distrito:</strong> <span id="direccionDistrito"></span></p>
            <p><strong>Provincia:</strong> <span id="direccionProvincia"></span></p>
            <p><strong>Región:</strong> <span id="direccionRegion"></span></p>
            <p><strong>Fecha Baja:</strong> <span id="direccionFechaBaja"></span></p>
            <p><strong>Entidad:</strong> <span id="direccionEntidad"></span></p>
            <p><strong>RUC:</strong> <span id="direccionRuc"></span></p>
            <p><strong>Creado por:</strong> <span id="direccionCreador"></span></p>
            <p><strong>Última modif. por:</strong> <span id="direccionEditor"></span></p>
        </div>
    </div>
</x-modal-show>
<script>
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-detalles-direccion')) {
            const btn = e.target.closest('.btn-detalles-direccion');
            const data = btn.dataset;

            document.getElementById('direccionId').textContent = data.id;
            document.getElementById('direccionNombre').textContent = data.nombre;
            document.getElementById('direccionTipo').textContent = data.tipo;
            document.getElementById('direccionDistrito').textContent = data.distrito || '—';
            document.getElementById('direccionProvincia').textContent = data.provincia || '—';
            document.getElementById('direccionRegion').textContent = data.region || '—';
            document.getElementById('direccionEstado').textContent = data.estado;
            document.getElementById('direccionFechaBaja').textContent = data.fecha_baja;
            document.getElementById('direccionEntidad').textContent = data.entidad;
            document.getElementById('direccionRuc').textContent = data.ruc;
            document.getElementById('direccionCreador').textContent = data.creador;
            document.getElementById('direccionEditor').textContent = data.editor;

            toggleModal('direccionModal');
        }
    });
</script>
<script>
    $(document).ready(function() {
        $('#myTable-direcciones').DataTable({
            paging: true, // Habilitar paginación
            searching: true, // Habilitar barra de búsqueda
            ordering: true, // Habilitar ordenamiento
            responsive: false, // Hacer la tabla responsive
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}" // Textos en español
            }
        });
    });
</script>
