@php
    $meses = [
        'January' => 'Enero',
        'February' => 'Febrero',
        'March' => 'Marzo',
        'April' => 'Abril',
        'May' => 'Mayo',
        'June' => 'Junio',
        'July' => 'Julio',
        'August' => 'Agosto',
        'September' => 'Septiembre',
        'October' => 'Octubre',
        'November' => 'Noviembre',
        'December' => 'Diciembre',
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Reporte AFP y PDT - Plame') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            <!-- Modal para filtrar por mes -->
            @php
                $mesActual = now()->format('F');
            @endphp
            <x-modal-show id="modal-filtrar-mes-afp-pdtplame" title="Filtrar AFP y PDT PLAME de empresas por mes">
                <form action="{{ route('empresas_clientes.filtrar_por_mes_afp_pdtplame') }}"
                    method="GET"class="space-y-4">
                    <div class="flex gap-4">
                        <div class="w-1/2">
                            <label for="mes" class="block text-sm font-medium text-gray-700">
                                Seleccione el mes:
                            </label>
                            <select name="mes" id="mes"
                                class="block w-full border-gray-300 rounded-md shadow-sm">
                                @foreach ($meses as $key => $mes)
                                    <option value="{{ $key }}" {{ $key === $mesActual ? 'selected' : '' }}>
                                        {{ $mes }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-1/2">
                            <label for="anio_mes" class="block text-sm font-medium text-gray-700">
                                Seleccione el año:
                            </label>
                            <select name="anio" id="anio_mes"
                                class="block w-full border-gray-300 rounded-md shadow-sm">
                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white shadow-md">
                            <i class="bi bi-search"></i> {{ __('Buscar') }}
                        </button>
                    </div>
                </form>
            </x-modal-show>

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col mb-6">
                @if (Auth::user()->tipo_usuario == 1)
                    <a href="{{ route('empresas_clientes.create') }}"
                        class="flex items-center gap-2 bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-building-add"></i> Crear Empresa
                    </a>
                @endif

                <a href="#" onclick="toggleModal('modal-fechas-mes')"
                    class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white text-center w-full md:w-auto">
                    <i class="bi bi-calendar-event"></i> Vencimientos
                </a>

                <!-- Botón para abrir el modal de filtrar por mes -->
                <a href="#" onclick="toggleModal('modal-filtrar-mes-afp-pdtplame')"
                    class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                    <i class="bi bi-calendar-range"></i> Reportes por Mes/Año
                </a>

                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <a href="{{ route('empresas_clientes.gestion_planilla') }}"
                        class="flex items-center gap-2 bg-indigo-500 px-4 py-2 rounded-md hover:bg-indigo-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-clipboard-data"></i> Gestión de Planilla
                    </a>
                @endif
            </div>


            <div class="overflow-x-auto bg-white shadow-lg rounded-lg mt-4">
                <table class="min-w-[1200px] table-auto" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">UD</th>
                            <th class="px-4 py-2 border-b">Razón Social</th>
                            <th class="px-4 py-2 border-b">Estado Recursos</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">Usuario AFP</th>
                            <th class="px-4 py-2 border-b">Clave AFP</th>
                            {{-- <th>Período</th> --}}
                            @php
                                $periodo = $periodoDeclaracion; // Usar el período correcto del controlador
                            @endphp
                            <th>Vencimiento AFP|Período: <span class="text-blue-500">{{ ucfirst($periodo) }}</span>
                            </th>
                            <th>Vencimiento PDT PLAME|Período: <span
                                    class="text-blue-500">{{ ucfirst($periodo) }}</span></th>
                            {{-- <th>detraccion</th>
                            <th>retencion</th>
                            <th>rubro</th>
                            <th>afp usuario</th>
                            <th>afp clave</th>
                            <th>bn usuario</th>
                            <th>bn clave</th> --}}
                            <th class="px-4 py-2 border-b">Estado Actual</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas_clientes as $cliente)
                            @php
                                $ud = substr($cliente->ruc, -1);

                                // Obtener el día de vencimiento para PDT desde el array
                                $diaPDT = $fechasVencimientoPDT[$ud] ?? null;

                                if ($diaPDT) {
                                    $vencimientoPDT = \Carbon\Carbon::createFromFormat(
                                        'Y-m-d',
                                        $periodoVencimiento . '-01',
                                    )
                                        ->setDay((int) $diaPDT)
                                        ->format('d/m/Y');
                                } else {
                                    $vencimientoPDT = 'NO CONFIGURADO';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $ud }}</td>
                                <td class="px-4 py-2 border-b">{{ $cliente->razon_social }}</td>
                                <td class="px-4 py-2 border-b">{{ $cliente->estado_recursos }}</td>
                                <td class="px-4 py-2 border-b">{{ $cliente->ruc }}</td>
                                <td class="px-4 py-2 border-b">{{ $cliente->afp_usuario ?? 'VACÍO' }}</td>
                                <td class="px-4 py-2 border-b">{{ $cliente->afp_clave ?? 'VACÍO' }}</td>
                                <td class="px-4 py-2 border-b">{{ $vencimientoAFP }}</td>
                                <td class="px-4 py-2 border-b">{{ $vencimientoPDT }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($cliente->estado_actual == 1)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                            Activo
                                        </span>
                                    @elseif($cliente->estado_actual == 2)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">
                                            Suspendido
                                        </span>
                                    @elseif($cliente->estado_actual == 0)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                            Retirado
                                        </span>
                                    @elseif($cliente->estado_actual == 3)
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

                                {{-- <td>{{ $cliente->detraccion }}</td>
                                <td>{{ $cliente->retencion }}</td>
                                <td>{{ $cliente->rubro ?: 'NULL' }}</td>
                                <td>{{ $cliente->afp_usuario ?: 'NULL' }}</td>
                                <td>{{ $cliente->afp_clave ?: 'NULL' }}</td>
                                <td>{{ $cliente->bn_usuario ?: 'NULL' }}</td>
                                <td>{{ $cliente->bn_clave ?: 'NULL' }}</td> --}}
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        <a href="javascript:void(0)"
                                            class="flex items-center py-1 rounded-md tooltip btn-detalles-empresa"
                                            style="color: #6B7280; transition: color 0.3s;"
                                            onmouseover="this.style.color='#4B5563';"
                                            onmouseout="this.style.color='#6B7280';"
                                            data-razon="{{ $cliente->razon_social ?? 'VACÍO' }}"
                                            data-ruc="{{ $cliente->ruc ?? 'VACÍO' }}"
                                            data-sol_usuario="{{ $cliente->estado == 'C1' ? $cliente->sol_usuario ?? 'VACÍO' : $cliente->sol2_usuario ?? 'VACÍO' }}"
                                            data-sol_clave="{{ $cliente->estado == 'C1' ? $cliente->sol_clave ?? 'VACÍO' : $cliente->sol2_clave ?? 'VACÍO' }}"
                                            data-detraccion="{{ $cliente->detraccion ?? 'VACÍO' }}"
                                            data-retencion="{{ $cliente->retencion ?? 'VACÍO' }}"
                                            data-rubro="{{ $cliente->rubro ?? 'VACÍO' }}"
                                            data-afp_usuario="{{ $cliente->afp_usuario ?? 'VACÍO' }}"
                                            data-afp_clave="{{ $cliente->afp_clave ?? 'VACÍO' }}"
                                            data-bn_usuario="{{ $cliente->bn_usuario ?? 'VACÍO' }}"
                                            data-bn_clave="{{ $cliente->bn_clave ?? 'VACÍO' }}"
                                            data-fecha_ingreso="{{ $cliente->fecha_ingreso ? \Carbon\Carbon::parse($cliente->fecha_ingreso)->format('d/m/Y') : 'VACÍO' }}"
                                            data-fecha_retiro="{{ $cliente->fecha_retiro ? \Carbon\Carbon::parse($cliente->fecha_retiro)->format('d/m/Y') : '—' }}"
                                            data-mes_inicio_declaracion="{{ $cliente->mes_inicio_declaracion ? Str::ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $cliente->mes_inicio_declaracion)->translatedFormat('F Y')) : 'VACÍO' }}"
                                            data-mes_inicio_planilla="{{ $cliente->mes_inicio_planilla ? Str::ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $cliente->mes_inicio_planilla)->translatedFormat('F Y')) : 'VACÍO' }}"
                                            data-estado_actual="{{ $cliente->estado_actual == 1 ? 'Activo' : ($cliente->estado_actual == 2 ? 'Suspendido' : 'Retirado') }}">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon />
                                        </a>

                                        {{-- @if (Auth::user()->tipo_usuario == 1)
                                            <a href="{{ route('empresas_clientes.edit', $cliente->id_empresas_clientes) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #F59E0B; transition: color 0.3s;"
                                                onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </a>
                                        @endif --}}
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
                                                        @php
                                                            $vencimientoPDT = \Carbon\Carbon::createFromFormat(
                                                                'd/m/Y',
                                                                $vencimientoPDT,
                                                            )->format('Y-m-d');
                                                        @endphp
                                                        <div>
                                                            @if (!$cliente->existeRegistroAfp)
                                                                <x-dropdown-link href="#"
                                                                    onclick="abrirModalAfp({{ $cliente->id_empresas_clientes }}, '{{ '07/' . date('m/Y') }}')">
                                                                    <i class="bi bi-piggy-bank"></i>
                                                                    {{ __('AFP') }}
                                                                </x-dropdown-link>
                                                            @else
                                                                <x-dropdown-link href="#"
                                                                    style="cursor: not-allowed; opacity: 0.5;">
                                                                    <i class="bi bi-piggy-bank"></i>
                                                                    {{ __('AFP') }}
                                                                </x-dropdown-link>
                                                            @endif

                                                            @if (!$cliente->existeRegistroPdtPlame)
                                                                <x-dropdown-link href="#"
                                                                    onclick="abrirModalPdtplame({{ $cliente->id_empresas_clientes }}, '{{ $vencimientoPDT }}')">
                                                                    <i class="bi bi-file-earmark-text"></i>
                                                                    {{ __('PDT Plame') }}
                                                                </x-dropdown-link>
                                                            @else
                                                                <x-dropdown-link href="#"
                                                                    style="cursor: not-allowed; opacity: 0.5;">
                                                                    <i class="bi bi-file-earmark-text"></i>
                                                                    {{ __('PDT Plame') }}
                                                                </x-dropdown-link>
                                                            @endif
                                                        </div>                                                      
                                                        <x-dropdown-link
                                                            href="{{ route('empresas_clientes.editar_afp', $cliente->id_empresas_clientes) }}">
                                                            <i class="bi bi-pencil-fill"></i>
                                                            {{ __('Cambiar credenciales AFP y Mes de Inicio Planilla') }}
                                                        </x-dropdown-link>
                                                    </div>
                                                </x-slot>
                                            </x-dropdown>
                                        </div>
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
        </div>

        {{-- Cuentas adicionales --}}
        <div class="grid grid-cols-2 gap-2">
            <p><strong>Usuario AFP:</strong> <span id="empresaAfpUsuario"></span></p>
            <p><strong>Clave AFP:</strong> <span id="empresaAfpClave"></span></p>
            <p><strong>Usuario BN:</strong> <span id="empresaBnUsuario"></span></p>
            <p><strong>Clave BN:</strong> <span id="empresaBnClave"></span></p>
        </div>

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

<!-- Modal para fechas de vencimiento -->
<x-modal-show id="modal-fechas-mes" title="Fechas de Vencimiento">
    <div class="space-y-4">
        @php
            $mes = date('F', strtotime('-1 month'));
            $año = date('Y', strtotime('+0 month'));
        @endphp
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Declaración Mensual del Período: <span
                class="text-blue-500">{{ $meses[$mes] }}-{{ $año }}</span></h3>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-200 px-4 py-2">UD</th>
                        <th class="border border-gray-200 px-4 py-2">Vencimiento PDT</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($ud = 0; $ud <= 9; $ud++)
                        @php
                            $diaPDT = $fechasVencimientoPDT[$ud] ?? null;

                            $vencimientoPDT = $diaPDT
                                ? \Carbon\Carbon::createFromFormat('Y-m-d', $periodoVencimiento . '-01')
                                    ->setDay((int) $diaPDT)
                                    ->format('d/m/Y')
                                : 'NO CONFIGURADO';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-200 px-4 py-2 text-center">{{ $ud }}
                            </td>
                            <td class="border border-gray-200 px-4 py-2 text-center">{{ $vencimientoPDT }}
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</x-modal-show>

<!-- Modal para registrar AFP -->
<x-modal-show id="modal-afp" title="Registrar AFP">
    <form method="POST" action="{{ route('afp_empresas_clientes.store') }}" class="space-y-6" autocomplete="off">
        @csrf
        <input type="hidden" name="id_empresas_clientes_afp" id="id_empresas_clientes_afp">
        <input type="hidden" name="vencimientoAFP" id="vencimientoAFP">

        <!-- Campo "si/no" -->
        <div>
            <x-input-label :value="__('¿Aplica AFP?')" />
            <div class="mt-2 space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion_si_no" value="si" class="form-radio" required>
                    <span class="ml-2">Sí</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion_si_no" value="no" class="form-radio" required>
                    <span class="ml-2">No</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion_si_no" value="na" class="form-radio" checked required>
                    <span class="ml-2">N/A</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('opcion_si_no')" />
        </div>

        <!-- Observación -->
        <div>
            <x-input-label for="observacion" :value="__('Observación')" />
            <x-text-input id="observacion" name="observacion" type="text" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('observacion')" />
        </div>

        <div class="flex justify-end mt-6">
            <x-primary-button class="px-6 py-2">
                {{ __('Guardar') }}
            </x-primary-button>
        </div>
    </form>
</x-modal-show>

<!-- Modal para registrar PDT Plame -->
<x-modal-show id="modal-pdtplame" title="Registrar PDT Plame">
    <form method="POST" action="{{ route('pdtplame_empresas_clientes.store') }}" class="space-y-6"
        autocomplete="off">
        @csrf
        <input type="hidden" name="id_empresas_clientes_pdtplame" id="id_empresas_clientes_pdtplame">
        <input type="hidden" name="vencimientoPDTPlame" id="vencimientoPDTPlame">

        <!-- Campo "si/no" -->
        <div>
            <x-input-label :value="__('¿Aplica PDT Plame?')" />
            <div class="mt-2 space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion_si_no" value="si" class="form-radio" required>
                    <span class="ml-2">Sí</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion_si_no" value="no" class="form-radio" required>
                    <span class="ml-2">No</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion_si_no" value="na" class="form-radio" checked required>
                    <span class="ml-2">N/A</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('opcion_si_no')" />
        </div>

        <!-- Observación -->
        <div>
            <x-input-label for="observacion" :value="__('Observación')" />
            <x-text-input id="observacion" name="observacion" type="text" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('observacion')" />
        </div>

        <div class="flex justify-end mt-6">
            <x-primary-button class="px-6 py-2">
                {{ __('Guardar') }}
            </x-primary-button>
        </div>
    </form>
</x-modal-show>


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
            document.getElementById('empresaAfpUsuario').textContent = data.afp_usuario;
            document.getElementById('empresaAfpClave').textContent = data.afp_clave;
            document.getElementById('empresaBnUsuario').textContent = data.bn_usuario;
            document.getElementById('empresaBnClave').textContent = data.bn_clave;
            document.getElementById('empresaFechaIngreso').textContent = data.fecha_ingreso;
            document.getElementById('empresaFechaRetiro').textContent = data.fecha_retiro;
            document.getElementById('empresaMesInicioDeclaracion').textContent = data
                .mes_inicio_declaracion;
            document.getElementById('empresaMesInicioPlanilla').textContent = data.mes_inicio_planilla;
            document.getElementById('empresaEstado').textContent = data.estado_actual;

            toggleModal('empresaModal');
        });
    });

    function abrirModalAfp(idEmpresaCliente, vencimientoAFP) {
        // Asignar los valores al formulario
        document.getElementById('id_empresas_clientes_afp').value = idEmpresaCliente;
        document.getElementById('vencimientoAFP').value = vencimientoAFP;

        // Abrir el modal
        toggleModal('modal-afp');
    }

    function abrirModalPdtplame(idEmpresaCliente, vencimientoPDTPlame) {
        // Asignar los valores al formulario
        document.getElementById('id_empresas_clientes_pdtplame').value = idEmpresaCliente;
        document.getElementById('vencimientoPDTPlame').value = vencimientoPDTPlame;

        // Abrir el modal
        toggleModal('modal-pdtplame');
    }
</script>
