<x-app-layout>
    @php
        $meses_es = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre',
        ];

        $desdeParts = explode('-', $desde ?? now()->format('Y-m'));
        $hastaParts = explode('-', $hasta ?? now()->format('Y-m'));

        $nombreDesde = ($meses_es[$desdeParts[1]] ?? '') . ' ' . $desdeParts[0];
        $nombreHasta = ($meses_es[$hastaParts[1]] ?? '') . ' ' . $hastaParts[0];
        $textoRango = $nombreDesde === $nombreHasta ? $nombreDesde : "$nombreDesde al $nombreHasta";
    @endphp

    <style>
        #tabla-abonos-facturas tbody tr[data-estado]:not([data-estado="bancarizado"]):not([data-estado="bancarizado_cliente"]) {
            cursor: pointer;
        }

        #tabla-abonos-facturas tbody tr.fila-seleccionada td {
            background-color: #bfdbfe !important;
        }
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Abonos de Facturas — {{ $textoRango }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">

            {{-- FILTROS --}}
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md mb-6 flex flex-wrap items-end gap-4">
                <form method="GET" action="{{ route('abono_facturas.index') }}" class="flex flex-wrap items-end gap-4"
                    id="form-filtros">
                    <div class="flex-1 min-w-[340px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar en toda la
                            Base de
                            Datos</label>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                            placeholder="RUC, Razon Social (proveedor o receptor), orden..."
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                    </div>
                    <div class="flex flex-col">
                        <label for="desde"
                            class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Desde</label>
                        <input type="month" name="desde" id="desde"
                            value="{{ $desde ?? request('desde', now()->format('Y-m')) }}"
                            class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm w-40 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex flex-col">
                        <label for="hasta"
                            class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Hasta</label>
                        <input type="month" name="hasta" id="hasta"
                            value="{{ $hasta ?? request('hasta', now()->format('Y-m')) }}"
                            class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm w-40 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                        <select name="estado"
                            class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm w-30">
                            <option value="">Todos</option>
                            @foreach (['Pendiente', 'Bancarizado', 'B.Cliente'] as $e)
                                <option value="{{ $e }}" {{ request('estado') == $e ? 'selected' : '' }}>
                                    {{ $e }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="tipo" class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Tipo
                            Factura</label>
                        <select name="tipo" id="tipo"
                            class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm w-42 focus:ring-blue-500 focus:border-blue-500">
                            {{-- <option value="todos" {{ $tipo == 'todos' ? 'selected' : '' }}>Todos los tipos</option> --}}
                            {{-- ! NO SE BANCARIZA <option value="3" {{ $tipo == '3' ? 'selected' : '' }}>Factura R2→R1</option> --}}
                            @if (Auth::id() != 16)
                                <option value="1" {{ $tipo === '1' ? 'selected' : '' }}>Factura Clientes</option>
                            @endif
                            @if (Auth::id() != 22)
                                <option value="2" {{ $tipo === '2' ? 'selected' : '' }}>Factura R1→A1</option>
                            @endif
                        </select>
                    </div>
                    {{-- <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition h-[42px]">
                        <i class="bi bi-search"></i> Filtrar
                    </button> --}}

                    <a href="{{ route('abono_facturas.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-md transition h-[42px] leading-[26px]">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </form>

                <button type="button" id="btn-abrir-exportar"
                    class="flex items-center gap-2 bg-green-800 hover:bg-green-900 text-white font-semibold px-4 py-2 rounded-md transition">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Excel
                </button>

                <button type="button" id="btn-abrir-cuentas"
                    class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-md transition">
                    <i class="bi bi-bank"></i> Cuentas Bancarias
                </button>
            </div>

            {{-- TABLA --}}
            <div id="table-container"
                class="bg-white shadow-md rounded-lg overflow-x-auto relative transition-opacity duration-300">
                <table class="min-w-[1700px] table-auto border" id="tabla-abonos-facturas">
                    <thead class="bg-gray-100 text-sm text-gray-700">
                        <tr>
                            <th class="px-3 py-2 border-b text-center w-10">ID</th>
                            <th class="px-3 py-2 border-b text-left">Contacto</th>
                            <th class="px-3 py-2 border-b text-left">RUC Proveedor</th>
                            <th class="px-3 py-2 border-b text-left">Razón Social Proveedor</th>
                            <th class="px-3 py-2 border-b text-left">Nro Factura</th>
                            <th class="px-3 py-2 border-b text-left">RUC Receptor</th>
                            <th class="px-3 py-2 border-b text-left">Razón Social Receptor</th>
                            <th class="px-3 py-2 border-b text-right">Factura Total</th>
                            <th class="px-3 py-2 border-b text-center">Fecha Factura</th>
                            <th class="px-3 py-2 border-b text-center">Fecha Pago</th>
                            <th class="px-3 py-2 border-b text-center">Días para pagar</th>
                            <th class="px-3 py-2 border-b text-right">Total Abonado</th>
                            <th class="px-3 py-2 border-b text-right">Pendiente</th>
                            <th class="px-3 py-2 border-b text-center">Estado Abono</th>
                            <th class="px-3 py-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($facturas as $factura)
                            @php
                                $proveedor = $empresas[$factura->ruc_emisor] ?? null;
                                $razonReceptor =
                                    $factura->empresa->razon_social ?? ($factura->cliente->razon_social ?? '—');
                                $contactoReceptor = $factura->empresa->contacto ?? ($factura->cliente->contacto ?? '—');
                                $simbolo = $factura->tipo_moneda === 'USD' ? '$' : 'S/';
                                $esPendiente = $factura->estado_abono !== 'bancarizado';

                                if ($factura->tipo_moneda === 'USD') {
                                    $montoMostrado =
                                        $factura->precio_dolar > 0
                                            ? $factura->total_neto / $factura->precio_dolar
                                            : $factura->total_neto;
                                } else {
                                    $montoMostrado = $factura->total_neto;
                                }

                                $totalAbonado = $factura->abonos->sum(function ($abono) use ($factura) {
                                    if ($abono->moneda === $factura->tipo_moneda) {
                                        return $abono->monto;
                                    }
                                    $tc =
                                        $abono->precio_dolar > 0
                                            ? $abono->precio_dolar
                                            : ($factura->precio_dolar > 0
                                                ? $factura->precio_dolar
                                                : 1);
                                    if ($factura->tipo_moneda === 'USD' && $abono->moneda === 'PEN') {
                                        return $abono->monto / $tc;
                                    } elseif ($factura->tipo_moneda === 'PEN' && $abono->moneda === 'USD') {
                                        return $abono->monto * $tc;
                                    }
                                    return $abono->monto;
                                });
                                $montoPendiente = max(0, $montoMostrado - $totalAbonado);

                                // ¿Tiene abonos en grupo?
                                $grupoAbono = $factura->abonos
                                    ->whereNotNull('id_grupo_abono')
                                    ->sortByDesc('created_at')
                                    ->first();
                                $tieneGrupo = $grupoAbono !== null;

                                // Precio Dolar Prioritario: Último abono con precio_dolar > 0
                                $ultimoAbonoConDolar = $factura->abonos
                                    ->where('precio_dolar', '>', 0)
                                    ->sortByDesc('fecha')
                                    ->first();
                                $precioDolarSugerido = $ultimoAbonoConDolar ? $ultimoAbonoConDolar->precio_dolar : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 text-sm" data-id="{{ $factura->id_factura }}"
                                data-simbolo="{{ $simbolo }}" data-moneda="{{ $factura->tipo_moneda }}"
                                data-total="{{ round($montoMostrado, 2) }}"
                                data-pendiente-row="{{ round($montoPendiente, 2) }}"
                                data-estado="{{ $factura->estado_abono }}"
                                data-precio-dolar="{{ $precioDolarSugerido }}">

                                {{-- CHECKBOX --}}
                                <td class="px-3 py-2 border-b text-center text-gray-400 text-xs select-none">
                                    {{ $factura->id_factura }}
                                </td>
                                <td class="px-3 py-2 border-b font-medium text-gray-700">{{ $contactoReceptor }}</td>

                                <td class="px-3 py-2 border-b">{{ $factura->ruc_emisor }}</td>
                                <td class="px-3 py-2 border-b font-medium">{{ $proveedor->razon_social ?? '—' }}</td>

                                {{-- NRO FACTURA + etiqueta de grupo --}}
                                <td class="px-3 py-2 border-b">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span>{{ $factura->nro_factura ?? 'Sin registro' }}</span>
                                        @if ($tieneGrupo)
                                            <button type="button"
                                                class="btn-ver-grupo inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-700 hover:bg-purple-200 transition cursor-pointer border border-purple-200"
                                                data-id="{{ $factura->id_factura }}"
                                                title="Ver facturas del mismo lote de abono masivo">
                                                <i class="bi bi-layers-fill"></i> Grupo
                                            </button>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-3 py-2 border-b">{{ $factura->ruc_receptor }}</td>
                                <td class="px-3 py-2 border-b font-medium">{{ $razonReceptor }}</td>
                                <td
                                    class="px-3 py-2 border-b text-right font-semibold whitespace-nowrap {{ $factura->monto_retencion > 0 ? 'text-blue-600' : '' }}">
                                    {{ $simbolo }} {{ number_format($montoMostrado, 2) }}
                                    @if ($factura->monto_retencion > 0)
                                        <span class="ml-1 text-xs font-normal text-blue-400" title="Tiene retención"><i
                                                class="bi bi-info-circle-fill"></i></span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 border-b text-center">
                                    {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}
                                </td>
                                <td class="px-3 py-2 border-b text-center">
                                    {{ $factura->fecha_pago ? \Carbon\Carbon::parse($factura->fecha_pago)->format('d/m/Y') : '—' }}
                                </td>
                                @php
                                    $mostrarDias = true;
                                    if (
                                        $factura->estado_abono === 'bancarizado' ||
                                        $factura->estado_abono === 'bancarizado_cliente' ||
                                        $montoPendiente <= 0
                                    ) {
                                        $mostrarDias = false;
                                    }

                                    $htmlDias = '<span class="text-gray-400">—</span>';
                                    if ($factura->fecha_pago) {
                                        $fechaPagoObj = \Carbon\Carbon::parse($factura->fecha_pago)->startOfDay();
                                        $hoy = now()->startOfDay();
                                        $diasRestantes = $hoy->diffInDays($fechaPagoObj, false);

                                        if ($diasRestantes <= 10) {
                                            $colorDias = 'text-red-700 bg-red-100';
                                        } elseif ($diasRestantes <= 20) {
                                            $colorDias = 'text-orange-700 bg-orange-100';
                                        } else {
                                            $colorDias = 'text-green-700 bg-green-100';
                                        }
                                        $textoDias = $diasRestantes . ' ' . (abs($diasRestantes) == 1 ? 'día' : 'días');
                                        $htmlDias =
                                            '<span class="inline-block px-2 py-1 rounded font-semibold text-xs ' .
                                            $colorDias .
                                            '">' .
                                            $textoDias .
                                            '</span>';
                                    }
                                @endphp
                                <td class="px-3 py-2 border-b text-center">
                                    <div class="dias-pagar-{{ $factura->id_factura }}"
                                        data-original-html="{{ e($htmlDias) }}">
                                        {!! $mostrarDias ? $htmlDias : '<span class="text-gray-400">—</span>' !!}
                                    </div>
                                </td>
                                <td class="px-3 py-2 border-b text-right whitespace-nowrap">
                                    <span
                                        class="total-abonado-{{ $factura->id_factura }} font-semibold text-green-700">
                                        {{ $simbolo }} {{ number_format($totalAbonado, 2) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 border-b text-right whitespace-nowrap">
                                    <span
                                        class="monto-pendiente-{{ $factura->id_factura }} font-semibold {{ $montoPendiente > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                        {{ $simbolo }} {{ number_format($montoPendiente, 2) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 border-b text-center">
                                    <span class="badge-estado-abono-{{ $factura->id_factura }}">
                                        @if ($factura->estado_abono === 'bancarizado')
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700 cursor-pointer hover:bg-green-200 transition"
                                                onclick="toggleBancarizado({{ $factura->id_factura }})">
                                                <i class="bi bi-check-circle-fill"></i> Bancarizado
                                            </span>
                                        @elseif($factura->estado_abono === 'bancarizado_cliente')
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 cursor-pointer hover:bg-blue-200 transition"
                                                onclick="toggleBancarizado({{ $factura->id_factura }})">
                                                <i class="bi bi-person-fill-check"></i> B. Cliente
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">
                                                <i class="bi bi-hourglass-split"></i> Pendiente
                                            </span>
                                        @endif
                                    </span>
                                </td>

                                {{-- ACCIONES --}}
                                <td class="px-3 py-2 border-b text-center">
                                    <div class="flex justify-center gap-2 flex-wrap items-center">

                                        {{-- Btn Abonar / bloqueado --}}
                                        @if ($factura->estado_abono === 'bancarizado' || $factura->estado_abono === 'bancarizado_cliente')
                                            <span
                                                class="btn-bloqueado-abono flex items-center py-1 tooltip cursor-not-allowed opacity-30">
                                                <x-tax-icon />
                                            </span>
                                        @else
                                            <button type="button"
                                                class="btn-crear-abono flex items-center py-1 rounded-md tooltip"
                                                style="color: #22c55e; transition: color 0.3s;"
                                                onmouseover="this.style.color='#16a34a';"
                                                onmouseout="this.style.color='#22c55e';"
                                                data-id="{{ $factura->id_factura }}"
                                                data-nro="{{ $factura->nro_factura ?? 'Sin nro.' }}"
                                                data-moneda="{{ $factura->tipo_moneda }}"
                                                data-pendiente="{{ round($montoPendiente, 2) }}"
                                                data-razon="{{ $razonReceptor }}"
                                                data-proveedor="{{ $proveedor->razon_social ?? '—' }}"
                                                data-precio-dolar="{{ $precioDolarSugerido }}"
                                                data-fecha-emision="{{ \Carbon\Carbon::parse($factura->fecha_emision)->format('Y-m-d') }}">
                                                <span class="tooltip-text">Abonar</span>
                                                <x-tax-icon />
                                            </button>
                                        @endif

                                        {{-- Btn Ver historial de abonos --}}
                                        <button type="button"
                                            class="btn-ver-abonos flex items-center py-1 rounded-md tooltip"
                                            style="color: #3b82f6; transition: color 0.3s;"
                                            onmouseover="this.style.color='#2563eb';"
                                            onmouseout="this.style.color='#3b82f6';"
                                            data-id="{{ $factura->id_factura }}"
                                            data-nro="{{ $factura->nro_factura ?? 'Sin nro.' }}">
                                            <span class="tooltip-text">Ver abonos</span>
                                            <x-details-icon />
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 text-black text-sm font-semibold sticky bottom-0">
                        <tr class="border-t border-gray-300">
                            <td colspan="7" class="px-3 py-2 text-right">Total Soles (S/):</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap" id="tfoot-factura-total-pen">S/ 0.00
                            </td>
                            <td class="px-4 py-2" colspan="3"></td>
                            <td class="px-4 py-2 text-right whitespace-nowrap text-green-600"
                                id="tfoot-total-abonado-pen">S/ 0.00</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap text-red-600" id="tfoot-pendiente-pen">
                                S/ 0.00</td>
                            <td colspan="2" class="px-3 py-2"></td>
                        </tr>
                        <tr class="border-t border-gray-200">
                            <td colspan="7" class="px-3 py-2 text-right">Total Dólares ($):</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap" id="tfoot-factura-total-usd">$ 0.00
                            </td>
                            <td class="px-4 py-2" colspan="3"></td>
                            <td class="px-4 py-2 text-right whitespace-nowrap text-green-600"
                                id="tfoot-total-abonado-usd">$ 0.00</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap text-red-600" id="tfoot-pendiente-usd">$
                                0.00</td>
                            <td colspan="2" class="px-3 py-2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- BARRA FLOTANTE --}}
    <div id="barra-seleccion"
        class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-4
               bg-gray-900 text-white px-6 py-3 rounded-2xl shadow-2xl border border-gray-700 transition-all duration-300">
        <div class="flex items-center gap-2 text-sm">
            <i class="bi bi-check2-square text-blue-400 text-lg"></i>
            <span id="barra-contador" class="font-bold text-blue-300"></span>
            <span class="text-gray-400">factura(s) seleccionada(s)</span>
            <span id="barra-moneda-aviso" class="hidden ml-2 text-xs text-yellow-400 font-semibold">
                <i class="bi bi-exclamation-triangle-fill"></i> Monedas mixtas
            </span>
        </div>
        <div class="w-px h-5 bg-gray-600"></div>
        <button type="button" id="btn-abono-masivo"
            class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white text-sm font-semibold px-4 py-1.5 rounded-lg transition">
            <i class="bi bi-cash-stack"></i> Abonar seleccionadas
        </button>
        <button type="button" id="btn-limpiar-seleccion"
            class="flex items-center gap-2 text-gray-400 hover:text-white text-sm px-2 py-1.5 rounded-lg transition">
            <i class="bi bi-x-lg"></i> Limpiar
        </button>
    </div>
</x-app-layout>

{{-- ===================================================================
     MODAL: CREAR ABONO INDIVIDUAL
=================================================================== --}}
<x-modal-show id="modal-crear-abono" title="Registrar Abono" size="default">
    <div class="space-y-4">
        <input type="hidden" id="crear-id-factura">
        <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 text-sm space-y-1">
            <div class="flex justify-between">
                <span class="text-gray-500">Factura:</span>
                <span id="modal-crear-nro" class="font-semibold text-blue-700"></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Receptor:</span>
                <span id="modal-crear-razon" class="font-medium text-gray-800"></span>
            </div>
            <div class="flex justify-between border-t border-blue-100 pt-1 mt-1">
                <span class="text-gray-500">Monto pendiente:</span>
                <button type="button" id="modal-crear-pendiente"
                    onclick="document.getElementById('crear-monto').value=document.getElementById('crear-pendiente-raw').value;document.getElementById('crear-monto').focus();"
                    class="font-bold text-red-600 hover:text-red-500 hover:underline cursor-pointer transition"
                    title="Click para llenar el monto automáticamente"></button>
                <input type="hidden" id="crear-pendiente-raw">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">Moneda <span
                        class="text-red-500">*</span></label>
                <select id="crear-moneda"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="PEN">PEN (S/)</option>
                    <option value="USD">USD ($)</option>
                </select>
            </div>
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">Monto <span
                        class="text-red-500">*</span></label>
                <input type="number" id="crear-monto" step="0.01" min="0.01" placeholder="0.00"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div id="crear-precio-dolar-wrap" class="hidden">
            <div class="flex items-center gap-2 p-2.5 bg-blue-50 border border-blue-200 rounded-lg">
                <input type="checkbox" id="crear-usar-precio-dolar" class="rounded cursor-pointer accent-blue-600">
                <label for="crear-usar-precio-dolar"
                    class="text-sm font-semibold text-gray-700 cursor-pointer flex-1">
                    Guardar Tipo Cambio
                </label>
                <input type="number" id="crear-precio-dolar-input" step="0.001"
                    class="w-24 text-sm font-bold font-mono bg-white border border-blue-300 px-2 py-1 rounded text-blue-700 shadow-sm focus:ring-blue-500 focus:border-blue-500 hidden"
                    placeholder="0.000">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">Fecha <span
                        class="text-red-500">*</span></label>
                <input type="date" id="crear-fecha"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">Banco</label>
                <select id="crear-banco"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— Seleccionar —</option>
                    <option>BCP</option>
                    <option>BBVA</option>
                    <option>INTERBANK</option>
                    <option>CAJA TRUJILLO</option>
                    <option>SCOTIABANK</option>
                </select>
            </div>
        </div>
        <div class="flex flex-col">
            <label class="text-sm font-semibold text-gray-700 mb-1">Nro Operación</label>
            <input type="text" id="crear-nro-operacion" placeholder="Nro de operación bancaria"
                class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div id="crear-error" class="hidden text-sm text-red-600 bg-red-50 px-3 py-2 rounded-md"></div>
        <div class="flex justify-end gap-3 pt-2 border-t">
            <button type="button" onclick="toggleModal('modal-crear-abono')"
                class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">Cancelar</button>
            <button type="button" id="btn-guardar-abono"
                class="px-5 py-2 text-sm font-semibold text-white bg-green-500 hover:bg-green-600 rounded-md transition flex items-center gap-2">
                <i class="bi bi-check-lg"></i> Guardar Abono
            </button>
        </div>
    </div>
</x-modal-show>

{{-- ===================================================================
     MODAL: VER HISTORIAL DE ABONOS
=================================================================== --}}
<x-modal-show id="modal-ver-abonos" title="Historial de Abonos" size="large">
    <div class="flex items-start gap-4 mb-4 -mt-1">
        <div>
            <div class="flex items-center gap-2">
                <i class="bi bi-list-ul text-blue-500 text-lg"></i>
                <h3 class="text-base font-bold text-gray-800">Abonos — <span id="modal-ver-nro"
                        class="text-blue-700"></span></h3>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-6">
                Total factura: <span id="ver-total-factura" class="font-semibold text-gray-700"></span>
                &nbsp;|&nbsp; Abonado: <span id="ver-total-abonado" class="font-semibold text-green-600"></span>
                &nbsp;|&nbsp; Pendiente: <span id="ver-monto-pendiente" class="font-semibold text-red-600"></span>
            </p>
        </div>
    </div>
    <div class="overflow-y-auto max-h-[50vh]">
        <div id="ver-loading" class="text-center py-10 text-gray-500">
            <i class="bi bi-arrow-repeat animate-spin text-2xl"></i> Cargando...
        </div>
        <table id="tabla-detalle-abonos" class="min-w-full text-sm hidden">
            <thead class="bg-gray-100 text-gray-700 sticky top-0">
                <tr>
                    <th class="px-3 py-2 text-left border-b">Fecha</th>
                    <th class="px-3 py-2 text-left border-b">Banco</th>
                    <th class="px-3 py-2 text-left border-b">Nro Operación</th>
                    <th class="px-3 py-2 text-right border-b">Monto</th>
                    <th class="px-3 py-2 text-center border-b">Moneda</th>
                    <th class="px-3 py-2 text-center border-b">Precio Dólar</th>
                    <th class="px-3 py-2 text-left border-b">Registrado por</th>
                    <th class="px-3 py-2 text-center border-b">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbody-abonos"></tbody>
            <tfoot class="bg-gray-50 font-semibold text-sm">
                <tr>
                    <td colspan="3" class="px-3 py-2 text-right border-t">Total abonado:</td>
                    <td class="px-3 py-2 text-right border-t text-green-700" id="tfoot-total-abonado"></td>
                    <td colspan="4" class="border-t"></td>
                </tr>
            </tfoot>
        </table>
        <div id="ver-sin-abonos" class="hidden text-center py-10 text-gray-400 text-sm">
            <i class="bi bi-inbox text-3xl block mb-2"></i> Esta factura no tiene abonos registrados.
        </div>
    </div>
    <div class="flex justify-end pt-3 mt-2 border-t">
        <button type="button" onclick="toggleModal('modal-ver-abonos')"
            class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">Cerrar</button>
    </div>
</x-modal-show>

{{-- ===================================================================
     MODAL: EDITAR ABONO
=================================================================== --}}
<x-modal-show id="modal-editar-abono" title="" size="default">
    <div class="flex items-center gap-2 mb-4 -mt-1">
        <i class="bi bi-pencil-square text-yellow-500 text-lg"></i>
        <h3 class="text-base font-bold text-gray-800">Editar Abono</h3>
    </div>
    <div class="space-y-4">
        <input type="hidden" id="editar-id-abono">
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">Moneda <span
                        class="text-red-500">*</span></label>
                <select id="editar-moneda"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="PEN">PEN (S/)</option>
                    <option value="USD">USD ($)</option>
                </select>
            </div>
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">Monto <span
                        class="text-red-500">*</span></label>
                <input type="number" id="editar-monto" step="0.01" min="0.01"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">Fecha <span
                        class="text-red-500">*</span></label>
                <input type="date" id="editar-fecha"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">Banco</label>
                <select id="editar-banco"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— Seleccionar —</option>
                    <option>BCP</option>
                    <option>BBVA</option>
                    <option>INTERBANK</option>
                    <option>CAJA TRUJILLO</option>
                    <option>SCOTIABANK</option>
                </select>
            </div>
        </div>
        <div class="flex flex-col">
            <label class="text-sm font-semibold text-gray-700 mb-1">Nro Operación</label>
            <input type="text" id="editar-nro-operacion"
                class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div id="editar-error" class="hidden text-sm text-red-600 bg-red-50 px-3 py-2 rounded-md"></div>
        <div class="flex justify-end gap-3 pt-2 border-t">
            <button type="button" onclick="toggleModal('modal-editar-abono')"
                class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">Cancelar</button>
            <button type="button" id="btn-actualizar-abono"
                class="px-5 py-2 text-sm font-semibold text-white bg-yellow-500 hover:bg-yellow-600 rounded-md transition flex items-center gap-2">
                <i class="bi bi-check-lg"></i> Actualizar
            </button>
        </div>
    </div>
</x-modal-show>

{{-- ===================================================================
     MODAL: ABONO MASIVO
=================================================================== --}}
<x-modal-show id="modal-abono-masivo" title="Abono Masivo de Facturas" size="large">
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">Moneda <span
                        class="text-red-500">*</span></label>
                <select id="masivo-moneda"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="PEN">PEN (S/)</option>
                    <option value="USD">USD ($)</option>
                </select>
            </div>
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">Fecha <span
                        class="text-red-500">*</span></label>
                <input type="date" id="masivo-fecha"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">Banco</label>
                <select id="masivo-banco"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— Seleccionar —</option>
                    <option>BCP</option>
                    <option>BBVA</option>
                    <option>INTERBANK</option>
                    <option>CAJA TRUJILLO</option>
                    <option>SCOTIABANK</option>
                </select>
            </div>
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">Nro Operación</label>
                <input type="text" id="masivo-nro-operacion" placeholder="Nro de operación bancaria"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-semibold text-gray-700">
                    Facturas seleccionadas
                    <span id="masivo-paginas-aviso" class="ml-2 text-xs font-normal text-purple-600">
                        <i class="bi bi-info-circle"></i> Incluye selecciones de todas las páginas
                    </span>
                </label>
                <div class="flex gap-3">
                    <button type="button" id="btn-completar-pendientes"
                        class="text-xs text-blue-600 hover:text-blue-800 underline transition"
                        title="Rellena cada monto con su pendiente exacto">
                        <i class="bi bi-lightning-fill"></i> Completar pendientes
                    </button>
                    <button type="button" id="btn-limpiar-montos"
                        class="text-xs text-gray-500 hover:text-gray-700 underline transition">Limpiar montos</button>
                </div>
            </div>
            <div class="overflow-y-auto max-h-64 border rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-600 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left border-b">Nro Factura</th>
                            <th class="px-3 py-2 text-left border-b">Receptor</th>
                            <th class="px-3 py-2 text-center border-b">Moneda</th>
                            <th class="px-3 py-2 text-center border-b">Precio Dólar</th>
                            <th class="px-3 py-2 text-right border-b">Pendiente</th>
                            <th class="px-3 py-2 text-right border-b w-36">Monto a Abonar <span
                                    class="text-red-500">*</span></th>
                            <th class="px-3 py-2 text-center border-b w-8"></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-masivo"></tbody>
                    <tfoot class="bg-gray-50 font-semibold text-sm">
                        <tr>
                            <td colspan="4" class="px-3 py-2 text-right border-t text-gray-600">Total:</td>
                            <td class="px-3 py-2 text-right border-t text-gray-500" id="masivo-total-pendiente"></td>
                            <td class="px-3 py-2 text-right border-t text-green-700 font-bold"
                                id="masivo-total-monto">—</td>
                            <td class="border-t"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div id="masivo-error" class="hidden text-sm text-red-600 bg-red-50 px-3 py-2 rounded-md"></div>
        <div class="flex justify-end gap-3 pt-2 border-t">
            <button type="button" onclick="toggleModal('modal-abono-masivo')"
                class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">Cancelar</button>
            <button type="button" id="btn-guardar-masivo"
                class="px-5 py-2 text-sm font-semibold text-white bg-green-500 hover:bg-green-600 rounded-md transition flex items-center gap-2">
                <i class="bi bi-check-lg"></i> Guardar Abonos
            </button>
        </div>
    </div>
</x-modal-show>

{{-- ===================================================================
     MODAL: VER GRUPO DE FACTURAS
=================================================================== --}}
<x-modal-show id="modal-ver-grupo" title="Facturas del mismo lote" size="large">
    <div class="flex items-center gap-2 mb-4 -mt-1">
        <i class="bi bi-layers-fill text-purple-500 text-lg"></i>
        <div>
            <h3 class="text-base font-bold text-gray-800">Lote de abono masivo</h3>
            <p class="text-xs text-gray-500 mt-0.5">
                Factura origen: <span id="grupo-nro-origen" class="font-semibold text-purple-700"></span>
                &nbsp;·&nbsp; ID lote: <span id="grupo-id-label" class="font-mono text-gray-400 text-xs"></span>
            </p>
        </div>
    </div>

    <div id="grupo-loading" class="text-center py-10 text-gray-500">
        <i class="bi bi-arrow-repeat animate-spin text-2xl"></i> Cargando...
    </div>

    <div id="grupo-contenido" class="hidden">
        <div class="overflow-y-auto max-h-[55vh]">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-700 sticky top-0">
                    <tr>
                        <th class="px-3 py-2 text-left border-b">Nro Factura</th>
                        <th class="px-3 py-2 text-left border-b">RUC Receptor</th>
                        <th class="px-3 py-2 text-left border-b">Razón Social Receptor</th>
                        <th class="px-3 py-2 text-center border-b">Fecha</th>
                        <th class="px-3 py-2 text-center border-b">Moneda</th>
                        <th class="px-3 py-2 text-right border-b">Total Factura</th>
                        <th class="px-3 py-2 text-right border-b">Abonado en este lote</th>
                        <th class="px-3 py-2 text-right border-b">Total Abonado</th>
                        <th class="px-3 py-2 text-right border-b">Pendiente</th>
                        <th class="px-3 py-2 text-center border-b">Estado</th>
                    </tr>
                </thead>
                <tbody id="tbody-grupo"></tbody>
            </table>
        </div>

        {{-- Resumen del lote --}}
        <div id="grupo-resumen"
            class="mt-3 bg-purple-50 border border-purple-100 rounded-lg px-4 py-3 text-sm grid grid-cols-3 gap-4 text-center">
        </div>
    </div>

    <div id="grupo-sin-datos" class="hidden text-center py-10 text-gray-400 text-sm">
        <i class="bi bi-inbox text-3xl block mb-2"></i> No se encontró información del grupo.
    </div>

    <div class="flex justify-end pt-3 mt-2 border-t">
        <button type="button" onclick="toggleModal('modal-ver-grupo')"
            class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">Cerrar</button>
    </div>
</x-modal-show>

<x-modal-show id="modal-exportar" title="Exportar Abonos a Excel" size="default">
    <div class="space-y-4">

        {{-- <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 rounded-lg px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
            <i class="bi bi-info-circle-fill mr-1"></i>
            Genera un Excel con todas las facturas del receptor seleccionado en el período indicado,
            incluyendo el detalle de cada abono (fecha, monto, banco, nro. operación).
        </div> --}}

        {{-- Rol de la Entidad --}}
        <div class="flex flex-col">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Filtrar por rol <span
                    class="text-red-500">*</span></label>
            <select id="export-rol"
                class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm text-sm">
                <option value="emisor">Proveedor</option>
                <option value="receptor" selected>Receptor</option>
            </select>
        </div>

        {{-- Tipo de entidad --}}
        <div class="flex flex-col">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Tipo de entidad <span
                    class="text-red-500">*</span></label>
            <div class="flex gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="export-tipo" value="empresa" id="export-tipo-empresa" checked
                        class="text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Empresa Cliente</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="export-tipo" value="cliente" id="export-tipo-cliente"
                        class="text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Cliente Auditoría</span>
                </label>
            </div>
        </div>

        {{-- Selector de entidad --}}
        <div class="flex flex-col">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Entidad <span
                    class="text-red-500">*</span></label>
            <select id="export-ruc" class="select-abonofactura">
                <option value="">— Seleccionar entidad —</option>
            </select>
        </div>

        {{-- Rango de Meses --}}
        <div id="filtros-rango-pdf"
            class="space-y-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Rango de Meses a exportar <span
                    class="text-red-500">*</span></p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label :value="__('Desde')" />
                    <input type="month" id="export-fecha-desde"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <x-input-label :value="__('Hasta')" />
                    <input type="month" id="export-fecha-hasta"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm text-sm">
                </div>
            </div>
            <p class="text-xs text-blue-600 dark:text-blue-400 italic">
                <i class="bi bi-info-circle"></i> Selecciona un rango para generar el reporte consolidado.
            </p>
        </div>
    </div>

    {{-- Filtro por Estado --}}
    <div class="flex flex-col">
        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Estado de la factura <span
                class="text-red-500">*</span></label>
        <select id="export-estado"
            class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
            <option value="todas">Todas</option>
            <option value="ambas">Ambas Bancas</option>
            <option value="bancarizado">Bancarizado</option>
            <option value="bancarizado_cliente">Ban. Cliente</option>
            <option value="pendiente">Pendiente</option>
        </select>
    </div>

    <div id="export-error"
        class="hidden text-sm text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400 px-3 py-2 rounded-md"></div>

    <div class="flex justify-end gap-3 pt-2 border-t dark:border-gray-600">
        <button type="button" onclick="toggleModal('modal-exportar')"
            class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition">
            Cancelar
        </button>
        <button type="button" id="btn-descargar-excel"
            class="px-5 py-2 text-sm font-semibold text-white bg-green-800 hover:bg-green-900 dark:bg-green-700 dark:hover:bg-green-800 rounded-md transition flex items-center gap-2">
            <i class="bi bi-file-earmark-spreadsheet"></i> Descargar Excel
        </button>
    </div>
    </div>
</x-modal-show>

{{-- ===================================================================
     MODAL: CONSULTAR CUENTAS BANCARIAS POR EMPRESA
=================================================================== --}}
<x-modal-show id="modal-cuentas-empresa" title="Cuentas Bancarias por Empresa" size="large">
    <div class="space-y-4">

        <div class="bg-indigo-50 border border-indigo-100 rounded-lg px-4 py-3 text-sm text-indigo-700">
            <i class="bi bi-info-circle-fill mr-1"></i>
            Solo se muestran empresas con al menos una cuenta bancaria activa.
        </div>

        <div class="flex flex-col">
            <label class="text-sm font-semibold text-gray-700 mb-1">
                Empresa <span class="text-red-500">*</span>
            </label>
            <select id="cuentas-select-empresa" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                <option value="">— Buscar empresa —</option>
            </select>
        </div>

        <div id="cuentas-loading" class="hidden text-center py-6 text-gray-500">
            <i class="bi bi-arrow-repeat animate-spin text-2xl"></i> Cargando cuentas...
        </div>

        <div id="cuentas-sin-datos" class="hidden text-center py-6 text-gray-400 text-sm">
            <i class="bi bi-inbox text-3xl block mb-2"></i>
            Esta empresa no tiene cuentas bancarias activas.
        </div>

        <div id="cuentas-tabla-wrap" class="hidden overflow-x-auto rounded-lg border">
            <table class="min-w-full text-sm">
                <thead class="bg-indigo-900 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Sede</th>
                        <th class="px-4 py-3 text-left">Banco</th>
                        <th class="px-4 py-3 text-left">Nro. Cuenta</th>
                        <th class="px-4 py-3 text-left">CCI</th>
                        <th class="px-4 py-3 text-center w-10"></th>
                    </tr>
                </thead>
                <tbody id="cuentas-tbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>

        <div class="flex justify-between items-center pt-2 border-t">
            <div id="cuentas-footer-copy"
                class="hidden text-xs text-indigo-600 hover:text-indigo-800 font-medium cursor-pointer transition flex items-center gap-1"
                title="Click para copiar Razón Social (RUC)">
                <i class="bi bi-copy"></i> <span id="cuentas-footer-text"></span>
            </div>
            <button type="button" onclick="toggleModal('modal-cuentas-empresa')"
                class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                Cerrar
            </button>
        </div>
    </div>
</x-modal-show>

<script>
    /* =====================================================================
   CONFIGURACIÓN GLOBAL
===================================================================== */
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const ROUTES = {
        store: '{{ route('abono_facturas.store') }}',
        storeMasivo: '{{ route('abono_facturas.store_masivo') }}',
        getAbonos: (id) => `/abono-facturas/factura/${id}`,
        getGrupo: (id) => `/abono-facturas/grupo/${id}`,
        update: (id) => `/abono-facturas/${id}`,
        destroy: (id) => `/abono-facturas/${id}`,
        toggleBancarizado: (id) => `{{ route('abono_facturas.toggle_bancarizado', ':id') }}`.replace(':id', id),
        exportar: '{{ route('abono_facturas.exportar') }}',
    };

    let facturaActiva = null;
    let nroFacturaActiva = '';

    const fmt = (n) => parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    const EPSILON = 0.005;
    const montoSuperaPendiente = (v, p) => v > p + EPSILON;

    // Cache para consultas SUNAT de tipo de cambio
    const cacheTipoCambio = {};

    async function getTipoCambio(fechaEmision) {
        if (!fechaEmision) return 0;
        const fechaObj = new Date(fechaEmision + 'T00:00:00');
        const month = fechaObj.getMonth() + 1;
        const year = fechaObj.getFullYear();
        const cacheKey = `${year}-${month}`;

        if (!cacheTipoCambio[cacheKey]) {
            try {
                const res = await fetch('{{ route('consultas.tipoCambio') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        month,
                        year
                    }),
                });
                const result = await res.json();
                if (result.success && result.data) {
                    cacheTipoCambio[cacheKey] = Array.isArray(result.data) ? result.data : [result.data];
                } else {
                    cacheTipoCambio[cacheKey] = [];
                }
            } catch (e) {
                console.error('Error al obtener tipo de cambio:', e);
                cacheTipoCambio[cacheKey] = [];
            }
        }

        const datos = cacheTipoCambio[cacheKey];
        const item = datos.find(d => d.date === fechaEmision) ?? datos[datos.length - 1];
        if (item && item.sell_price) {
            return parseFloat(item.sell_price);
        }
        return 0;
    }

    /* =====================================================================
       SELECCIÓN MÚLTIPLE 
    ===================================================================== */
    /* =====================================================================
   SELECCIÓN MÚLTIPLE — clic en cualquier parte de la fila
===================================================================== */
    const seleccionadas = new Map();
    const pendingUpdates = new Map();

    function actualizarBarraSeleccion() {
        const barra = document.getElementById('barra-seleccion');
        const contador = document.getElementById('barra-contador');
        const aviso = document.getElementById('barra-moneda-aviso');

        if (seleccionadas.size === 0) {
            barra.classList.add('hidden');
            return;
        }
        barra.classList.remove('hidden');
        contador.textContent = seleccionadas.size;

        const monedas = new Set([...seleccionadas.values()].map(f => f.moneda));
        const mixtas = monedas.size > 1;
        aviso.classList.toggle('hidden', !mixtas);

        const btnMasivo = document.getElementById('btn-abono-masivo');
        btnMasivo.disabled = mixtas;
        btnMasivo.classList.toggle('opacity-50', mixtas);
        btnMasivo.classList.toggle('cursor-not-allowed', mixtas);
        btnMasivo.classList.toggle('hover:bg-green-600', !mixtas);
    }

    function sincronizarFilasSeleccionadas() {
        document.querySelectorAll('#tabla-abonos-facturas tbody tr[data-id]').forEach(fila => {
            const id = fila.dataset.id;
            const estado = fila.dataset.estado;
            const esBancarizado = estado === 'bancarizado' || estado === 'bancarizado_cliente';
            const cb = fila.querySelector('.check-fila');

            if (seleccionadas.has(id) && !esBancarizado) {
                fila.classList.add('fila-seleccionada');
                if (cb) cb.checked = true;
            } else {
                fila.classList.remove('fila-seleccionada');
                if (cb) cb.checked = false;
            }
        });
    }

    // Delegado en document para sobrevivir al reemplazo de DOM por Live Filtering
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#tabla-abonos-facturas')) return;
        if (e.target.closest('button, a, input, select, .btn-ver-grupo, [onclick]')) return;

        const fila = e.target.closest('tr[data-id]');
        if (!fila) return;

        const estado = fila.dataset.estado;
        if (estado === 'bancarizado' || estado === 'bancarizado_cliente') return;

        const id = fila.dataset.id;

        if (seleccionadas.has(id)) {
            seleccionadas.delete(id);
            fila.classList.remove('fila-seleccionada');
        } else {
            const btnAbono = fila.querySelector('.btn-crear-abono');
            seleccionadas.set(id, {
                moneda: fila.dataset.moneda,
                pendiente: parseFloat(fila.dataset.pendienteRow ?? 0),
                nro: btnAbono?.dataset?.nro ?? 'Sin nro.',
                razon: fila.querySelector('td:nth-child(6)')?.textContent?.trim() ?? '—',
                precio_dolar: parseFloat(fila.dataset.precioDolar ?? 0),
                fecha_emision: btnAbono?.dataset?.fechaEmision ?? '',
            });
            fila.classList.add('fila-seleccionada');
        }

        actualizarBarraSeleccion();
    });

    document.getElementById('btn-limpiar-seleccion').addEventListener('click', () => {
        seleccionadas.clear();
        sincronizarFilasSeleccionadas();
        actualizarBarraSeleccion();
    });

    /* =====================================================================
       MODAL ABONO MASIVO
    ===================================================================== */
    document.getElementById('btn-abono-masivo').addEventListener('click', () => {
        if (seleccionadas.size === 0) return;

        const monedas = new Set([...seleccionadas.values()].map(f => f.moneda));
        document.getElementById('masivo-moneda').value = monedas.size === 1 ? [...monedas][0] : 'PEN';
        document.getElementById('masivo-fecha').value = new Date().toISOString().split('T')[0];
        document.getElementById('masivo-banco').value = '';
        document.getElementById('masivo-nro-operacion').value = '';
        document.getElementById('masivo-error').classList.add('hidden');

        renderTablaMasivo();
        toggleModal('modal-abono-masivo');
    });

    async function renderTablaMasivo() {
        const tbody = document.getElementById('tbody-masivo');
        tbody.innerHTML = [...seleccionadas.entries()].map(([id, f]) => {
            const sim = f.moneda === 'USD' ? '$' : 'S/';

            let dolarHtml = '';
            if (f.precio_dolar > 0) {
                dolarHtml =
                    `<input type="number" step="0.001" class="masivo-precio-dolar-input border border-gray-300 rounded-md px-2 py-1 text-center w-24 text-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-blue-700" data-id="${id}" value="${parseFloat(f.precio_dolar).toFixed(3)}" placeholder="0.000">`;
            } else if (f.fecha_emision) {
                dolarHtml =
                    `<input type="number" step="0.001" id="masivo-dolar-input-${id}" class="masivo-precio-dolar-input border border-gray-300 rounded-md px-2 py-1 text-center w-24 text-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-blue-700" data-id="${id}" value="" placeholder="Cargando...">`;
            } else {
                dolarHtml =
                    `<input type="number" step="0.001" class="masivo-precio-dolar-input border border-gray-300 rounded-md px-2 py-1 text-center w-24 text-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-blue-700" data-id="${id}" value="" placeholder="0.000">`;
            }

            return `
        <tr class="border-b hover:bg-gray-50" data-masivo-id="${id}">
            <td class="px-3 py-2 text-blue-700 font-medium">${f.nro}</td>
            <td class="px-3 py-2 text-gray-700 max-w-[180px] truncate" title="${f.razon}">${f.razon}</td>
            <td class="px-3 py-2 text-center">
                <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-700">${f.moneda}</span>
            </td>
            <td class="px-3 py-2 text-center" id="masivo-dolar-td-${id}">
                ${dolarHtml}
            </td>
            <td class="px-3 py-2 text-right text-red-600 font-semibold whitespace-nowrap">${sim} ${fmt(f.pendiente)}</td>
            <td class="px-3 py-2 text-right">
                <input type="number"
                    class="masivo-monto-input border border-gray-300 rounded-md px-2 py-1 text-right w-32 text-sm focus:ring-blue-500 focus:border-blue-500"
                    step="0.01" min="0.01" placeholder="0.00"
                    data-id="${id}" data-pendiente="${f.pendiente}">
            </td>
            <td class="px-3 py-2 text-center">
                <button type="button" class="btn-quitar-masivo text-gray-400 hover:text-red-500 transition" data-id="${id}" title="Quitar">
                    <i class="bi bi-x-circle text-base"></i>
                </button>
            </td>
        </tr>`;
        }).join('');

        actualizarTotalMasivo();
        bindMasivoEvents();

        // Consultar tipo de cambio para las facturas que no tienen precio dólar pero sí fecha
        for (const [id, f] of seleccionadas.entries()) {
            if (f.precio_dolar <= 0 && f.fecha_emision) {
                const tasa = await getTipoCambio(f.fecha_emision);
                const inpDolar = document.getElementById(`masivo-dolar-input-${id}`);
                if (inpDolar) {
                    if (tasa > 0) {
                        inpDolar.value = tasa.toFixed(3);
                        inpDolar.placeholder = '0.000';
                    } else {
                        inpDolar.placeholder = 'Propio...';
                    }
                }
            }
        }
    }

    function actualizarTotalMasivo() {
        let totalMonto = 0,
            totalPendiente = 0;
        seleccionadas.forEach(f => {
            totalPendiente += f.pendiente;
        });
        document.querySelectorAll('.masivo-monto-input').forEach(inp => {
            const v = parseFloat(inp.value);
            if (!isNaN(v)) totalMonto += v;
        });
        const sim = document.getElementById('masivo-moneda').value === 'USD' ? '$' : 'S/';
        document.getElementById('masivo-total-pendiente').textContent = `${sim} ${fmt(totalPendiente)}`;
        document.getElementById('masivo-total-monto').textContent = totalMonto > 0 ? `${sim} ${fmt(totalMonto)}` : '—';
    }

    function bindMasivoEvents() {
        document.querySelectorAll('.masivo-monto-input').forEach(inp => inp.addEventListener('input',
            actualizarTotalMasivo));
        document.querySelectorAll('.btn-quitar-masivo').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                seleccionadas.delete(id);
                // Desmarcar en la página actual si está visible
                const cb = document.querySelector(`.check-fila[data-id="${id}"]`);
                if (cb) cb.checked = false;
                actualizarBarraSeleccion();
                actualizarCheckAll();
                if (seleccionadas.size === 0) {
                    toggleModal('modal-abono-masivo');
                    return;
                }
                renderTablaMasivo();
            });
        });
    }

    document.getElementById('btn-completar-pendientes').addEventListener('click', () => {
        document.querySelectorAll('.masivo-monto-input').forEach(inp => {
            inp.value = parseFloat(inp.dataset.pendiente).toFixed(2);
        });
        actualizarTotalMasivo();
    });
    document.getElementById('btn-limpiar-montos').addEventListener('click', () => {
        document.querySelectorAll('.masivo-monto-input').forEach(inp => {
            inp.value = '';
        });
        actualizarTotalMasivo();
    });
    document.getElementById('masivo-moneda').addEventListener('change', actualizarTotalMasivo);

    document.getElementById('btn-guardar-masivo').addEventListener('click', async () => {
        const btn = document.getElementById('btn-guardar-masivo');
        const errDiv = document.getElementById('masivo-error');
        errDiv.classList.add('hidden');

        const inputs = document.querySelectorAll('.masivo-monto-input');
        let hayError = false;
        const facturasPayload = [];

        inputs.forEach(inp => {
            const monto = parseFloat(inp.value);
            const pendiente = parseFloat(inp.dataset.pendiente);
            if (isNaN(monto) || monto <= 0 || montoSuperaPendiente(monto, pendiente)) {
                inp.classList.add('border-red-500');
                hayError = true;
            } else {
                inp.classList.remove('border-red-500');
                const precioDolarInp = document.querySelector(
                    `.masivo-precio-dolar-input[data-id="${inp.dataset.id}"]`);
                const precioDolarVal = precioDolarInp ? parseFloat(precioDolarInp.value) || 0 : 0;
                facturasPayload.push({
                    id_factura: inp.dataset.id,
                    monto: monto.toFixed(2),
                    precio_dolar: precioDolarVal
                });
            }
        });

        if (hayError) {
            errDiv.textContent =
                'Verifica los montos: deben ser mayores a 0 y no superar el pendiente de cada factura.';
            errDiv.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Guardando...';

        try {
            const res = await fetch(ROUTES.storeMasivo, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    facturas: facturasPayload,
                    moneda: document.getElementById('masivo-moneda').value,
                    banco: document.getElementById('masivo-banco').value,
                    fecha: document.getElementById('masivo-fecha').value,
                    nro_operacion: document.getElementById('masivo-nro-operacion').value,
                }),
            });
            const data = await res.json();

            if (!res.ok) {
                errDiv.textContent = data.errors ? Object.values(data.errors).flat().join(' | ') : (data
                    .message || 'Error al guardar.');
                errDiv.classList.remove('hidden');
                return;
            }

            // Actualizar TODAS las filas procesadas (estén o no en la página actual)
            data.resultados.forEach(r => {
                actualizarFilaDOM(r.id_factura, r.total_abonado, r.monto_pendiente, r.estado_abono,
                    r.grupo_id);
            });
            recalcularTotalesTfoot($('#tabla-abonos-facturas').DataTable());

            seleccionadas.clear();
            sincronizarFilasSeleccionadas();
            actualizarBarraSeleccion();
            toggleModal('modal-abono-masivo');
            mostrarToast(`${data.procesados} abono(s) registrado(s) correctamente`, 'success');
        } catch (e) {
            errDiv.textContent = `Error: ${e.message}`;
            errDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Guardar Abonos';
        }
    });

    /* =====================================================================
       MODAL VER GRUPO
    ===================================================================== */
    // Delegado en document para sobrevivir al reemplazo de DOM por Live Filtering
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#tabla-abonos-facturas')) return;
        const btn = e.target.closest('.btn-ver-grupo');
        if (btn) abrirModalGrupo(btn.dataset.id, btn.dataset.id);
    });


    async function abrirModalGrupo(idFactura, nroFactura) {
        document.getElementById('grupo-nro-origen').textContent = nroFactura ?? '—';
        document.getElementById('grupo-id-label').textContent = '';
        document.getElementById('grupo-loading').classList.remove('hidden');
        document.getElementById('grupo-contenido').classList.add('hidden');
        document.getElementById('grupo-sin-datos').classList.add('hidden');
        toggleModal('modal-ver-grupo');

        try {
            const res = await fetch(ROUTES.getGrupo(idFactura));
            const data = await res.json();

            document.getElementById('grupo-loading').classList.add('hidden');

            if (!data.grupo_id || !data.facturas?.length) {
                document.getElementById('grupo-sin-datos').classList.remove('hidden');
                return;
            }

            document.getElementById('grupo-id-label').textContent = data.grupo_id;

            const tbody = document.getElementById('tbody-grupo');
            tbody.innerHTML = data.facturas.map(f => {
                const estadoBadge = f.estado_abono === 'bancarizado' ?
                    `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-700 cursor-pointer hover:bg-green-200 transition" onclick="toggleBancarizado(${f.id_factura})"><i class="bi bi-check-circle-fill"></i> Bancarizado</span>` :
                    (f.estado_abono === 'bancarizado_cliente' ?
                        `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 cursor-pointer hover:bg-blue-200 transition" onclick="toggleBancarizado(${f.id_factura})"><i class="bi bi-person-check-fill"></i> B. Cliente</span>` :
                        `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700"><i class="bi bi-hourglass-split"></i> Pendiente</span>`
                    );
                return `
            <tr class="hover:bg-gray-50 border-b text-sm">
                <td class="px-3 py-2 font-medium text-blue-700">${f.nro_factura}</td>
                <td class="px-3 py-2 text-gray-600">${f.ruc_receptor}</td>
                <td class="px-3 py-2 font-medium text-gray-800 max-w-[200px] truncate" title="${f.razon_receptor}">${f.razon_receptor}</td>
                <td class="px-3 py-2 text-center text-gray-600">${f.fecha_emision}</td>
                <td class="px-3 py-2 text-center">
                    <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-700">${f.tipo_moneda}</span>
                </td>
                <td class="px-3 py-2 text-right font-semibold whitespace-nowrap">${f.simbolo} ${fmt(f.monto_base)}</td>
                <td class="px-3 py-2 text-right font-semibold text-purple-700 whitespace-nowrap">${f.simbolo} ${fmt(f.monto_en_grupo)}</td>
                <td class="px-3 py-2 text-right font-semibold text-green-700 whitespace-nowrap">${f.simbolo} ${fmt(f.total_abonado)}</td>
                <td class="px-3 py-2 text-right font-semibold whitespace-nowrap ${parseFloat(f.monto_pendiente) > 0 ? 'text-red-600' : 'text-gray-400'}">
                    ${f.simbolo} ${fmt(f.monto_pendiente)}
                </td>
                <td class="px-3 py-2 text-center">${estadoBadge}</td>
            </tr>`;
            }).join('');

            // Resumen del lote
            const totalBase = data.facturas.reduce((s, f) => s + f.monto_base, 0);
            const totalGrupo = data.facturas.reduce((s, f) => s + f.monto_en_grupo, 0);
            const totalPendiente = data.facturas.reduce((s, f) => s + f.monto_pendiente, 0);
            // Intentar inferir símbolo del lote (mayoría)
            const monedas = data.facturas.map(f => f.tipo_moneda);
            const simResumen = monedas.filter(m => m === 'USD').length >= monedas.length / 2 ? '$' : 'S/';

            document.getElementById('grupo-resumen').innerHTML = `
            <div>
                <div class="text-xs text-gray-500 mb-1">Facturas en el lote</div>
                <div class="text-lg font-bold text-purple-700">${data.facturas.length}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500 mb-1">Total abonado en este lote</div>
                <div class="text-lg font-bold text-purple-700">${simResumen} ${fmt(totalGrupo)}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500 mb-1">Pendiente total (post-lote)</div>
                <div class="text-lg font-bold ${totalPendiente > 0 ? 'text-red-600' : 'text-gray-400'}">${simResumen} ${fmt(totalPendiente)}</div>
            </div>`;

            document.getElementById('grupo-contenido').classList.remove('hidden');
        } catch (e) {
            document.getElementById('grupo-loading').innerHTML =
                '<span class="text-red-500">Error al cargar el grupo.</span>';
        }
    }

    /* =====================================================================
       HELPER CENTRAL: actualiza el DOM de una fila, esté o no visible
       (DataTables puede tener la fila oculta en otra página pero en el DOM)
    ===================================================================== */
    function actualizarFilaDOM(idFactura, totalAbonado, montoPendiente, estadoAbono, grupoId) {
        const idStr = String(idFactura);

        // ── Buscar la fila. DataTables con paginación client-side puede tener
        //    las filas de otras páginas FUERA del DOM visible. Si no está accesible
        //    ahora, guardamos en pendingUpdates y se aplicará en el próximo draw.
        const fila = document.querySelector(`tr[data-id="${idStr}"]`);

        if (!fila || fila.closest('table') === null || fila.offsetParent === null && !fila.closest('tbody')) {
            // Fila no está en el DOM todavía (otra página) — guardar para después
            pendingUpdates.set(idStr, {
                totalAbonado,
                montoPendiente,
                estadoAbono,
                grupoId
            });
            // También limpiar de selección inmediatamente (aunque la fila no esté visible)
            if (estadoAbono === 'bancarizado') {
                seleccionadas.delete(idStr);
                actualizarBarraSeleccion();
            }
            return;
        }

        // ── Fila encontrada: aplicar cambios ──────────────────────────────────
        _aplicarCambiosEnFila(fila, idStr, totalAbonado, montoPendiente, estadoAbono, grupoId);

        // Si había un pendingUpdate para esta fila, limpiarlo
        pendingUpdates.delete(idStr);
    }

    /**
     * Aplica todos los cambios visuales directamente sobre el nodo <tr>.
     * Separado para poder llamarlo tanto desde actualizarFilaDOM como desde el draw.
     */
    function _aplicarCambiosEnFila(fila, idStr, totalAbonado, montoPendiente, estadoAbono, grupoId) {
        const simbolo = fila.dataset.simbolo;
        const moneda = fila.dataset.moneda;
        const pendFmt = parseFloat(montoPendiente).toFixed(2);

        // Montos
        const spAbonado = fila.querySelector(`.total-abonado-${idStr}`);
        const spPendiente = fila.querySelector(`.monto-pendiente-${idStr}`);
        const spEstado = fila.querySelector(`.badge-estado-abono-${idStr}`);

        if (spAbonado) spAbonado.textContent = `${simbolo} ${fmt(totalAbonado)}`;
        if (spPendiente) {
            spPendiente.textContent = `${simbolo} ${fmt(montoPendiente)}`;
            spPendiente.className =
                `monto-pendiente-${idStr} font-semibold ${parseFloat(montoPendiente) > 0 ? 'text-red-600' : 'text-gray-400'}`;
        }
        if (spEstado && estadoAbono) {
            if (estadoAbono === 'bancarizado') {
                spEstado.innerHTML =
                    `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700 cursor-pointer hover:bg-green-200 transition" onclick="toggleBancarizado(${idStr})"><i class="bi bi-check-circle-fill"></i> Bancarizado</span>`;
            } else if (estadoAbono === 'bancarizado_cliente') {
                spEstado.innerHTML =
                    `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 cursor-pointer hover:bg-blue-200 transition" onclick="toggleBancarizado(${idStr})"><i class="bi bi-person-check-fill"></i> B. Cliente</span>`;
            } else {
                spEstado.innerHTML =
                    `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700"><i class="bi bi-hourglass-split"></i> Pendiente</span>`;
            }
        }

        const spDias = fila.querySelector(`.dias-pagar-${idStr}`);
        if (spDias) {
            if (estadoAbono === 'bancarizado' || estadoAbono === 'bancarizado_cliente' || parseFloat(montoPendiente) <=
                0) {
                spDias.innerHTML = '<span class="text-gray-400">—</span>';
            } else {
                if (spDias.dataset.originalHtml) {
                    spDias.innerHTML = spDias.dataset.originalHtml;
                }
            }
        }

        fila.dataset.pendienteRow = pendFmt;
        $(fila).data('pendiente-row', pendFmt);

        // Badge "Grupo" en columna Nro Factura (col 4)
        if (grupoId) {
            const tdNro = fila.querySelector('td:nth-child(4)');
            if (tdNro && !tdNro.querySelector('.btn-ver-grupo')) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className =
                    'btn-ver-grupo inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-700 hover:bg-purple-200 transition cursor-pointer border border-purple-200';
                btn.dataset.id = idStr;
                btn.title = 'Ver facturas del mismo lote de abono masivo';
                btn.innerHTML = '<i class="bi bi-layers-fill"></i> Grupo';
                tdNro.querySelector('div')?.appendChild(btn);
            }
        }

        // Btn Abonar / bloqueado y checkbox
        const tdAcciones = fila.querySelector('td:last-child');
        const tdCheck = fila.querySelector('td:first-child');
        if (!tdAcciones || !estadoAbono) return;

        const btnActual = tdAcciones.querySelector('.btn-crear-abono, .btn-bloqueado-abono');
        if (!btnActual) return;

        const nroDato = btnActual.dataset?.nro ?? fila.querySelector('.btn-ver-abonos')?.dataset?.nro ?? '';
        const razon = fila.querySelector('td:nth-child(7)')?.textContent?.trim() ?? '';
        const proveedor = fila.querySelector('td:nth-child(4)')?.textContent?.trim() ?? '';

        if (estadoAbono === 'bancarizado' || estadoAbono === 'bancarizado_cliente') {
            btnActual.outerHTML = `<span class="btn-bloqueado-abono flex items-center py-1 tooltip cursor-not-allowed opacity-30">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:22px;height:22px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
        </span>`;
            if (tdCheck) tdCheck.innerHTML = `<span class="text-gray-300 text-xs">—</span>`;
            seleccionadas.delete(idStr);
            actualizarBarraSeleccion();
        } else {
            const fechaEmision = btnActual.dataset?.fechaEmision ?? fila.dataset?.fechaEmision ?? '';
            const precioDolar = btnActual.dataset?.precioDolar ?? fila.dataset?.precioDolar ?? '0';
            btnActual.outerHTML = `<button type="button"
            class="btn-crear-abono flex items-center py-1 rounded-md tooltip"
            style="color: #22c55e; transition: color 0.3s;"
            onmouseover="this.style.color='#16a34a';" onmouseout="this.style.color='#22c55e';"
            data-id="${idStr}" data-nro="${nroDato}" data-moneda="${moneda}"
            data-pendiente="${pendFmt}" data-razon="${razon}" data-proveedor="${proveedor}"
            data-fecha-emision="${fechaEmision}" data-precio-dolar="${precioDolar}">
            <span class="tooltip-text">Abonar</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:22px;height:22px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
        </button>`;
            if (tdCheck && !tdCheck.querySelector('.check-fila')) {
                tdCheck.innerHTML = `<input type="checkbox"
                class="check-fila rounded cursor-pointer"
                ${seleccionadas.has(idStr) ? 'checked' : ''}
                data-id="${idStr}" data-moneda="${moneda}"
                data-pendiente="${pendFmt}" data-nro="${nroDato}" data-razon="${razon}"
                data-proveedor="${proveedor}" data-precio-dolar="${precioDolar}">`;
            }
        }

        // Actualizar data-pendiente en elementos que ya existan
        tdAcciones.querySelector('.btn-crear-abono')?.setAttribute('data-pendiente', pendFmt);
        tdCheck?.querySelector('.check-fila')?.setAttribute('data-pendiente', pendFmt);
    }

    /* =====================================================================
       MODAL CREAR ABONO INDIVIDUAL
    ===================================================================== */
    async function abrirModalCrear(e) {
        const btn = e.currentTarget;
        facturaActiva = btn.dataset.id;
        nroFacturaActiva = btn.dataset.nro;
        const moneda = btn.dataset.moneda;
        const pendiente = btn.dataset.pendiente ?? '0';
        const razon = btn.dataset.razon ?? '—';
        const proveedor = btn.dataset.proveedor ?? '—';
        const simbolo = moneda === 'USD' ? '$' : 'S/';
        const precioDolarBD = parseFloat(btn.dataset.precioDolar ?? 0);
        const fechaEmision = btn.dataset.fechaEmision ?? null;

        document.querySelector('#modal-crear-abono h2').innerHTML =
            `Registrar Abono de <span class="font-semibold text-blue-700">${proveedor}</span>`;
        document.getElementById('modal-crear-nro').textContent = nroFacturaActiva;
        document.getElementById('modal-crear-razon').textContent = razon;
        document.getElementById('modal-crear-pendiente').textContent =
            `${simbolo} ${parseFloat(pendiente).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
        document.getElementById('crear-pendiente-raw').value = parseFloat(pendiente).toFixed(2);
        document.getElementById('crear-id-factura').value = facturaActiva;
        document.getElementById('crear-moneda').value = moneda;
        document.getElementById('crear-monto').value = '';
        document.getElementById('crear-banco').value = '';
        document.getElementById('crear-fecha').value = new Date().toISOString().split('T')[0];
        document.getElementById('crear-nro-operacion').value = '';
        document.getElementById('crear-error').classList.add('hidden');

        const wrapDolar = document.getElementById('crear-precio-dolar-wrap');
        const inputDolar = document.getElementById('crear-precio-dolar-input');
        const checkDolar = document.getElementById('crear-usar-precio-dolar');
        wrapDolar.classList.remove('hidden');
        inputDolar.value = '';
        inputDolar.classList.add('hidden');
        checkDolar.checked = false;

        // Al marcar el checkbox: consultar API SUNAT y mostrar el tipo de cambio
        checkDolar.onchange = async () => {
            if (!checkDolar.checked) {
                inputDolar.classList.add('hidden');
                return;
            }
            inputDolar.value = '';
            inputDolar.placeholder = 'Cargando...';
            inputDolar.classList.remove('hidden');

            if (fechaEmision) {
                try {
                    const fechaObj = new Date(fechaEmision + 'T00:00:00');
                    const month = fechaObj.getMonth() + 1;
                    const year = fechaObj.getFullYear();
                    const res = await fetch('{{ route('consultas.tipoCambio') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                        },
                        body: JSON.stringify({
                            month,
                            year
                        }),
                    });
                    const result = await res.json();

                    let tasaVenta = null;
                    if (result.success && result.data) {
                        const datos = Array.isArray(result.data) ? result.data : [result.data];
                        const item = datos.find(d => d.date === fechaEmision) ?? datos[datos.length - 1];
                        if (item && item.sell_price) {
                            tasaVenta = parseFloat(item.sell_price);
                        }
                    }

                    if (tasaVenta && tasaVenta > 0) {
                        inputDolar.value = tasaVenta.toFixed(3);
                    } else if (precioDolarBD > 0) {
                        inputDolar.value = precioDolarBD.toFixed(3);
                    } else {
                        inputDolar.placeholder = 'Propio...';
                    }
                } catch (err) {
                    console.error('Error al consultar tipo de cambio SUNAT:', err);
                    inputDolar.value = precioDolarBD > 0 ? precioDolarBD.toFixed(3) : '';
                    inputDolar.placeholder = 'Propio...';
                }
            } else if (precioDolarBD > 0) {
                inputDolar.value = precioDolarBD.toFixed(3);
            } else {
                inputDolar.placeholder = 'Propio...';
            }
        };

        toggleModal('modal-crear-abono');
    }

    // Delegar para que funcione con botones dinámicos también
    document.getElementById('table-container').addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-crear-abono');
        if (btn) abrirModalCrear({
            currentTarget: btn
        });
    });

    document.getElementById('btn-guardar-abono').addEventListener('click', async () => {
        const btn = document.getElementById('btn-guardar-abono');
        const errDiv = document.getElementById('crear-error');
        errDiv.classList.add('hidden');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Guardando...';

        const montoIng = parseFloat(document.getElementById('crear-monto').value);
        const montoPend = parseFloat(document.getElementById('crear-pendiente-raw').value);
        const sim = document.getElementById('crear-moneda').value === 'USD' ? '$' : 'S/';

        if (montoSuperaPendiente(montoIng, montoPend)) {
            errDiv.textContent =
                `El monto (${sim} ${fmt(montoIng)}) supera el pendiente de ${sim} ${fmt(montoPend)}.`;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Guardar Abono';
            return;
        }

        try {
            const res = await fetch(ROUTES.store, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    id_factura: document.getElementById('crear-id-factura').value,
                    monto: document.getElementById('crear-monto').value,
                    moneda: document.getElementById('crear-moneda').value,
                    banco: document.getElementById('crear-banco').value,
                    fecha: document.getElementById('crear-fecha').value,
                    nro_operacion: document.getElementById('crear-nro-operacion').value,
                    precio_dolar: document.getElementById('crear-usar-precio-dolar')
                        .checked ?
                        parseFloat(document.getElementById('crear-precio-dolar-input')
                            .value) || 0 : 0,
                }),
            });
            const data = await res.json();

            if (!res.ok) {
                const texto = await res.text();
                console.error('Respuesta servidor:', res.status, texto);
                errDiv.textContent = data.errors ? Object.values(data.errors).flat().join(' | ') : (data
                    .message || 'Error al guardar.');
                errDiv.classList.remove('hidden');
                return;
            }

            actualizarFilaDOM(facturaActiva, data.total_abonado, data.monto_pendiente, data.estado_abono,
                null);
            recalcularTotalesTfoot($('#tabla-abonos-facturas').DataTable());
            if (data.precio_dolar > 0) {
                const fila = document.querySelector(`tr[data-id="${facturaActiva}"]`);
                if (fila) {
                    fila.dataset.precioDolar = data.precio_dolar;
                    const btnAbono = fila.querySelector('.btn-crear-abono');
                    if (btnAbono) btnAbono.dataset.precioDolar = data.precio_dolar;
                    const cbFila = fila.querySelector('.check-fila');
                    if (cbFila) cbFila.dataset.precioDolar = data.precio_dolar;
                    if (seleccionadas.has(String(facturaActiva))) {
                        const sel = seleccionadas.get(String(facturaActiva));
                        sel.precio_dolar = data.precio_dolar;
                        seleccionadas.set(String(facturaActiva), sel);
                    }
                }
            }
            toggleModal('modal-crear-abono');
            mostrarToast('Abono registrado correctamente', 'success');
        } catch (e) {
            errDiv.textContent = 'Error de conexión. Intenta de nuevo.';
            errDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Guardar Abono';
        }
    });

    /* =====================================================================
       MODAL VER ABONOS
    ===================================================================== */
    async function cargarAbonos(idFactura) {
        const loading = document.getElementById('ver-loading');
        const tabla = document.getElementById('tabla-detalle-abonos');
        const sinAbonos = document.getElementById('ver-sin-abonos');
        const tbody = document.getElementById('tbody-abonos');

        loading.classList.remove('hidden');
        tabla.classList.add('hidden');
        sinAbonos.classList.add('hidden');

        try {
            const res = await fetch(ROUTES.getAbonos(idFactura));
            const data = await res.json();

            const sim = data.tipo_moneda === 'USD' ? '$' : 'S/';
            document.getElementById('ver-total-factura').textContent = `${sim} ${fmt(data.monto_facturable)}`;
            document.getElementById('ver-total-abonado').textContent = `${sim} ${fmt(data.total_abonado)}`;
            document.getElementById('ver-monto-pendiente').textContent = `${sim} ${fmt(data.monto_pendiente)}`;
            document.getElementById('tfoot-total-abonado').textContent = `${sim} ${fmt(data.total_abonado)}`;
            loading.classList.add('hidden');

            if (!data.abonos?.length) {
                sinAbonos.classList.remove('hidden');
                return;
            }

            tbody.innerHTML = data.abonos.map(a => {
                const tc = parseFloat(a.precio_dolar);
                let equivHtml = '';

                if (tc > 0) {
                    if (data.tipo_moneda === 'USD' && a.moneda === 'PEN') {
                        const equiv = (parseFloat(a.monto_raw) / tc).toFixed(2);
                        equivHtml = `<div class="text-xs text-gray-400 font-normal mt-0.5 cursor-pointer hover:text-blue-600 transition"
                        title="Click para copiar"
                        onclick="navigator.clipboard.writeText('${equiv}'); mostrarToast('Monto $ ${equiv} copiado', 'success'); event.stopPropagation();">
                        $ ${equiv} <i class="bi bi-copy ml-0.5"></i>
                    </div>`;
                    } else if (data.tipo_moneda === 'PEN' && a.moneda === 'USD') {
                        const equiv = (parseFloat(a.monto_raw) * tc).toFixed(2);
                        equivHtml = `<div class="text-xs text-gray-400 font-normal mt-0.5 cursor-pointer hover:text-blue-600 transition"
                        title="Click para copiar"
                        onclick="navigator.clipboard.writeText('${equiv}'); mostrarToast('Monto S/ ${equiv} copiado', 'success'); event.stopPropagation();">
                        S/ ${equiv} <i class="bi bi-copy ml-0.5"></i>
                    </div>`;
                    }
                }

                const precioDolarHtml = a.precio_dolar && parseFloat(a.precio_dolar) > 0 ?
                    `<span class="px-2 py-0.5 text-xs rounded bg-blue-50 text-blue-700 font-mono">S/ ${parseFloat(a.precio_dolar).toFixed(3)}</span>` :
                    `<span class="text-gray-300">—</span>`;

                return `
            <tr class="hover:bg-gray-50 border-b" id="fila-abono-${a.id_abono_factura}">
                <td class="px-3 py-2">${a.fecha}</td>
                <td class="px-3 py-2">${a.banco}</td>
                <td class="px-3 py-2">
                    ${a.nro_operacion}
                    ${a.id_grupo_abono ? `<span class="ml-1 text-xs text-purple-500" title="Abono masivo — lote ${a.id_grupo_abono}"><i class="bi bi-layers-fill"></i></span>` : ''}
                </td>
                <td class="px-3 py-2 text-right font-semibold">
                    <div>${a.moneda === 'USD' ? '$' : 'S/'} ${a.monto}</div>
                    ${equivHtml}
                </td>
                <td class="px-3 py-2 text-center">
                    <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-700">${a.moneda}</span>
                </td>
                <td class="px-3 py-2 text-center">${precioDolarHtml}</td>
                <td class="px-3 py-2 text-sm text-gray-600">
                    <div class="flex items-center gap-1"><i class="bi bi-person-fill text-gray-400"></i> ${a.usuario_nombre}</div>
                </td>
                <td class="px-3 py-2 text-center">
                    <div class="flex justify-center gap-2">
                        <button type="button" class="btn-editar-abono transition"
                            style="color:#eab308;" onmouseover="this.style.color='#ca8a04';" onmouseout="this.style.color='#eab308';"
                            data-abono='${JSON.stringify(a)}' title="Editar">
                            <i class="bi bi-pencil-square text-lg"></i>
                        </button>
                        <button type="button" class="btn-eliminar-abono transition"
                            style="color:#ef4444;" onmouseover="this.style.color='#dc2626';" onmouseout="this.style.color='#ef4444';"
                            data-id="${a.id_abono_factura}" data-factura="${idFactura}" title="Eliminar">
                            <i class="bi bi-trash text-lg"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
            }).join('');

            tabla.classList.remove('hidden');
            bindAbonoButtons();
        } catch (e) {
            loading.innerHTML = '<span class="text-red-500">Error al cargar abonos.</span>';
        }
    }

    // Delegar ver-abonos también
    document.getElementById('table-container').addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-ver-abonos');
        if (!btn) return;
        facturaActiva = btn.dataset.id;
        nroFacturaActiva = btn.dataset.nro;
        document.getElementById('modal-ver-nro').textContent = nroFacturaActiva;
        toggleModal('modal-ver-abonos');
        cargarAbonos(facturaActiva);
    });

    /* =====================================================================
       BIND EDITAR / ELIMINAR ABONOS (dinámico en modal)
    ===================================================================== */
    function bindAbonoButtons() {
        document.querySelectorAll('.btn-editar-abono').forEach(btn => {
            btn.addEventListener('click', () => {
                const a = JSON.parse(btn.dataset.abono);
                document.getElementById('editar-id-abono').value = a.id_abono_factura;
                document.getElementById('editar-monto').value = a.monto_raw;
                document.getElementById('editar-moneda').value = a.moneda;
                document.getElementById('editar-banco').value = (a.banco && a.banco !== '—') ? a.banco :
                    '';
                document.getElementById('editar-fecha').value = a.fecha_raw;
                document.getElementById('editar-nro-operacion').value = a.nro_operacion !== '—' ? a
                    .nro_operacion : '';
                document.getElementById('editar-error').classList.add('hidden');
                document.getElementById('modal-editar-abono').dataset.precioDolar = a.precio_dolar ?? 0;
                toggleModal('modal-editar-abono');
            });
        });

        document.querySelectorAll('.btn-eliminar-abono').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('¿Eliminar este abono? Esta acción no se puede deshacer.')) return;
                const id = btn.dataset.id;
                const idFactura = btn.dataset.factura;
                try {
                    const res = await fetch(ROUTES.destroy(id), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json'
                        },
                    });
                    const data = await res.json();
                    if (data.success) {
                        document.getElementById(`fila-abono-${id}`)?.remove();
                        actualizarFilaDOM(idFactura, data.total_abonado, data.monto_pendiente, data
                            .estado_abono, null);
                        recalcularTotalesTfoot($('#tabla-abonos-facturas').DataTable());

                        const sim = data.tipo_moneda === 'USD' ? '$ ' : 'S/ ';
                        document.getElementById('tfoot-total-abonado').textContent = sim + fmt(data
                            .total_abonado);
                        document.getElementById('ver-total-abonado').textContent = sim + fmt(data
                            .total_abonado);
                        document.getElementById('ver-monto-pendiente').textContent = sim + fmt(data
                            .monto_pendiente);

                        if (!document.querySelectorAll('#tbody-abonos tr').length) {
                            document.getElementById('tabla-detalle-abonos').classList.add('hidden');
                            document.getElementById('ver-sin-abonos').classList.remove('hidden');
                        }
                        mostrarToast('Abono eliminado correctamente', 'error');
                    }
                } catch (e) {
                    alert('Error al eliminar. Intenta de nuevo.');
                }
            });
        });
    }

    /* =====================================================================
       MODAL EDITAR
    ===================================================================== */
    document.getElementById('btn-actualizar-abono').addEventListener('click', async () => {
        const btn = document.getElementById('btn-actualizar-abono');
        const errDiv = document.getElementById('editar-error');
        const id = document.getElementById('editar-id-abono').value;
        errDiv.classList.add('hidden');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Actualizando...';

        try {
            const res = await fetch(ROUTES.update(id), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    monto: document.getElementById('editar-monto').value,
                    moneda: document.getElementById('editar-moneda').value,
                    banco: document.getElementById('editar-banco').value,
                    fecha: document.getElementById('editar-fecha').value,
                    nro_operacion: document.getElementById('editar-nro-operacion').value,
                    precio_dolar: parseFloat(document.getElementById('modal-editar-abono')
                        .dataset.precioDolar) || 0,
                }),
            });
            const data = await res.json();

            if (!res.ok) {
                errDiv.textContent = data.errors ? Object.values(data.errors).flat().join(' | ') : (data
                    .message || 'Error al actualizar.');
                errDiv.classList.remove('hidden');
                return;
            }

            toggleModal('modal-editar-abono');
            actualizarFilaDOM(facturaActiva, data.total_abonado, data.monto_pendiente, data.estado_abono,
                null);
            recalcularTotalesTfoot($('#tabla-abonos-facturas').DataTable());
            cargarAbonos(facturaActiva);
            mostrarToast('Abono actualizado correctamente', 'edit');
        } catch (e) {
            errDiv.textContent = 'Error de conexión. Intenta de nuevo.';
            errDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Actualizar';
        }
    });

    /* =====================================================================
       TOAST
    ===================================================================== */
    function mostrarToast(mensaje, tipo = 'success') {
        if (typeof showAjaxToast === 'function') {
            showAjaxToast(mensaje, tipo);
        }
    }

    async function toggleBancarizado(idFactura) {
        if (!confirm('¿Estás seguro de realizar el cambio?')) {
            return;
        }
        try {
            const res = await fetch(ROUTES.toggleBancarizado(idFactura), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                }
            });
            const data = await res.json();
            if (data.success) {
                // Si el modal de grupo está abierto, recargar su contenido
                const modalGrupo = document.getElementById('modal-ver-grupo');
                if (modalGrupo && !modalGrupo.classList.contains('hidden')) {
                    const nro = document.getElementById('grupo-nro-origen').textContent;
                    abrirModalGrupo(idFactura, nro);
                }

                const fila = document.querySelector(`tr[data-id="${idFactura}"]`);
                if (fila) {
                    const spEstado = fila.querySelector(`.badge-estado-abono-${idFactura}`);
                    if (spEstado) {
                        if (data.estado_abono === 'bancarizado') {
                            spEstado.innerHTML =
                                `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700 cursor-pointer hover:bg-green-200 transition" onclick="toggleBancarizado(${idFactura})"><i class="bi bi-check-circle-fill"></i> Bancarizado</span>`;
                        } else {
                            spEstado.innerHTML =
                                `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 cursor-pointer hover:bg-blue-200 transition" onclick="toggleBancarizado(${idFactura})"><i class="bi bi-person-check-fill"></i> B. Cliente</span>`;
                        }
                    }
                }

                mostrarToast('Estado actualizado correctamente', 'success');
            } else {
                mostrarToast(data.message || 'Error al cambiar estado', 'error');
            }
        } catch (e) {
            console.error(e);
            mostrarToast('Error de conexión', 'error');
        }
    }

    function recalcularTotalesTfoot(dtApi) {
        let totals = {
            PEN: {
                factura: 0,
                abonado: 0,
                pendiente: 0
            },
            USD: {
                factura: 0,
                abonado: 0,
                pendiente: 0
            }
        };

        dtApi.rows({
            page: 'current',
            search: 'applied'
        }).nodes().each(function(nodo) {
            const fila = $(nodo);
            const moneda = fila.data('moneda'); // 'PEN' o 'USD'
            const total = parseFloat(fila.data('total') ?? 0);
            const pendiente = parseFloat(fila.data('pendiente-row') ?? 0);
            const abonado = Math.max(0, total - pendiente);
            if (totals[moneda]) {
                totals[moneda].factura += total;
                totals[moneda].abonado += abonado;
                totals[moneda].pendiente += pendiente;
            }
        });

        const fmtNum = (n, sim) => sim + ' ' + parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        document.getElementById('tfoot-factura-total-pen').textContent = fmtNum(totals.PEN.factura, 'S/');
        document.getElementById('tfoot-total-abonado-pen').textContent = fmtNum(totals.PEN.abonado, 'S/');
        document.getElementById('tfoot-pendiente-pen').textContent = fmtNum(totals.PEN.pendiente, 'S/');
        document.getElementById('tfoot-factura-total-usd').textContent = fmtNum(totals.USD.factura, '$');
        document.getElementById('tfoot-total-abonado-usd').textContent = fmtNum(totals.USD.abonado, '$');
        document.getElementById('tfoot-pendiente-usd').textContent = fmtNum(totals.USD.pendiente, '$');
    }

    /* =====================================================================
       DATATABLES Y LIVE FILTERING
    ===================================================================== */
    let dtAbonos = null;

    function initAbonosTable() {
        if ($.fn.DataTable.isDataTable('#tabla-abonos-facturas')) {
            $('#tabla-abonos-facturas').DataTable().destroy();
        }

        dtAbonos = $('#tabla-abonos-facturas').DataTable({
            responsive: false,
            paging: true,
            ordering: true,
            pageLength: 25,
            order: [
                [8, 'desc']
            ], // col 8 = Fecha Factura
            columnDefs: [{
                    orderable: false,
                    searchable: false,
                    targets: 0
                }, // checkbox
                {
                    orderable: false,
                    searchable: false,
                    targets: -1
                } // acciones
            ],
            language: {
                url: '/js/datatables/lang/es-ES.json'
            }
        });

        dtAbonos.on('draw', function() {
            // Numeración dinámica (Columna #)
            const totalCount = dtAbonos.rows({
                search: 'applied'
            }).count();
            dtAbonos.column(0, {
                search: 'applied',
                order: 'applied'
            }).nodes().each(function(cell, i) {
                const row = cell.closest('tr');
                if (row) {
                    const estado = row.dataset.estado;
                    if (estado === 'bancarizado' || estado === 'bancarizado_cliente') {
                        cell.innerHTML = '—';
                    } else if (!cell.querySelector('input[type="checkbox"]')) {
                        cell.innerHTML = totalCount - i;
                    }
                }
            });

            if (typeof sincronizarFilasSeleccionadas === 'function') {
                sincronizarFilasSeleccionadas();
            }
            recalcularTotalesTfoot(dtAbonos);

            if (typeof pendingUpdates !== 'undefined' && pendingUpdates.size > 0) {
                document.querySelectorAll('#tabla-abonos-facturas tbody tr[data-id]').forEach(fila => {
                    const idStr = fila.dataset.id;
                    if (!pendingUpdates.has(idStr)) return;
                    const u = pendingUpdates.get(idStr);
                    if (typeof _aplicarCambiosEnFila === 'function') {
                        _aplicarCambiosEnFila(fila, idStr, u.totalAbonado, u.montoPendiente, u
                            .estadoAbono, u.grupoId);
                    }
                    pendingUpdates.delete(idStr);
                });
            }
        });
    }

    $(document).ready(function() {
        initAbonosTable();

        // ── LIVE FILTERING LOGIC ──
        const form = document.getElementById('form-filtros');
        if (!form) return;

        let debounceTimer;

        form.addEventListener('input', function(e) {
            if (e.target.name === 'buscar') {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchFiltros(), 500);
            }
        });

        form.addEventListener('change', function(e) {
            if (e.target.name !== 'buscar') {
                fetchFiltros();
            }
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchFiltros();
        });

        async function fetchFiltros() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = form.action + '?' + params.toString();

            const container = document.getElementById('table-container');
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newContainer = doc.getElementById('table-container');
                if (newContainer) {
                    container.innerHTML = newContainer.innerHTML;

                    // Re-inicializamos el DataTable después de reemplazar el DOM
                    initAbonosTable();

                    // Volver a enlazar los eventos delegados que no dependan del document
                    // (Los eventos on('click', ...) delegados al document seguirán funcionando,
                    // pero si hay eventos directos habría que reasignarlos aquí).
                }

                window.history.pushState({}, '', url);

            } catch (error) {
                console.error("Error cargando filtros en vivo:", error);
            } finally {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            }
        }
    });
