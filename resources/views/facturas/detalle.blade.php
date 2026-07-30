{{-- Estilos para impresión limpia --}}
<style>
    @page {
        margin: 20mm;
        size: auto;
    }

    @media print {

        /* Evita que se impriman encabezados/pies del navegador */
        body::before,
        body::after {
            display: none !important;
        }

        @page {
            margin: 0;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>
<style>
    @media print {
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        body * {
            visibility: hidden;
        }

        #factura-imprimible,
        #factura-imprimible * {
            visibility: visible;
        }

        #factura-imprimible {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 0;
            margin: 0;
        }

        .print\:hidden {
            display: none !important;
        }

        .table-auto {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .table-auto th,
        .table-auto td {
            border: 1px solid #000 !important;
            padding: 6px !important;
        }

        h2,
        h3,
        p,
        strong {
            color: #000 !important;
        }

        .text-blue-600,
        .text-gray-800,
        .text-gray-700 {
            color: #000 !important;
        }

        .text-sm,
        .text-xs {
            font-size: 12px !important;
        }

        .text-lg {
            font-size: 14px !important;
        }

        .rounded-full {
            border-radius: 0 !important;
        }

        .overflow-x-auto {
            overflow: visible !important;
        }
    }
</style>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4 print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="bi bi-receipt-cutoff me-2 text-gray-500"></i>
                Detalles de la Factura #{{ $factura->id_factura }}
            </h2>
        </div>
    </x-slot>

    @php
        $esDolar = $factura->tipo_moneda === 'USD';

        if ($esDolar) {
            if ($factura->precio_dolar_factura) {
                $precioDolarUsado = $factura->precio_dolar;
                $tipoDolarTexto = 'Precio dólar factura';
            } else {
                $precioDolarUsado = $factura->precio_dolar;
                $tipoDolarTexto = 'Precio dólar mensual';
            }
        } else {
            $precioDolarUsado = 1;
            $tipoDolarTexto = null;
        }

        $simbolo = $esDolar ? '$' : 'S/';
    @endphp

    <div class="py-8" id="factura-imprimible">
        <div class="w-full max-w-7xl mx-auto">

            {{-- Barra de botones separada del título y del contenido blanco --}}
            <div class="flex flex-wrap items-center gap-3 mb-6 print:hidden">
                <div class="relative">
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button
                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center gap-2 shadow-sm transition duration-200">
                                ← Volver
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="absolute z-50 w-56 bg-white shadow-lg rounded-md border p-2">
                                <x-dropdown-link :href="route('facturas.index')">
                                    <i class="bi bi-journal-text mr-2 text-green-500"></i> Todas las facturas
                                </x-dropdown-link>
                                {{-- Facturas por EMISOR --}}
                                <x-dropdown-link :href="route(
                                    'facturas.inventario',
                                    $factura->detalles->first()->producto->inventario->id_inventario,
                                )">
                                    <i class="bi bi-send-fill mr-2 text-blue-500"></i> Emitidas por el Emisor
                                </x-dropdown-link>

                                {{-- Facturas por RECEPTOR --}}
                                @if ($factura->id_cliente)
                                    <x-dropdown-link :href="route('facturas.por_cliente', $factura->id_cliente)">
                                        <i class="bi bi-person-fill mr-2 text-purple-500"></i> Recibidas por Cliente
                                    </x-dropdown-link>
                                @elseif($factura->id_empresa_cliente)
                                    <x-dropdown-link :href="route('facturas.por_empresa', $factura->id_empresa_cliente)">
                                        <i class="bi bi-buildings-fill mr-2 text-indigo-500"></i> Recibidas por Empresa
                                    </x-dropdown-link>
                                @endif
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                <a href="{{ route('productos.porInventario', $emisor->inventarios) }}"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 flex items-center gap-2 shadow-sm transition duration-200">
                    <i class="bi bi-box"></i> INVENTARIO
                </a>

                <div class="relative">
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button
                                class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 flex items-center gap-2 shadow-sm transition duration-200">
                                Cambiar estado
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="absolute left-0 z-50 w-48 bg-white shadow-lg rounded-md border p-2">
                                @foreach (['Facturada', 'Pendiente', 'Anulada'] as $estado)
                                    @if ($factura->estado !== $estado)
                                        <form method="POST"
                                            action="{{ route('factura.actualizar_estado', $factura->id_factura) }}">
                                            @csrf
                                            <input type="hidden" name="estado" value="{{ $estado }}">
                                            <x-dropdown-link href="#"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                @if ($estado === 'Facturada')
                                                    <i class="bi bi-check-circle-fill text-green-500 mr-2"></i>
                                                @elseif ($estado === 'Pendiente')
                                                    <i class="bi bi-hourglass-split text-yellow-500 mr-2"></i>
                                                @elseif ($estado === 'Anulada')
                                                    <i class="bi bi-x-circle-fill text-red-500 mr-2"></i>
                                                @endif
                                                Marcar como {{ $estado }}
                                            </x-dropdown-link>
                                        </form>
                                    @endif
                                @endforeach
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                <div class="relative">
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button
                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 flex items-center gap-2 shadow-sm transition duration-200">
                                Nueva Factura
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="absolute left-0 z-50 w-48 bg-white shadow-lg rounded-md border p-2">
                                <x-dropdown-link :href="route(
                                    'factura.create_empresa',
                                    $emisor->inventarios->first()->id_inventario,
                                )">
                                    <i class="bi bi-building me-2"></i> Empresa
                                </x-dropdown-link>
                                <x-dropdown-link :href="route(
                                    'factura.create_cliente',
                                    $emisor->inventarios->first()->id_inventario,
                                )">
                                    <i class="bi bi-person me-2"></i> Cliente
                                </x-dropdown-link>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                <button onclick="descargarExcel()"
                    class="bg-green-800 text-white px-4 py-2 rounded hover:bg-green-900 flex items-center gap-2 shadow-sm transition duration-200">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Descargar Excel
                </button>

                @if (Auth::user()->tipo_usuario == 1 ||
                        Auth::user()->tipo_usuario == 2 ||
                        (Auth::user()->tipo_usuario == 3 && Auth::user()->id_area == 7 && Auth::user()->puede_editar))
                    <a href="{{ route('factura.edit', $factura->id_factura) }}"
                        class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 flex items-center gap-2 shadow-sm transition duration-200">
                        <i class="bi bi-pencil-square"></i> EDITAR
                    </a>
                @endif
            </div>

            <div class="bg-white p-8 space-y-8 shadow-sm rounded-lg border border-gray-100">
                {{-- Información General --}}
                <div class="bg-white rounded-lg shadow-sm p-5 mb-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                        <i class="bi bi-file-earmark-text-fill text-blue-600 me-2"></i>
                        Información General - Factura #{{ $factura->id_factura }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Sección Emisor/Receptor -->
                        <div class="space-y-3">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                                    <i class="bi bi-building text-gray-500 me-2"></i> Emisor
                                </h4>
                                <p class="text-sm">{{ $emisor->razon_social ?? '—' }}</p>
                                <p class="text-xs text-gray-500 mt-1">RUC: <strong>{{ $factura->ruc_emisor }}</strong>
                                </p>
                                @if($emisor)
                                    @if($emisor->sol_usuario)
                                        <div class="group cursor-pointer text-xs text-gray-500 mt-1 print:hidden" onclick="copiarAlPortapapeles('{{ $emisor->sol_usuario }}','Usuario SOL copiado')">
                                            Usuario SOL: 
                                            <strong class="blur-[3px] group-hover:blur-none transition-all duration-300 text-gray-800 dark:text-gray-200">{{ $emisor->sol_usuario }}</strong>
                                        </div>
                                    @endif
                                    @if($emisor->sol_clave)
                                        <div class="group cursor-pointer text-xs text-gray-500 mt-0.5 print:hidden" onclick="copiarAlPortapapeles('{{ $emisor->sol_clave }}','Clave SOL copiado')">
                                            Clave SOL: 
                                            <strong class="blur-[3px] group-hover:blur-none transition-all duration-300 text-gray-800 dark:text-gray-200">{{ $emisor->sol_clave }}</strong>
                                        </div>
                                    @endif
                                @endif

                                <div class="mt-2 text-xs text-gray-500">
                                    <span class="font-semibold">Cuentas activas:</span>
                                    @if ($emisor->cuentas->where('estado', 1)->isNotEmpty())
                                        <ul class="list-disc list-inside space-y-1 mt-1">
                                            @foreach ($emisor->cuentas->where('estado', 1) as $c)
                                                <li>
                                                    {{ $c->tipo == 0 ? 'S/.' : '$' }} {{ $c->nombre }}
                                                    {{ $c->sede }}
                                                    – <span class="cursor-pointer hover:text-blue-600"
                                                        onclick="copiarAlPortapapeles('{{ $c->nro_cuenta ?? '—' }}','N° Cuenta copiado')">
                                                        N°: {{ $c->nro_cuenta ?? '—' }}
                                                    </span>
                                                    – <span class="cursor-pointer hover:text-blue-600"
                                                        onclick="copiarAlPortapapeles('{{ $c->nro_interbancario ?? '—' }}','CCI copiado')">
                                                        CCI: {{ $c->nro_interbancario ?? '—' }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="italic text-gray-400">Sin cuentas activas</p>
                                    @endif
                                </div>
                            </div>

                            <div class="p-3 bg-gray-50 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                                    <i class="bi bi-person-badge text-gray-500 me-2"></i> Receptor
                                </h4>
                                <p class="text-sm">
                                    {{ $factura->cliente->razon_social ?? ($factura->empresa->razon_social ?? '—') }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">RUC:
                                    <strong>{{ $factura->ruc_receptor }}</strong>
                                </p>
                            </div>
                        </div>

                        <!-- Sección Datos Factura -->
                        <div class="space-y-3">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                                    <i class="bi bi-receipt text-gray-500 me-2"></i> Datos Factura
                                </h4>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div><span class="text-gray-600">N° Solicitud:</span>
                                        {{ $factura->numero_solicitud ?? '—' }}</div>
                                    <div><span class="text-gray-600">N° Cotizacion:</span>
                                        {{ $factura->nro_cotizacion ?? '—' }}</div>
                                    <div><span class="text-gray-600">N° Orden:</span>
                                        {{ $factura->nombre_correlativo ?? '—' }}</div>
                                    <div><span class="text-gray-600">N° Factura:</span>
                                        {{ $factura->nro_factura ?? '—' }}
                                    </div>
                                    <div><span class="text-gray-600">Emisión:</span>
                                        {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}</div>
                                    <div><span class="text-gray-600">Pago:</span>
                                        {{ \Carbon\Carbon::parse($factura->fecha_pago)->format('d/m/Y') }}</div>
                                </div>
                            </div>

                            <div class="p-3 bg-gray-50 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                                    <i class="bi bi-truck text-gray-500 me-2"></i> Transporte
                                </h4>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div><span class="text-gray-600">GRR:</span> {{ $factura->grr ?? '—' }}</div>
                                    <div><span class="text-gray-600">GRT:</span> {{ $factura->grt ?? '—' }}</div>
                                    <div><span class="text-gray-600">Fecha Traslado:</span>
                                        {{ $factura->fecha_traslado ? \Carbon\Carbon::parse($factura->fecha_traslado)->format('d/m/Y') : '—' }}
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-gray-600">Conductor:</span>
                                        {{ $factura->programacionChofer ? ($factura->programacionChofer->nombre . ($factura->programacionChofer->placa ? ' [' . $factura->programacionChofer->placa . ']' : '')) : '—' }}
                                    </div>
                                    @if ($factura->programacionChofer && $factura->programacionChofer->empresa)
                                        @php
                                            $choferEmpresa = $factura->programacionChofer->empresa;
                                        @endphp
                                        <div class="col-span-2 mt-0.5">
                                            <span class="text-gray-600">Empresa Conductor:</span>
                                            {{ $choferEmpresa->razon_social }} - RUC: {{ $choferEmpresa->ruc }}
                                        </div>
                                        @if ($choferEmpresa->sol_usuario)
                                            <div class="col-span-2 group cursor-pointer text-xs text-gray-500 print:hidden mt-1" onclick="copiarAlPortapapeles('{{ $choferEmpresa->sol_usuario }}','Usuario SOL Conductor copiado')">
                                                Usuario SOL Conductor: 
                                                <strong class="blur-[3px] group-hover:blur-none transition-all duration-300 text-gray-800 dark:text-gray-200">
                                                    {{ $choferEmpresa->sol_usuario }}
                                                </strong>
                                            </div>
                                        @endif
                                        @if ($choferEmpresa->sol_clave)
                                            <div class="col-span-2 group cursor-pointer text-xs text-gray-500 print:hidden mt-0.5" onclick="copiarAlPortapapeles('{{ $choferEmpresa->sol_clave }}','Clave SOL Conductor copiado')">
                                                Clave SOL Conductor: 
                                                <strong class="blur-[3px] group-hover:blur-none transition-all duration-300 text-gray-800 dark:text-gray-200">
                                                    {{ $choferEmpresa->sol_clave }}
                                                </strong>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Sección Estado/Moneda -->
                        <div class="space-y-3">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                                    <i class="bi bi-info-circle text-gray-500 me-2"></i> Estado
                                </h4>
                                @php
                                    $estadoColor = match ($factura->estado) {
                                        'Facturada' => 'bg-green-100 text-green-800',
                                        'Pendiente' => 'bg-yellow-100 text-yellow-800',
                                        'Anulada' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="text-xs font-medium px-2 py-1 rounded-full {{ $estadoColor }}">
                                    {{ $factura->estado }}
                                </span>

                                <div class="mt-2 text-sm">
                                    <div><span class="text-gray-600">Agente Retenedor:</span>
                                        {{ $factura->agente_retenedor === 'S' ? 'Sí' : 'No' }}</div>
                                    <div><span class="text-gray-600">Moneda:</span> {{ $factura->tipo_moneda }}</div>
                                    @if ($esDolar)
                                        <div>
                                            <span class="text-gray-600">{{ $tipoDolarTexto }}:</span>
                                            S/ {{ number_format($precioDolarUsado, 3) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="p-3 bg-gray-50 rounded-lg text-sm">
                                <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                                    <i class="bi bi-person-gear text-gray-500 me-2"></i> Auditoría
                                </h4>
                                <div><span class="text-gray-600">Creado por:</span>
                                    {{ $factura->creador->name ?? '—' }}</div>
                                <div><span class="text-gray-600">Fecha creado:</span>
                                    {{ $factura->created_at->format('d/m/Y H:i') }}</div>
                                <div class="mt-2 pt-2 border-t">
                                    <span class="text-gray-600">Última actualización por:</span>
                                    {{ $factura->usuario->name ?? '—' }}
                                </div>
                                <div><span class="text-gray-600">Fecha actualización:</span>
                                    {{ $factura->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabla de productos --}}
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                        <i class="bi bi-box-seam me-1 text-gray-500"></i>
                        Productos Facturados
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border rounded-lg overflow-hidden shadow-sm print:text-xs">
                            <thead class="bg-gray-100 text-sm text-gray-700">
                                <tr>
                                    <th class="border px-4 py-2 text-left">Código</th>
                                    <th class="border px-4 py-2 text-left">Producto</th>
                                    <th class="border px-4 py-2 text-center">Unidad</th>
                                    <th class="border px-4 py-2 text-center">Cantidad</th>
                                    <th class="border px-4 py-2 text-right">Precio Unitario</th>
                                    <th class="border px-4 py-2 text-right">V.V.U</th>
                                    <th class="border px-4 py-2 text-right">Peso Total</th>
                                    <th class="border px-4 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-gray-800">
                                @foreach ($factura->detalles as $detalle)
                                    @php
                                        $factor = $precioDolarUsado;
                                        $pu = $detalle->precio_unitario / $factor;
                                        $pt = $detalle->precio_total / $factor;
                                    @endphp
                                    <tr>
                                        <td class="border px-4 py-2 font-mono">{{ $detalle->producto->id_producto }}
                                        </td>
                                        <td class="border px-4 py-2">{{ mb_strtoupper($detalle->producto->nombre) }}
                                        </td>
                                        <td class="border px-4 py-2 text-center">
                                            {{ $detalle->unidad_medida ?? $detalle->producto->unidad_medida }}
                                        </td>
                                        <td class="border px-4 py-2 text-center">{{ $detalle->cantidad }}</td>

                                        {{-- Precio Unitario --}}
                                        <td class="border px-4 py-2 text-right">
                                            {{ $simbolo }} {{ number_format($pu, 4) }}
                                        </td>

                                        {{-- V.V.U (Precio sin IGV) --}}
                                        <td class="border px-4 py-2 text-right">
                                            {{ $simbolo }} {{ number_format($pu / 1.18, 4) }}
                                        </td>

                                        {{-- Peso Total --}}
                                        <td class="border px-4 py-2 text-right">
                                            {{ number_format($detalle->peso_total, 2) }} kg
                                        </td>

                                        {{-- Subtotal --}}
                                        <td class="border px-4 py-2 text-right">
                                            {{ $simbolo }} {{ number_format($pt, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Totales --}}
                @php
                    $factor = $precioDolarUsado;

                    $totalBruto = $factura->total_bruto / $factor;
                    $igv = $totalBruto * 0.18;
                    $retencion = $factura->monto_retencion / $factor;
                    $totalNeto = $factura->total_neto / $factor;
                @endphp

                <div class="flex justify-end">
                    <div class="w-full md:w-1/2 space-y-2 text-right text-gray-800">
                        <p><strong>Total Bruto:</strong>
                            {{ $simbolo }} {{ number_format($totalBruto, 2) }}
                        </p>
                        <p><strong>IGV (18%):</strong>
                            {{ $simbolo }} {{ number_format($totalBruto * 0.18, 2) }}
                        </p>
                        <p><strong>Retención (3%):</strong>
                            {{ $simbolo }} {{ number_format($retencion, 2) }}
                        </p>

                        <p class="text-lg"><strong>Total Neto:</strong>
                            <span class="text-blue-600">
                                {{ $simbolo }} {{ number_format($totalNeto, 2) }}
                            </span>
                        </p>

                        <p><strong>Peso Neto:</strong> {{ number_format($factura->peso_neto, 2) }} kg</p>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>
<script>
    // Función global para copiar al portapapeles
    function copiarAlPortapapeles(texto, mensaje = 'Copiado') {
        navigator.clipboard.writeText(texto)
            .then(() => {
                const aviso = document.createElement('div');
                aviso.textContent = mensaje;
                aviso.className =
                    'fixed top-4 right-4 bg-green-500 text-white text-sm px-4 py-2 rounded shadow z-50';
                document.body.appendChild(aviso);

                setTimeout(() => {
                    aviso.remove();
                }, 2000);
            })
            .catch(err => {
                console.error('Error al copiar:', err);
            });
    }

    // Función para descargar Excel
    function descargarExcel() {
        @php
            $simbolo = $factura->tipo_moneda === 'USD' ? '$' : 'S/';
            $factor = $factura->tipo_moneda === 'USD' ? $factura->precio_dolar : 1;
        @endphp

        let htmlContent = `
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
                th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                th { background-color: #f0f0f0; font-weight: bold; }
                .header { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
                .section { margin-bottom: 20px; }
                .right { text-align: right; }
                .center { text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">Detalles de la Factura #{{ $factura->id_factura }}</div>
            
            <div class="section">
                <h3>Información General</h3>
                <table>
                    <tr><th>Campo</th><th>Valor</th></tr>
                    <tr><td>ID Factura</td><td>{{ $factura->id_factura }}</td></tr>
                    <tr><td>N° Factura</td><td>{{ $factura->nro_factura ?? '—' }}</td></tr>
                    <tr><td>N° Orden</td><td>{{ $factura->nombre_correlativo ?? '—' }}</td></tr>
                    <tr><td>Fecha Emisión</td><td>{{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}</td></tr>
                    <tr><td>Fecha Pago</td><td>{{ \Carbon\Carbon::parse($factura->fecha_pago)->format('d/m/Y') }}</td></tr>
                    <tr><td>Emisor</td><td>{{ $emisor->razon_social ?? '—' }}</td></tr>
                    <tr><td>RUC Emisor</td><td>{{ $factura->ruc_emisor }}</td></tr>
                    <tr><td>Receptor</td><td>{{ $factura->cliente->razon_social ?? ($factura->empresa->razon_social ?? '—') }}</td></tr>
                    <tr><td>RUC Receptor</td><td>{{ $factura->ruc_receptor }}</td></tr>
                    <tr><td>Estado</td><td>{{ $factura->estado }}</td></tr>
                    <tr><td>Moneda</td><td>{{ $factura->tipo_moneda }}</td></tr>
                    <tr><td>Agente Retenedor</td><td>{{ $factura->agente_retenedor === 'S' ? 'Sí' : 'No' }}</td></tr>
                    @if ($esDolar)
                    <tr>
                        <td>Tipo de dólar</td>
                        <td>{{ $tipoDolarTexto }}</td>
                    </tr>
                    <tr>
                        <td>Valor dólar usado</td>
                        <td>S/ {{ number_format($precioDolarUsado, 3) }}</td>
                    </tr>
                    @endif
                    <tr><td>GRR</td><td>{{ $factura->grr ?? '—' }}</td></tr>
                    <tr><td>GRT</td><td>{{ $factura->grt ?? '—' }}</td></tr>
                    <tr><td>Fecha Traslado</td><td>{{ $factura->fecha_traslado ? \Carbon\Carbon::parse($factura->fecha_traslado)->format('d/m/Y') : '—' }}</td></tr>
                </table>
            </div>
            
            <div class="section">
                <h3>Productos Facturados</h3>
                <table>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th class="center">Unidad</th>
                        <th class="center">Cantidad</th>
                        <th class="right">Precio Unitario</th>
                        <th class="right">V.V.U</th>
                        <th class="right">Peso Total</th>
                        <th class="right">Subtotal</th>
                    </tr>
                    @foreach ($factura->detalles as $detalle)
                    @php
                        $pu = $detalle->precio_unitario / $factor;
                        $pt = $detalle->precio_total / $factor;
                    @endphp
                    <tr>
                        <td>{{ $detalle->producto->id_producto }}</td>
                        <td>{{ mb_strtoupper($detalle->producto->nombre) }}</td>
                        <td class="center">{{ $detalle->unidad_medida ?? $detalle->producto->unidad_medida }}</td>
                        <td class="center">{{ $detalle->cantidad }}</td>
                        <td class="right">{{ $simbolo }} {{ number_format($pu, 4) }}</td>
                        <td class="right">{{ $simbolo }} {{ number_format($pu / 1.18, 4) }}</td>
                        <td class="right">{{ number_format($detalle->peso_total, 2) }} kg</td>
                        <td class="right">{{ $simbolo }} {{ number_format($pt, 2) }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            
            <div class="section">
                <h3>Totales</h3>
                <table>
                    <tr><th>Concepto</th><th class="right">Monto</th></tr>
                    <tr><td>Total Bruto</td><td class="right">{{ $simbolo }} {{ number_format($factura->total_bruto / $factor, 2) }}</td></tr>
                    <tr><td>IGV (18%)</td><td class="right">{{ $simbolo }} {{ number_format($igv, 2) }}</td></tr>
                    <tr><td>Retención (3%)</td><td class="right">{{ $simbolo }} {{ number_format($factura->monto_retencion / $factor, 2) }}</td></tr>
                    <tr><td><strong>Total Neto</strong></td><td class="right"><strong>{{ $simbolo }} {{ number_format($factura->total_neto / $factor, 2) }}</strong></td></tr>
                    <tr><td>Peso Neto</td><td class="right">{{ number_format($factura->peso_neto, 2) }} kg</td></tr>
                </table>
            </div>
        </body>
        </html>`;

        const blob = new Blob([htmlContent], {
            type: 'application/vnd.ms-excel'
        });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        const timestamp = Date.now();
        const nombreEmpresa = '{{ str_replace([' ', '.', ',', '-'], '_', $emisor->razon_social ?? 'Emisor') }}';
        const fechaEmision = '{{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d-m-Y') }}';
        link.download = `FACTURA_${nombreEmpresa}_EMITIDO_${fechaEmision}_${timestamp}.xls`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    }
</script>
