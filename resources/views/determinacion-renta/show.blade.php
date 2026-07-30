<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Determinación de Renta para el Año: ') . $preLiquidacion->anio }}
        </h2>
    </x-slot>
    {{-- !NO FORMATEAR EL CODIGO QUE SE MALOGRA EL FORMATO A LA HORA DE EXPORTAR COMO EXCEL --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Volver -->
            <div class="mb-4 flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('determinacion-renta.index') }}"
                    class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white">
                    <i class="bi bi-arrow-left-circle"></i> Volver
                </a>

                <a href="{{ route('pre-liquidacion.edit', ['id' => $determinacionRenta->preLiquidacion->id_pre_liquidacion]) }}"
                    class="flex items-center gap-2 bg-yellow-500 px-4 py-2 rounded-md hover:bg-yellow-600 transition duration-300 text-white">
                    <i class="bi bi-pencil-square"></i> Editar Pre-liquidación
                </a>

                <a href="{{ route('determinacion-renta.edit', $determinacionRenta->id_determinacion) }}"
                    class="flex items-center gap-2 bg-yellow-500 px-4 py-2 rounded-md hover:bg-yellow-600 transition duration-300 text-white">
                    <i class="bi bi-calculator-fill"></i> Editar Impuesto a la Renta
                </a>

                <a href="javascript:void(0)"
                    onclick="abrirModalPDFRenta('{{ $preLiquidacion->id_pre_liquidacion }}', '{{ addslashes($preLiquidacion->empresaCliente->razon_social) }}', '{{ $preLiquidacion->anio }}')"
                    class="flex items-center gap-2 bg-red-600 px-4 py-2 rounded-md hover:bg-red-700 transition duration-300 text-white">
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF Renta Anual
                </a>
            </div>

            <!-- Tabla combinada -->
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto" id="myTable-liqui-deter">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b"></th>
                            <th class="px-4 py-2 border-b"></th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td class="text-lg font-semibold text-gray-800">
                                {{ __('RAZÓN SOCIAL:') }} <span
                                    class="text-blue-500">{{ $preLiquidacion->empresaCliente->razon_social }}</span>
                            </td>
                            <td>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-lg font-semibold text-gray-800">
                                {{ __('RUC: ') }} <span
                                    class="text-blue-500">{{ $preLiquidacion->empresaCliente->ruc }}</span>
                            </td>
                            <td>
                            </td>
                        </tr>

                        <!-- Encabezado de la sección PRE-LIQUIDACION RENTA ANUAL -->
                        <tr class="bg-blue-100">
                            <td class="px-4 py-2 font-bold text-center">
                                PRE-LIQUIDACION RENTA ANUAL {{ $preLiquidacion->anio }}
                            </td>
                            <td>
                            </td>
                        </tr>

                        <!-- Datos de Pre-Liquidación -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Ventas Netas</td>
                            <td class="px-4 py-2 border-b">{{ number_format($preLiquidacion->ventas_netas, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Costo de Ventas</td>
                            <td class="px-4 py-2 border-b">{{ number_format($preLiquidacion->costo_ventas, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Resultado Bruto</td>
                            <td class="px-4 py-2 border-b">{{ number_format($preLiquidacion->resultado_bruto, 2) }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Gastos de Ventas</td>
                            <td class="px-4 py-2 border-b">{{ number_format($preLiquidacion->gastos_ventas, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Gastos de Administración</td>
                            <td class="px-4 py-2 border-b">
                                {{ number_format($preLiquidacion->gastos_administracion, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Depreciación</td>
                            <td class="px-4 py-2 border-b">{{ number_format($preLiquidacion->depreciacion, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Resultado de Operación</td>
                            <td class="px-4 py-2 border-b">{{ number_format($preLiquidacion->resultado_operacion, 2) }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Pérdida por Tipo de Cambio</td>
                            <td class="px-4 py-2 border-b">{{ number_format($preLiquidacion->perdida_tipo_cambio, 2) }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Intereses y Otros Gastos</td>
                            <td class="px-4 py-2 border-b">
                                {{ number_format($preLiquidacion->intereses_otros_gastos, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Total Gastos Financieros</td>
                            <td class="px-4 py-2 border-b">
                                {{ number_format($preLiquidacion->total_gastos_financieros, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Ingresos Financieros Gravados</td>
                            <td class="px-4 py-2 border-b">
                                {{ number_format($preLiquidacion->ingresos_financieros_gravados, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Ingresos Financieros por Tipo de Cambio</td>
                            <td class="px-4 py-2 border-b">
                                {{ number_format($preLiquidacion->ingresos_financieros_tipo_cambio, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Total Ingresos Financieros</td>
                            <td class="px-4 py-2 border-b">
                                {{ number_format($preLiquidacion->total_ingresos_financieros, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Otros Ingresos Gravados</td>
                            <td class="px-4 py-2 border-b">
                                {{ number_format($preLiquidacion->otros_ingresos_gravados, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">UTILIDAD ANTES DE IMPUESTOS</td>
                            <td class="px-4 py-2 border-b">{{ number_format($preLiquidacion->total, 2) }}</td>
                        </tr>

                        <!-- Encabezado de la sección DETERMINACION DE IMPUESTO A LA RENTA 3CA -->
                        <tr class="bg-blue-100">
                            <td class="px-4 py-2 font-bold text-center">
                                @if ($preLiquidacion->total > 0)
                                    DETERMINACION DE IMPUESTO A LA RENTA 3CA {{ $preLiquidacion->anio }}
                                @else
                                    DETERMINACION DE IMPUESTO A LA RENTA 3CA {{ $preLiquidacion->anio }}
                                @endif
                            </td>
                            <td>
                            </td>
                        </tr>
                        <!-- Datos de Determinación de Renta -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Utilidad Antes</td>
                            <td class="px-4 py-2 border-b">{{ number_format($determinacionRenta->utilidad_antes, 2) }}
                            </td>
                        </tr>
                        @if ($preLiquidacion->total > 0)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Depreciacion de Activo Fijo en Exceso </td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->adicion_depreciacion, 2) }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Gastos personales y de sustento del contribuyente y sus familiares
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->adicion_gastos_personales, 2) }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Multas, Recargos, Intereses moratorios y sanciones </td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->adicion_multas_cargos, 2) }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Gastos cuya documentacion sustentatoria no cumplan con el RCP
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->adicion_gastos_rcp, 2) }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Otros
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->adicion_otros, 2) }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Total Adiciones</td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->adicion_total, 2) }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Pago de vacaciones de ejercicios anteriores </td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->deduccion_vaciones, 2) }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Depreciación acelerada por contrato de leasing </td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->deduccion_depreciacion, 2) }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Otros
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->deduccion_otros, 2) }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Total Deducciones</td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->deduccion_total, 2) }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">UTILIDAD DEL EJERCICIO (Tributario)</td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->utilidad_tributaria, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Impuesto Hasta 15 UIT</td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->impuesto_hasta_15_uit, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Impuesto Exceso 15 UIT</td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->impuesto_exceso_15_uit, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">Impuesto Total</td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacionRenta->impuesto_total, 2) }}
                                </td>
                            </tr>
                        @endif
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Pagos a cuenta del ejercicio</td>
                            <td class="px-4 py-2 border-b">{{ number_format($determinacionRenta->pagos_cuenta, 2) }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Pagos a cuenta ITAN</td>
                            <td class="px-4 py-2 border-b">{{ number_format($determinacionRenta->pago_itan, 2) }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-semibold">Saldos No Aplicados</td>
                            <td class="px-4 py-2 border-b">
                                {{ number_format($determinacionRenta->saldos_no_aplicados, 2) }}</td>
                        </tr>
                        @if ($preLiquidacion->total > 0)
                            <tr class="hover:bg-gray-50">
                                {{-- <td class="px-4 py-2 border-b font-semibold">TOTAL RENTA ANUAL {{$preLiquidacion->anio}}</td> --}}
                                <td class="px-4 py-2 border-b font-semibold">TOTAL RENTA ANUAL {{ $preLiquidacion->anio }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format(round($determinacionRenta->total_por_pagar, 2), 0, '.', ',').'.00' }}</td>
                            </tr>
                        @else
                            <tr class="hover:bg-gray-50">
                                {{-- <td class="px-4 py-2 border-b font-semibold">SALDO A FAVOR RENTA ANUAL {{$preLiquidacion->anio}}</td> --}}
                                <td class="px-4 py-2 border-b font-semibold">SALDO A FAVOR RENTA ANUAL {{ $preLiquidacion->anio }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format(round($determinacionRenta->total_a_favor, 2), 0, '.', ',').'.00' }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('determinacion-renta.index') }}"
                    class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white">
                    <i class="bi bi-arrow-left-circle"></i> Volver
                </a>

                <a href="{{ route('pre-liquidacion.edit', ['id' => $determinacionRenta->preLiquidacion->id_pre_liquidacion]) }}"
                    class="flex items-center gap-2 bg-yellow-500 px-4 py-2 rounded-md hover:bg-yellow-600 transition duration-300 text-white">
                    <i class="bi bi-pencil-square"></i> Editar Pre-liquidación
                </a>

                <a href="{{ route('determinacion-renta.edit', $determinacionRenta->id_determinacion) }}"
                    class="flex items-center gap-2 bg-yellow-500 px-4 py-2 rounded-md hover:bg-yellow-600 transition duration-300 text-white">
                    <i class="bi bi-calculator-fill"></i> Editar Impuesto a la Renta
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- MODAL: Generar Reporte de Renta Anual --}}
<x-modal-show id="modal-pdf-renta" title="Generar Reporte de Renta Anual">
    <form method="GET" action="{{ route('renta-anual.pdf') }}" target="_blank" class="space-y-6">
        <input type="hidden" name="id_pre_liquidacion" id="pdf-id-pre-liquidacion">

        <div>
            <x-input-label :value="__('Empresa')" />
            <x-text-input id="pdf-empresa" type="text" class="mt-1 block w-full" disabled />
        </div>

        <div>
            <x-input-label :value="__('Título del reporte')" />
            <select name="titulo" id="pdf-titulo"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required
                onchange="toggleSelectMes(this.value)">
                <option value="">Seleccione una opción</option>
                <option value="PRE-LIQUIDACION RENTA ANUAL AL CIERRE">PRE-LIQUIDACION RENTA ANUAL AL CIERRE</option>
                <option value="PRE-LIQUIDACION RENTA ANUAL">PRE-LIQUIDACION RENTA ANUAL</option>
                <option value="LIQUIDACION RENTA ANUAL">LIQUIDACION RENTA ANUAL</option>
            </select>
        </div>

        {{-- Selector de mes: solo visible si título incluye “AL CIERRE” --}}
        <div id="contenedor-mes" class="hidden">
            <x-input-label :value="__('Mes de cierre')" />
            <select name="mes_cierre"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">
                        {{ strtoupper(\Carbon\Carbon::create()->month($m)->locale('es')->monthName) }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="flex justify-end mt-6">
            <x-primary-button class="px-6 py-2">Generar PDF</x-primary-button>
        </div>
    </form>
</x-modal-show>
<script>
    function abrirModalPDFRenta(id, razon, anio) {
        document.getElementById('pdf-id-pre-liquidacion').value = id;
        document.getElementById('pdf-empresa').value = razon + ' (' + anio + ')';
        toggleModal('modal-pdf-renta');
    }

    function toggleSelectMes(valor) {
        const contMes = document.getElementById('contenedor-mes');
        if (valor.includes('AL CIERRE')) contMes.classList.remove('hidden');
        else contMes.classList.add('hidden');
    }
</script>

@if ($preLiquidacion->total > 0)
    <script>
        $(document).ready(function() {
            $('#myTable-liqui-deter').DataTable({
                responsive: false, // Hacer la tabla responsive
                paging: false, // Deshabilita la paginación
                ordering: false, // Deshabilita el ordenamiento
                searching: false, // Opcional: deshabilita la barra de búsqueda
                info: false, // Opcional: oculta la información de "Mostrando X de Y registros"
                dom: 'Bfrtip', // Activa el diseño para los botones
                buttons: [
                    /*{
                                            extend: 'print',
                                            text: 'Imprimir',
                                            title: function() {
                                                const fecha = new Date();
                                                const dia = String(fecha.getDate()).padStart(2, '0');
                                                const mes = fecha.toLocaleString('default', {
                                                    month: 'long'
                                                });
                                                const anio = fecha.getFullYear();
                                                return `Preliquidación_y_Determinación_POR_PAGAR_Hoy_${dia}_${mes}_${anio}`;
                                            },
                                        },*/
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                        title: function() {
                            const fecha = new Date();
                            const anio = "{{ $preLiquidacion->anio }}";

                            // Obtener el nombre de la empresa desde la variable de Blade o JavaScript
                            let razonSocial =
                                "{{ $preLiquidacion->empresaCliente->razon_social }}";

                            // Eliminar caracteres especiales para evitar problemas en el nombre del archivo
                            razonSocial = razonSocial.replace(/[^a-zA-Z0-9]/g, "_");

                            return `Preliquidación_y_Determinación_POR_PAGAR_${razonSocial}_${anio}`;
                        },
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];

                            // 🔹 1. Eliminar la primera fila generada por defecto
                            $('row:first', sheet).remove();

                            // 🔹 2. COMBINAR Y CENTRAR CELDAS A5:B5
                            if (!$('mergeCells', sheet).length) {
                                $('worksheet', sheet).append('<mergeCells count="1"></mergeCells>');
                            }
                            $('mergeCells', sheet).append('<mergeCell ref="A5:B5"/>');

                            // 🔹 3. Aplicar estilos
                            /*$('row c[r="A5"], row c[r="B5"], row c[r="A21"], row c[r="B21"]', sheet)
                                .attr('s', '51'); // Asegurar formato en B5*/

                            $('row c[r="A3"], row c[r="A4"], row c[r="A5"], row c[r="A21"]', sheet)
                                .attr('s', '2');

                            //BORDES EN BLANCO
                            /*$('row c[r^="A7"], row c[r^="A8"], row c[r^="A9"], row c[r^="A10"], row c[r^="A11"], row c[r^="A13"], row c[r^="A14"], row c[r^="A16"], row c[r^="A17"], row c[r^="A19"],row c[r^="B7"], row c[r^="B8"], row c[r^="B9"], row c[r^="B10"], row c[r^="B11"], row c[r^="B13"], row c[r^="B14"], row c[r^="B16"], row c[r^="B17"], row c[r^="B19"], row c[r^="A23"], row c[r^="A24"], row c[r^="A25"], row c[r^="A26"], row c[r^="A27"], row c[r^="A29"], row c[r^="A30"], row c[r^="A31"], row c[r^="A34"], row c[r^="A35"], row c[r^="A38"], row c[r^="B23"], row c[r^="B24"], row c[r^="B25"], row c[r^="B26"], row c[r^="B27"], row c[r^="B29"], row c[r^="B30"], row c[r^="B31"], row c[r^="B34"], row c[r^="B35"], row c[r^="B38"]',
                                sheet).attr("s", "25");*/
                            //BORDES EN MORADO
                            $('row c[r="A6"], row c[r="A8"], row c[r="A12"], row c[r="A15"], row c[r="A18"],row c[r="B6"], row c[r="B8"], row c[r="B12"], row c[r="B15"], row c[r="B18"], row c[r="A22"], row c[r="A28"], row c[r="A32"], row c[r="A33"], row c[r="A36"], row c[r="B22"], row c[r="B28"], row c[r="B32"], row c[r="B33"], row c[r="B36"]',
                                    sheet)
                                .attr('s', '2');
                            //sin bordes ROJO
                            $('row c[r="A37"], row c[r="B37"]', sheet)
                                .attr('s', '10');
                            //SIN BORDES VERDE
                            $('row c[r="A20"], row c[r="B20"], row c[r="A39"], row c[r="B39"]',
                                    sheet)
                                .attr('s', '17');

                            $('mergeCells', sheet).append('<mergeCell ref="A21:B21"/>');
                        }
                    }
                    /*,
                                        {
                                            extend: 'pdf',
                                            text: 'Exportar a PDF',
                                            title: function() {
                                                const fecha = new Date();
                                                const dia = String(fecha.getDate()).padStart(2, '0');
                                                const mes = fecha.toLocaleString('default', {
                                                    month: 'long'
                                                });
                                                const anio = fecha.getFullYear();
                                                return `Preliquidación_y_Determinación_POR_PAGAR_Hoy_${dia}_${mes}_${anio}`;
                                            },
                                        }*/
                ],
                language: {
                    url: "/js/datatables/lang/es-ES.json" // Textos en español
                },
                order: [
                    [0, 'desc']
                ]
            });
        });
    </script>
@else
    <script>
        $(document).ready(function() {
            $('#myTable-liqui-deter').DataTable({
                responsive: false, // Hacer la tabla responsive
                paging: false, // Deshabilita la paginación
                ordering: false, // Deshabilita el ordenamiento
                searching: false, // Opcional: deshabilita la barra de búsqueda
                info: false, // Opcional: oculta la información de "Mostrando X de Y registros"
                dom: 'Bfrtip', // Activa el diseño para los botones
                buttons: [
                    /*{
                                            extend: 'print',
                                            text: 'Imprimir',
                                            title: function() {
                                                const fecha = new Date();
                                                const anio = fecha.getFullYear();

                                                // Obtener el nombre de la empresa desde la variable de Blade o JavaScript
                                                let razonSocial =
                                                    "{{ $preLiquidacion->empresaCliente->razon_social }}";

                                                // Eliminar caracteres especiales para evitar problemas en el nombre del archivo
                                                razonSocial = razonSocial.replace(/[^a-zA-Z0-9]/g, "_");

                                                return `Preliquidación_y_Determinación_A_FAVOR_${razonSocial}_${anio}`;
                                            },
                                        },*/
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                        title: function() {
                            const fecha = new Date();
                            const anio = "{{ $preLiquidacion->anio }}";

                            // Obtener el nombre de la empresa desde la variable de Blade o JavaScript
                            let razonSocial =
                                "{{ $preLiquidacion->empresaCliente->razon_social }}";

                            // Eliminar caracteres especiales para evitar problemas en el nombre del archivo
                            razonSocial = razonSocial.replace(/[^a-zA-Z0-9]/g, "_");

                            return `Preliquidación_y_Determinación_A_FAVOR_${razonSocial}_${anio}`;
                        },
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];

                            // 🔹 1. Eliminar la primera fila generada por defecto
                            $('row:first', sheet).remove();

                            // 🔹 2. COMBINAR Y CENTRAR CELDAS A5:B5
                            if (!$('mergeCells', sheet).length) {
                                $('worksheet', sheet).append('<mergeCells count="1"></mergeCells>');
                            }
                            $('mergeCells', sheet).append('<mergeCell ref="A5:B5"/>');

                            // 🔹 3. Aplicar estilos
                            $('row c[r="A5"], row c[r="B5"], row c[r="A21"], row c[r="B21"]', sheet)
                                .attr('s', '51'); // Asegurar formato en B5

                            $('row c[r="A3"], row c[r="A4"], row c[r="A5"], row c[r="A21"]', sheet)
                                .attr('s', '2');

                            //BORDES EN BLANCO
                            /*$('row c[r^="A7"], row c[r^="A8"], row c[r^="A9"], row c[r^="A10"], row c[r^="A11"], row c[r^="A13"], row c[r^="A14"], row c[r^="A16"], row c[r^="A17"], row c[r^="A19"], row c[r^="A22"], row c[r^="A24"],row c[r^="B7"], row c[r^="B8"], row c[r^="B9"], row c[r^="B10"], row c[r^="B11"], row c[r^="B13"], row c[r^="B14"], row c[r^="B16"], row c[r^="B17"], row c[r^="B19"], row c[r^="B22"], row c[r^="B24"]',
                                sheet).attr("s", "25");*/
                            //BORDES EN MORADO es el numero 45
                            //blanco 27
                            $('row c[r="A6"], row c[r="A8"], row c[r="A12"], row c[r="A15"], row c[r="A18"],row c[r="B6"], row c[r="B8"], row c[r="B12"], row c[r="B15"], row c[r="B18"]',
                                    sheet)
                                .attr('s', '2');
                            //BORDES ROJO
                            $('row c[r="A23"], row c[r="B23"]', sheet)
                                .attr('s', '10');
                            //BORDES VERDE
                            $('row c[r="A20"], row c[r="B20"], row c[r="A25"], row c[r="B25"]',
                                    sheet)
                                .attr('s', '17');

                            $('mergeCells', sheet).append('<mergeCell ref="A21:B21"/>');

                        }
                    }
                    /*,
                                        {
                                            extend: 'pdf',
                                            text: 'Exportar a PDF',
                                            title: function() {
                                                const fecha = new Date();
                                                const dia = String(fecha.getDate()).padStart(2, '0');
                                                const mes = fecha.toLocaleString('default', {
                                                    month: 'long'
                                                });
                                                const anio = fecha.getFullYear();
                                                return `Preliquidación_y_Determinación_A_FAVOR_Hoy_${dia}_${mes}_${anio}`;
                                            },
                                        }*/
                ],
                language: {
                    url: "/js/datatables/lang/es-ES.json" // Textos en español
                },
                order: [
                    [0, 'desc']
                ]
            });
        });
    </script>
@endif