</script>

<script>
    /* =====================================================================
   MODAL CUENTAS BANCARIAS
===================================================================== */
    $(document).ready(function() {

        const empresasConCuentas = @json($empresasConCuentas);

        const $sel = $('#cuentas-select-empresa');

        $sel.select2({
            dropdownParent: $('#modal-cuentas-empresa'),
            placeholder: 'Escribe para buscar...',
            allowClear: true,
            width: '100%',
            data: [{
                    id: '',
                    text: '— Buscar empresa —'
                },
                ...empresasConCuentas.map(e => ({
                    id: e.ruc,
                    text: `${e.razon} (${e.ruc})`
                }))
            ]
        });

        // ── Al seleccionar una empresa, cargar sus cuentas vía AJAX
        $sel.on('select2:select', async function() {
            const ruc = $(this).val();
            const text = $sel.select2('data')[0].text;
            if (!ruc) return;

            const $footer = $('#cuentas-footer-copy');
            const $footerText = $('#cuentas-footer-text');
            $footerText.text(text);
            $footer.removeClass('hidden');

            document.getElementById('cuentas-loading').classList.remove('hidden');
            document.getElementById('cuentas-sin-datos').classList.add('hidden');
            document.getElementById('cuentas-tabla-wrap').classList.add('hidden');

            try {
                const res = await fetch(`/cuentas-empresas/por-ruc?ruc=${encodeURIComponent(ruc)}`);
                const data = await res.json();
                document.getElementById('cuentas-loading').classList.add('hidden');
                if (!data.length) {
                    document.getElementById('cuentas-sin-datos').classList.remove('hidden');
                    return;
                }

                document.getElementById('cuentas-tbody').innerHTML = data.map((c, i) => `
                <tr class="hover:bg-indigo-50 transition ${i % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full
                                    bg-indigo-100 text-indigo-700 font-semibold">
                            <i class="bi bi-geo-alt-fill"></i> ${c.sede}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800">${c.banco}</td>
                    <td class="px-4 py-3 font-mono text-gray-700">${c.cuenta}</td>
                    <td class="px-4 py-3 font-mono text-gray-500 text-xs">${c.cci}</td>
                    <td class="px-4 py-3 text-center">
                        <button type="button"
                            class="btn-copiar-cuenta text-indigo-400 hover:text-indigo-600 transition"
                            data-cuenta="${c.banco}, Nro Cuenta: ${c.cuenta}, CCI: ${c.cci}" title="Copiar Info de la Cuenta">
                            <i class="bi bi-copy"></i>
                        </button>
                    </td>
                </tr>
            `).join('');

                // Bind copiar
                document.querySelectorAll('.btn-copiar-cuenta').forEach(btn => {
                    btn.addEventListener('click', () => {
                        navigator.clipboard.writeText(btn.dataset.cuenta).then(
                            () => {
                                btn.innerHTML =
                                    '<i class="bi bi-copy text-green-500"></i>';
                                setTimeout(() => {
                                    btn.innerHTML =
                                        '<i class="bi bi-copy"></i>';
                                }, 1500);
                            });
                    });
                });

                document.getElementById('cuentas-tabla-wrap').classList.remove('hidden');
            } catch (e) {
                console.error(e);
                document.getElementById('cuentas-loading').innerHTML =
                    '<span class="text-red-500">Error al cargar cuentas.</span>';
            }
        });

        // ── Limpiar al cerrar el modal
        $sel.on('select2:clear', function() {
            document.getElementById('cuentas-sin-datos').classList.add('hidden');
            document.getElementById('cuentas-tabla-wrap').classList.add('hidden');
            document.getElementById('cuentas-footer-copy').classList.add('hidden');
        });

        // ── Copiar empresa desde el footer
        document.getElementById('cuentas-footer-copy').addEventListener('click', function() {
            const text = document.getElementById('cuentas-footer-text').textContent;
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = this.innerHTML;
                this.innerHTML =
                    '<i class="bi bi-copy text-green-500"></i> Copiado';
                setTimeout(() => {
                    this.innerHTML = originalHtml;
                }, 1500);
            });
        });

        // ── Abrir modal
        document.getElementById('btn-abrir-cuentas').addEventListener('click', () => {
            $sel.val(null).trigger('change');
            document.getElementById('cuentas-sin-datos').classList.add('hidden');
            document.getElementById('cuentas-tabla-wrap').classList.add('hidden');
            document.getElementById('cuentas-loading').classList.add('hidden');
            document.getElementById('cuentas-footer-copy').classList.add('hidden');
            toggleModal('modal-cuentas-empresa');
            setTimeout(() => $sel.trigger('change.select2'), 100);
        });
    });
