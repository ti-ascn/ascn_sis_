<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Recepción de documentos de Empresas - ') }}
            {{ ucfirst(\Carbon\Carbon::parse($fecha)->isoFormat('MMMM YYYY')) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex flex-wrap gap-4 items-center">
                <a href="{{ route('recepcion.documentos') }}"
                    class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                    {{ __('Volver') }}
                </a>
                <button onclick="event.preventDefault(); toggleModal('modal-c1-periodo')"
                    class="bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white">
                    {{ __('Todos los Registros por Período') }}
                </button>
            </div>
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto" id="myTable-periodo">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">UD</th>
                            <th class="px-4 py-2 border-b">EMPRESA</th>
                            <th class="px-4 py-2 border-b">SOLICITUD 1</th>
                            <th class="px-4 py-2 border-b">SOLICITUD 2</th>
                            <th class="px-4 py-2 border-b">SOLICITUD 3</th>
                            <th class="px-4 py-2 border-b">RECEPCIÓN</th>
                            <th class="px-4 py-2 border-b">ENTREGA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datosEmpresas as $empresa)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $empresa['ud'] }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa['empresa'] }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa['solicitud_1'] }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa['solicitud_2'] }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa['solicitud_3'] }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa['recepcion'] }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa['entrega'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Modal para seleccionar el período -->
<x-modal-show id="modal-c1-periodo" title="Seleccionar Periodo">
    <form id="form-c1-periodo" method="GET" action="{{ route('recepcion.documentos.empresasC1') }}">
        <div class="mb-4">
            <label for="periodo" class="block text-sm font-medium text-gray-700">Seleccione un periodo:</label>
            <select name="fecha" id="periodo" onchange="this.form.submit()" class="block w-full border-gray-300 rounded-md shadow-sm">
                @php
                    // Generar un array con los últimos 12 meses
                    $meses = [];
                    $base = \Carbon\Carbon::now()->startOfMonth();
                    for ($i = 0; $i < 12; $i++) {
                        $meses[] = $base->copy()->subMonths($i)->format('Y-m');
                    }

                    // Extraer el año y el mes de $fecha (formato "Y-m")
                    $fechaFormateada = \Carbon\Carbon::parse($fecha)->format('Y-m');
                @endphp
                @foreach ($meses as $mes)
                    <!-- Seleccionar automáticamente el mes actual -->
                    <option value="{{ $mes }}" {{ $mes === $fechaFormateada ? 'selected' : '' }}>
                        {{ ucfirst(\Carbon\Carbon::parse($mes)->isoFormat('MMMM YYYY')) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- <div class="flex justify-end">
            <button type="submit"
                class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div> --}}
    </form>
</x-modal-show>
<script>
    $(document).ready(function() {
        $('#myTable-periodo').DataTable({
            responsive: false,
            paging: true,
            ordering: true,
            searching: true,
            info: true,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: 'RECEPCION_DOCUMENTOS_{{ strtoupper(\Carbon\Carbon::parse($fecha)->isoFormat('MMMM_YYYY')) }}'
            }],
            language: {
                url: "/js/datatables/lang/es-ES.json" // Textos en español
            },
            order: [
                [0, 'asc']
            ]
        });
    });
</script>
