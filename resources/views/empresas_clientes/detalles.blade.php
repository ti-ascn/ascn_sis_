<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles de la Empresa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ url()->previous() }}"
                    class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                    {{ __('Volver') }}
                </a>
            </div>

            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    {{ $empresa->razon_social }}
                </h3>

                <!-- Tabla de procesos -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200" id="myTable-spcv">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 border-b text-left">PROCESO</th>
                                <th class="px-4 py-2 border-b text-center">STATUS</th>
                                <th class="px-4 py-2 border-b text-center">FECHA</th>
                                <th class="px-4 py-2 border-b text-center">PLAZO</th>
                                <th class="px-4 py-2 border-b text-left">RESPONSABLE</th>
                                <th class="px-4 py-2 border-b text-left">OBSERVACIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                function formatStatus($value)
                                {
                                    return match (strtolower($value)) {
                                        'si' => 'SI',
                                        'no' => 'NO',
                                        'na' => 'NO APLICA',
                                        default => '-',
                                    };
                                }

                                function formatDateTime($date)
                                {
                                    return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y H:i') : '-';
                                }
                            @endphp

                            <!-- REGISTRO DE VENTAS -->
                            @if ($empresa->registrosVentas->isNotEmpty())
                                @foreach ($empresa->registrosVentas as $registro)
                                    <tr>
                                        <td class="px-4 py-2 border-b">REGISTRO DE VENTAS</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            {{ formatStatus($registro->registro) }}</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            {{ formatDateTime($registro->created_at) }}
                                        </td>
                                        <td class="px-4 py-2 border-b text-center"><span
                                                class="flex justify-center items-center px-4 py-1 text-white bg-gray-500 rounded-full w-24">
                                                No tiene
                                            </span></td>
                                        <td class="px-4 py-2 border-b">{{ $registro->usuario->name ?? '-' }}</td>
                                        <td class="px-4 py-2 border-b">{{ $registro->observacion ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-4 py-2 border-b">REGISTRO DE VENTAS</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b">-</td>
                                    <td class="px-4 py-2 border-b">-</td>
                                </tr>
                            @endif

                            <!-- REGISTRO DE COMPRAS -->
                            @if ($empresa->registrosCompras->isNotEmpty())
                                @foreach ($empresa->registrosCompras as $registro)
                                    <tr>
                                        <td class="px-4 py-2 border-b">REGISTRO DE COMPRAS</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            {{ formatStatus($registro->registro) }}</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            {{ formatDateTime($registro->created_at) }}
                                        </td>
                                        <td class="px-4 py-2 border-b text-center"><span
                                                class="flex justify-center items-center px-4 py-1 text-white bg-gray-500 rounded-full w-24">
                                                No tiene
                                            </span></td>
                                        <td class="px-4 py-2 border-b">{{ $registro->usuario->name ?? '-' }}</td>
                                        <td class="px-4 py-2 border-b">{{ $registro->observacion ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-4 py-2 border-b">REGISTRO DE COMPRAS</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b">-</td>
                                    <td class="px-4 py-2 border-b">-</td>
                                </tr>
                            @endif

                            <!-- DECLARACIÓN SIRE -->
                            @if ($empresa->registrosSire->isNotEmpty())
                                @foreach ($empresa->registrosSire as $registro)
                                    <tr>
                                        <td class="px-4 py-2 border-b">DECLARACIÓN SIRE</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            {{ formatStatus($registro->opcion_si_no) }}</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            {{ formatDateTime($registro->created_at) }}
                                        </td>
                                        <td class="px-4 py-2 border-b text-center">
                                            @if ($registro->estado == 'dentro')
                                                <span
                                                    class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                                    Dentro
                                                </span>
                                            @else
                                                <span
                                                    class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                                    Fuera
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 border-b">{{ $registro->usuario->name ?? '-' }}</td>
                                        <td class="px-4 py-2 border-b">{{ $registro->observacion ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-4 py-2 border-b">DECLARACIÓN SIRE</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b">-</td>
                                    <td class="px-4 py-2 border-b">-</td>
                                </tr>
                            @endif

                            <!-- DECLARACIÓN PDT -->
                            @if ($empresa->registrosPdt->isNotEmpty())
                                @foreach ($empresa->registrosPdt as $registro)
                                    <tr>
                                        <td class="px-4 py-2 border-b">DECLARACIÓN PDT</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            {{ formatStatus($registro->opcion_si_no) }}</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            {{ formatDateTime($registro->created_at) }}
                                        </td>
                                        <td class="px-4 py-2 border-b text-center">
                                            @if ($registro->estado == 'dentro')
                                                <span
                                                    class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                                    Dentro
                                                </span>
                                            @else
                                                <span
                                                    class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                                    Fuera
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 border-b">{{ $registro->usuario->name ?? '-' }}</td>
                                        <td class="px-4 py-2 border-b">{{ $registro->observacion ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-4 py-2 border-b">DECLARACIÓN PDT</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b">-</td>
                                    <td class="px-4 py-2 border-b">-</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


<script>
    $(document).ready(function() {
        $('#myTable-spcv').DataTable({
            responsive: false, // Hacer la tabla responsive
            paging: false, // Deshabilita la paginación
            ordering: false, // Deshabilita el ordenamiento
            searching: false, // Opcional: deshabilita la barra de búsqueda
            info: false, // Opcional: oculta la información de "Mostrando X de Y registros"
            dom: 'Bfrtip', // Activa el diseño para los botones
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: '{{ $empresa->razon_social }}_REGISTROS_SIREPDT_COMPRASVENTAS_{{ $mes }}_{{ date('Y') }}'
            }],
            language: {
                url: "/js/datatables/lang/es-ES.json" // Textos en español
            },
            order: [
                [0, 'desc']
            ]
        });
    });
</script>
