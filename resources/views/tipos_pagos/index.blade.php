<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Tipos de Pago') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('tipos_pagos.create') }}"
                    class="block bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white text-center w-full md:w-auto">
                    {{ __('Registrar Tipo de Pago') }}
                </a>
            </div>

            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive nowrap" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Nombre</th>
                            <th class="px-4 py-2 border-b">Descripción</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tipos_pago as $tipo)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $tipo->id_tipo_pago }}</td>
                                <td class="px-4 py-2 border-b">{{ $tipo->nombre }}</td>
                                <td class="px-4 py-2 border-b">{{ $tipo->descripcion ?? 'Sin descripción' }}</td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        <!-- Botón de Editar -->
                                        <a href="{{ route('tipos_pagos.edit', $tipo->id_tipo_pago) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #F59E0B; transition: color 0.3s;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </a>

                                        <!-- Botón de Eliminar -->
                                        <form action="{{ route('tipos_pagos.destroy', $tipo->id_tipo_pago) }}"
                                            method="POST" style="display:inline;"
                                            onsubmit="return confirm('¿Seguro que deseas eliminar este tipo de pago?')">
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
