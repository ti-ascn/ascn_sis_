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
            {{ __('Registrar Reporte IGV - Renta') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            <!-- Modal para filtrar por año -->
            <x-modal-show id="modal-filtrar-anio" title="Filtrar empresas sin pre-liquidación y determinación por año">
                <form action="{{ route('empresas_clientes.filtrar_no_preliquidacion') }}" method="GET" class="space-y-4">
                    <div class="flex flex-col">
                        <label for="anio" class="block text-sm font-medium text-gray-700">Seleccione el año:</label>
                        <select name="anio" id="anio" class="block w-full border-gray-300 rounded-md shadow-sm">
                            @for ($i = date('Y'); $i >= date('Y') - 10; $i--)
                                <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white shadow-md">
                            <i class="bi bi-search"></i> {{ __('Buscar') }}
                        </button>
                    </div>
                </form>
            </x-modal-show>

            @php
                $mesActual = now()->format('F');
            @endphp

            <x-modal-show id="modal-filtrar-mes" title="Filtrar Compras, Ventas, Sire y PDT de empresas por mes">
                <form action="{{ route('empresas_clientes.filtrar_por_mes') }}" method="GET" class="space-y-4">
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
                                    <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
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

                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <a href="{{ route('determinacion_mensual.sinDeterminacion') }}"
                        class="flex items-center gap-2 bg-red-500 px-4 py-2 rounded-md hover:bg-red-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-exclamation-circle"></i> Sin Determinación Mensual
                    </a>

                    <a href="#" onclick="toggleModal('modal-filtrar-anio')"
                        class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-funnel"></i> Filtrar por Año
                    </a>
                @endif

                <a href="#" onclick="toggleModal('modal-filtrar-mes')"
                    class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                    <i class="bi bi-calendar-range"></i> Reportes por Mes/Año
                </a>
            </div>




            <div class="overflow-x-auto bg-white shadow-lg rounded-lg mt-4">
                <table class="min-w-[1200px] table-auto" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">UD</th>
                            <th class="px-4 py-2 border-b">Razón Social</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">Usuario SOL</th>
                            <th class="px-4 py-2 border-b">Clave SOL</th>
                            <th class="px-4 py-2 border-b text-center">Recepción de Documentos</th>
                            @php
                                $periodo = $periodoDeclaracion; // Usar el período correcto del controlador
                            @endphp
                            <th>Vencimiento SIRE | Período: <span class="text-blue-500">{{ ucfirst($periodo) }}</span>
                            </th>
                            <th>Vencimiento PDT | Período: <span class="text-blue-500">{{ ucfirst($periodo) }}</span>
                            </th>
                            <th class="px-4 py-2 border-b">Estado Actual</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas_clientes as $cliente)
                            @php

                                $ud = substr($cliente->ruc, -1);

                                $diaSIRE = $fechasVencimientoSIRE[$ud] ?? null;
                                $diaPDT = $fechasVencimientoPDT[$ud] ?? null;

                                $vencimientoSIRE = $diaSIRE
                                    ? Carbon\Carbon::createFromFormat('Y-m-d', $periodoVencimiento . '-01')
                                        ->setDay((int) $diaSIRE)
                                        ->format('d/m/Y')
                                    : 'NO CONFIGURADO';

                                $vencimientoPDT = $diaPDT
                                    ? Carbon\Carbon::createFromFormat('Y-m-d', $periodoVencimiento . '-01')
                                        ->setDay((int) $diaPDT)
                                        ->format('d/m/Y')
                                    : 'NO CONFIGURADO';

                                $ultimoEstadoRecepcion = \App\Models\RecepcionDocumento::where('id_empresas_clientes', $cliente->id_empresas_clientes)
                                    ->orderBy('id_recepcion', 'desc')
                                    ->first();
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $ud }}</td>
                                <td class="px-4 py-2 border-b">{{ $cliente->razon_social }}</td>
                                <td class="px-4 py-2 border-b">{{ $cliente->estado_recursos }}</td>
                                <td class="px-4 py-2 border-b">{{ $cliente->ruc }}</td>
                                <td class="px-4 py-2 border-b">{{ $cliente->sol_usuario }}</td>
                                <td class="px-4 py-2 border-b">{{ $cliente->sol_clave }}</td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex justify-center">
                                        <a href="{{ url('recepcion-documentos/' . $cliente->id_empresas_clientes . '/registros-mes-actual') }}" target="_blank" class="hover:opacity-80 transition-opacity">
                                            @if ($ultimoEstadoRecepcion && $ultimoEstadoRecepcion->estado === 'solicitud')
                                                <span class="flex justify-center items-center px-4 py-1 text-white bg-blue-500 rounded-full w-24">
                                                    Solicitud
                                                </span>
                                            @elseif($ultimoEstadoRecepcion && $ultimoEstadoRecepcion->estado === 'recepcion')
                                                <span class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">
                                                    Recepción
                                                </span>
                                            @elseif($ultimoEstadoRecepcion && $ultimoEstadoRecepcion->estado === 'entrega')
                                                <span class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                                    Entregado
                                                </span>
                                            @else
                                                <span class="flex justify-center items-center px-4 py-1 text-white bg-gray-400 rounded-full w-24">
                                                    -
                                                </span>
                                            @endif
                                        </a>
                                    </div>
                                </td>
                                <td class="px-4 py-2 border-b">{{ $vencimientoSIRE }}</td>
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
                                            data-direcciones="{{ $cliente->direcciones->isNotEmpty()
                                                ? $cliente->direcciones->map(
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
                                            data-razon="{{ $cliente->razon_social ?? 'VACÍO' }}"
                                            data-ruc="{{ $cliente->ruc ?? 'VACÍO' }}"
                                            data-sol_usuario="{{ $cliente->sol_usuario ?? 'VACÍO' }}"
                                            data-sol_clave="{{ $cliente->sol_clave ?? 'VACÍO' }}"
                                            data-detraccion="{{ $cliente->detraccion ?? 'VACÍO' }}"
                                            data-retencion="{{ $cliente->retencion ?? 'VACÍO' }}"
                                            data-rubro="{{ $cliente->rubro ?? 'VACÍO' }}"
                                            data-afp_usuario="{{ $cliente->afp_usuario ?? 'VACÍO' }}"
                                            data-afp_clave="{{ $cliente->afp_clave ?? 'VACÍO' }}"
                                            data-bn_usuario="{{ $cliente->bn_usuario ?? 'VACÍO' }}"
                                            data-bn_clave="{{ $cliente->bn_clave ?? 'VACÍO' }}"
                                            data-cuentas="{{ $cliente->cuentas->where('estado', 1)->isNotEmpty()
                                                ? $cliente->cuentas->where('estado', 1)->map(function ($c) {
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
                                            data-fecha_ingreso="{{ $cliente->fecha_ingreso ? \Carbon\Carbon::parse($cliente->fecha_ingreso)->format('d/m/Y') : 'VACÍO' }}"
                                            data-fecha_retiro="{{ $cliente->fecha_retiro ? \Carbon\Carbon::parse($cliente->fecha_retiro)->format('d/m/Y') : '—' }}"
                                            data-mes_inicio_declaracion="{{ $cliente->mes_inicio_declaracion ? Str::ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $cliente->mes_inicio_declaracion)->translatedFormat('F Y')) : 'VACÍO' }}"
                                            data-mes_inicio_planilla="{{ $cliente->mes_inicio_planilla ? Str::ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $cliente->mes_inicio_planilla)->translatedFormat('F Y')) : 'VACÍO' }}"
                                            data-estado_actual="{{ $cliente->estado_actual == 1 ? 'Activo' : ($cliente->estado_actual == 2 ? 'Suspendido' : 'Retirado') }}">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon />
                                        </a>
                                        @if (Auth::user()->tipo_usuario == 1)
                                            {{-- <a href="{{ route('pre-liquidacion.create', $cliente->id_empresas_clientes) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #10B981; transition: color 0.3s;"
                                                onmouseover="this.style.color='#059669';"
                                                onmouseout="this.style.color='#10B981';">
                                                <span class="tooltip-text">+Pre Liquidación</span>
                                                <x-tax-icon />
                                            </a> --}}
                                            <a href="{{ route('empresas_clientes.edit', $cliente->id_empresas_clientes) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #F59E0B; transition: color 0.3s;"
                                                onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </a>
                                            {{-- <form
                                                action="{{ route('empresas_clientes.destroy', $cliente->id_empresas_clientes) }}"
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
                                        @endif
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
                                                        @php
                                                            $vencimientoSIRE = \Carbon\Carbon::createFromFormat(
                                                                'd/m/Y',
                                                                $vencimientoSIRE,
                                                            )->format('Y-m-d');
                                                            $vencimientoPDT = \Carbon\Carbon::createFromFormat(
                                                                'd/m/Y',
                                                                $vencimientoPDT,
                                                            )->format('Y-m-d');
                                                        @endphp
                                                        @if (!$cliente->existeRegistroSire)
                                                            <x-dropdown-link href="#"
                                                                onclick="abrirModalSire({{ $cliente->id_empresas_clientes }}, '{{ $vencimientoSIRE }}')">
                                                                <i class="bi bi-stripe"></i> {{ __('Sire') }}
                                                            </x-dropdown-link>
                                                        @else
                                                            <x-dropdown-link href="#"
                                                                style="cursor: not-allowed; opacity: 0.5;">
                                                                <i class="bi bi-stripe"></i> {{ __('Sire') }}
                                                            </x-dropdown-link>
                                                        @endif
                                                        @if (!$cliente->existeRegistroPDT)
                                                            <x-dropdown-link href="#"
                                                                onclick="abrirModalPdt({{ $cliente->id_empresas_clientes }}, '{{ $vencimientoPDT }}')">
                                                                <i class="bi bi-file-earmark-text-fill"></i>
                                                                {{ __('PDT') }}
                                                            </x-dropdown-link>
                                                        @else
                                                            <x-dropdown-link href="#"
                                                                style="cursor: not-allowed; opacity: 0.5;">
                                                                <i class="bi bi-file-earmark-text-fill"></i>
                                                                {{ __('PDT') }}
                                                            </x-dropdown-link>
                                                        @endif
                                                        @if (!$cliente->existeRegistroCompras)
                                                            <x-dropdown-link href="#"
                                                                onclick="abrirModalCompras({{ $cliente->id_empresas_clientes }})">
                                                                <i class="bi bi-currency-dollar"></i>
                                                                {{ __('Compras') }}
                                                            </x-dropdown-link>
                                                        @else
                                                            <x-dropdown-link href="#"
                                                                style="cursor: not-allowed; opacity: 0.5;">
                                                                <i class="bi bi-currency-dollar"></i>
                                                                {{ __('Compras') }}
                                                            </x-dropdown-link>
                                                        @endif
                                                        @if (!$cliente->existeRegistroVentas)
                                                            <x-dropdown-link href="#"
                                                                onclick="abrirModalVentas({{ $cliente->id_empresas_clientes }})">
                                                                <i class="bi bi-cart-fill"></i> {{ __('Ventas') }}
                                                            </x-dropdown-link>
                                                        @else
                                                            <x-dropdown-link href="#"
                                                                style="cursor: not-allowed; opacity: 0.5;">
                                                                <i class="bi bi-cart-fill"></i>
                                                                {{ __('Ventas') }}
                                                            </x-dropdown-link>
                                                        @endif
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
            <p><strong>Clave Extranet:</strong> <span id="empresaClaveExtranet"></span></p>
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

<!-- Modal para fechas de vencimiento -->
<x-modal-show id="modal-fechas-mes" title="Fechas de Vencimiento">
    <div class="space-y-4">
        @php
            // Periodo a declarar (mes anterior)
            $periodoDeclaracionModal = Carbon\Carbon::now()->subMonth()->translatedFormat('F-Y');
        @endphp

        <h3 class="text-lg font-semibold text-gray-700 mb-4">
            Declaración Mensual del Período:
            <span class="text-blue-500">{{ ucfirst($periodoDeclaracionModal) }}</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-200 px-4 py-2">UD</th>
                        <th class="border border-gray-200 px-4 py-2">Vencimiento SIRE</th>
                        <th class="border border-gray-200 px-4 py-2">Vencimiento PDT</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($ud = 0; $ud <= 9; $ud++)
                        @php
                            $diaSIRE = $fechasVencimientoSIRE[$ud] ?? null;
                            $diaPDT = $fechasVencimientoPDT[$ud] ?? null;

                            $vencimientoSIRE = $diaSIRE
                                ? \Carbon\Carbon::createFromFormat('Y-m-d', $periodoVencimiento . '-01')
                                    ->setDay((int) $diaSIRE)
                                    ->format('d/m/Y')
                                : 'NO CONFIGURADO';

                            $vencimientoPDT = $diaPDT
                                ? \Carbon\Carbon::createFromFormat('Y-m-d', $periodoVencimiento . '-01')
                                    ->setDay((int) $diaPDT)
                                    ->format('d/m/Y')
                                : 'NO CONFIGURADO';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-200 px-4 py-2 text-center">{{ $ud }}</td>
                            <td class="border border-gray-200 px-4 py-2 text-center">{{ $vencimientoSIRE }}</td>
                            <td class="border border-gray-200 px-4 py-2 text-center">{{ $vencimientoPDT }}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</x-modal-show>

<!-- Modal para registrar SIRE -->
<x-modal-show id="modal-sire" title="Registrar SIRE">
    <form method="POST" action="{{ route('sire_empresas_clientes.store') }}" class="space-y-6" autocomplete="off">
        @csrf
        <input type="hidden" name="id_empresas_clientes_sire" id="id_empresas_clientes_sire">
        <input type="hidden" name="vencimientoSIRE" id="vencimientoSIRE">

        <!-- Campo "si/no" -->
        <div>
            <x-input-label :value="__('¿Aplica SIRE?')" />
            <div class="mt-2 space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion_si_no" value="si" class="form-radio" required>
                    <span class="ml-2">Sí</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion_si_no" value="no" class="form-radio" checked required>
                    <span class="ml-2">No</span>
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
<!-- Modal para registrar PDT -->
<x-modal-show id="modal-pdt" title="Registrar PDT">
    <form method="POST" action="{{ route('pdt_empresas_clientes.store') }}" class="space-y-6" autocomplete="off">
        @csrf
        <input type="hidden" name="id_empresas_clientes_pdt" id="id_empresas_clientes_pdt">
        <input type="hidden" name="vencimientoPDT" id="vencimientoPDT">

        <!-- Campo "si/no" -->

        <div>
            <x-input-label :value="__('¿Aplica PDT?')" />
            <div class="mt-2 space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion_si_no" value="si" class="form-radio" required>
                    <span class="ml-2">Sí</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion_si_no" value="no" class="form-radio" checked required>
                    <span class="ml-2">No</span>
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

<x-modal-show id="modal-ventas" title="Registrar Ventas">
    <form method="POST" action="{{ route('ventas_empresas_clientes.store') }}" class="space-y-6"
        autocomplete="off">
        @csrf
        <input type="hidden" name="id_empresas_clientes_ventas" id="id_empresas_clientes_ventas">

        <div>
            <x-input-label :value="__('Registro')" />
            <div class="mt-2 space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="registro" value="si" class="form-radio" required>
                    <span class="ml-2">Sí</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="registro" value="no" class="form-radio" checked required>
                    <span class="ml-2">No</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="registro" value="na" class="form-radio" required>
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

<x-modal-show id="modal-compras" title="Registrar Compras">
    <form method="POST" action="{{ route('compras_empresas_clientes.store') }}" class="space-y-6"
        autocomplete="off">
        @csrf
        <input type="hidden" name="id_empresas_clientes_compras" id="id_empresas_clientes_compras">

        <div>
            <x-input-label :value="__('Registro')" />
            <div class="mt-2 space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="registro" value="si" class="form-radio" required>
                    <span class="ml-2">Sí</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="registro" value="no" class="form-radio" checked required>
                    <span class="ml-2">No</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="registro" value="na" class="form-radio" required>
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
            document.getElementById('empresaClaveExtranet').textContent = data.clave_extranet;

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

    function abrirModalSire(idEmpresaCliente, vencimientoSIRE) {
        // Asignar los valores al formulario
        document.getElementById('id_empresas_clientes_sire').value = idEmpresaCliente;
        document.getElementById('vencimientoSIRE').value = vencimientoSIRE;

        // Abrir el modal
        toggleModal('modal-sire');
    }

    function abrirModalPdt(idEmpresaCliente, vencimientoPDT) {
        // Asignar los valores al formulario
        document.getElementById('id_empresas_clientes_pdt').value = idEmpresaCliente;
        document.getElementById('vencimientoPDT').value = vencimientoPDT;

        // Abrir el modal
        toggleModal('modal-pdt');
    }

    function abrirModalVentas(idEmpresaCliente) {
        // Asignar el valor al formulario
        document.getElementById('id_empresas_clientes_ventas').value = idEmpresaCliente;

        // Abrir el modal
        toggleModal('modal-ventas');
    }

    function abrirModalCompras(idEmpresaCliente) {
        // Asignar el valor al formulario
        document.getElementById('id_empresas_clientes_compras').value = idEmpresaCliente;

        // Abrir el modal
        toggleModal('modal-compras');
    }
</script>
