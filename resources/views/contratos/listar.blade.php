<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col space-y-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Lista de Contratos de Empresas A1') }}
            </h2>
            <a href="{{ route('contratos.index') }}"
                class="inline-flex items-center gap-2 bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded transition w-fit">
                <i class="bi bi-arrow-left"></i>
                Atrás
            </a>
        </div>
    </x-slot>

    <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
        <table class="min-w-full table-auto display nowrap responsive nowrap" id="myTable">
            <thead>
                <tr class="bg-gray-100 text-left">
                    {{-- <th class="px-4 py-2 border-b">Estado</th> --}}
                    <th class="px-4 py-2 border-b">RUC</th>
                    <th class="px-4 py-2 border-b">Razón Social</th>
                    <th class="px-4 py-2 border-b">Contratos Activos</th>
                    <th class="px-4 py-2 border-b">Contratos Inactivos</th>
                    <th class="px-4 py-2 border-b">Total Contratos</th>
                    <th class="px-4 py-2 border-b">Estado Actual</th>
                    <th class="px-4 py-2 border-b">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($empresas_clientes as $empresa)
                    <tr class="hover:bg-gray-50">
                        {{-- <td class="px-4 py-2 border-b">{{ $empresa->estado ?? 'Vacío' }}</td> --}}
                        <td class="px-4 py-2 border-b">{{ $empresa->ruc ?? 'Vacío' }}</td>
                        <td class="px-4 py-2 border-b">{{ $empresa->razon_social ?? 'Vacío' }}</td>
                        <td class="px-4 py-2 border-b text-center">{{ $empresa->total_activos }}</td>
                        <td class="px-4 py-2 border-b text-center">{{ $empresa->total_inactivos }}</td>
                        <td class="px-4 py-2 border-b text-center">{{ $empresa->total_contratos }}</td>
                        <td class="px-4 py-2 border-b">
                            @if ($empresa->estado_actual == 1)
                                <span
                                    class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                    Activo
                                </span>
                            @elseif($empresa->estado_actual == 2)
                                <span
                                    class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">
                                    Suspendido
                                </span>
                            @elseif($empresa->estado_actual == 0)
                                <span
                                    class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                    Retirado
                                </span>
                            @else
                                <span
                                    class="flex justify-center items-center px-4 py-1 text-white bg-gray-500 rounded-full w-24">
                                    Sin estado
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2 border-b">
                            <a href="{{ route('contratos.listarindividual', $empresa->id_empresas_clientes) }}"
                                class="flex items-center rounded-md tooltip" style="color: #2986cc;"
                                onmouseover="this.style.color='#2ba4ff';" onmouseout="this.style.color='#2986cc';">
                                <x-list-icon />
                                <span class="tooltip-text">Lista</span>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
</x-app-layout>

<script>
    $('#tabla-relaciones').DataTable({
        responsive: false,
        paging: true,
        ordering: true,
        pageLength: 200,
        lengthMenu: [10, 25, 50, 100, 200],
        dom: 'Bfrtip',
        buttons: [{
            extend: 'excel',
            text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
            title: tituloArchivo,
            exportOptions: {
                //columns: ':not(:last)',
                format: {
                    body: function(data, row, column, node) {
                        // Si hay texto oculto .export-data, úsalo
                        const exportEl = node.querySelector('.export-data');
                        let text = exportEl ? exportEl.textContent : data;
                        // Eliminar etiquetas HTML residuales
                        return text.replace(/<[^>]*>?/gm, '').trim();
                    }
                }
            }
        }],
        language: {
            url: "/js/datatables/lang/es-ES.json"
        }
    });
</script>
