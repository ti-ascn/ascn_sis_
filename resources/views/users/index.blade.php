<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />


            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->area->nombre == 'ADMINISTRACION')
                <!-- Crear Usuario Button -->
                @if (Auth::user()->tipo_usuario == 1)
                    <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col mb-6">
                        <a href="{{ route('usuarios.create') }}"
                            class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                            <i class="bi bi-person-plus-fill"></i> Crear Usuario
                        </a>
                    </div>
                @endif
                <!-- Usuarios Table -->
                <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                    <table class="min-w-full table-auto" id="myTable">
                        <thead>
                            <tr class="bg-gray-100 text-left">
                                <th class="px-4 py-2 border-b">ID</th>
                                <th class="px-4 py-2 border-b">Nombre</th>
                                <th class="px-4 py-2 border-b">Correo Electrónico</th>
                                <th class="px-4 py-2 border-b">Rol</th>
                                <th class="px-4 py-2 border-b">Area</th>
                                <th class="px-4 py-2 border-b">Estado</th>
                                @if (Auth::user()->tipo_usuario == 1)
                                    <th class="px-4 py-2 border-b">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $usuario)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">{{ $usuario->id }}</td>
                                    <td class="px-4 py-2 border-b">{{ $usuario->name }}</td>
                                    <td class="px-4 py-2 border-b">{{ $usuario->email }}</td>
                                    <td class="px-4 py-2 border-b">
                                        @if ($usuario->tipo_usuario == 1)
                                            Administrador
                                        @elseif ($usuario->tipo_usuario == 2)
                                            Jefe
                                        @elseif ($usuario->tipo_usuario == 3)
                                            Asistente
                                        @else
                                            Desconocido
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">{{ $usuario->area->nombre ?? 'SIN ÁREA' }}</td>
                                    <td class="px-4 py-2 border-b">
                                        @if ($usuario->estado == 1)
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
                                    @if (Auth::user()->tipo_usuario == 1)
                                        <td class="px-4 py-2 border-b">
                                            <div class="flex space-x-2">
                                                @if ($usuario->id !== 1)
                                                    @if ($usuario->estado == 1)
                                                        <!-- Botón para Editar -->
                                                        <a href="{{ route('usuarios.edit', $usuario->id) }}"
                                                            class="flex items-center py-1 rounded-md tooltip"
                                                            style="color: #F59E0B; transition: color 0.3s;"
                                                            onmouseover="this.style.color='#D97706';"
                                                            onmouseout="this.style.color='#F59E0B';">
                                                            <span class="tooltip-text">Editar</span>
                                                            <x-edit-icon />
                                                        </a>
                                                        @if (Auth::id() !== $usuario->id)
                                                            <!-- Formulario para Desactivar -->
                                                            <form action="{{ route('usuarios.destroy', $usuario->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('¿Estás seguro de que deseas desactivar este usuario?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="flex items-center py-1 rounded-md tooltip"
                                                                    style="color: #dc2626; transition: color 0.3s;"
                                                                    onmouseover="this.style.color='#b91c1c';"
                                                                    onmouseout="this.style.color='#dc2626';">
                                                                    <span class="tooltip-text">Desactivar</span>
                                                                    <x-user-off-icon />
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        <!-- Botón para Activar -->
                                                        @if (Auth::id() !== $usuario->id)
                                                            <form
                                                                action="{{ route('usuarios.activate', $usuario->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('¿Estás seguro de que deseas activar este usuario?')">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit"
                                                                    class="flex items-center py-1 rounded-md tooltip"
                                                                    style="color: #10b981; transition: color 0.3s;"
                                                                    onmouseover="this.style.color='#059669';"
                                                                    onmouseout="this.style.color='#10b981';">
                                                                    <span class="tooltip-text">Activar</span>
                                                                    <x-user-on-icon />
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <h1>Vacio</h1>
            @endif
        </div>
    </div>
    </div>
</x-app-layout>
