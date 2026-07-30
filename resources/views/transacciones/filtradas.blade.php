<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transacciones de la Cuenta: ') . $cuenta->nombre . ' ' . $cuenta->tipo . ' del mes ' . $mesNombre . ' del ' . $anio .' de '.$cuenta->empresa->nombre}}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Información del filtro -->
            {{-- <div class="mb-4">
                <h4 class="font-bold text-lg">
                    {{ __('Transacciones para el mes: ') . $mesNombre . ' ' . $anio }}
                </h4>
            </div> --}}
            <!-- Volver -->
            <div class="mb-4">
                <a href="{{ route('transacciones.listarPorCuenta', $cuenta->id_cuenta) }}"
                    class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                    {{ __('Volver') }}
                </a>
            </div>

            <!-- Transacciones Table -->
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive" id="myTable-filtradas">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Fecha</th>
                            <th class="px-4 py-2 border-b">Monto</th>
                            <th class="px-4 py-2 border-b">Descripción</th>
                            <th class="px-4 py-2 border-b">Hecho por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transacciones as $transaccion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $transaccion->id_transaccion }}</td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->fecha }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($transaccion->tipo == 'ingreso')
                                        +{{ number_format($transaccion->monto, 2) }}
                                    @elseif ($transaccion->tipo == 'egreso')
                                        -{{ number_format($transaccion->monto, 2) }}
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->descripcion ?: 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->user->name ?? 'No asignado' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Total de Egresos -->
            <div class="mt-4 bg-gray-100 p-4 rounded-md">
                <h4 class="font-semibold text-lg">
                    {{ __('Egresos Mensuales: ') }}
                    <span class="font-extrabold text-2xl text-red-600">
                        @if (isset($cuenta) && isset($total_egresos))
                            @if ($cuenta->tipo == 'Soles')
                                - S/ {{ number_format($total_egresos, 2) }}
                            @elseif($cuenta->tipo == 'Dolares')
                                - $ {{ number_format($total_egresos, 2) }}
                            @endif
                        @else
                            {{ __('No hay datos disponibles') }}
                        @endif
                    </span>
                </h4>
            </div>

            <!-- Total de Ingresos -->
            <div class="bg-gray-100 p-4 rounded-md">
                <h4 class="font-semibold text-lg">
                    {{ __('Ingresos Mensuales: ') }}
                    <span class="text-green-600 font-bold">
                        @if (isset($cuenta) && isset($total_ingresos))
                            @if ($cuenta->tipo == 'Soles')
                                + S/ {{ number_format($total_ingresos, 2) }}
                            @elseif($cuenta->tipo == 'Dolares')
                                + $ {{ number_format($total_ingresos, 2) }}
                            @endif
                        @else
                            {{ __('No hay datos disponibles') }}
                        @endif
                    </span>
                </h4>
            </div>


        </div>
    </div>
</x-app-layout>

