<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Formato N° Cotizaciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <div class="mb-6 flex flex-wrap gap-4">
                <a href="{{ route('formato_cotizaciones.create') }}"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition duration-300 flex items-center gap-2 shadow-sm">
                    <i class="bi bi-plus-circle"></i> Crear Formato
                </a>
                
                <button type="button" onclick="toggleModal('modal-organizar-cotizaciones')"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-md transition duration-300 flex items-center gap-2 shadow-sm">
                    <i class="bi bi-filter-circle"></i> Organizar por Empresa
                </button>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto text-sm" id="myTable-formato-cotizaciones">
                        <thead>
                            <tr class="bg-gray-50 text-left text-gray-700">
                                <th class="px-4 py-2 border-b">RUC Emisor</th>
                                <th class="px-4 py-2 border-b">Razon Social Emisor</th>
                                <th class="px-4 py-2 border-b">RUC Receptor</th>
                                <th class="px-4 py-2 border-b">Razon Social Receptor</th>
                                <th class="px-4 py-2 border-b">Formato</th>
                                {{-- <th class="px-4 py-2 border-b">Contador</th> --}}
                                <th class="px-4 py-2 border-b">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($formatos as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        {{ $item->empresaEmisora->ruc ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">
                                        {{ $item->empresaEmisora->razon_social ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $item->empresa->ruc ?? ($item->cliente->ruc ?? 'N/A') }}</td>
                                    <td class="px-4 py-2">
                                        {{ $item->empresa->razon_social ?? ($item->cliente->razon_social ?? 'N/A') }}
                                    </td>
                                    <td class="px-4 py-2">{{ $item->generarCadenaFormateada() ?? 'N/A' }}
                                    </td>
                                    {{-- <td class="px-4 py-2 text-gray-500">{{ $item->contador_actual ?? '0' }}</td> --}}
                                    <td class="px-4 py-2 flex gap-2">
                                        <a href="{{ route('formato_cotizaciones.edit', $item->id_formato_cotizaciones) }}"
                                            class="flex items-center py-1 rounded-md tooltip" style="color: #F59E0B;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';">
                                            <span class="tooltip-text">Editar</span> <x-edit-icon />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-modal-show id="modal-historial-formato" title="Historial de Formatos">
    <div id="pagos-contenido" class="space-y-4">
        <p class="text-center text-gray-400 italic">No hay Formatos registrados</p>
    </div>

    <div class="flex justify-end mt-4">
        <x-secondary-button onclick="toggleModal('modal-historial-formato')">
            Cerrar
        </x-secondary-button>
    </div>
</x-modal-show>

<x-modal-show id="modal-organizar-cotizaciones" title="Organizar Cotizaciones por Empresa" size="large">
    <div class="space-y-6">
        <!-- Selector de Tipo -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <span class="font-semibold text-gray-700 dark:text-gray-300"><i class="bi bi-funnel"></i> Buscar por:</span>
            <div class="flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="radio" name="tipo_empresa" value="receptor" checked class="form-radio text-indigo-600 focus:ring-indigo-500 w-5 h-5 transition-transform group-hover:scale-110">
                    <span class="text-gray-700 dark:text-gray-300 font-medium">Receptor (Cliente)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="radio" name="tipo_empresa" value="emisor" class="form-radio text-indigo-600 focus:ring-indigo-500 w-5 h-5 transition-transform group-hover:scale-110">
                    <span class="text-gray-700 dark:text-gray-300 font-medium">Emisor (Proveedor)</span>
                </label>
            </div>
        </div>

        <!-- Selector de Empresa (Select2) -->
        <div class="w-full relative z-50">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Seleccione la Empresa</label>
            <select id="empresa_select2" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 select2-interactive">
                <!-- Opciones dinámicas -->
            </select>
        </div>

        <!-- Resultados -->
        <div class="mt-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3 flex items-center gap-2" id="titulo_resultados">
                <i class="bi bi-list-check"></i> Cotizaciones Registradas
            </h3>
            <div id="resultados_container" class="border rounded-lg border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900 shadow-inner min-h-[150px]">
                <p class="text-center text-gray-500 dark:text-gray-400 py-8 flex flex-col items-center gap-2">
                    <i class="bi bi-building text-3xl text-gray-300 dark:text-gray-600"></i>
                    <span>Seleccione una empresa para ver con quién tiene formatos.</span>
                </p>
            </div>
        </div>
    </div>

    <div class="flex justify-end mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <x-secondary-button onclick="toggleModal('modal-organizar-cotizaciones')">
            Cerrar
        </x-secondary-button>
    </div>
</x-modal-show>

<script>
    $(document).ready(function() {
        $('#myTable-formato-cotizaciones').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            responsive: false,
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: function() {
                    const hoy = new Date();
                    const dia = hoy.getDate().toString().padStart(2, '0');
                    const mes = (hoy.getMonth() + 1).toString().padStart(2, '0');
                    const anio = hoy.getFullYear();
                    return `TODAS_LAS_EMPRESAS_COTIZACIONES_RECEPCION_SISTEMA_ASECON_DESCARGADO_${dia}_${mes}_${anio}`;
                },
                exportOptions: {
                    columns: ':not(:last)'
                }
            }],
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            }
        });

        // ==========================================
        // Lógica para Organizar Cotizaciones en Modal
        // ==========================================
        @php
            $formatosMapped = $formatos->map(function($item) {
                return [
                    'emisor_ruc' => $item->empresaEmisora->ruc ?? 'N/A',
                    'emisor_nombre' => $item->empresaEmisora->razon_social ?? 'N/A',
                    'receptor_ruc' => $item->empresa->ruc ?? ($item->cliente->ruc ?? 'N/A'),
                    'receptor_nombre' => $item->empresa->razon_social ?? ($item->cliente->razon_social ?? 'N/A'),
                    'formato' => $item->generarCadenaFormateada() ?? 'N/A',
                ];
            });
        @endphp
        const formatosData = @json($formatosMapped);

        const $tipoSelect = $('input[name="tipo_empresa"]');
        const $empresaSelect = $('#empresa_select2');
        const $resultadosContainer = $('#resultados_container');

        // Inicializar Select2 en el modal
        $empresaSelect.select2({
            dropdownParent: $('#modal-organizar-cotizaciones'),
            width: '100%',
            placeholder: 'Seleccione una empresa...',
            allowClear: true
        });

        function updateSelect2() {
            const tipo = $('input[name="tipo_empresa"]:checked').val();
            let uniqueEmpresas = new Map();

            formatosData.forEach(item => {
                if (tipo === 'receptor') {
                    if (item.receptor_ruc !== 'N/A' || item.receptor_nombre !== 'N/A') {
                        uniqueEmpresas.set(item.receptor_ruc + ' - ' + item.receptor_nombre, { ruc: item.receptor_ruc, nombre: item.receptor_nombre });
                    }
                } else {
                    if (item.emisor_ruc !== 'N/A' || item.emisor_nombre !== 'N/A') {
                        uniqueEmpresas.set(item.emisor_ruc + ' - ' + item.emisor_nombre, { ruc: item.emisor_ruc, nombre: item.emisor_nombre });
                    }
                }
            });

            // Ordenar alfabéticamente
            const sortedEmpresas = Array.from(uniqueEmpresas.entries()).sort((a, b) => a[0].localeCompare(b[0]));

            // Llenar select2
            $empresaSelect.empty();
            $empresaSelect.append('<option value=""></option>'); // Necesario para allowClear
            
            sortedEmpresas.forEach(([key, val]) => {
                const uniqueId = val.ruc + '||' + val.nombre;
                $empresaSelect.append(new Option(key, uniqueId));
            });
            
            $empresaSelect.trigger('change');
        }

        function updateResults() {
            const tipo = $('input[name="tipo_empresa"]:checked').val();
            const selectedVal = $empresaSelect.val();
            
            $resultadosContainer.empty();

            if (!selectedVal) {
                $('#titulo_resultados').html('<i class="bi bi-list-check"></i> Cotizaciones Registradas');
                $resultadosContainer.html(`
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8 flex flex-col items-center gap-2">
                        <i class="bi bi-building text-3xl text-gray-300 dark:text-gray-600"></i>
                        <span>Seleccione una empresa para ver con quién tiene formatos.</span>
                    </p>
                `);
                return;
            }

            const [selectedRuc, selectedNombre] = selectedVal.split('||');

            if (tipo === 'receptor') {
                $('#titulo_resultados').html(`<i class="bi bi-building-down"></i> Emisores que cotizan a <strong>${selectedNombre}</strong>`);
            } else {
                $('#titulo_resultados').html(`<i class="bi bi-building-up"></i> Receptores a los que <strong>${selectedNombre}</strong> cotiza`);
            }

            let results = formatosData.filter(item => {
                if (tipo === 'receptor') {
                    return item.receptor_ruc === selectedRuc && item.receptor_nombre === selectedNombre;
                } else {
                    return item.emisor_ruc === selectedRuc && item.emisor_nombre === selectedNombre;
                }
            });

            if (results.length === 0) {
                $resultadosContainer.html(`
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8 flex flex-col items-center gap-2">
                        <i class="bi bi-emoji-frown text-3xl text-gray-300 dark:text-gray-600"></i>
                        <span>No se encontraron formatos registrados.</span>
                    </p>
                `);
                return;
            }

            let tableTitleRuc = tipo === 'receptor' ? 'RUC Emisor' : 'RUC Receptor';
            let tableTitleNombre = tipo === 'receptor' ? 'Razón Social Emisor' : 'Razón Social Receptor';

            let html = '<div class="overflow-x-auto"><table class="min-w-full table-auto text-sm border-collapse">';
            html += '<thead class="bg-indigo-50 dark:bg-gray-800/50">';
            html += '<tr class="text-left text-gray-700 dark:text-gray-300">';
            html += '<th class="px-4 py-3 border-b dark:border-gray-700 font-semibold">' + tableTitleRuc + '</th>';
            html += '<th class="px-4 py-3 border-b dark:border-gray-700 font-semibold">' + tableTitleNombre + '</th>';
            html += '<th class="px-4 py-3 border-b dark:border-gray-700 font-semibold text-center">Formato</th>';
            html += '</tr></thead><tbody class="divide-y divide-gray-200 dark:divide-gray-700">';

            results.forEach(item => {
                let ruc = tipo === 'receptor' ? item.emisor_ruc : item.receptor_ruc;
                let nombre = tipo === 'receptor' ? item.emisor_nombre : item.receptor_nombre;
                
                html += '<tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200">';
                html += '<td class="px-4 py-3 text-gray-800 dark:text-gray-200">' + ruc + '</td>';
                html += '<td class="px-4 py-3 text-gray-800 dark:text-gray-200 font-medium">' + nombre + '</td>';
                html += '<td class="px-4 py-3 text-center">';
                html += '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-800">';
                html += '<i class="bi bi-file-earmark-text mr-1"></i> ' + item.formato;
                html += '</span></td>';
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            $resultadosContainer.html(html);
        }

        $tipoSelect.on('change', updateSelect2);
        $empresaSelect.on('change', updateResults);

        // Disparar inicialmente
        updateSelect2();
    });
</script>
