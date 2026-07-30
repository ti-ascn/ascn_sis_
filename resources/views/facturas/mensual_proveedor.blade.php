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
        $nombreMesActual = $meses_es[$mes] ?? '—';
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Facturas de {{ $nombreMesActual }} del Proveedor: {{ $proveedor->razon_social }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-100 p-4 rounded-lg shadow-md mb-6 flex flex-wrap items-end gap-4">
                {{-- Filtros (Mes y Año + Buscar) --}}
                <form method="GET" action="{{ route('facturas.mensual_proveedor', ['ruc_emisor' => $proveedor->ruc]) }}"
                    class="flex flex-wrap items-end gap-4">
                    {{-- Selector de Mes --}}
                    <div class="flex flex-col">
                        <label for="mes" class="text-sm font-semibold text-gray-700 mb-1">Mes</label>
                        <select name="mes" id="mes"
                            class="border-gray-300 rounded-md shadow-sm w-32 focus:ring-blue-500 focus:border-blue-500">
                            @foreach ($meses_es as $numero => $nombre)
                                <option value="{{ $numero }}"
                                    {{ (request('mes') ?? now()->format('m')) == $numero ? 'selected' : '' }}>

                                    {{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selector de Año --}}
                    <div class="flex flex-col">
                        <label for="anio" class="text-sm font-semibold text-gray-700 mb-1">Año</label>
                        <select name="anio" id="anio"
                            class="border-gray-300 rounded-md shadow-sm w-32 focus:ring-blue-500 focus:border-blue-500">
                            @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" {{ request('anio') == $y ? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Botón Buscar --}}
                    <div>
                        <button type="submit"
                            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md transition">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </form>
                {{-- Botón Crear Factura (con dropdown) --}}
                <div class="relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center gap-2 bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white">
                                <i class="bi bi-receipt-cutoff"></i> Crear Factura
                                <i class="bi bi-chevron-down text-sm"></i>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="p-2">
                                <a href="#"
                                    class="btn-abrir-modal-factura block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    data-tipo="empresa">
                                    <i class="bi bi-building me-2"></i> Empresa
                                </a>
                                <a href="#"
                                    class="btn-abrir-modal-factura block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    data-tipo="cliente">
                                    <i class="bi bi-person me-2"></i> Cliente
                                </a>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>


                <a href="{{ route('facturas.index') }}"
                    class="flex items-center gap-2 bg-gray-600 px-4 py-2 rounded-md text-white hover:bg-gray-700 transition">
                    <i class="bi bi-journal-text"></i> Todas las Facturas
                </a>
                <a href="{{ route('facturas.mes_actual') }}"
                    class="flex items-center gap-2 bg-indigo-500 px-4 py-2 rounded-md text-white hover:bg-indigo-600 transition">
                    <i class="bi bi-calendar2-week"></i> Facturas del Mes
                </a>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-x-auto">
                @php
                    $totalSoles = 0;
                    $totalDolares = 0;
                @endphp
                <table class="min-w-full table-auto border" id="myTable-facturas-mensuales">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b">RUC - Receptor</th>
                            <th class="px-4 py-2 border-b">Razón Social - Receptor</th>
                            <th class="px-4 py-2 border-b">Fecha Emisión</th>
                            <th class="px-4 py-2 border-b">Nro Factura</th>
                            <th class="px-4 py-2 border-b">Factura Total</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($facturas as $factura)
                            @php
                                $esDolar = $factura->tipo_moneda === 'USD';
                                $montoBruto = $esDolar
                                    ? $factura->total_bruto / $factura->precio_dolar
                                    : $factura->total_bruto;
                                $simbolo = $esDolar ? '$' : 'S/';
                                if ($factura->estado !== 'Anulada') {
                                    $totalSoles += $esDolar ? 0 : $montoBruto;
                                    $totalDolares += ($esDolar ? $factura->total_bruto : 0) / $factura->precio_dolar;
                                }
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">
                                    <span class="text-sm text-gray-800">{{ $factura->ruc_receptor }}</span>
                                </td>
                                <td class="px-4 py-2 border-b">
                                    <span class="font-semibold">
                                        {{ $factura->empresa->razon_social ?? ($factura->cliente->razon_social ?? '—') }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 border-b">{{ $factura->nro_factura ?? 'Sin registro' }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ $simbolo }} {{ number_format($montoBruto, 2) }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @if ($factura->estado === 'Facturada')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">Facturada</span>
                                    @elseif ($factura->estado === 'Pendiente')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">Pendiente</span>
                                    @elseif ($factura->estado === 'Anulada')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">Anulada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b text-center">
                                    <div class="flex justify-center space-x-2">
                                        @if (Auth::user()->tipo_usuario == 1 ||
                                                Auth::user()->tipo_usuario == 2 ||
                                                (Auth::user()->tipo_usuario == 3 && Auth::user()->id_area == 7 && Auth::user()->puede_editar))
                                            <a href="{{ route('factura.edit', $factura->id_factura) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #F59E0B; transition: color 0.3s;"
                                                onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </a>
                                        @endif
                                        <a href="{{ route('factura.detalle', $factura->id_factura) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #6B7280; transition: color 0.3s;"
                                            onmouseover="this.style.color='#4B5563';"
                                            onmouseout="this.style.color='#6B7280';" target="_blank">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon />
                                        </a>
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
                                                        @foreach (['Facturada', 'Pendiente', 'Anulada'] as $estado)
                                                            @if ($factura->estado !== $estado)
                                                                <form method="POST"
                                                                    action="{{ route('factura.actualizar_estado', $factura->id_factura) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="estado"
                                                                        value="{{ $estado }}">
                                                                    <x-dropdown-link href="#"
                                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                                        @if ($estado === 'Facturada')
                                                                            <i
                                                                                class="bi bi-check-circle-fill text-green-500 mr-2"></i>
                                                                        @elseif ($estado === 'Pendiente')
                                                                            <i
                                                                                class="bi bi-hourglass-split text-yellow-500 mr-2"></i>
                                                                        @elseif ($estado === 'Anulada')
                                                                            <i
                                                                                class="bi bi-x-circle-fill text-red-500 mr-2"></i>
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
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 text-sm font-semibold text-gray-700">
                        @if ($totalSoles > 0)
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-right border-t border-b"></td>
                                <td class="px-4 py-2 text-right border-t border-b">Total Soles:</td>
                                <td class="px-4 py-2 text-right border-t border-b">
                                    S/ {{ number_format($totalSoles, 2) }}
                                </td>
                                <td class="border-t border-b"></td>
                                <td class="border-t border-b"></td>
                            </tr>
                        @endif

                        @if ($totalDolares > 0)
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-right border-t border-b"></td>
                                <td class="px-4 py-2 text-right border-t border-b">Total Dólares:</td>
                                <td class="px-4 py-2 text-right border-t border-b">
                                    $ {{ number_format($totalDolares, 2) }}
                                </td>
                                <td class="border-t border-b"></td>
                                <td class="border-t border-b"></td>
                            </tr>
                        @endif

                        @if ($totalSoles == 0 && $totalDolares == 0)
                            <tr>
                                <td colspan="7" class="px-4 py-2 text-center border-t border-b text-gray-500">
                                    No hay montos totales para mostrar
                                </td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</x-app-layout>
<x-modal-show id="modalInventariosGlobal" title="Selecciona un inventario para facturar">
    <div class="text-sm text-gray-800 max-h-[70vh] overflow-y-auto px-1">
        <p><strong>Destinatario:</strong> <span id="nombreDestinatario"></span></p>

        <table class="min-w-full mt-3 bg-white rounded-md text-sm" id="myTable-inventarios">
            <thead class="bg-gray-100 text-gray-700 sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-2 text-left">Inventario</th>
                    <th class="px-4 py-2 text-left">Código</th>
                    <th class="px-4 py-2 text-left">Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inventarios as $inv)
                    <tr>
                        <td class="px-4 py-2 border-b">{{ $inv->empresa->razon_social ?? '—' }}</td>
                        <td class="px-4 py-2 border-b">{{ $inv->nombre_codigo }}</td>
                        <td class="px-4 py-2 border-b">
                            <a href="#" class="btn-redirigir-factura flex items-center py-1 rounded-md"
                                data-id="{{ $inv->id_inventario }}" title="Crear factura"
                                style="color: #10b981; transition: color 0.3s;"
                                onmouseover="this.style.color='#059669';" onmouseout="this.style.color='#10b981';">
                                <x-factura-icon />
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-modal-show>
<script>
    $(document).ready(function() {
        const today = new Date();
        const mes = String(today.getMonth() + 1).padStart(2, '0');
        const anio = today.getFullYear();
        const nombreMesActual = '{{ strtoupper($nombreMesActual) }}'

        // Obtener fecha actual en formato DD_MM_AAAA
        const hoy = new Date();
        const dd = String(hoy.getDate()).padStart(2, '0');
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const yyyy = hoy.getFullYear();
        const fechaDescarga = `${dd}_${mm}_${yyyy}`;

        const tituloArchivo =
            `FACTURAS_${nombreMesActual}_PROVEEDOR_{{ $proveedor->razon_social }}_${anio}_EXPORTADO_${fechaDescarga}`;

        $('#myTable-facturas-mensuales').DataTable({
            responsive: true,
            paging: true,
            ordering: true,
            pageLength: 10,
            order: [
                [2, 'desc']
            ], // Orden por fecha emisión descendente
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excel',
                    text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                    title: tituloArchivo,
                    exportOptions: {
                        columns: ':not(:last)'
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                    title: tituloArchivo,
                    exportOptions: {
                        columns: ':not(:last)'
                    },
                    orientation: 'landscape',
                    pageSize: 'A4'
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer"></i> Imprimir',
                    title: tituloArchivo,
                    exportOptions: {
                        columns: ':not(:last)'
                    }
                }
            ],
            language: {
                url: "/js/datatables/lang/es-ES.json"
            }
        });
    });
</script>
<script>
    let tipoFactura = null;

    // Abre modal según tipo seleccionado
    document.querySelectorAll('.btn-abrir-modal-factura').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            tipoFactura = btn.dataset.tipo;
            document.getElementById('nombreDestinatario').textContent = tipoFactura === 'empresa' ?
                'EMPRESA NUEVA' : 'CLIENTE NUEVO';
            toggleModal('modalInventariosGlobal');
        });
    });

    // Redirige al crear factura
    document.querySelectorAll('.btn-redirigir-factura').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const id = btn.dataset.id;

            if (tipoFactura === 'empresa') {
                window.location.href = `/facturas/create/empresa/${id}`;
            } else {
                window.location.href = `/facturas/create/cliente/${id}`;
            }
        });
    });

    // DataTable para el modal
    $('#myTable-inventarios').DataTable({
        responsive: false,
        paging: true,
        ordering: true,
        pageLength: 5,
        language: {
            url: "/js/datatables/lang/es-ES.json"
        }
    });
</script>
