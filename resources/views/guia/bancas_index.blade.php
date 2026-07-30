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
            {{ __('Guías – Bancas | ' . $nombreMesActual . ' ' . $anio) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">

            <x-success-error-bagdes />

            {{-- Filtros mes / año --}}
            <div class="bg-gray-100 p-4 rounded-lg shadow-md mb-6 flex flex-wrap items-end gap-4">
                <form method="GET" action="{{ route('guias.bancas') }}" class="flex flex-wrap items-end gap-4">

                    {{-- Filtro Mes --}}
                    <div class="flex flex-col">
                        <label for="mes" class="text-sm font-semibold text-gray-700 mb-1">Mes</label>
                        <select name="mes" id="mes" onchange="this.form.submit()"
                            class="border-gray-300 rounded-md shadow-sm w-36 focus:ring-blue-500 focus:border-blue-500">
                            @foreach ($meses_es as $numero => $nombre)
                                <option value="{{ $numero }}" {{ $mes == $numero ? 'selected' : '' }}>
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro Año --}}
                    <div class="flex flex-col">
                        <label for="anio" class="text-sm font-semibold text-gray-700 mb-1">Año</label>
                        <select name="anio" id="anio" onchange="this.form.submit()"
                            class="border-gray-300 rounded-md shadow-sm w-32 focus:ring-blue-500 focus:border-blue-500">
                            @for ($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}" {{ $anio == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- Botón Exportar Guías Excel --}}
                    <div class="w-full md:w-auto">
                        <button type="button" onclick="toggleModal('modalExportarGuias')"
                            class="flex items-center justify-center gap-2 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition w-full md:w-auto">
                            <i class="bi bi-file-earmark-excel"></i> Exportar
                        </button>
                    </div>

                </form>
            </div>

            {{-- Tabla principal --}}
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto" id="myTable-bancas-guias">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-3 border-b font-semibold text-gray-700">Contacto</th>
                            <th class="px-4 py-3 border-b font-semibold text-gray-700">RUC</th>
                            <th class="px-4 py-3 border-b font-semibold text-gray-700">Razón Social</th>
                            <th class="px-4 py-3 border-b font-semibold text-gray-700 text-right">Monto</th>
                            <th class="px-4 py-3 border-b font-semibold text-gray-700 text-center">Bancarización</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resumen as $key => $item)
                            <tr class="hover:bg-gray-50">

                                {{-- Contacto --}}
                                <td class="px-4 py-2 border-b text-gray-700">
                                    {{ $item['contacto'] ?: '-' }}
                                </td>

                                {{-- RUC --}}
                                <td class="px-4 py-2 border-b text-gray-700">
                                    {{ $item['ruc'] }}
                                </td>

                                {{-- Razón Social con link a abonos/facturas --}}
                                <td class="px-4 py-2 border-b">
                                    @php
                                        if ($item['bancarizacion'] === 'si') {
                                            $colorClass = 'text-green-600 hover:text-green-700';
                                        } else {
                                            $colorClass = 'text-blue-600 hover:text-blue-800';
                                        }
                                    @endphp
                                    <a href="{{ route('abono_facturas.index', [
                                            'buscar' => $item['ruc'],
                                            'desde'  => $anio . '-' . $mes,
                                            'hasta'  => $anio . '-' . $mes,
                                        ]) }}"
                                        class="{{ $colorClass }} hover:underline font-medium"
                                        title="Ver abonos/facturas de {{ $item['razon_social'] }}">
                                        {{ $item['razon_social'] }}
                                    </a>
                                </td>

                                {{-- Monto --}}
                                <td class="px-4 py-2 border-b text-right font-medium text-gray-800">
                                    {{ $item['simbolo'] }} {{ number_format($item['monto'], 2) }}
                                </td>

                                {{-- Bancarización --}}
                                <td class="px-4 py-2 border-b">
                                    <div class="flex flex-col items-center">
                                        <span class="flex justify-center items-center px-4 py-1 text-white rounded-full w-24
                                            {{ $item['bancarizacion'] === 'si' ? 'bg-green-500' : ($item['bancarizacion'] === 'no' ? 'bg-red-500' : 'bg-gray-500') }}">
                                            {{ $item['bancarizacion'] === 'si' ? 'Si' : ($item['bancarizacion'] === 'no' ? 'No' : '-') }}
                                        </span>

                                        @php
                                            $pendienteAbono = max(0, $item['monto_facturado'] - $item['monto_abonado']);
                                        @endphp

                                        @if ($pendienteAbono > 0)
                                            <span class="text-xs text-red-600 font-semibold mt-1">
                                                {{ $item['simbolo'] }} {{ number_format($pendienteAbono, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                    @if (count($resumen) > 0)
                        <tfoot class="bg-gray-100 font-semibold">
                            {{-- Total Soles --}}
                            @php
                                $totalPEN = collect($resumen)->where('moneda', 'PEN')->sum('monto');
                                $totalUSD = collect($resumen)->where('moneda', 'USD')->sum('monto');
                            @endphp
                            @if ($totalPEN > 0)
                                <tr>
                                    <td colspan="3" class="px-4 py-2 border-t text-right">Total (S/):</td>
                                    <td class="px-4 py-2 border-t text-right">S/ {{ number_format($totalPEN, 2) }}</td>
                                    <td class="px-4 py-2 border-t"></td>
                                </tr>
                            @endif
                            @if ($totalUSD > 0)
                                <tr>
                                    <td colspan="3" class="px-4 py-2 border-t text-right">Total ($):</td>
                                    <td class="px-4 py-2 border-t text-right">$ {{ number_format($totalUSD, 2) }}</td>
                                    <td class="px-4 py-2 border-t"></td>
                                </tr>
                            @endif
                        </tfoot>
                    @endif
                </table>
            </div>

        </div>
    </div>
</x-app-layout>

<script>
    $(document).ready(function () {
        $('#myTable-bancas-guias').DataTable({
            responsive: false,
            paging: true,
            ordering: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: {
                url: '/js/datatables/lang/es-ES.json'
            }
        });
    });

    // Código para el modal Exportar Guías Excel
    document.addEventListener('DOMContentLoaded', function() {
        const empresasExport = @json($empresas_export ?? []);
        const clientesExport = @json($clientes_export ?? []);
        
        const tipoEntidadRadios = document.querySelectorAll('input[name="tipo_entidad"]');
        const idEntidadSelect = $('#id_entidad'); 
        
        if ($.fn.select2) {
            idEntidadSelect.select2({
                dropdownParent: $('#modalExportarGuias'),
                width: '100%'
            });
        }

        tipoEntidadRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const tipo = this.value;
                idEntidadSelect.empty().append('<option value="" disabled selected>Seleccione la entidad</option>');
                
                let data = [];
                if (tipo === 'empresa') {
                    data = empresasExport;
                } else if (tipo === 'cliente') {
                    data = clientesExport;
                }

                data.forEach(item => {
                    const id = tipo === 'empresa' ? item.id_empresas_clientes : item.id_cliente;
                    const ruc = item.ruc ? item.ruc + ' - ' : '';
                    const text = ruc + item.razon_social;
                    const option = new Option(text, id, false, false);
                    idEntidadSelect.append(option);
                });

                idEntidadSelect.prop('disabled', false).trigger('change');
            });
        });

        const currentViewMonth = "{{ str_pad($mes ?? date('m'), 2, '0', STR_PAD_LEFT) }}";
        const currentViewYear = "{{ $anio ?? date('Y') }}";
        const currentRealMonth = "{{ date('m') }}";
        const currentRealYear = "{{ date('Y') }}";
        
        if(document.getElementById('desde')) {
            document.getElementById('desde').value = `${currentViewYear}-${currentViewMonth}`;
            document.getElementById('hasta').value = `${currentRealYear}-${currentRealMonth}`;
        }
    });
