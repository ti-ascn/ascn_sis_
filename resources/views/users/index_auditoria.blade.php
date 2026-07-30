<x-app-layout>
    <x-slot name='header'>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de usuarios de Auditoría') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <!-- Crear Usuario Button -->
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col mb-6">
                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    @php
                        // Verificar el estado actual
                        $currentStatus =
                            App\Models\User::where('tipo_usuario', 3)->where('id_area', 7)->value('estado') ?? 0;

                        $buttonText = $currentStatus ? 'Desactivar Asistentes' : 'Activar Asistentes';
                        $buttonColor = $currentStatus
                            ? 'bg-green-500 hover:bg-green-600'
                            : 'bg-red-500 hover:bg-red-600';
                        $buttonIcon = $currentStatus ? 'bi-person-dash' : 'bi-person-check';
                    @endphp

                    <form action="{{ route('usuarios.toggleAuditoria') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-2 {{ $buttonColor }} text-white px-4 py-2 rounded-md transition duration-300">
                            <i class="bi {{ $buttonIcon }}"></i> {{ $buttonText }}
                        </button>
                    </form>
                @endif
            </div>
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
                            <th class="px-4 py-2 border-b">Acciones</th>
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
                                        Activo
                                    @else
                                        Inactivo
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        @if ($usuario->id !== 1)
                                            @if ($usuario->estado == 1)
                                                <!-- Botón para Editar -->
                                                {{-- <a href="{{ route('usuarios.edit', $usuario->id) }}"
                                                    class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #F59E0B; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#D97706';"
                                                    onmouseout="this.style.color='#F59E0B';">
                                                    <span class="tooltip-text">Editar</span>
                                                    <x-edit-icon />
                                                </a> --}}
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
                                                    <form action="{{ route('usuarios.activate', $usuario->id) }}"
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
</x-app-layout>
