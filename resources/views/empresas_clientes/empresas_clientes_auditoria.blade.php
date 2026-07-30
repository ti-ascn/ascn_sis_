<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Empresas Clientes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                @if (Auth::user()->area->nombre != 'LEGAL')
                    <a href="{{ route('clientes.index') }}"
                        class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-person-lines-fill"></i> Clientes
                    </a>
                    <a href="{{ route('choferes.index') }}"
                        class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-truck-front-fill"></i> Choferes
                    </a>
                    <a href="{{ route('direcciones.index') }}"
                        class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-geo-alt-fill"></i> Direcciones
                    </a>
                @endif
                <a href="{{ route('cuentas_empresas.index') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white w-full md:w-auto">
                    <i class="bi bi-bank"></i> Cuentas
                </a>
                <a href="{{ route('gerente_socios.index') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white w-full md:w-auto">
                    <i class="bi bi-people"></i> Gerente Socios
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
                            <th class="px-4 py-2 border-b">Usuario SOL</th>
                            <th class="px-4 py-2 border-b">Clave SOL</th>
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
                                <td class="px-4 py-2 border-b">{{ $empresa->sol_usuario ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->sol_clave ?? 'Vacío' }}</td>
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
                                            data-sol_usuario="{{ $empresa->sol_usuario ?? 'VACÍO' }}"
                                            data-sol_clave="{{ $empresa->sol_clave ?? 'VACÍO' }}"
                                            data-rubro="{{ $empresa->rubro ?? 'VACÍO' }}"
                                            data-contacto="{{ $empresa->contacto ?? 'VACÍO' }}"
                                            data-correo="{{ $empresa->correo ?? 'VACÍO' }}"
                                            data-nro_celular="{{ $empresa->nro_celular ?? 'VACÍO' }}"
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
                                                            ($c->nro_interbancario ?? '—') .
                                                            ', Sede: ' .
                                                            $c->sede;
                                                    })->implode('|')
                                                : 'Sin cuentas activas' }}">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon />
                                        </button>
                                        {{-- !AGREGANDO PERMISOS PARA EL USUARIO 35 --}}
                                        @if (Auth::user()->area->nombre != 'LEGAL' && Auth::user()->id != 35)
                                            <a href="{{ route('direcciones.create_empresa', $empresa->id_empresas_clientes) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #dc2626; transition: color 0.3s;"
                                                onmouseover="this.style.color='#b91c1c';"
                                                onmouseout="this.style.color='#dc2626';">
                                                <span class="tooltip-text">+Dirección</span>
                                                <x-location-icon />
                                            </a>
                                        @endif
                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                            <a href="{{ route('cuentas_empresas.create', $empresa->id_empresas_clientes) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #10B981; transition: color 0.3s;"
                                                onmouseover="this.style.color='#059669';"
                                                onmouseout="this.style.color='#10B981';">
                                                <span class="tooltip-text">+Cuenta</span>
                                                <x-bank-icon />
                                            </a>
                                            @if (Auth::user()->area->nombre == 'LEGAL')
                                                @if ($empresa->estado == 'A1')
                                                    {{-- BOTÓN HABILITADO --}}
                                                    <a href="{{ route('gerente_socios.create', $empresa->id_empresas_clientes) }}"
                                                        class="flex items-center py-1 rounded-md tooltip"
                                                        style="color: #3575DD; transition: color 0.3s;"
                                                        onmouseover="this.style.color='#3B82F6';"
                                                        onmouseout="this.style.color='#3575DD';">
                                                        <span class="tooltip-text">+Gerente/Socios</span>
                                                        <x-add-people-icon />
                                                    </a>
                                                @else
                                                    {{-- BOTÓN DESHABILITADO --}}
                                                    <span
                                                        class="flex items-center py-1 rounded-md tooltip cursor-not-allowed opacity-50"
                                                        style="color: #9CA3AF;">
                                                        <span class="tooltip-text">Disponible solo para empresas
                                                            A1</span>
                                                        <x-add-people-icon />
                                                    </span>
                                                @endif
                                            @endif
                                            @if (Auth::user()->area->nombre != 'LEGAL')
                                                <a href="{{ route('choferes.create', $empresa->id_empresas_clientes) }}"
                                                    class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #F59E0B; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#D97706';"
                                                    onmouseout="this.style.color='#F59E0B';">
                                                    <span class="tooltip-text">+Chofer</span>
                                                    <x-driver-icon />
                                                </a>
                                            @endif
                                        @endif
                                        <!-- Contenedor principal del menú -->
                                        {{-- !AGREGANDO PERMISOS PARA EL USUARIO 35 --}}
                                        @if (Auth::user()->id != 35)
                                            <div class="relative">
                                                <x-dropdown align="right" width="48">
                                                    <x-slot name="trigger">
                                                        <button class="flex items-center py-1 rounded-md tooltip"
                                                            style="color: #6B7280; transition: color 0.3s;"
                                                            onmouseover="this.style.color='#4B5563';"
                                                            onmouseout="this.style.color='#6B7280';">
                                                            <x-dots-vertical-icon />
                                                        </button>
                                                    </x-slot>

                                                    <x-slot name="content">
                                                        <div
                                                            class="absolute right-0 z-50 w-48 bg-white shadow-lg rounded-md border p-2">
                                                            <x-dropdown-link :href="route(
                                                                'cuentas_empresas.porEmpresa',
                                                                $empresa->id_empresas_clientes,
                                                            )">
                                                                <i class="bi bi-bank mr-2"></i> Ver Cuentas
                                                            </x-dropdown-link>
                                                            <x-dropdown-link :href="route(
                                                                'gerente_socios.listar',
                                                                $empresa->id_empresas_clientes,
                                                            )">
                                                                <i class="bi bi-people mr-2"></i> Ver Gerentes/Socios
                                                            </x-dropdown-link>
                                                            @if (Auth::user()->area->nombre != 'LEGAL')
                                                                <x-dropdown-link :href="route(
                                                                    'guias.create_empresa',
                                                                    $empresa->id_empresas_clientes,
                                                                )">
                                                                    <i class="bi bi-file-earmark-plus mr-2"></i>
                                                                    {{ __('Crear Guía') }}
                                                                </x-dropdown-link>
                                                                @if ((Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2) && $empresa->inventarios->isEmpty())
                                                                    <x-dropdown-link href="#"
                                                                        onclick="openInventarioModal({{ $empresa->id_empresas_clientes }}, '{{ $empresa->razon_social }}'); return false;">
                                                                        <i class="bi bi-box-seam mr-2"></i> Crear
                                                                        Inventario
                                                                    </x-dropdown-link>
                                                                @endif
                                                                <x-dropdown-link :href="route(
                                                                    'empresas_clientes.editar_contacto',
                                                                    $empresa->id_empresas_clientes,
                                                                )">
                                                                    <i
                                                                        class="bi bi-person-lines-fill mr-2"></i>{{ __('Editar datos') }}
                                                                </x-dropdown-link>

                                                                <x-dropdown-link :href="route(
                                                                    'choferes.porEmpresa',
                                                                    $empresa->id_empresas_clientes,
                                                                )">
                                                                    <i
                                                                        class="bi bi-people-fill mr-2"></i>{{ __('Ver Choferes') }}
                                                                </x-dropdown-link>
                                                                <x-dropdown-link :href="route(
                                                                    'direcciones.porEmpresa',
                                                                    $empresa->id_empresas_clientes,
                                                                )">
                                                                    <i
                                                                        class="bi bi-map mr-2"></i>{{ __('Ver Direcciones') }}
                                                                </x-dropdown-link>

                                                                <x-dropdown-link :href="route(
                                                                    'facturas.por_empresa',
                                                                    $empresa->id_empresas_clientes,
                                                                )">
                                                                    <i class="bi bi-receipt-cutoff mr-2"></i> Ver
                                                                    Facturas
                                                                </x-dropdown-link>
                                                            @endif
                                                        </div>
                                                    </x-slot>
                                                </x-dropdown>
                                            </div>
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
<x-modal-show id="empresaModal" title="Detalles de la Empresa Cliente">
    <div class="space-y-4 text-sm text-gray-800">
        <div class="grid grid-cols-2 gap-2">
            <p><strong>Razón Social:</strong> <span id="empresaRazon"></span></p>
            <p><strong>RUC:</strong> <span id="empresaRuc"></span></p>
            <p><strong>Usuario SOL:</strong> <span id="empresaSolUsuario"></span></p>
            <p><strong>Clave SOL:</strong> <span id="empresaSolClave"></span></p>
            <p><strong>Rubro:</strong> <span id="empresaRubro"></span></p>
            <p><strong>Contacto:</strong> <span id="empresaContacto"></span></p>
            <p><strong>Correo:</strong> <span id="empresaCorreo"></span></p>
            <p><strong>Celular:</strong> <span id="empresaCelular"></span></p>
            <p><strong>Estado Actual:</strong> <span id="empresaEstado"></span></p>
        </div>

        <div>
            <h4 class="font-semibold text-gray-700">Cuentas activas:</h4>
            <ul id="empresaCuentas" class="list-disc list-inside space-y-1 text-sm"></ul>
        </div>

        <div>
            <h4 class="font-semibold text-gray-700">Direcciones:</h4>
            <ul id="empresaDirecciones" class="list-disc list-inside space-y-1 text-sm"></ul>
        </div>
    </div>
