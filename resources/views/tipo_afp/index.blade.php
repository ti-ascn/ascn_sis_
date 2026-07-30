<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tipos de AFP</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col mb-6">
                <a href="{{ route('tipo-afp.create') }}"
                    class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 flex items-center gap-2">
                    <i class="bi bi-plus-circle"></i> Nuevo Tipo AFP
                </a>
                <a href="{{ route('feriados.index') }}"
                    class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center gap-2">
                    <i class="bi bi-calendar"></i> Feriados
                </a>
            </div>

            <div class="bg-white shadow-lg rounded-lg overflow-x-auto">
                <table class="min-w-full table-auto" id="myTable">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Nombre</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tipoAfp as $afp)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $afp->id_afp }}</td>
                                <td class="px-4 py-2 border-b">{{ $afp->nombre }}</td>
                                <td class="px-4 py-2 border-b">{{ $afp->estado ? 'Activo' : 'Inactivo' }}</td>
                                <td class="px-4 py-2 border-b flex space-x-2">
                                    <a href="{{ route('tipo-afp.edit', $afp->id_afp) }}"
                                        class="text-yellow-500 hover:text-yellow-600">
                                        <x-edit-icon />
                                    </a>
                                    <form action="{{ route('tipo-afp.destroy', $afp->id_afp) }}" method="POST"
                                        onsubmit="return confirm('¿Eliminar este tipo AFP?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700">
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
    </div>
</x-app-layout>
