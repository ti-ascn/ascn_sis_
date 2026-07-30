<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles de AFP y PDT PLAME de la Empresa') }}
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
                    <table class="min-w-full bg-white border border-gray-200" id="myTable-afp-pdt">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 border-b text-center">PROCESO</th>
                                <th class="px-4 py-2 border-b text-right">STATUS</th>
                                <th class="px-4 py-2 border-b text-center">FECHA</th>
                                <th class="px-4 py-2 border-b text-center">RESPONSABLE</th>
                                <th class="px-4 py-2 border-b text-center">OBSERVACIÓN</th>
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

                            <!-- REGISTRO DE AFP -->
                            @if ($empresa->registrosAfp->isNotEmpty())
                                @foreach ($empresa->registrosAfp as $registro)
                                    <tr>
                                        <td class="px-4 py-2 border-b">REGISTRO DE AFP</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            {{ formatStatus($registro->opcion_si_no) }}</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            {{ formatDateTime($registro->created_at) }}
                                        </td>
                                        <td class="px-4 py-2 border-b">{{ $registro->usuario->name ?? '-' }}</td>
                                        <td class="px-4 py-2 border-b">{{ $registro->observacion ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-4 py-2 border-b">REGISTRO DE AFP</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b text-center">-</td>
                                    <td class="px-4 py-2 border-b">-</td>
                                    <td class="px-4 py-2 border-b">-</td>
                                </tr>
                            @endif

                            <!-- REGISTRO DE PDT PLAME -->
                            @if ($empresa->registrosPdtplame->isNotEmpty())
                                @foreach ($empresa->registrosPdtplame as $registro)
                                    <tr>
                                        <td class="px-4 py-2 border-b">REGISTRO DE PDT PLAME</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            {{ formatStatus($registro->opcion_si_no) }}</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            {{ formatDateTime($registro->created_at) }}
                                        </td>
                                        <td class="px-4 py-2 border-b">{{ $registro->usuario->name ?? '-' }}</td>
                                        <td class="px-4 py-2 border-b">{{ $registro->observacion ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-4 py-2 border-b">REGISTRO DE PDT PLAME</td>
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
        $('#myTable-afp-pdt').DataTable({
            responsive: false, // Hacer la tabla responsive
            paging: false, // Deshabilita la paginación
            ordering: false, // Deshabilita el ordenamiento
            searching: false, // Opcional: deshabilita la barra de búsqueda
            info: false, // Opcional: oculta la información de "Mostrando X de Y registros"
            dom: 'Bfrtip', // Activa el diseño para los botones
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: '{{ $empresa->razon_social }}_REGISTROS_AFP_PDTPLAME_{{ $mes }}_{{ date('Y') }}'
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