<script>
    $(document).ready(function() {
        $('#myTable-filtradas').DataTable({
            paging: true, // Habilitar paginación
            searching: true, // Habilitar barra de búsqueda
            ordering: true, // Habilitar ordenamiento
            responsive: true, // Hacer la tabla responsive
            dom: 'Bfrtip', // Activa el diseño para los botones
            buttons: [{
                    extend: 'print',
                    text: 'Imprimir',
                    customize: function(win) {
                        const egresos =
                            `{{ isset($total_egresos) ? ($cuenta->tipo === 'Soles' ? 'S/ ' . number_format($total_egresos, 2) : '$ ' . number_format($total_egresos, 2)) : 'No hay datos disponibles' }}`;
                        const ingresos =
                            `{{ isset($total_ingresos) ? ($cuenta->tipo === 'Soles' ? 'S/ ' . number_format($total_ingresos, 2) : '$ ' . number_format($total_ingresos, 2)) : 'No hay datos disponibles' }}`;
                        // Agregar contenido personalizado a la impresión
                        $(win.document.body).append(`
                            <h3>Total de Egresos Mensuales: <span style="color: red;">${egresos}</span></h3>
                            <h3>Total de Ingresos Mensuales: <span style="color: green;">${ingresos}</span></h3>
                            <hr>
                        `);
                    }
                },
                {
                    extend: 'excel',
                    text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                    title: function() {
                        const fecha = new Date();

                        // Obtener día, mes y año en formato DD/MM/YYYY
                        const dia = String(fecha.getDate()).padStart(2,
                            '0'); // Día con 2 dígitos
                        const mes = String(fecha.getMonth() + 1).padStart(2,
                            '0'
                            ); // Mes con 2 dígitos (sumando 1 porque getMonth() comienza desde 0)
                        const anio = fecha.getFullYear();

                        // Obtener el mes y año de las variables de Blade
                        const mesNombre = '{{ $mesNombre }}';
                        const anioValor = '{{ $anio }}';

                        // Crear el nombre del archivo con el formato DD/MM/YYYY
                        return `Reporte_Transacciones_ASECON_mes_${mesNombre}_${anioValor}_DESCARGADO_${dia}_${mes}_${anio}`;
                    },
                    customizeData: function(data) {
                        const egresos =
                            `{{ isset($total_egresos) ? ($cuenta->tipo === 'Soles' ? 'S/ ' . number_format($total_egresos, 2) : '$ ' . number_format($total_egresos, 2)) : 'No hay datos disponibles' }}`;
                        const ingresos =
                            `{{ isset($total_ingresos) ? ($cuenta->tipo === 'Soles' ? 'S/ ' . number_format($total_ingresos, 2) : '$ ' . number_format($total_ingresos, 2)) : 'No hay datos disponibles' }}`;

                        // Agregar filas personalizadas a los datos exportados
                        data.body.push(['', '', '', '', '']);
                        data.body.push(['', '', '', 'Total de Egresos Mensuales', egresos]);
                        data.body.push(['', '', '', 'Total de Ingresos Mensuales', ingresos]);
                    }
                },
                {
                    extend: 'pdf',
                    text: 'Exportar a PDF',
                    title: function() {
                        const fecha = new Date();

                        // Obtener día, mes y año en formato DD/MM/YYYY
                        const dia = String(fecha.getDate()).padStart(2,
                            '0'); // Día con 2 dígitos
                        const mes = String(fecha.getMonth() + 1).padStart(2,
                            '0'
                            ); // Mes con 2 dígitos (sumando 1 porque getMonth() comienza desde 0)
                        const anio = fecha.getFullYear();

                        // Obtener el mes y año de las variables de Blade
                        const mesNombre = '{{ $mesNombre }}';
                        const anioValor = '{{ $anio }}';

                        // Crear el nombre del archivo con el formato DD/MM/YYYY
                        return `Reporte_Transacciones_ASECON_mes_${mesNombre}_${anioValor}_DESCARGADO_${dia}_${mes}_${anio}`;
                    },
                    customize: function(doc) {
                        const egresos =
                            `{{ isset($total_egresos) ? ($cuenta->tipo === 'Soles' ? 'S/ ' . number_format($total_egresos, 2) : '$ ' . number_format($total_egresos, 2)) : 'No hay datos disponibles' }}`;
                        const ingresos =
                            `{{ isset($total_ingresos) ? ($cuenta->tipo === 'Soles' ? 'S/ ' . number_format($total_ingresos, 2) : '$ ' . number_format($total_ingresos, 2)) : 'No hay datos disponibles' }}`;

                        // Agregar contenido personalizado al PDF después de la tabla
                        doc.content.push({
                            text: [{
                                    text: '\n\nTotal de Egresos Mensuales: ',
                                    bold: true,
                                    color: 'red'
                                },
                                `${egresos}\n\n`,
                                {
                                    text: 'Total de Ingresos Mensuales: ',
                                    bold: true,
                                    color: 'green'
                                },
                                `${ingresos}\n\n`
                            ],
                            margin: [0, 0, 0, 12] // Margen superior e inferior
                        });
                    }
                }

            ],
            language: {
                url: "/js/datatables/lang/es-ES.json" // Textos en español
            }
        });
    });
</script>
