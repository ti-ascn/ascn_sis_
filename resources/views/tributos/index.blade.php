<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Tributos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <a href="{{ route('tributos.create') }}"
                        class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-plus-circle"></i> Crear Tributo
                    </a>
                    @if (Auth::user()->tipo_usuario == 1)
                        <a href="{{ route('calendario.index') }}"
                            class="flex items-center gap-2 bg-red-500 px-4 py-2 rounded-md hover:bg-red-600 transition duration-300 text-white text-center w-full md:w-auto">
                            <i class="bi bi-calendar"></i> Ver Vencimientos ¡NO MOVER!
                        </a>
                    @endif
                @endif
            </div>

            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive nowrap" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Nombre</th>
                            <th class="px-4 py-2 border-b">Código</th>
                            <th class="px-4 py-2 border-b">Tipo</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tributos as $tributo)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $tributo->id_tributo }}</td>
                                <td class="px-4 py-2 border-b">{{ $tributo->nombre }}</td>
                                <td class="px-4 py-2 border-b">{{ $tributo->codigo }}</td>
                                <td class="px-4 py-2 border-b">{{ ucfirst($tributo->tipo) }}</td>
                                <td class="px-4 py-2 border-b">{{ $tributo->estado ? 'Activo' : 'Inactivo' }}</td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('tributos.edit', $tributo->id_tributo) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #F59E0B; transition: color 0.3s;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </a>
                                        <form action="{{ route('tributos.destroy', $tributo->id_tributo) }}"
                                            method="POST" style="display:inline;"
                                            onsubmit="return confirm('¿Estás seguro de que deseas eliminar este tributo?')">
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
</x-app-layout>
