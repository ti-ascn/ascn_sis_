<x-app-layout>
    <style>
        tr.fila-seleccionada td {
            background-color: #bfdbfe !important;
        }
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Todas las Facturas Emitidas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col mb-4">

                <div class="relative w-full md:w-auto">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center justify-center gap-2 bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white text-center w-full md:w-auto">
                                <i class="bi bi-receipt-cutoff"></i> Crear Factura
                                <i class="bi bi-chevron-down text-sm"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="absolute right-0 z-50 w-48 bg-white shadow-lg rounded-md border p-2">
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

                <a href="{{ route('facturas.mes_actual') }}"
                    class="flex items-center justify-center gap-2 bg-indigo-500 px-4 py-2 rounded-md text-white hover:bg-indigo-600 transition w-full md:w-auto">
                    <i class="bi bi-calendar2-week"></i> Facturas del Mes
                </a>

                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <button onclick="abrirModal()"
                        class="flex items-center justify-center gap-2 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 w-full md:w-auto">
                        <i class="bi bi-sliders2-vertical"></i> Permisos de Edición
                    </button>
                @endif

                <a href="{{ route('cotizaciones.plantillas.index') }}"
                    class="flex items-center justify-center gap-2 bg-green-500 px-4 py-2 rounded-md text-white hover:bg-green-600 transition w-full md:w-auto">
                    <i class="bi bi-file-earmark-excel-fill"></i> Plantillas de Cotizaciones
                </a>

                <a href="{{ route('guias.index') }}"
                    class="flex items-center justify-center gap-2 bg-yellow-600 px-4 py-2 rounded-md text-white hover:bg-yellow-700 transition w-full md:w-auto">
                    <i class="bi bi-newspaper"></i> Guías
                </a>

                <button onclick="toggleModal('modal-export-cuentas')"
                    class="flex items-center justify-center gap-2 bg-green-700 px-4 py-2 rounded-md text-white hover:bg-green-800 transition w-full md:w-auto">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Cuentas
                </button>

            </div>
            <br>

            {{-- FILTROS --}}
            <div class="bg-white shadow-lg rounded-lg p-4 mb-4">
                <form method="GET" action="{{ route('facturas.todos') }}" class="flex flex-wrap gap-4 items-end"
                    id="form-filtros-facturas">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar en toda la Base de
                            Datos</label>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                            placeholder="RUC, Razon Social (emisor o receptor), n° factura, orden..."
                            class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                        <select name="anio" class="border-gray-300 rounded-md shadow-sm w-32">
                            <option value="">Todos</option>
                            @foreach ($anios as $a)
                                <option value="{{ $a }}" {{ request('anio') == $a ? 'selected' : '' }}>
                                    {{ $a }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
                        <select name="mes" class="border-gray-300 rounded-md shadow-sm w-40">
                            <option value="">Todos</option>
                            @foreach ($mesesArray as $num => $nombre)
                                <option value="{{ $num }}" {{ request('mes') == $num ? 'selected' : '' }}>
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="estado" class="border-gray-300 rounded-md shadow-sm w-40">
                            <option value="">Todos</option>
                            @foreach (['Pendiente', 'Facturada', 'Anulada'] as $e)
                                <option value="{{ $e }}" {{ request('estado') == $e ? 'selected' : '' }}>
                                    {{ $e }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Estado</label>
                        <select name="tipo_factura" class="border-gray-300 rounded-md shadow-sm w-40">
                            <option value="">Todos</option>
                            @foreach (['Clientes', 'R2→R1', 'R1→A1'] as $tf)
                                <option value="{{ $tf }}"
                                    {{ request('tipo_factura') == $tf ? 'selected' : '' }}>
                                    {{ $tf }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition h-[42px]">
                        <i class="bi bi-search"></i> Filtrar
                    </button> --}}

                    <a href="{{ route('facturas.todos') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md transition h-[42px] leading-[26px]">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </form>
            </div>

            <div id="table-container-facturas"
                class="bg-white shadow-md rounded-lg overflow-x-auto relative transition-opacity duration-300">
                <table class="min-w-[1200px] table-auto border" id="myTable-facturas">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b">#</th>
                            <th class="px-4 py-2 border-b">Tipo Factura</th>
                            <th class="px-4 py-2 border-b">Nro Solicitud</th>
                            <th class="px-4 py-2 border-b">Nro Cotizacion</th>
                            <th class="px-4 py-2 border-b">Nro Orden</th>
                            <th class="px-4 py-2 border-b">Nro Factura</th>
                            <th class="px-4 py-2 border-b">Emisor</th>
                            <th class="px-4 py-2 border-b">Fecha Emisión</th>
                            <th class="px-4 py-2 border-b">Receptor</th>
                            <th class="px-4 py-2 border-b">Total Bruto</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($facturas as $factura)
                            <tr class="fila-factura hover:bg-gray-50 {{ $factura->estado === 'Anulada' ? 'cursor-pointer' : '' }}"
                                data-id="{{ $factura->id_factura }}" data-estado="{{ $factura->estado }}">
                                <td class="px-4 py-2 border-b">{{ $factura->id_factura }}</td>
                                <td class="px-4 py-2 border-b text-center">
                                    @if ($factura->tipo_factura == 1)
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Clientes
                                        </span>
                                    @elseif($factura->tipo_factura == 2)
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                            R1→A1
                                        </span>
                                    @elseif($factura->tipo_factura == 3)
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            R2→R1
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $factura->tipo_factura }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">{{ $factura->numero_solicitud ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">{{ $factura->nro_cotizacion ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">{{ $factura->nombre_correlativo ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">{{ $factura->nro_factura ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">
                                    @php
                                        $emisor =
                                            $empresas[$factura->ruc_emisor]->razon_social ??
                                            ($clientes[$factura->ruc_emisor]->razon_social ?? null);
                                    @endphp
                                    @if ($emisor)
                                        <span class="font-semibold">{{ $emisor }}</span><br>
                                    @endif
                                    <span class="text-sm text-gray-600">RUC: {{ $factura->ruc_emisor }}</span>
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @if ($factura->empresa)
                                        <span class="font-semibold">{{ $factura->empresa->razon_social }}</span><br>
                                        <span class="text-sm text-gray-600">RUC: {{ $factura->empresa->ruc }}</span>
                                    @elseif ($factura->cliente)
                                        <span class="font-semibold">{{ $factura->cliente->razon_social }}</span><br>
                                        <span class="text-sm text-gray-600">RUC: {{ $factura->cliente->ruc }}</span>
                                    @else
                                        —
                                    @endif
                                </td>

                                @php
                                    $simbolo = 'S/.';
                                    $monto = $factura->total_bruto;
                                    $tiene_dolar_factura = null;

                                    if ($factura->tipo_moneda === 'USD') {
                                        $simbolo = '$';
                                        if ($factura->precio_dolar_factura) {
                                            $monto = $factura->total_bruto / $factura->precio_dolar;
                                            $tiene_dolar_factura = 'Valor dólar independiente';
                                        } else {
                                            $monto = $factura->total_bruto / $factura->precio_dolar;
                                            $tiene_dolar_factura = 'Valor dólar del mes';
                                        }
                                    }
                                @endphp

                                <td class="px-4 py-2 border-b">
                                    <div class="flex flex-col items-end leading-tight">
                                        <span
                                            class="font-medium whitespace-nowrap {{ $factura->monto_retencion > 0 ? 'text-blue-600' : '' }}">
                                            {{ $simbolo }} {{ number_format($monto, 2) }}
                                            @if ($factura->monto_retencion > 0)
                                                <span class="ml-1 text-xs font-normal text-blue-400"
                                                    title="Tiene retención">
                                                    <i class="bi bi-info-circle-fill"></i>
                                                </span>
                                            @endif
                                        </span>
                                        @if ($tiene_dolar_factura)
                                            <span
                                                class="mt-1 px-2 py-0.5 text-xs rounded bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200 whitespace-nowrap">
                                                {{ $tiene_dolar_factura }}
                                            </span>
                                        @endif
                                    </div>
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
                                            {{-- Editar --}}
                                            <a href="{{ route('factura.edit', $factura->id_factura) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #F59E0B; transition: color 0.3s;"
                                                onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </a>
                                        @endif

                                        {{-- Detalles --}}
                                        <a href="{{ route('factura.detalle', $factura->id_factura) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #6B7280; transition: color 0.3s;"
                                            onmouseover="this.style.color='#4B5563';"
                                            onmouseout="this.style.color='#6B7280';" target="_blank">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon />
                                        </a>

                                        {{-- COTIZACIÓN EXCEL (Emisor) --}}
                                        @if (in_array(trim($factura->ruc_emisor), $empresasConPlantilla ?? []))
                                            <a href="{{ route('cotizaciones.descargar', $factura->id_factura) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #16a34a; transition: color 0.3s;"
                                                onmouseover="this.style.color='#15803d';"
                                                onmouseout="this.style.color='#16a34a';">
                                                <span class="tooltip-text">Generar Cotización (Emisión)</span>
                                                <x-excel-icon />
                                            </a>
                                        @else
                                            <span class="flex items-center py-1 tooltip"
                                                style="color: #d1d5db; cursor: not-allowed;">
                                                <span class="tooltip-text">Sin plantilla Cotización (Emisor)</span>
                                                <x-excel-icon />
                                            </span>
                                        @endif

                                        {{-- SOLICITUD EXCEL (Receptor) --}}
                                        @if (in_array(trim($factura->ruc_receptor), $empresasConSolicitud ?? []))
                                            <a href="{{ route('cotizaciones.solicitud.descargar', $factura->id_factura) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #2563eb; transition: color 0.3s;"
                                                onmouseover="this.style.color='#1d4ed8';"
                                                onmouseout="this.style.color='#2563eb';">
                                                <span class="tooltip-text">Generar Solicitud (Receptor)</span>
                                                <x-excel-icon />
                                            </a>
                                        @else
                                            <span class="flex items-center py-1 tooltip"
                                                style="color: #d1d5db; cursor: not-allowed;">
                                                <span class="tooltip-text">Sin plantilla Solicitud (Receptor)</span>
                                                <x-excel-icon />
                                            </span>
                                        @endif

                                        {{-- ✅ ORDEN DE COMPRA EXCEL (Receptor) --}}
                                        @if (in_array(trim($factura->ruc_receptor), $empresasConOrdCompra ?? []))
                                            <a href="{{ route('cotizaciones.ord_compra.descargar', $factura->id_factura) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #9333ea; transition: color 0.3s;"
                                                onmouseover="this.style.color='#7e22ce';"
                                                onmouseout="this.style.color='#9333ea';">
                                                <span class="tooltip-text">Generar Orden Compra (Receptor)</span>
                                                <x-excel-icon />
                                            </a>
                                        @else
                                            <span class="flex items-center py-1 tooltip"
                                                style="color: #d1d5db; cursor: not-allowed;">
                                                <span class="tooltip-text">Sin plantilla Ord. Compra (Receptor)</span>
                                                <x-excel-icon />
                                            </span>
                                        @endif

                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                            {{-- Eliminar --}}
                                            <form action="{{ route('factura.destroy', $factura->id_factura) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta factura?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';">
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Dropdown Cambiar estado --}}
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
                </table>
                <div class="mt-4">
                    {{ $facturas->links() }}
                </div>
            </div>
        </div>
    </div>

    <div id="container-eliminar-flotante" class="fixed bottom-10 left-1/2 transform -translate-x-1/2 z-[100] hidden">
        <button type="button" id="btn-eliminar-seleccionados"
            class="bg-red-600 text-white px-6 py-3 rounded-full font-semibold transition-colors duration-150">
            <i class="bi bi-trash3-fill"></i><span>Eliminar (<span id="count-seleccionados">0</span>)</span>
        </button>
    </div>
    <style>
        #btn-eliminar-seleccionados:active {
            background-color: #b91c1c;
        }
    </style>

    <x-modal-show id="modal-export-cuentas" title="Exportar Cuentas de Empresas">
        <p class="mb-4 text-sm text-gray-600">
            Selecciona los filtros para la exportación en Excel.
        </p>

        <form action="{{ route('facturas.export_cuentas') }}" method="GET" class="space-y-6">
            <div>
                <x-input-label for="export_estado" value="Estado de la Cuenta" />
                <select id="export_estado" name="estado"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="1" selected>Activo</option>
                    <option value="0">Inactivo</option>
                    <option value="ambas">Ambas</option>
                </select>
            </div>

            <div>
                <x-input-label for="export_sede" value="Sede" />
                <select id="export_sede" name="sede"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="Trujillo" selected>Trujillo</option>
                    <option value="Lima">Lima</option>
                    <option value="Ambas">Ambas</option>
                </select>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button onclick="toggleModal('modal-export-cuentas')">
                    Cancelar
                </x-secondary-button>

                <x-primary-button class="bg-green-600 hover:bg-green-700">
                    Exportar Excel
                </x-primary-button>
            </div>
        </form>
    </x-modal-show>

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

<x-modal-show id="modalPermisos" title="Gestión de Permisos para AUDITORIA">
    <div class="space-y-4">
        @foreach ($usuariosAuditoria as $usuario)
            <div class="flex items-center justify-between">
                <span>{{ $usuario->name }}</span>
                <form action="{{ route('user.togglePermiso', $usuario->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 rounded-md {{ $usuario->puede_editar ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }} text-white transition duration-300">
                        {{ $usuario->puede_editar ? 'Desactivar' : 'Activar' }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</x-modal-show>

<script>
    window.addEventListener('load', function() {
        let facturasSeleccionadas = [];

        $('#container-eliminar-flotante').addClass('hidden');
        $('#count-seleccionados').text(0);

        // Delegamos el evento al contenedor para que sobreviva a recargas AJAX
        $('#table-container-facturas').on('click', '.fila-factura', function() {
            const id = $(this).data('id');
            const estado = $(this).data('estado');
            if (estado !== 'Anulada') return;

            if (facturasSeleccionadas.includes(id)) {
                facturasSeleccionadas = facturasSeleccionadas.filter(item => item !== id);
                $(this).removeClass('fila-seleccionada');
            } else {
                facturasSeleccionadas.push(id);
                $(this).addClass('fila-seleccionada');
            }

            actualizarBotonEliminar();
        });

        function actualizarBotonEliminar() {
            const container = $('#container-eliminar-flotante');
            const span = $('#count-seleccionados');
            if (facturasSeleccionadas.length > 0) {
                container.removeClass('hidden');
                span.text(facturasSeleccionadas.length);
            } else {
                container.addClass('hidden');
            }
        }

        $('#btn-eliminar-seleccionados').on('click', function() {
            if (confirm('¿Estás seguro de que deseas eliminar las facturas seleccionadas?')) {
                $.ajax({
                    url: "{{ route('facturas.bulk_destroy') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids: facturasSeleccionadas
                    },
                    success: function(response) {
                        if (response.success) {
                            facturasSeleccionadas = [];
                            actualizarBotonEliminar();
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        const res = xhr.responseJSON;
                        alert('Error: ' + (res ? res.message :
                            'Ocurrió un error inesperado.'));
                    }
                });
            }
        });
    });
</script>

<script>
    function abrirModal() {
        toggleModal('modalPermisos');
    }
</script>
<script>
    let tipoFactura = null;

    document.querySelectorAll('.btn-abrir-modal-factura').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            tipoFactura = btn.dataset.tipo;
            document.getElementById('nombreDestinatario').textContent = tipoFactura === 'empresa' ?
                'EMPRESA NUEVA' : 'CLIENTE NUEVO';
            toggleModal('modalInventariosGlobal');
        });
    });

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

    $('#myTable-inventarios').DataTable({
        responsive: false,
        paging: true,
        ordering: true,
        pageLength: 5,
        language: {
            url: "/js/datatables/lang/es-ES.json"
        }
    });

    let dtFacturas = null;

    function initFacturasTable() {
        if ($.fn.DataTable.isDataTable('#myTable-facturas')) {
            $('#myTable-facturas').DataTable().destroy();
        }

        dtFacturas = $('#myTable-facturas').DataTable({
            responsive: false,
            info: false,
            paging: false,
            ordering: true,
            searching: true,
            language: {
                url: "/js/datatables/lang/es-ES.json"
            },
            order: [
                [0, 'desc']
            ]
        });
    }

    $(document).ready(function() {
        initFacturasTable();

        // ── LIVE FILTERING LOGIC ──
        const form = document.getElementById('form-filtros-facturas');
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

            const container = document.getElementById('table-container-facturas');
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

                const newContainer = doc.getElementById('table-container-facturas');
                if (newContainer) {
                    container.innerHTML = newContainer.innerHTML;
                    initFacturasTable();
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
