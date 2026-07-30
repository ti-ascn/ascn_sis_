<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Formato Nro Cotizaciones — Por Emisor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <div class="mb-6 flex flex-wrap gap-4">
                <a href="{{ route('formato_cotizaciones_2.create') }}"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition duration-300 flex items-center gap-2 shadow-sm">
                    <i class="bi bi-plus-circle"></i> Crear Formato
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto text-sm" id="table-formato-coti-2">
                        <thead>
                            <tr class="bg-gray-50 text-left text-gray-700">
                                <th class="px-4 py-2 border-b">RUC Emisor</th>
                                <th class="px-4 py-2 border-b">Razón Social Emisor</th>
                                <th class="px-4 py-2 border-b">Formato Final</th>
                                <th class="px-4 py-2 border-b text-center">Contador Actual</th>
                                <th class="px-4 py-2 border-b text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($formatos as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $item->emisor->ruc ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">{{ $item->emisor->razon_social ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-center font-semibold text-gray-700">
                                            {{ $item->formato_final ?? 'Sin formato' }}
                                    </td>
                                    <td class="px-4 py-2 text-center font-semibold text-gray-700">
                                        {{ $item->contador_actual }}
                                    </td>
                                    <td class="px-4 py-2 text-center flex justify-center gap-2">
                                        <a href="{{ route('formato_cotizaciones_2.edit', $item->id_formato_cotizaciones_2) }}"
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

<script>
    $(document).ready(function() {
        $('#table-formato-coti-2').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            }
        });
    });
</script>