</script>

<script>
    /* =====================================================================
   EXPORTAR EXCEL
===================================================================== */

    // Datos de entidades inyectados desde Blade
    const EMPRESAS_LIST = @json($empresasList);
    const CLIENTES_LIST = @json($clientesList);

    function poblarSelectExport(tipo) {
        const $select = $('#export-ruc');
        const lista = tipo === 'cliente' ? CLIENTES_LIST : EMPRESAS_LIST;

        $select.empty().append('<option value="">— Seleccionar receptor —</option>');
        lista.forEach(item => {
            $select.append(new Option(`${item.razon} (${item.ruc})`, item.ruc));
        });

        $select.trigger('change');
    }

    document.getElementById('btn-abrir-exportar').addEventListener('click', () => {
        // Resetear radio a 'empresa' y poblar correlativamente
        document.getElementById('export-rol').value = 'receptor';
        document.getElementById('export-tipo-empresa').checked = true;
        poblarSelectExport('empresa');
        document.getElementById('export-estado').value = 'todas';
        document.getElementById('export-fecha-desde').value = '';
        document.getElementById('export-fecha-hasta').value = '';
        document.getElementById('export-error').classList.add('hidden');
        toggleModal('modal-exportar');
    });

    document.querySelectorAll('input[name="export-tipo"]').forEach(radio => {
        radio.addEventListener('change', () => poblarSelectExport(radio.value));
    });

    document.getElementById('btn-descargar-excel').addEventListener('click', () => {
        const errDiv = document.getElementById('export-error');
        errDiv.classList.add('hidden');

        const ruc = document.getElementById('export-ruc').value;
        const rol = document.getElementById('export-rol').value;
        const tipo = document.querySelector('input[name="export-tipo"]:checked').value;
        const estado = document.getElementById('export-estado').value;
        const desde = document.getElementById('export-fecha-desde').value;
        const hasta = document.getElementById('export-fecha-hasta').value;

        if (!ruc) {
            errDiv.textContent = 'Debes seleccionar una entidad.';
            errDiv.classList.remove('hidden');
            return;
        }

        if (!desde && !hasta) {
            errDiv.textContent = 'Debes especificar al menos un mes del rango.';
            errDiv.classList.remove('hidden');
            return;
        }

        const btn = document.getElementById('btn-descargar-excel');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Generando...';

        const url =
            `/abono-facturas/exportar?ruc=${encodeURIComponent(ruc)}&mes=&anio=&desde=${desde}&hasta=${hasta}&tipo=${tipo}&estado=${estado}&rol=${rol}`;
        window.location.href = url;

        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-file-earmark-spreadsheet"></i> Descargar Excel';
            toggleModal('modal-exportar');
            mostrarToast('Excel generado correctamente', 'success');
        }, 2500);
    });
</script>

<script>
    $(document).ready(function() {
        $('.select-abonofactura').select2({
            placeholder: 'Selecciona un Proveedor o Receptor',
            allowClear: true,
            width: 'resolve'
        });
    });
</script>
