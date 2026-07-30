<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Empresas') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <!-- Crear Empresa button -->
            @if (Auth::user()->tipo_usuario != 3)
                <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                    <a href="{{ route('empresas.create') }}"
                        class="block bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                        {{ __('Crear Empresa') }}
                    </a>
                </div>
            @endif
            <br>

            <!-- Empresas Table -->
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap display responsive nowrap" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Nombre</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">Dirección</th>
                            <th class="px-4 py-2 border-b">Teléfono</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $empresa)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $empresa->id_empresa }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->nombre }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->ruc }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->direccion ?: 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->telefono ?: 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        <!-- Botón para ver cuentas -->
                                        <a href="{{ route('empresas.showCuentas', $empresa->id_empresa) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #10B981; transition: color 0.3s;"
                                            onmouseover="this.style.color='#059669';"
                                            onmouseout="this.style.color='#10B981';">
                                            <span class="tooltip-text">Cuentas</span>
                                            <!-- Usar el componente del ícono del banco -->
                                            <x-bank-icon />
                                        </a>

                                        <!-- Botón para ver transacciones -->
                                        {{-- <a href="{{ route('empresas.showTransacciones', $empresa->id_empresa) }}"
                                            class="btn btn-info btn-sm bg-blue-500 hover:bg-blue-600 py-1 px-3 rounded-md">
                                            Ver transacciones
                                        </a> --}}
                                        @if (Auth::check() && Auth::user()->tipo_usuario != 3)
                                            <!-- Botón para editar -->
                                            <a href="{{ route('empresas.edit', $empresa->id_empresa) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #F59E0B; transition: color 0.3s;"
                                                onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />

                                            </a>

                                            <!-- Formulario para eliminar -->

                                            <form action="{{ route('empresas.destroy', $empresa->id_empresa) }}"
                                                method="POST" style="display:inline;"
                                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta empresa?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';" >
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form>
                                        @endif
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
