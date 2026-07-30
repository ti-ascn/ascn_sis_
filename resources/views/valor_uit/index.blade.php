<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mantenimiento de Valores UIT') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            <div class="mb-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                <p class="text-sm text-blue-700">
                    <i class="bi bi-info-circle-fill mr-1"></i>
                    Los datos de los Valores UIT anuales se obtienen de:
                    <a href="https://www.sunat.gob.pe/indicestasas/uit.html" target="_blank"
                        class="font-bold underline hover:text-blue-900">
                        https://www.sunat.gob.pe/indicestasas/uit.html
                    </a>
                </p>
            </div>

            <div class="flex mb-4">
                <button type="button"
                    class="btn-open-modal-crear flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                    <i class="bi bi-plus-circle"></i> Nuevo Valor UIT
                </button>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive nowrap" id="tabla-uit">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            {{-- <th class="px-4 py-2 border-b">ID</th> --}}
                            <th class="px-4 py-2 border-b">Año</th>
                            <th class="px-4 py-2 border-b">Valor (S/)</th>
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($valores as $valor)
                            <tr class="hover:bg-gray-50">
                                {{-- <td class="px-4 py-2 border-b">{{ $valor->id_uit }}</td> --}}
                                <td class="px-4 py-2 border-b">{{ $valor->anio }}</td>
                                <td class="px-4 py-2 border-b font-bold">{{ number_format($valor->valor, 2) }}</td>
                                <td class="px-4 py-2 border-b text-center">
                                    <div class="flex space-x-2 justify-center">
                                        <button type="button"
                                            class="btn-open-modal-editar flex items-center py-1 rounded-md tooltip"
                                            style="color: #F59E0B; transition: color 0.3s;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';"
                                            data-id="{{ $valor->id_uit }}" data-anio="{{ $valor->anio }}"
                                            data-valor="{{ $valor->valor }}">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </button>

                                        <form action="{{ route('valor_uit.destroy', $valor->id_uit) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('¿Estás seguro de eliminar este valor UIT?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #dc2626; transition: color 0.3s;"
                                                onmouseover="this.style.color='#b91c1c';"
                                                onmouseout="this.style.color='#dc2626';">
                                                <span class="tooltip-text">Eliminar</span>
                                                <x-delete-icon />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    <x-modal-show id="modalUit" title="Valor UIT">
        <form method="POST" id="formUit" action="{{ route('valor_uit.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="_method" id="methodUit" value="POST">

            <div>
                <x-input-label value="Año" />
                <x-text-input type="number" name="anio" id="anio_uit" class="mt-1 block w-full" required />
            </div>

            <div>
                <x-input-label value="Valor (S/)" />
                <x-text-input type="number" step="0.01" name="valor" id="valor_uit_input"
                    class="mt-1 block w-full" required />
            </div>

            <div class="flex justify-end pt-4">
                <x-primary-button class="px-6 py-2">
                    Guardar
                </x-primary-button>
            </div>
        </form>
    </x-modal-show>

</x-app-layout>
<script>
    document.addEventListener('click', function(e) {
        const btnCrear = e.target.closest('.btn-open-modal-crear');
        const btnEditar = e.target.closest('.btn-open-modal-editar');

        if (btnCrear) {
            document.getElementById('formUit').action = "{{ route('valor_uit.store') }}";
            document.getElementById('methodUit').value = "POST";
            document.getElementById('anio_uit').value = "";
            document.getElementById('valor_uit_input').value = "";
            toggleModal('modalUit');
        }

        if (btnEditar) {
            const data = btnEditar.dataset;
            document.getElementById('formUit').action = `/valor_uit/${data.id}`;
            document.getElementById('methodUit').value = "PUT";
            document.getElementById('anio_uit').value = data.anio;
            document.getElementById('valor_uit_input').value = data.valor;
            toggleModal('modalUit');
        }
    });

    $(document).ready(function() {
        $('#tabla-uit').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            }
        });
    });
</script>
