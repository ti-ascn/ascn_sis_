<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Direcciones de <span class="text-blue-500">{{ $empresa->razon_social }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('empresas_clientes_auditoria.all') }}"
                    class="block bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white text-center w-full md:w-auto">
                    {{ __('← Volver a Empresas') }}
                </a>
                <a href="{{ route('direcciones.create_empresa', $empresa->id_empresas_clientes) }}"
                    class="block bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white text-center w-full md:w-auto">
                    <i class="bi bi-geo-alt-fill"></i> {{ __('Crear Dirección') }}
                </a>
            </div>

            <br />

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive nowrap" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Nombre</th>
                            <th class="px-4 py-2 border-b">Tipo</th>
                            <th class="px-4 py-2 border-b">Distrito</th>
                            <th class="px-4 py-2 border-b">Provincia</th>
                            <th class="px-4 py-2 border-b">Región</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($direcciones as $direccion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $direccion->nombre }}</td>
                                <td class="px-4 py-2 border-b">{{ $direccion->tipo_domicilio }}</td>
                                <td class="px-4 py-2 border-b">{{ $direccion->distrito ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">{{ $direccion->provincia ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">{{ $direccion->region ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($direccion->estado)
                                        <span
                                            class="inline-block px-3 py-1 text-xs font-semibold text-white bg-green-500 rounded-full">
                                            Activo
                                        </span>
                                    @else
                                        <span
                                            class="inline-block px-3 py-1 text-xs font-semibold text-white bg-red-500 rounded-full">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2 justify-center">
                                        <a href="{{ route('direcciones.edit', $direccion->id_direccion) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #F59E0B; transition: color 0.3s;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </a>

                                        {{-- <form action="{{ route('direcciones.destroy', $direccion->id_direccion) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta dirección?')"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';">
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form> --}}
                                        {{-- Botón Activar/Desactivar --}}
                                        @if ($direccion->estado)
                                            <form
                                                action="{{ route('direcciones.desactivar', $direccion->id_direccion) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626;" onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';">
                                                    <x-deactivate-icon />
                                                    <span class="tooltip-text">Desactivar</span>
                                                </button>
                                            </form>
                                        @else
                                            <form
                                                action="{{ route('direcciones.reactivar', $direccion->id_direccion) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #16a34a;" onmouseover="this.style.color='#15803d';"
                                                    onmouseout="this.style.color='#16a34a';">
                                                    <x-activate-icon />
                                                    <span class="tooltip-text">Activar</span>
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
