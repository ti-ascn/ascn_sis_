<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Todos los Registros de Recepción de Documentos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <div class="mb-4 flex flex-wrap gap-4 items-center">
                <a href="{{ route('recepcion.documentos') }}"
                    class="bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white">
                    <i class="bi bi-arrow-left-circle"></i> {{ __('Volver') }}
                </a>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto" id="myTable-recepcion">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">UD</th>
                            <th class="px-4 py-2 border-b">RAZÓN SOCIAL</th>
                            <th class="px-4 py-2 border-b">FECHA</th>
                            <th class="px-4 py-2 border-b">PERIODO</th>
                            <th class="px-4 py-2 border-b">OPCIÓN</th>
                            <th class="px-4 py-2 border-b">ESTADO</th>
                            <th class="px-4 py-2 border-b">TIPO DE CONTACTO</th>
                            <th class="px-4 py-2 border-b">TIPO DE DOCUMENTO</th>
                            <th class="px-4 py-2 border-b">OBSERVACIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documentos as $registro)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $registro->ud }}</td>
                                <td class="px-4 py-2 border-b">{{ $registro->empresaCliente->razon_social }}</td>
                                <td class="px-4 py-2 border-b">{{ $registro->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-4 py-2 border-b">{{ $registro->opcion }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ ucfirst(\Carbon\Carbon::parse($registro->fecha)->isoFormat('MMMM YYYY')) }}
                                    <!-- Mes en español -->
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @if ($registro->estado === 'solicitud')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-blue-500 rounded-full w-24">
                                            Solicitud
                                        </span>
                                    @elseif($registro->estado === 'recepcion')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">
                                            Recepción
                                        </span>
                                    @elseif($registro->estado === 'entrega')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                            Entrega
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">{{ strtoupper($registro->tipo_contacto ?? '-') }}
                                </td>
                                <td class="px-4 py-2 border-b">{{ strtoupper($registro->tipo_documento ?? '-') }}
                                </td>
                                <td class="px-4 py-2 border-b">{{ $registro->observacion ?? 'Sin observación' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    $(document).ready(function() {
        $('#myTable-recepcion').DataTable({
            responsive: false,
            paging: true,
            ordering: true,
            searching: true,
            info: true,
            language: {
                url: "/js/datatables/lang/es-ES.json"
            },
            order: [
                [2, 'desc'] // Ordenar por fecha (tercera columna)
            ]
        });
    });
</script>
