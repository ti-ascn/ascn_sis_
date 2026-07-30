<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Empresas Clientes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                @if (Auth::user()->tipo_usuario == 1)
                    <a href="{{ route('empresas_clientes.create') }}"
                        class="block bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-building-add"></i> {{ __('Crear Empresa') }}
                    </a>
                @endif
                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <a href="{{ route('inventario.branding') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
                        Firma y Logo
                    </a>
                @endif
            </div>

            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-[1200px] table-auto display nowrap responsive nowrap" id="myTable-all">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Acciones</th>
                            <th class="px-4 py-2 border-b">UD</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Razón Social</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">Usuario SOL</th>
                            <th class="px-4 py-2 border-b">Clave SOL</th>
                            <th class="px-4 py-2 border-b">Detracción</th>
                            <th class="px-4 py-2 border-b">Retención</th>
                            {{-- <th class="px-4 py-2 border-b">Rubro</th> --}}
                            <th class="px-4 py-2 border-b">Usuario AFP</th>
                            <th class="px-4 py-2 border-b">Clave AFP</th>
                            <th class="px-4 py-2 border-b">Usuario BN</th>
                            <th class="px-4 py-2 border-b">Clave BN</th>
                            <th class="px-4 py-2 border-b">Clave Extranet</th>
                            <th class="px-4 py-2 border-b">Fecha Ingreso</th>
                            <th class="px-4 py-2 border-b">Fecha Retiro</th>
                            <th class="px-4 py-2 border-b">Inicio Declaración</th>
                            <th class="px-4 py-2 border-b">Inicio Planilla</th>
                            <th class="px-4 py-2 border-b">Estado Actual</th>
                            <th class="px-4 py-2 border-b">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas_clientes as $empresa)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">
                                    <div class="flex justify-center items-center gap-2">
                                        @if (Auth::user()->tipo_usuario == 1)
                                            <a href="{{ route('empresas_clientes.edit', $empresa->id_empresas_clientes) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #F59E0B; transition: color 0.3s;"
                                                onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </a>
                                        @endif
                                        <a href="javascript:void(0)"
                                            class="flex items-center py-1 rounded-md tooltip btn-detalles-empresa"
                                            style="color: #6B7280; transition: color 0.3s;"
                                            onmouseover="this.style.color='#4B5563';"
                                            onmouseout="this.style.color='#6B7280';"
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
                                            data-razon="{{ $empresa->razon_social ?? 'VACÍO' }}"
                                            data-ruc="{{ $empresa->ruc ?? 'VACÍO' }}"
                                            data-sol_usuario="{{ $empresa->sol_usuario ?? 'VACÍO' }}"
                                            data-sol_clave="{{ $empresa->sol_clave ?? 'VACÍO' }}"
                                            data-detraccion="{{ $empresa->detraccion ?? 'VACÍO' }}"
                                            data-retencion="{{ $empresa->retencion ?? 'VACÍO' }}"
                                            data-rubro="{{ $empresa->rubro ?? 'VACÍO' }}"
                                            data-correo="{{ $empresa->correo ?? 'VACÍO' }}"
                                            data-nro_celular="{{ $empresa->nro_celular ?? 'VACÍO' }}"
                                            data-afp_usuario="{{ $empresa->afp_usuario ?? 'VACÍO' }}"
                                            data-afp_clave="{{ $empresa->afp_clave ?? 'VACÍO' }}"
                                            data-bn_usuario="{{ $empresa->bn_usuario ?? 'VACÍO' }}"
                                            data-bn_clave="{{ $empresa->bn_clave ?? 'VACÍO' }}"
                                            data-clave_extranet="{{ $empresa->clave_extranet ?? 'VACÍO' }}"
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
                                                : 'Sin cuentas activas' }}"
                                            data-fecha_ingreso="{{ $empresa->fecha_ingreso ? \Carbon\Carbon::parse($empresa->fecha_ingreso)->format('d/m/Y') : 'VACÍO' }}"
                                            data-fecha_retiro="{{ $empresa->fecha_retiro ? \Carbon\Carbon::parse($empresa->fecha_retiro)->format('d/m/Y') : '—' }}"
                                            data-mes_inicio_declaracion="{{ $empresa->mes_inicio_declaracion ? Str::ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $empresa->mes_inicio_declaracion)->translatedFormat('F Y')) : 'VACÍO' }}"
                                            data-mes_inicio_planilla="{{ $empresa->mes_inicio_planilla ? Str::ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $empresa->mes_inicio_planilla)->translatedFormat('F Y')) : 'VACÍO' }}"
                                            data-estado_actual="{{ $empresa->estado_actual == 1 ? 'Activo' : ($empresa->estado_actual == 2 ? 'Suspendido' : ($empresa->estado_actual == 3 ? 'Pendiente' : 'Retirado')) }}">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon />
                                        </a>
                                    </div>
                                </td>
                                <td class="px-4 py-2 border-b">{{ substr($empresa->ruc, -1) }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->estado ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->razon_social ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->ruc ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->sol_usuario ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->sol_clave ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->detraccion ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->retencion ?? 'Vacío' }}</td>
                                {{-- <td class="px-4 py-2 border-b">{{ $empresa->rubro ?? 'Vacío' }}</td> --}}
                                <td class="px-4 py-2 border-b">{{ $empresa->afp_usuario ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->afp_clave ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->bn_usuario ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->bn_clave ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->clave_extranet ?? 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ $empresa->fecha_ingreso ? \Carbon\Carbon::parse($empresa->fecha_ingreso)->format('d/m/Y') : 'Vacío' }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $empresa->fecha_retiro ? \Carbon\Carbon::parse($empresa->fecha_retiro)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $empresa->mes_inicio_declaracion ? Str::ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $empresa->mes_inicio_declaracion)->translatedFormat('F Y')) : 'Vacío' }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $empresa->mes_inicio_planilla ? Str::ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $empresa->mes_inicio_planilla)->translatedFormat('F Y')) : 'Vacío' }}
                                </td>
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
                                    @elseif($empresa->estado_actual == 3)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-blue-500 rounded-full w-24">
                                            Pendiente
                                        </span>
                                    @else
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-gray-500 rounded-full w-24">
                                            Sin estado
                                        </span>
                                    @endif
                                </td>
                                @if (Auth::user()->tipo_usuario == 1)
                                    <td class="px-4 py-2 border-b">
                                        <div class="flex space-x-2">
                                            {{-- <form
                                                action="{{ route('empresas_clientes.destroy', $empresa->id_empresas_clientes) }}"
                                                method="POST" style="display:inline;"
                                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta empresa?')">
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
                                            <!-- Contenedor principal del menú -->
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
                                                            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                                                @if ($empresa->estado_actual == 1)
                                                                    {{-- ACTIVO --}}
                                                                    <form method="POST"
                                                                        action="{{ route('empresas_clientes.retirar', $empresa->id_empresas_clientes) }}"
                                                                        style="display:inline;">
                                                                        @csrf
                                                                        <x-dropdown-link :href="route(
                                                                            'empresas_clientes.retirar',
                                                                            $empresa->id_empresas_clientes,
                                                                        )"
                                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                                            <i
                                                                                class="bi bi-trash-fill"></i>{{ __(' Retirar') }}
                                                                        </x-dropdown-link>
                                                                    </form>
                                                                    <form method="POST"
                                                                        action="{{ route('empresas_clientes.suspender', $empresa->id_empresas_clientes) }}"
                                                                        style="display:inline;">
                                                                        @csrf
                                                                        <x-dropdown-link :href="route(
                                                                            'empresas_clientes.suspender',
                                                                            $empresa->id_empresas_clientes,
                                                                        )"
                                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                                            <i
                                                                                class="bi bi-pause-circle-fill"></i>{{ __(' Suspender') }}
                                                                        </x-dropdown-link>
                                                                    </form>
                                                                    <form method="POST"
                                                                        action="{{ route('empresas_clientes.pendiente', $empresa->id_empresas_clientes) }}"
                                                                        style="display:inline;">
                                                                        @csrf
                                                                        <x-dropdown-link :href="route(
                                                                            'empresas_clientes.pendiente',
                                                                            $empresa->id_empresas_clientes,
                                                                        )"
                                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                                            <i
                                                                                class="bi bi-hourglass-split"></i>{{ __(' Pendiente') }}
                                                                        </x-dropdown-link>
                                                                    </form>
                                                                @elseif($empresa->estado_actual == 2)
                                                                    {{-- SUSPENDIDO --}}
                                                                    <form method="POST"
                                                                        action="{{ route('empresas_clientes.activar', $empresa->id_empresas_clientes) }}"
                                                                        style="display:inline;">
                                                                        @csrf
                                                                        <x-dropdown-link :href="route(
                                                                            'empresas_clientes.activar',
                                                                            $empresa->id_empresas_clientes,
                                                                        )"
                                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                                            <i
                                                                                class="bi bi-play-circle-fill"></i>{{ __(' Activar') }}
                                                                        </x-dropdown-link>
                                                                    </form>
                                                                    <form method="POST"
                                                                        action="{{ route('empresas_clientes.retirar', $empresa->id_empresas_clientes) }}"
                                                                        style="display:inline;">
                                                                        @csrf
                                                                        <x-dropdown-link :href="route(
                                                                            'empresas_clientes.retirar',
                                                                            $empresa->id_empresas_clientes,
                                                                        )"
                                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                                            <i
                                                                                class="bi bi-trash-fill"></i>{{ __(' Retirar') }}
                                                                        </x-dropdown-link>
                                                                    </form>
                                                                    <form method="POST"
                                                                        action="{{ route('empresas_clientes.pendiente', $empresa->id_empresas_clientes) }}"
                                                                        style="display:inline;">
                                                                        @csrf
                                                                        <x-dropdown-link :href="route(
                                                                            'empresas_clientes.pendiente',
                                                                            $empresa->id_empresas_clientes,
                                                                        )"
                                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                                            <i
                                                                                class="bi bi-hourglass-split"></i>{{ __(' Pendiente') }}
                                                                        </x-dropdown-link>
                                                                    </form>
                                                                @elseif($empresa->estado_actual == 0)
                                                                    {{-- RETIRADO --}}
                                                                    <form method="POST"
                                                                        action="{{ route('empresas_clientes.activar', $empresa->id_empresas_clientes) }}"
                                                                        style="display:inline;">
                                                                        @csrf
                                                                        <x-dropdown-link :href="route(
                                                                            'empresas_clientes.activar',
                                                                            $empresa->id_empresas_clientes,
                                                                        )"
                                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                                            <i
                                                                                class="bi bi-play-circle-fill"></i>{{ __(' Activar') }}
                                                                        </x-dropdown-link>
                                                                    </form>
                                                                    <form method="POST"
                                                                        action="{{ route('empresas_clientes.suspender', $empresa->id_empresas_clientes) }}"
                                                                        style="display:inline;">
                                                                        @csrf
                                                                        <x-dropdown-link :href="route(
                                                                            'empresas_clientes.suspender',
                                                                            $empresa->id_empresas_clientes,
                                                                        )"
                                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                                            <i
                                                                                class="bi bi-pause-circle-fill"></i>{{ __(' Suspender') }}
                                                                        </x-dropdown-link>
                                                                    </form>
                                                                    <form method="POST"
                                                                        action="{{ route('empresas_clientes.pendiente', $empresa->id_empresas_clientes) }}"
                                                                        style="display:inline;">
                                                                        @csrf
                                                                        <x-dropdown-link :href="route(
                                                                            'empresas_clientes.pendiente',
                                                                            $empresa->id_empresas_clientes,
                                                                        )"
                                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                                            <i
                                                                                class="bi bi-hourglass-split"></i>{{ __(' Pendiente') }}
                                                                        </x-dropdown-link>
                                                                    </form>
                                                                @elseif($empresa->estado_actual == 3)
                                                                    {{-- PENDIENTE --}}
                                                                    <form method="POST"
                                                                        action="{{ route('empresas_clientes.activar', $empresa->id_empresas_clientes) }}"
                                                                        style="display:inline;">
                                                                        @csrf
                                                                        <x-dropdown-link :href="route(
                                                                            'empresas_clientes.activar',
                                                                            $empresa->id_empresas_clientes,
                                                                        )"
                                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                                            <i
                                                                                class="bi bi-play-circle-fill"></i>{{ __(' Activar') }}
                                                                        </x-dropdown-link>
                                                                    </form>
                                                                    <form method="POST"
                                                                        action="{{ route('empresas_clientes.suspender', $empresa->id_empresas_clientes) }}"
                                                                        style="display:inline;">
                                                                        @csrf
                                                                        <x-dropdown-link :href="route(
                                                                            'empresas_clientes.suspender',
                                                                            $empresa->id_empresas_clientes,
                                                                        )"
                                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                                            <i
                                                                                class="bi bi-pause-circle-fill"></i>{{ __(' Suspender') }}
                                                                        </x-dropdown-link>
                                                                    </form>
                                                                    <form method="POST"
                                                                        action="{{ route('empresas_clientes.retirar', $empresa->id_empresas_clientes) }}"
                                                                        style="display:inline;">
                                                                        @csrf
                                                                        <x-dropdown-link :href="route(
                                                                            'empresas_clientes.retirar',
                                                                            $empresa->id_empresas_clientes,
                                                                        )"
                                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                                            <i
                                                                                class="bi bi-trash-fill"></i>{{ __(' Retirar') }}
                                                                        </x-dropdown-link>
                                                                    </form>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </x-slot>
                                                </x-dropdown>
                                            </div>
                                        </div>
                                    </td>
                                @else
                                    <td class="px-4 py-2 border-b">
                                        -
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
<!-- Modal para datos completos de una empresa -->
<x-modal-show id="empresaModal" title="Detalles de la Empresa">
    <div class="space-y-4 text-sm text-gray-800">

        {{-- Datos principales --}}
        <div class="grid grid-cols-2 gap-2">
            <p><strong>Razón Social:</strong> <span id="empresaRazon"></span></p>
            <p><strong>RUC:</strong> <span id="empresaRUC"></span></p>
            <p><strong>Usuario SOL:</strong> <span id="empresaSolUsuario"></span></p>
            <p><strong>Clave SOL:</strong> <span id="empresaSolClave"></span></p>
            <p><strong>Detracción:</strong> <span id="empresaDetraccion"></span></p>
            <p><strong>Retención:</strong> <span id="empresaRetencion"></span></p>
            <p><strong>Rubro:</strong> <span id="empresaRubro"></span></p>
            <p><strong>Correo:</strong> <span id="empresaCorreo"></span></p>
            <p><strong>Celular:</strong> <span id="empresaCelular"></span></p>
        </div>

        {{-- Cuentas adicionales --}}
        <div class="grid grid-cols-2 gap-2">
            <p><strong>Usuario AFP:</strong> <span id="empresaAfpUsuario"></span></p>
            <p><strong>Clave AFP:</strong> <span id="empresaAfpClave"></span></p>
            <p><strong>Usuario BN:</strong> <span id="empresaBnUsuario"></span></p>
            <p><strong>Clave BN:</strong> <span id="empresaBnClave"></span></p>
            <p><strong>Clave Extranet:</strong> <span id="empresaExtranetClave"></span></p>
        </div>

        {{-- Info de cuentas solo para admins --}}
        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
            <div>
                <h4 class="font-semibold text-gray-700">Cuentas activas:</h4>
                <ul id="empresaCuentas" class="list-disc list-inside space-y-1 text-sm"></ul>
            </div>
            <div>
                <h4 class="font-semibold text-gray-700">Direcciones:</h4>
                <ul id="empresaDirecciones" class="list-disc list-inside space-y-1 text-sm"></ul>
            </div>
        @endif

        {{-- Fechas --}}
        <div class="grid grid-cols-2 gap-2">
            <p><strong>Fecha de Ingreso:</strong> <span id="empresaFechaIngreso"></span></p>
            <p><strong>Fecha de Retiro:</strong> <span id="empresaFechaRetiro"></span></p>
            <p><strong>Mes Inicio Declaración:</strong> <span id="empresaMesInicioDeclaracion"></span></p>
            <p><strong>Mes Inicio Planilla:</strong> <span id="empresaMesInicioPlanilla"></span></p>
        </div>

        {{-- Estado --}}
        <div>
            <p><strong>Estado Actual:</strong> <span id="empresaEstado"></span></p>
        </div>
    </div>