</x-modal-show>

<x-modal-show id="inventarioModal" title="Crear Inventario">
    <form method="POST" action="{{ route('inventario.store') }}" id="inventarioForm">
        @csrf
        <input type="hidden" name="id_empresas_cliente" id="empresaIdInput">

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Empresa</label>
            <p id="empresaNombreTexto" class="text-gray-800 font-semibold mt-1"></p>
        </div>

        <div class="mb-4">
            <label for="prefijo" class="block text-sm font-medium text-gray-700">Prefijo de Código</label>
            <input type="text" name="prefijo" id="prefijo" required maxlength="10"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase"
                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')">Ejemplo: AFOR, GRUP, etc.</p>
        </div>

        <div class="flex justify-end">
            <x-primary-button>Guardar</x-primary-button>
        </div>
    </form>
</x-modal-show>
<script>
    document.querySelectorAll('.btn-detalles-empresa').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;

            // Datos básicos
            document.getElementById('empresaRazon').textContent = data.razon;
            document.getElementById('empresaRuc').textContent = data.ruc;
            document.getElementById('empresaSolUsuario').textContent = data.sol_usuario;
            document.getElementById('empresaSolClave').textContent = data.sol_clave;
            document.getElementById('empresaRubro').textContent = data.rubro;
            document.getElementById('empresaContacto').textContent = data.contacto;
            document.getElementById('empresaCorreo').textContent = data.correo;
            document.getElementById('empresaCelular').textContent = data.nro_celular;

            // Estado actual
            let estadoTexto = 'Sin estado';
            if (data.estado === '1') estadoTexto = 'Activo';
            else if (data.estado === '2') estadoTexto = 'Suspendido';
            else if (data.estado === '0') estadoTexto = 'Retirado';
            document.getElementById('empresaEstado').textContent = estadoTexto;

            // Direcciones
            const dirList = document.getElementById('empresaDirecciones');
            dirList.innerHTML = '';
            if (data.direcciones && data.direcciones !== 'Sin direcciones activas') {
                const direcciones = data.direcciones.split('|');
                direcciones.forEach(dir => {
                    const li = document.createElement('li');
                    li.textContent = dir;
                    dirList.appendChild(li);
                });
            } else {
                const li = document.createElement('li');
                li.textContent = 'Sin direcciones activas';
                dirList.appendChild(li);
            }

            // Cuentas
            const cuentasList = document.getElementById('empresaCuentas');
            cuentasList.innerHTML = '';
            if (data.cuentas && data.cuentas !== 'Sin cuentas activas') {
                const cuentas = data.cuentas.split('|');
                cuentas.forEach(cuenta => {
                    const li = document.createElement('li');
                    li.textContent = cuenta;
                    cuentasList.appendChild(li);
                });
            } else {
                const li = document.createElement('li');
                li.textContent = 'Sin cuentas activas';
                cuentasList.appendChild(li);
            }

            toggleModal('empresaModal');
        });
    });
</script>

<script>
    function openInventarioModal(empresaId, razonSocial) {
        document.getElementById('empresaIdInput').value = empresaId;
        document.getElementById('empresaNombreTexto').textContent = razonSocial;
        toggleModal('inventarioModal');
    }
</script>
