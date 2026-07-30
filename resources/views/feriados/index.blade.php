<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Lista de Feriados</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <a href="{{ route('feriados.create') }}"
                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"><i
                    class="bi bi-plus-circle"></i> Nuevo Feriado</a>
            <br><br>

            <div class="bg-white shadow-lg rounded-lg overflow-x-auto">
                <table class="min-w-full table-auto" id="myTable">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b">Fecha</th>
                            <th class="px-4 py-2 border-b">Nombre</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feriados as $f)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($f->fecha_feriado)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 border-b">{{ $f->nombre_feriado }}</td>
                                <td class="px-4 py-2 border-b flex space-x-2">
                                    <a href="{{ route('feriados.edit', $f->id_feriado) }}"
                                        class="text-yellow-500 hover:text-yellow-600"><x-edit-icon /></a>
                                    <form action="{{ route('feriados.destroy', $f->id_feriado) }}" method="POST"
                                        onsubmit="return confirm('¿Eliminar este feriado?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:text-red-700"><x-delete-icon /></button>
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
