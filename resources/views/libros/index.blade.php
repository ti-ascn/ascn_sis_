<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mantenimiento de Libros') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            <div class="flex mb-4">
                <button type="button"
                    class="btn-open-modal-crear flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                    <i class="bi bi-plus-circle"></i> Nuevo Libro
                </button>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive nowrap" id="tabla-libros">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Nombre</th>
                            <th class="px-4 py-2 border-b">Régimen</th>
                            <th class="px-4 py-2 border-b">Tramo</th>
                            <th class="px-4 py-2 border-b">Área</th>
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($libros as $libro)
                            <tr class="hover:bg-gray-50">

                                <td class="px-4 py-2 border-b">{{ $libro->id_libro }}</td>
                                <td class="px-4 py-2 border-b">{{ $libro->nombre }}</td>
                                <td class="px-4 py-2 border-b uppercase">{{ $libro->tipo_regimen }}</td>
                                <td class="px-4 py-2 border-b uppercase">{{ $libro->tramo }}</td>
                                <td class="px-4 py-2 border-b uppercase">{{ $libro->area }}</td>
                                <td class="px-4 py-2 border-b text-center">
                                    <div class="flex space-x-2 justify-center">
                                        <button type="button"
                                            class="btn-open-modal-editar flex items-center py-1 rounded-md tooltip"
                                            style="color: #F59E0B; transition: color 0.3s;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';"
                                            data-id="{{ $libro->id_libro }}" data-nombre="{{ $libro->nombre }}"
                                            data-regimen="{{ $libro->tipo_regimen }}" data-tramo="{{ $libro->tramo }}"
                                            data-area="{{ $libro->area }}">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </button>

                                        <form action="{{ route('libros.destroy', $libro->id_libro) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('¿Estás seguro de eliminar este libro?')">
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
    <x-modal-show id="modalLibro" title="Libro">
        <form method="POST" id="formLibro" action="{{ route('libros.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="_method" id="methodLibro" value="POST">

            <div>
                <x-input-label value="Nombre del Libro" />
                <x-text-input name="nombre" id="nombre_libro" class="mt-1 block w-full" required />
            </div>

            <div>
                <x-input-label value="Tipo de Régimen" />
                <select name="tipo_regimen" id="regimen_libro"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="" disabled selected>Selecciona régimen</option>
                    <option value="mype">MYPE</option>
                    <option value="general">GENERAL</option>
                    <option value="especial">ESPECIAL</option>
                    <option value="otro">OTRO</option>
                </select>
            </div>

            <div>
                <x-input-label value="Tramo" />
                <select name="tramo" id="tramo_libro" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                    required>
                    <option value="" disabled selected>Selecciona tramo</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="otro">Otro</option>
                </select>
            </div>

            <div>
                <x-input-label value="Área" />
                <select name="area" id="area_libro" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                    required>
                    <option value="" disabled selected>Selecciona área</option>
                    <option value="legal">LEGAL</option>
                    <option value="contabilidad">CONTABILIDAD</option>
                </select>
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
            document.getElementById('formLibro').action = "{{ route('libros.store') }}";
            document.getElementById('methodLibro').value = "POST";
            document.getElementById('nombre_libro').value = "";
            document.getElementById('regimen_libro').value = "";
            document.getElementById('tramo_libro').value = "";
            document.getElementById('area_libro').value = "";
            toggleModal('modalLibro');
        }

        if (btnEditar) {
            const data = btnEditar.dataset;
            document.getElementById('formLibro').action = `/libros/${data.id}`;
            document.getElementById('methodLibro').value = "PUT";
            document.getElementById('nombre_libro').value = data.nombre;
            document.getElementById('regimen_libro').value = data.regimen;
            document.getElementById('tramo_libro').value = data.tramo;
            document.getElementById('area_libro').value = data.area;
            toggleModal('modalLibro');
        }
    });

    $(document).ready(function() {
        $('#tabla-libros').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            }
        });
    });
</script>