</x-modal-show>
<script>
    $(document).ready(function() {
        $('#myTable-all').DataTable({
            responsive: false, // Hacer la tabla responsive
            pageLength: 200,
            dom: 'Bfrtip', // Activa el diseño para los botones
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: function() {
                    const hoy = new Date();
                    const dia = hoy.getDate().toString().padStart(2, '0');
                    const mes = (hoy.getMonth() + 1).toString().padStart(2, '0');
                    const anio = hoy.getFullYear();
                    return `TODAS_LAS_EMPRESAS_SISTEMA_ASECON_DESCARGADO_${dia}_${mes}_${anio}`;
                },
                exportOptions: {
                    columns: ':not(:first):not(:last)' // Excluye la primera y la última columna
                }
            }],
            language: {
                url: "/js/datatables/lang/es-ES.json" // Textos en español
            },
        });
    });
</script>
<script>
    document.querySelectorAll('.btn-detalles-empresa').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;

            document.getElementById('empresaRazon').textContent = data.razon;
            document.getElementById('empresaRUC').textContent = data.ruc;
            document.getElementById('empresaSolUsuario').textContent = data.sol_usuario;
            document.getElementById('empresaSolClave').textContent = data.sol_clave;
            document.getElementById('empresaDetraccion').textContent = data.detraccion;
            document.getElementById('empresaRetencion').textContent = data.retencion;
            document.getElementById('empresaRubro').textContent = data.rubro;
            document.getElementById('empresaCorreo').textContent = data.correo;
            document.getElementById('empresaCelular').textContent = data.nro_celular;
            document.getElementById('empresaAfpUsuario').textContent = data.afp_usuario;
            document.getElementById('empresaAfpClave').textContent = data.afp_clave;
            document.getElementById('empresaBnUsuario').textContent = data.bn_usuario;
            document.getElementById('empresaBnClave').textContent = data.bn_clave;
            document.getElementById('empresaExtranetClave').textContent = data.clave_extranet;

            // Solo si existe en el modal
            const cuentasList = document.getElementById('empresaCuentas');
            if (cuentasList) {
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
                //para las direcciones
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
            }

            document.getElementById('empresaFechaIngreso').textContent = data.fecha_ingreso;
            document.getElementById('empresaFechaRetiro').textContent = data.fecha_retiro;
            document.getElementById('empresaMesInicioDeclaracion').textContent = data
                .mes_inicio_declaracion;
            document.getElementById('empresaMesInicioPlanilla').textContent = data.mes_inicio_planilla;
            document.getElementById('empresaEstado').textContent = data.estado_actual;

            toggleModal('empresaModal');
        });
    });
</script>