</script>

<x-modal-show id="modalExportarGuias" title="Exportar Guías a Excel">
    <form action="{{ route('guias.exportar_excel') }}" method="POST" id="formExportarGuias">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-800">
            <!-- Tipo de Entidad -->
            <div class="flex flex-col md:col-span-2">
                <label class="text-sm font-semibold text-gray-700 mb-2">Tipo de Entidad <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tipo_entidad" value="empresa" class="form-radio text-indigo-600 focus:ring-indigo-500" required>
                        Empresa Cliente
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tipo_entidad" value="cliente" class="form-radio text-indigo-600 focus:ring-indigo-500" required>
                        Cliente Auditoría
                    </label>
                </div>
            </div>

            <!-- Entidad (Select2) -->
            <div class="flex flex-col md:col-span-2">
                <label for="id_entidad" class="text-sm font-semibold text-gray-700 mb-1">Entidad <span class="text-red-500">*</span></label>
                <select name="id_entidad" id="id_entidad" class="select2 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required disabled>
                    <option value="" disabled selected>Seleccione la entidad</option>
                </select>
            </div>

            <!-- Desde -->
            <div class="flex flex-col">
                <label for="desde" class="text-sm font-semibold text-gray-700 mb-1">Desde</label>
                <input type="month" name="desde" id="desde" class="border-gray-300 rounded-md shadow-sm w-full focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <!-- Hasta -->
            <div class="flex flex-col">
                <label for="hasta" class="text-sm font-semibold text-gray-700 mb-1">Hasta</label>
                <input type="month" name="hasta" id="hasta" class="border-gray-300 rounded-md shadow-sm w-full focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-md transition-colors">
                <i class="bi bi-file-earmark-excel-fill"></i> Exportar Excel
            </button>
        </div>
    </form>
</x-modal-show>
