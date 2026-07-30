<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Empresas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('cuentas_empresas.index') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white w-full md:w-auto">
                    <i class="bi bi-bank"></i> Cuentas
                </a>
                <a href="{{ route('contratos.index') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white w-full md:w-auto">
                    <i class="bi bi-file-earmark-text"></i> Contratos
                </a>
            </div>

            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive nowrap" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            {{-- <th class="px-4 py-2 border-b">UD</th> --}}
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Razón Social</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            {{-- <th class="px-4 py-2 border-b">Contacto</th> --}}
                            {{-- <th class="px-4 py-2 border-b">Usuario SOL</th>
                            <th class="px-4 py-2 border-b">Clave SOL</th> --}}
                            {{-- <th class="px-4 py-2 border-b">Detracción</th> --}}
                            {{-- <th class="px-4 py-2 border-b">Retención</th> --}}
                            {{-- <th class="px-4 py-2 border-b">Rubro</th> --}}
                            {{-- <th class="px-4 py-2 border-b">Usuario AFP</th>
                            <th class="px-4 py-2 border-b">Clave AFP</th>
                            <th class="px-4 py-2 border-b">Usuario BN</th>
                            <th class="px-4 py-2 border-b">Clave BN</th> --}}
                            <th class="px-4 py-2 border-b">Estado Actual</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas_clientes as $empresa)
                            <tr class="hover:bg-gray-50">
                                {{-- <td class="px-4 py-2 border-b">{{ substr($empresa->ruc, -1) }}</td> --}}
                                <td class="px-4 py-2 border-b">{{ $empresa->estado ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->razon_social ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->ruc ?? 'Vacío' }}</td>
                                {{-- <td class="px-4 py-2 border-b">{{ $empresa->contacto ?? 'Vacío' }}</td> --}}
                                {{-- <td class="px-4 py-2 border-b">{{ $empresa->sol_usuario ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->sol_clave ?? 'Vacío' }}</td> --}}
                                {{-- <td class="px-4 py-2 border-b">{{ $empresa->detraccion ?? 'Vacío' }}</td> --}}
                                {{-- <td class="px-4 py-2 border-b">{{ $empresa->retencion ?? 'Vacío' }}</td> --}}
                                {{-- <td class="px-4 py-2 border-b">{{ $empresa->rubro ?? 'Vacío' }}</td> --}}
                                {{-- <td class="px-4 py-2 border-b">{{ $empresa->afp_usuario ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->afp_clave ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->bn_usuario ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->bn_clave ?? 'Vacío' }}</td> --}}
                                <td class="px-4 py-2 border-b">
                                    @if ($empresa->estado_actual == 1)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                            Activo
                                        </span>
                                    @elseif($empresa->estado_actual == 2)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">
                                            Suspendido
                                        </span>
                                    @elseif($empresa->estado_actual == 0)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                            Retirado
                                        </span>
                                    @else
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-gray-500 rounded-full w-24">
                                            Sin estado
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        <button type="button"
                                            class="flex items-center py-1 rounded-md tooltip btn-detalles-empresa"
                                            style="color: #6B7280; transition: color 0.3s;"
                                            data-id="{{ $empresa->id_empresas_clientes }}"
                                            data-razon="{{ $empresa->razon_social }}" data-ruc="{{ $empresa->ruc }}"
                                            data-rubro="{{ $empresa->rubro ?? 'VACÍO' }}"
                                            data-contacto="{{ $empresa->contacto ?? 'VACÍO' }}"
                                            data-correo="{{ $empresa->correo ?? 'VACÍO' }}"
                                            data-estado="{{ $empresa->estado_actual }}"
                                            data-direcciones="{{ $empresa->direcciones->isNotEmpty()
                                                ? $empresa->direcciones->map(
                                                        fn($d) => $d->tipo_domicilio .
                                                            ': ' .
                                                            $d->nombre .
                                                            ' - ' .
                                                            $d->distrito .
                                                            ', ' .
                                                            $d->provincia .
                                                            ', ' .
                                                            $d->region,
                                                    )->implode('|')
                                                : 'Sin direcciones activas' }}"
                                            data-cuentas="{{ $empresa->cuentas->where('estado', 1)->isNotEmpty()
                                                ? $empresa->cuentas->where('estado', 1)->map(function ($c) {
                                                        return ($c->tipo == 0 ? 'S/.' : '$') .
                                                            ' ' .
                                                            $c->nombre .
                                                            ' – N°: ' .
                                                            ($c->nro_cuenta ?? '—') .
                                                            ' – CCI: ' .
                                                            ($c->nro_interbancario ?? '—');
                                                    })->implode('|')
                                                : 'Sin cuentas activas' }}">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon />
                                        </button>
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
<x-modal-show id="empresaModal" title="Detalles de la Empresa Cliente">
    <div class="space-y-4 text-sm text-gray-800">
        <div class="grid grid-cols-2 gap-2">
            <p><strong>Razón Social:</strong> <span id="empresaRazon"></span></p>
            <p><strong>RUC:</strong> <span id="empresaRuc"></span></p>
            <p><strong>Rubro:</strong> <span id="empresaRubro"></span></p>
            <p><strong>Contacto:</strong> <span id="empresaContacto"></span></p>
            <p><strong>Correo:</strong> <span id="empresaCorreo"></span></p>
            <p><strong>Estado Actual:</strong> <span id="empresaEstado"></span></p>
        </div>

        <div class="hidden">
            <h4 class="font-semibold text-gray-700">Cuentas activas:</h4>
            <ul id="empresaCuentas" class="list-disc list-inside space-y-1 text-sm"></ul>
        </div>

        <div class="hidden">
            <h4 class="font-semibold text-gray-700">Direcciones:</h4>
            <ul id="empresaDirecciones" class="list-disc list-inside space-y-1 text-sm"></ul>
        </div>
    </div>
</x-modal-show>
<script>
    document.querySelectorAll('.btn-detalles-empresa').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;

            // Datos básicos
            document.getElementById('empresaRazon').textContent = data.razon;
            document.getElementById('empresaRuc').textContent = data.ruc;
            document.getElementById('empresaRubro').textContent = data.rubro;
            document.getElementById('empresaContacto').textContent = data.contacto;
            document.getElementById('empresaCorreo').textContent = data.correo;

            // Estado actual
            let estadoTexto = 'Sin estado';
            if (data.estado === '1') estadoTexto = 'Activo';
            else if (data.estado === '2') estadoTexto = 'Suspendido';
            else if (data.estado === '0') estadoTexto = 'Retirado';
            document.getElementById('empresaEstado').textContent = estadoTexto;

            toggleModal('empresaModal');
        });
    });
</script>
