@php
    $mesLabel = function ($i) {
        return strtoupper(\Carbon\Carbon::create()->month($i)->locale('es')->monthName);
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Deudas ({{ ucfirst($tipo) }}) al año <span class="text-blue-500">{{ $anio }}</span>
        </h2>
    </x-slot>

    <style>
        .empresa-link {
            color: #111827;
            text-decoration: none;
            border-bottom: 1px solid transparent;
        }

        .empresa-link:hover {
            border-bottom: 1px solid #3b82f6;
        }

        .estado-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 2px 10px;
            border-radius: 9999px;
            color: #fff;
            font-size: 12px;
        }

        .estado-activo {
            background: #10B981;
        }

        .estado-suspendido {
            background: #F59E0B;
        }

        .mini-estado {
            font-size: 11px;
            color: #6B7280;
            display: block;
            margin-top: 2px;
        }

        .cell-total {
            font-weight: 600;
        }
    </style>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            {{-- Selector de año --}}
            <div class="bg-gray-100 p-4 rounded-lg shadow mb-6 flex items-center gap-4">
                <form action="{{ route('deudas.index', $tipo) }}" method="GET" class="flex items-center gap-4">
                    <select name="anio" onchange="this.form.submit()"
                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @for ($a = now()->year; $a >= 2020; $a--)
                            <option value="{{ $a }}" {{ $a == $anio ? 'selected' : '' }}>{{ $a }}
                            </option>
                        @endfor
                    </select>
                    {{-- <button type="submit"
                        class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md text-white hover:bg-blue-600 transition duration-300">
                        <i class="bi bi-arrow-repeat"></i><span class="text-ml">Cambiar año</span>
                    </button> --}}
                </form>
                <br />
                <div class="flex justify-end gap-4">
                    <a href="{{ route('deudas.export', ['tipo' => $tipo, 'anio' => $anio]) }}"
                        class=" bg-green-800 px-4 py-2 rounded-md hover:bg-green-900 transition duration-300 text-white shadow-md">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Descargar Excel
                    </a>
                    @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                        <a href="{{ route('tributos.index') }}"
                            class=" bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white shadow-md">
                            <i class="bi bi-bank"></i> Tipos de Tributos
                        </a>
                    @endif
                    @if ($tipo === 'recursos' || $tipo === 'contabilidad')
                        <button type="button" onclick="toggleModal('modal-buscar-deudas-{{ $tipo }}')"
                            class="bg-blue-600 px-4 py-2 rounded-md hover:bg-blue-700 transition duration-300 text-white shadow-md">
                            <i class="bi bi-search"></i> Buscar Deudas
                            ({{ $tipo === 'recursos' ? 'Recursos' : 'Contabilidad' }})
                        </button>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-[1200px] table-auto text-sm" id="myTable-deudas">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-700">
                            <th class="px-3 py-2 border-b">UD</th>
                            <th class="px-3 py-2 border-b">{{ $tipo === 'recursos' ? 'ESTADO RECURSOS' : 'ESTADO' }}
                            </th>
                            <th class="px-3 py-2 border-b">RAZÓN SOCIAL</th>
                            <th class="px-3 py-2 border-b">RUC</th>
                            <th class="px-3 py-2 border-b">VENCIMIENTO (PDT)</th>
                            @for ($m = 1; $m <= 12; $m++)
                                <th class="px-3 py-2 border-b">{{ $mesLabel($m) }}</th>
                            @endfor
                            <th class="px-3 py-2 border-b">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($filas as $f)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">{{ $f['ud'] }}</td>
                                <td class="px-3 py-2">
                                    {{ $tipo === 'recursos' ? $f['estado_recursos'] : $f['estado'] }}
                                </td>
                                <td class="px-3 py-2">
                                    <a href="javascript:void(0)"
                                        class="empresa-link transition-colors hover:text-blue-600"
                                        onclick="abrirModalDeuda(this, {{ $f['id'] }}, '{{ addslashes($f['razon']) }}', '{{ $f['vencimiento_pdt'] }}')">
                                        {{ $f['razon'] }}
                                    </a>
                                </td>
                                <td class="px-3 py-2">
                                    <a href="javascript:void(0)"
                                        class="empresa-link transition-colors hover:text-blue-600"
                                        onclick="abrirModalPDF('{{ $f['id'] }}','{{ addslashes($f['razon']) }}')">
                                        {{ $f['ruc'] }}
                                    </a>
                                </td>

                                <td class="px-3 py-2">
                                    @php $v = $f['vencimiento_pdt']; @endphp
                                    <span
                                        class="text-gray-700">{{ $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '—' }}</span>
                                </td>

                                @for ($m = 1; $m <= 12; $m++)
                                    @php
                                        $c = $f['meses'][$m];
                                        $hasDeuda = $c['estado'] !== '—';
                                        $cellClasses = $hasDeuda ? 'text-gray-800' : 'text-gray-400';
                                        $monto = $hasDeuda
                                            ? 'S/. ' . number_format($c['total'], 0, '.', ',')
                                            : $mesLabel($m);
                                        $estado = $hasDeuda ? ($c['total'] == 0 ? 'Sin movimiento' : $c['estado']) : '';
                                        $estadoColor =
                                            $c['estado'] === 'Pendiente'
                                                ? 'text-red-600'
                                                : ($c['estado'] === 'Pagado'
                                                    ? 'text-emerald-600'
                                                    : 'text-gray-500');
                                        $exportText = $hasDeuda ? trim($monto . ' (' . $estado . ')') : $mesLabel($m);
                                    @endphp

                                    <td class="px-3 py-2 {{ $cellClasses }}" data-filter="{{ $exportText }}">

                                        {{-- Esto es lo que DataTables exporta --}}
                                        <span class="hidden export-data">{{ $exportText }}</span>

                                        {{-- Esto es lo que se muestra en la vista --}}
                                        <div class="leading-tight">
                                            <a href="{{ route('deudas.detalle', [
                                                'tipo' => $tipo,
                                                'empresa' => $f['id'],
                                                'anio' => $anio,
                                                'mes' => $m,
                                            ]) }}"
                                                target="_blank" rel="noopener noreferrer"
                                                class="font-semibold hover:text-blue-600 hover:underline">
                                                {!! $monto !!}
                                            </a>
                                            @if ($hasDeuda)
                                                <span class="block text-[11px] mt-0.5 {{ $estadoColor }}">
                                                    {{ $estado }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                @endfor
                                @php
                                    $totalFila = 0;
                                    for ($m = 1; $m <= 12; $m++) {
                                        $totalFila += $f['meses'][$m]['total'] ?? 0;
                                    }
                                @endphp

                                <td class="!px-5 !py-5 cell-total text-blue-600">
                                    S/. {{ number_format($totalFila, 0, '.', ',') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal: Crear Deuda (Contabilidad) --}}
    <x-modal-show id="modal-crear-deuda" title="Registrar deuda ({{ ucfirst($tipo) }})">

        <form method="POST" action="{{ route('deudas.store') }}" class="space-y-6" autocomplete="off">
            @csrf
            <input type="hidden" name="id_empresa_cliente" id="md-id-empresa">

            <div>
                <x-input-label :value="__('Empresa')" />
                <x-text-input id="md-razon" type="text" class="mt-1 block w-full" disabled />
            </div>

            <div>
                <x-input-label :value="__('Tributo a pagar')" />
                <select name="id_tributo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    @foreach ($tributos as $t)
                        <option value="{{ $t->id_tributo }}">{{ $t->nombre }} ({{ $t->codigo }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <x-input-label :value="__('Monto (entero)')" />
                    <x-text-input name="monto_deuda" type="number" step="1" min="0"
                        class="mt-1 block w-full" required />
                </div>
                <div>
                    <x-input-label :value="__('Fecha de vencimiento')" />
                    <x-text-input name="fecha_vencimiento" type="date" class="mt-1 block w-full" readonly required />
                    <small class="text-gray-500">* El interés corre desde el día siguiente si no se paga a
                        tiempo.</small>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <x-primary-button class="px-6 py-2">
                    {{ __('Crear deuda') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal-show>

</x-app-layout>

<x-modal-show id="modal-pdf" title="Generar reporte de Tributos">
    <form method="GET" action="{{ route('tributos.generar') }}" target="_blank">
        <input type="hidden" name="id_empresa_cliente" id="pdf-id-empresa">
        <input type="hidden" name="anio" value="{{ $anio }}">

        <div>
            <x-input-label :value="__('Empresa')" />
            <x-text-input id="pdf-razon" type="text" class="mt-1 block w-full" disabled />
        </div>

        <div>
            <x-input-label :value="__('Período hasta')" />
            <select name="mes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                        {{ strtoupper(\Carbon\Carbon::create()->month($m)->locale('es')->monthName) }}
                        {{ $anio }}
                    </option>
                @endfor
            </select>
            <small class="text-gray-500">* Se incluirán las deudas que vence hasta el mes siguiente al período
                seleccionado.</small>
        </div>

        <div>
            <x-input-label :value="__('Tributos')" />
            <select name="tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="recursos" {{ $tipo == 'recursos' ? 'selected' : '' }}>Recursos</option>
                <option value="contabilidad" {{ $tipo == 'contabilidad' ? 'selected' : '' }}>Contabilidad</option>
                <option value="ambas">Ambas</option>
            </select>
        </div>

        <div class="mt-3">
            <x-input-label :value="__('Estado de deuda')" />
            <select name="estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="ambos">Ambos</option>
                <option value="pendientes">Pendientes</option>
                <option value="pagados">Pagados</option>
            </select>
        </div>

        <div class="flex justify-end mt-6">
            <x-primary-button class="px-6 py-2">Generar PDF</x-primary-button>
        </div>
    </form>
</x-modal-show>

<script>
    function abrirModalPDF(id, razon) {
        document.getElementById('pdf-id-empresa').value = id;
        document.getElementById('pdf-razon').value = razon;
        toggleModal('modal-pdf');
    }
</script>

<script>
    function abrirModalDeuda(anchorEl, id, razon, vencimiento) {
        // pinta de azul definitivo el nombre en esa fila
        anchorEl.classList.add('text-blue-600');

        document.getElementById('md-id-empresa').value = id;
        document.getElementById('md-razon').value = razon;

        const fechaInput = document.querySelector('input[name="fecha_vencimiento"]');
        if (fechaInput) fechaInput.value = vencimiento ?? '';

        toggleModal('modal-crear-deuda');
    }

    $(document).ready(function() {
        const tipo = '{{ $tipo }}'; // 'contabilidad' o 'recursos'
        const anio = '{{ $anio }}';

        // Generar timestamp único (ej: 20251016_114503)
        const ahora = new Date();
        const pad = (num) => String(num).padStart(2, '0');
        const fechaNum =
            `${ahora.getFullYear()}${pad(ahora.getMonth() + 1)}${pad(ahora.getDate())}_${pad(ahora.getHours())}${pad(ahora.getMinutes())}${pad(ahora.getSeconds())}`;

        // Título del archivo exportado
        const tituloArchivo = `DEUDAS_${anio}_${tipo.toUpperCase()}_${fechaNum}`;

        $('#myTable-deudas').DataTable({
            responsive: false,
            paging: true,
            ordering: true,
            pageLength: 200,
            lengthMenu: [10, 25, 50, 100, 200],
            /*dom: 'Bfrtip',
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: tituloArchivo,
                exportOptions: {
                    //columns: ':not(:last)',
                    format: {
                        body: function(data, row, column, node) {
                            // Si hay texto oculto .export-data, úsalo
                            const exportEl = node.querySelector('.export-data');
                            let text = exportEl ? exportEl.textContent : data;

                            // Eliminar etiquetas HTML residuales
                            return text.replace(/<[^>]*>?/gm, '').trim();
                        }
                    }
                }
            }], */
            language: {
                url: "/js/datatables/lang/es-ES.json"
            }
        });
    });
</script>

@if ($tipo === 'recursos' || $tipo === 'contabilidad')
    {{-- Modal: Buscar Deudas --}}
    <x-modal-show id="modal-buscar-deudas-{{ $tipo }}"
        title="Buscar Deudas de {{ $tipo === 'recursos' ? 'Recursos' : 'Contabilidad' }}" size="full">
        <div class="space-y-6">
            <!-- Filtros -->
            <div class="grid md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div>
                    <x-input-label :value="__('Empresa')" />
                    <select id="search_id_empresa"
                        class="select-empresa mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Todas las empresas</option>
                        @foreach ($filas as $f)
                            <option value="{{ $f['id'] }}">{{ $f['razon'] }} - {{ $f['ruc'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label :value="__('Desde el Periodo')" />
                    <input type="month" id="search_fecha_desde"
                        class="cursor-pointer mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <x-input-label :value="__('Hasta el periodo')" />
                    <input type="month" id="search_fecha_hasta"
                        class="cursor-pointer mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <x-input-label :value="__('Estado')" />
                    <select id="search_estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="ambas">Ambas (Pendientes y Pagadas)</option>
                        <option value="pendientes">Solo Pendientes</option>
                        <option value="pagadas">Solo Pagadas</option>
                    </select>
                </div>
            </div>

            <!-- Contenedor de Resultados -->
            <div class="overflow-x-auto max-h-[400px] border border-gray-200 rounded-lg hidden"
                id="wrapper_search_results">
                <table class="min-w-full divide-y divide-gray-200 text-sm" id="table_search_results">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Empresa</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                RUC</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Tributo</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Período</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Vencimiento</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Últ. Pago</th>
                            <th
                                class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Plazo</th>
                            <th
                                class="px-4 py-2 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Monto</th>
                            <th
                                class="px-4 py-2 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Int. Pend.</th>
                            <th
                                class="px-4 py-2 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Total/Saldo</th>
                            <th
                                class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Estado</th>
                            <th
                                class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="tbody_search_results">
                        <!-- Filas dinámicas -->
                    </tbody>
                </table>
            </div>

            <!-- Mensaje de Carga / Sin resultados -->
            <div id="search_results_message" class="text-center py-8 text-gray-500 italic hidden">
                <!-- Mensaje dinámico -->
            </div>
        </div>
    </x-modal-show>

    <script>
        $(document).ready(function() {
            // Inicializar Select2 para la búsqueda de empresas
            $('.select-empresa').select2({
                placeholder: 'Selecciona una empresa',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modal-buscar-deudas-{{ $tipo }}')
            });

            // Variable para almacenar la petición AJAX actual y evitar condiciones de carrera (race conditions)
            let buscarDeudasAjax = null;

            function ejecutarBusquedaDeudas() {
                // Abortar la petición previa si aún está en curso
                if (buscarDeudasAjax) {
                    buscarDeudasAjax.abort();
                }

                // Destruir instancia previa de DataTable si existe
                if ($.fn.DataTable.isDataTable('#table_search_results')) {
                    $('#table_search_results').DataTable().destroy();
                }

                const idEmpresa = $('#search_id_empresa').val();
                const fechaDesde = $('#search_fecha_desde').val();
                const fechaHasta = $('#search_fecha_hasta').val();
                const estado = $('#search_estado').val();

                const container = $('#wrapper_search_results');
                const tbody = $('#tbody_search_results');
                const messageDiv = $('#search_results_message');

                container.addClass('hidden');
                tbody.empty();
                messageDiv.removeClass('hidden').html(
                    '<p class="flex items-center justify-center gap-2"><i class="bi bi-arrow-repeat animate-spin text-lg text-blue-600"></i> Buscando deudas de {{ $tipo === 'recursos' ? 'recursos' : 'contabilidad' }}...</p>'
                );

                buscarDeudasAjax = $.ajax({
                    url: "{{ route('deudas.buscar_ajax', ['tipo' => $tipo]) }}",
                    method: 'GET',
                    data: {
                        id_empresa: idEmpresa,
                        fecha_desde: fechaDesde,
                        fecha_hasta: fechaHasta,
                        estado: estado
                    },
                    success: function(res) {
                        messageDiv.addClass('hidden');
                        if (!res.success || res.rows.length === 0) {
                            messageDiv.removeClass('hidden').text(
                                'No se encontraron deudas con los filtros seleccionados.');
                            return;
                        }

                        res.rows.forEach(function(row) {
                            let badgeColor = 'bg-gray-100 text-gray-800';
                            if (row.estado === 'Pagado') {
                                badgeColor = 'bg-green-100 text-green-800';
                            } else if (row.estado === 'Pendiente') {
                                badgeColor = 'bg-red-100 text-red-800';
                            } else if (row.estado === 'Sin movimiento') {
                                badgeColor = 'bg-blue-100 text-blue-800';
                            }

                            let plazoBadge = 'bg-gray-100 text-gray-800';
                            if (row.plazo === 'Dentro') {
                                plazoBadge = 'bg-emerald-100 text-emerald-800';
                            } else if (row.plazo === 'Fuera') {
                                plazoBadge = 'bg-rose-100 text-rose-800 font-bold';
                            }

                            const tr = `
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-2 font-medium text-gray-800">${row.empresa}</td>
                                    <td class="px-4 py-2 text-gray-500">${row.ruc}</td>
                                    <td class="px-4 py-2 text-gray-700">${row.tributo}</td>
                                    <td class="px-4 py-2 text-xs font-semibold text-indigo-600">${row.periodo}</td>
                                    <td class="px-4 py-2 text-gray-600">${row.fecha_vcto}</td>
                                    <td class="px-4 py-2 text-gray-600">${row.fecha_pago_ult}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${plazoBadge}">
                                            ${row.plazo}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-right font-semibold text-gray-700">S/. ${parseFloat(row.monto_deuda).toLocaleString('es-PE', {minimumFractionDigits: 2})}</td>
                                    <td class="px-4 py-2 text-right text-gray-500">S/. ${parseFloat(row.interes_pendiente).toLocaleString('es-PE', {minimumFractionDigits: 2})}</td>
                                    <td class="px-4 py-2 text-right font-bold text-gray-900">S/. ${parseFloat(row.saldo).toLocaleString('es-PE', {minimumFractionDigits: 2})}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${badgeColor}">
                                            ${row.estado}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <a href="${row.url_detalle}" target="_blank"
                                            class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 hover:underline text-xs font-semibold">
                                            <i class="bi bi-eye-fill"></i> Ver Detalle
                                        </a>
                                    </td>
                                </tr>
                            `;
                            tbody.append(tr);
                        });

                        container.removeClass('hidden');

                        // Por seguridad adicional, verificar si se volvió a inicializar antes de proceder
                        if ($.fn.DataTable.isDataTable('#table_search_results')) {
                            $('#table_search_results').DataTable().destroy();
                        }

                        // Inicializar DataTables para los resultados del modal
                        $('#table_search_results').DataTable({
                            responsive: false,
                            paging: true,
                            ordering: true,
                            pageLength: 50,
                            lengthMenu: [10, 25, 50, 100],
                            dom: 'Bfrtip',
                            buttons: [{
                                extend: 'excel',
                                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Descargar Excel',
                                className: 'bg-green-800 hover:bg-green-900 text-white px-4 py-2 rounded-md transition duration-300 shadow-md font-semibold mb-3 border-0',
                                title: function() {
                                    const hoy = new Date();
                                    const dia = hoy.getDate().toString().padStart(2,
                                        '0');
                                    const mes = (hoy.getMonth() + 1).toString()
                                        .padStart(2, '0');
                                    const anio = hoy.getFullYear();
                                    return `BUSQUEDA_DEUDAS_{{ strtoupper($tipo) }}_${dia}_${mes}_${anio}`;
                                },
                                exportOptions: {
                                    columns: ':not(:last)', // Excluye la última columna (Acción)
                                    format: {
                                        body: function(data, row, column, node) {
                                            if (typeof data === 'string') {
                                                // Primero, remover cualquier etiqueta HTML (ej: para spans de Plazo y Estado)
                                                let text = data.replace(
                                                    /<[^>]*>?/gm, '').trim();

                                                // Decodificar entidades HTML como &amp; -> &
                                                let txt = document.createElement(
                                                    'textarea');
                                                txt.innerHTML = text;
                                                text = txt.value;

                                                // Si es un monto monetario, remover S/. y comas para dejarlo numérico
                                                if (text.includes('S/.')) {
                                                    let clean = text.replace(
                                                            /S\/\.\s?/g, '')
                                                        .replace(/,/g, '').trim();
                                                    return isNaN(clean) || clean ===
                                                        '' ? text : parseFloat(
                                                            clean);
                                                }
                                                return text;
                                            }
                                            return data;
                                        }
                                    }
                                }
                            }],
                            language: {
                                url: "/js/datatables/lang/es-ES.json"
                            }
                        });
                        // Inicializar filtro tipo Excel
                        $('#table_search_results').initExcelFilter();
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        if (textStatus === 'abort') {
                            return; // Ignorar si fue cancelado intencionalmente
                        }
                        messageDiv.removeClass('hidden').text(
                            'Ocurrió un error al realizar la búsqueda.');
                    }
                });
            }

            // Escuchar cualquier cambio en los filtros para recargar automáticamente
            $('#search_id_empresa, #search_fecha_desde, #search_fecha_hasta, #search_estado').on('change',
                function() {
                    ejecutarBusquedaDeudas();
                });

            // Cargar datos inicialmente al abrir el modal o cargar la página
            ejecutarBusquedaDeudas();
        });
    </script>
@endif
