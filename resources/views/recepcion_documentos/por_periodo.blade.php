<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Recepción de documentos de ') }}
            <span class="text-blue-500">{{ strtoupper($empresa->razon_social) }}</span>
            {{ __(' en ') }}
            <span class="text-blue-500">{{ ucfirst(\Carbon\Carbon::parse($fecha)->isoFormat('MMMM YYYY')) }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex flex-wrap gap-4 items-center">
                <a href="{{ route('recepcion.documentos') }}"
                    class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                    <i class="bi bi-arrow-left-circle"></i> {{ __('Volver') }}
                </a>
                <button onclick="openModal()"
                    class="bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white">
                    <i class="bi bi-calendar-range"></i> {{ __('Buscar por Periodo') }}
                </button>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg mt-4">
                <table class="min-w-full table-auto" id="myTable-periodo-documentos">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">FECHA</th>
                            <th class="px-4 py-2 border-b">PERIODO</th>
                            <th class="px-4 py-2 border-b">OPCIÓN</th>
                            <th class="px-4 py-2 border-b">ESTADO</th>
                            <th class="px-4 py-2 border-b">TIPO DE CONTACTO</th>
                            <th class="px-4 py-2 border-b">TIPO DE DOCUMENTO</th>
                            <th class="px-4 py-2 border-b">OBSERVACIÓN</th>
                            {{-- <th class="px-4 py-2 border-b">ACCIONES</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documentos as $doc)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($doc->created_at)->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ ucfirst(\Carbon\Carbon::parse($doc->fecha)->isoFormat('MMMM YYYY')) }}
                                </td>
                                <td class="px-4 py-2 border-b">{{ $doc->opcion }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($doc->estado === 'solicitud')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-blue-500 rounded-full w-24">
                                            Solicitud
                                        </span>
                                    @elseif($doc->estado === 'recepcion')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">
                                            Recepción
                                        </span>
                                    @elseif($doc->estado === 'entrega')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                            Entrega
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">{{ strtoupper($doc->tipo_contacto ?? '-') }}</td>
                                <td class="px-4 py-2 border-b">{{ strtoupper($doc->tipo_documento ?? '-') }}</td>
                                <td class="px-4 py-2 border-b">{{ $doc->observacion ?? 'Sin observación' }}</td>
                                {{-- <td class="px-4 py-2 border-b">
                                    @if ($doc->estado === 'solicitud' || $doc->estado === 'recepcion' || $doc->estado === 'entrega')
                                        <a href="{{ route('recepcion.documentos.edit', $doc->id_recepcion) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #F59E0B; transition: color 0.3s;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </a>
                                    @endif
                                </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para seleccionar el periodo -->
    <x-modal-show id="modal-periodo" title="Seleccionar Periodo">
        <form id="form-periodo" method="GET" action="{{ route('recepcion.documentos.por_periodo') }}">
            <input type="hidden" name="id_empresas_clientes" value="{{ $empresa->id_empresas_clientes }}">

            <div class="mb-4">
                <label for="periodo" class="block text-sm font-medium text-gray-700">Seleccione un periodo:</label>
                <select name="fecha" id="periodo" class="block w-full border-gray-300 rounded-md shadow-sm">
                    @php
                        $meses = [];
                        $base = \Carbon\Carbon::now()->startOfMonth();
                        for ($i = 0; $i < 12; $i++) {
                            $meses[] = $base->copy()->subMonths($i)->format('Y-m');
                        }
                    @endphp
                    @foreach ($meses as $mes)
                        <option value="{{ $mes }}"
                            {{ $mes === \Carbon\Carbon::parse($fecha)->format('Y-m') ? 'selected' : '' }}>
                            {{ ucfirst(\Carbon\Carbon::parse($mes)->isoFormat('MMMM YYYY')) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </form>
    </x-modal-show>

    <script>
        function openModal() {
            document.getElementById('modal-periodo').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal-periodo').classList.add('hidden');
        }
    </script>
</x-app-layout>

<script>
    $(document).ready(function() {
        $('#myTable-periodo-documentos').DataTable({
            responsive: false,
            paging: false,
            ordering: true,
            searching: false,
            info: false,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: 'RECEPCION_DOCUMENTOS_{{ strtoupper($empresa->razon_social) }}_{{ strtoupper(\Carbon\Carbon::parse($fecha)->isoFormat('MMMM_YYYY')) }}'
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
