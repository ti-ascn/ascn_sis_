<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Choferes de <span class="text-blue-500">{{ $empresa->razon_social }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('empresas_clientes_auditoria.all') }}"
                    class="block bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white text-center w-full md:w-auto">
                    {{ __('← Volver a Empresas') }}
                </a>
                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <a href="{{ route('choferes.create', $empresa->id_empresas_clientes) }}"
                        class="block bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-person-plus-fill"></i> {{ __('Crear Chofer') }}
                    </a>
                @endif
            </div>

            <br />

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive nowrap" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Nombre</th>
                            <th class="px-4 py-2 border-b">DNI</th>
                            <th class="px-4 py-2 border-b">Placa</th>
                            <th class="px-4 py-2 border-b">Peso (T)</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($choferes as $chofer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $chofer->nombre }}</td>
                                <td class="px-4 py-2 border-b">{{ $chofer->dni }}</td>
                                <td class="px-4 py-2 border-b">{{ $chofer->placa ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">{{ $chofer->peso ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($chofer->estado)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                            Activo
                                        </span>
                                    @else
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
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
                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                            <a href="{{ route('choferes.edit', $chofer->id_chofer) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #F59E0B; transition: color 0.3s;"
                                                onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </a>
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
                                                        <span class="tooltip-text">Activar</span>
                                                        <x-activate-icon />
                                                    </button>
                                                </form>
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
