<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Direcciones de <span class="text-blue-500">{{ $cliente->razon_social }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />
            <div class="flex gap-4 mb-4">
                <a href="{{ route('clientes.index') }}"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">← Volver a Clientes</a>
                <a href="{{ route('clientes.direcciones.create', $cliente->id_cliente) }}"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"><i
                        class="bi bi-geo-alt-fill"></i> Crear Dirección</a>
            </div>

            <div class="bg-white shadow-lg rounded-lg overflow-x-auto">
                <table class="min-w-full table-auto" id="myTable">
                    <thead class="bg-gray-100">
                        <tr>
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
                            <tr>
                                <td class="px-4 py-2 border-b">{{ $direccion->nombre }}</td>
                                <td class="px-4 py-2 border-b">{{ $direccion->tipo_domicilio }}</td>
                                <td class="px-4 py-2 border-b">{{ $direccion->distrito ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">{{ $direccion->provincia ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">{{ $direccion->region ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($direccion->estado)
                                        <span
                                            class="bg-green-500 text-white px-3 py-1 rounded-full text-xs">Activo</span>
                                    @else
                                        <span
                                            class="bg-gray-500 text-white px-3 py-1 rounded-full text-xs">Inactivo</span>
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
