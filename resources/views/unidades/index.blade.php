<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Unidades de Medida</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto">
        <x-success-error-bagdes />

        <button onclick="toggleModal('modalCrearUnidad')"
            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded mb-4">
            + Nueva Unidad
        </button>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full table-auto" id="myTable">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border-b">Nombre</th>
                        <th class="px-4 py-2 border-b">Estado</th>
                        <th class="px-4 py-2 border-b text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($unidades as $u)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b">{{ $u->nombre }}</td>

                            <td class="px-4 py-2 border-b">
                                @if ($u->estado == 1)
                                    <span
                                        class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                        Activo
                                    </span>
                                @else
                                    <span
                                        class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-2 border-b text-center flex justify-center gap-3">

                                <!-- Editar -->
                                <a onclick="openEditModal({{ $u->id_unidad_medida }}, '{{ $u->nombre }}')"
                                    class="flex items-center py-1 rounded-md tooltip cursor-pointer"
                                    style="color: #F59E0B; transition: color 0.3s;"
                                    onmouseover="this.style.color='#D97706';" onmouseout="this.style.color='#F59E0B';">
                                    <span class="tooltip-text">Editar</span>
                                    <x-edit-icon />

                                </a>

                                <!-- Activar/Desactivar -->

                                <form action="{{ route('unidades.toggle', $u->id_unidad_medida) }}" method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('¿Deseas cambiar el estado de esta empresa?')">
                                    @csrf
                                    @method('PUT')

                                    <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                        style="color: #ca8a04; transition: color 0.3s;"
                                        onmouseover="this.style.color='#a16207';"
                                        onmouseout="this.style.color='#ca8a04';">

                                        <span class="tooltip-text">
                                            {{ $u->estado == 1 ? 'Desactivar' : 'Activar' }}
                                        </span>

                                        <x-delete-icon />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>


<!-- Modal Crear -->
<x-modal-show id="modalCrearUnidad" title="Crear nueva unidad">
    <form method="POST" action="{{ route('unidades.store') }}">
        @csrf

        <label class="block mb-1 text-sm">Nombre</label>
        <input type="text" name="nombre" class="w-full border-gray-300 rounded" required>

        <div class="flex justify-end mt-4">
            <x-primary-button>Guardar</x-primary-button>
        </div>
    </form>
</x-modal-show>


<!-- Modal Editar -->
<x-modal-show id="modalEditarUnidad" title="Editar unidad">
    <form id="formEditarUnidad" method="POST">
        @csrf
        @method('PUT')

        <label class="block mb-1 text-sm">Nombre</label>
        <input type="text" id="editNombre" name="nombre" class="w-full border-gray-300 rounded" required>

        <div class="flex justify-end mt-4">
            <x-primary-button>Actualizar</x-primary-button>
        </div>
    </form>
</x-modal-show>

<script>
    function openEditModal(id, nombre) {
        toggleModal('modalEditarUnidad');

        document.getElementById('editNombre').value = nombre;
        document.getElementById('formEditarUnidad').action =
            '/unidades/' + id;
    }
</script>
