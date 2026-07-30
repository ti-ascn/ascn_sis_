<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Choferes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                {{-- @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2) --}}
                    <a href="{{ route('choferes.create') }}"
                        class="flex items-center justify-center gap-2 bg-green-500 px-4 py-2 rounded-md text-white hover:bg-green-600 transition duration-300 w-full md:w-auto">
                        <i class="bi bi-person-plus-fill"></i> Crear Chofer
                    </a>
                {{-- @endif --}}

                <a href="{{ route('empresas_clientes_auditoria.all') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md text-white hover:bg-blue-600 transition duration-300 w-full md:w-auto">
                    <i class="bi bi-building"></i> Empresas
                </a>

                <a href="{{ route('direcciones.index') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md text-white hover:bg-blue-600 transition duration-300 w-full md:w-auto">
                    <i class="bi bi-geo-alt-fill"></i> Direcciones
                </a>

                <a href="{{ route('clientes.index') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md text-white hover:bg-blue-600 transition duration-300 w-full md:w-auto">
                    <i class="bi bi-people-fill"></i> Clientes
                </a>

                <a href="{{ route('programacion.editar') }}"
                    class="flex items-center justify-center gap-2 bg-amber-500 px-4 py-2 rounded-md text-white hover:bg-amber-600 transition duration-300 w-full md:w-auto font-semibold">
                    <i class="bi bi-calendar3"></i> Control Transporte
                </a>
            </div>


            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive nowrap" id="myTable-choferes">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Empresa</th>
                            <th class="px-4 py-2 border-b">Nombre</th>
                            <th class="px-4 py-2 border-b">DNI</th>
                            <th class="px-4 py-2 border-b">Placa</th>
                            <th class="px-4 py-2 border-b">Peso (T)</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($choferes as $chofer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">
                                    {{ $chofer->empresa->razon_social ?? '—' }}
                                </td>
                                <td class="px-4 py-2 border-b">{{ $chofer->nombre }}</td>
                                <td class="px-4 py-2 border-b">{{ $chofer->dni }}</td>
                                <td class="px-4 py-2 border-b">{{ $chofer->placa ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">{{ $chofer->peso ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($chofer->estado)
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
                                <td class="px-4 py-2 border-b relative">
                                    <div class="flex space-x-2 justify-center">
                                        <a href="javascript:void(0)"
                                            class="flex items-center py-1 rounded-md tooltip btn-detalles-chofer"
                                            style="color: #6B7280; transition: color 0.3s;"
                                            onmouseover="this.style.color='#4B5563';"
                                            onmouseout="this.style.color='#6B7280';" data-id="{{ $chofer->id_chofer }}"
                                            data-nombre="{{ $chofer->nombre ?? '—' }}"
                                            data-dni="{{ $chofer->dni ?? '—' }}"
                                            data-placa="{{ $chofer->placa ?? '—' }}"
                                            data-marca="{{ $chofer->marca ?? '—' }}"
                                            data-licencia="{{ $chofer->licencia ?? '—' }}"
                                            data-certificado="{{ $chofer->certificado ?? '—' }}"
                                            data-config="{{ $chofer->config_vehicular ?? '—' }}"
                                            data-cng="{{ $chofer->cng ?? '—' }}"
                                            data-peso="{{ $chofer->peso ?? '—' }}"
                                            data-fecha_alta="{{ \Carbon\Carbon::parse($chofer->fecha_alta)->format('d/m/Y') }}"
                                            data-fecha_baja="{{ $chofer->fecha_baja ? \Carbon\Carbon::parse($chofer->fecha_baja)->format('d/m/Y') : '—' }}"
                                            data-estado="{{ $chofer->estado ? 'Activo' : 'Inactivo' }}"
                                            data-empresa="{{ $chofer->empresa->razon_social ?? '—' }}">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon />
                                        </a>

                                        {{-- Botón Programación --}}
                                        <a href="{{ route('programacion.index', $chofer->id_chofer) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #3B82F6; transition: color 0.3s;"
                                            onmouseover="this.style.color='#2563EB';"
                                            onmouseout="this.style.color='#3B82F6';">
                                            <span class="tooltip-text">Programación</span>
                                            <i class="bi bi-calendar3 text-[18px] ml-1"></i>
                                        </a>
                                        {{-- @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2) --}}
                                            {{-- Botón Editar --}}
                                            <a href="{{ route('choferes.edit', $chofer->id_chofer) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #F59E0B; transition: color 0.3s;"
                                                onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </a>

                                            {{-- Botón Activar/Desactivar --}}
                                            @if ($chofer->estado)
                                                <form action="{{ route('choferes.desactivar', $chofer->id_chofer) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="flex items-center py-1 rounded-md tooltip"
                                                        style="color: #dc2626;"
                                                        onmouseover="this.style.color='#b91c1c';"
                                                        onmouseout="this.style.color='#dc2626';">
                                                        <x-deactivate-icon />
                                                        <span class="tooltip-text">Desactivar</span>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('choferes.reactivar', $chofer->id_chofer) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="flex items-center py-1 rounded-md tooltip"
                                                        style="color: #16a34a;"
                                                        onmouseover="this.style.color='#15803d';"
                                                        onmouseout="this.style.color='#16a34a';">
                                                        <x-activate-icon />
                                                        <span class="tooltip-text">Activar</span>
                                                    </button>
                                                </form>
                                            @endif
                                        {{-- @endif --}}
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

<x-modal-show id="choferModal" title="Detalles del Chofer">
    <div class="space-y-4 text-sm text-gray-800">
        <div class="grid grid-cols-2 gap-2">
            <p><strong>ID:</strong> <span id="choferId"></span></p>
            <p><strong>Empresa:</strong> <span id="choferEmpresa"></span></p>
            <p><strong>Nombre:</strong> <span id="choferNombre"></span></p>
            <p><strong>DNI:</strong> <span id="choferDni"></span></p>
            <p><strong>Placa:</strong> <span id="choferPlaca"></span></p>
            <p><strong>Marca:</strong> <span id="choferMarca"></span></p>
            <p><strong>Licencia:</strong> <span id="choferLicencia"></span></p>
            <p><strong>Certificado:</strong> <span id="choferCertificado"></span></p>
            <p><strong>Configuración Vehicular:</strong> <span id="choferConfig"></span></p>
            <p><strong>CNG:</strong> <span id="choferCng"></span></p>
            <p><strong>Peso (T):</strong> <span id="choferPeso"></span></p>
            <p><strong>Fecha Alta:</strong> <span id="choferAlta"></span></p>
            <p><strong>Fecha Baja:</strong> <span id="choferBaja"></span></p>
            <p><strong>Estado:</strong> <span id="choferEstado"></span></p>
        </div>
    </div>
</x-modal-show>

<script>
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-detalles-chofer')) {
            const btn = e.target.closest('.btn-detalles-chofer');
            const data = btn.dataset;

            document.getElementById('choferId').textContent = data.id;
            document.getElementById('choferEmpresa').textContent = data.empresa;
            document.getElementById('choferNombre').textContent = data.nombre;
            document.getElementById('choferDni').textContent = data.dni;
            document.getElementById('choferPlaca').textContent = data.placa;
            document.getElementById('choferMarca').textContent = data.marca;
            document.getElementById('choferLicencia').textContent = data.licencia;
            document.getElementById('choferCertificado').textContent = data.certificado;
            document.getElementById('choferConfig').textContent = data.config;
            document.getElementById('choferCng').textContent = data.cng;
            document.getElementById('choferPeso').textContent = data.peso;
            document.getElementById('choferAlta').textContent = data.fecha_alta;
            document.getElementById('choferBaja').textContent = data.fecha_baja;
            document.getElementById('choferEstado').textContent = data.estado;

            toggleModal('choferModal');
        }
    });
</script>

<script>
    $(document).ready(function() {
        $('#myTable-choferes').DataTable({
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
