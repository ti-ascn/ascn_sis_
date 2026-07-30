<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Producto') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow p-6 rounded-lg">
                <form method="POST" action="{{ route('productos.update', $producto->id_producto) }}" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id_inventario" value="{{ $producto->id_inventario }}">

                    <div class="mb-4">
                        <label for="nombre" class="block text-sm font-medium">Nombre</label>
                        <input type="text" name="nombre" id="nombre"
                            value="{{ old('nombre', $producto->nombre) }}" required
                            class="w-full border-gray-300 rounded-md">
                    </div>

                    <div class="mb-4">
                        <label for="unidad_medida_modal" class="block text-sm font-medium">Unidad de Medida</label>
                        <select name="unidad_medida" id="unidad_medida_modal" required
                            class="w-full border-gray-300 rounded-md">
                            <option value="">Seleccione una unidad</option>

                            @foreach ($unidades as $u)
                                <option value="{{ $u->nombre }}">{{ $u->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="precio_unitario" class="block text-sm font-medium">Precio Unitario</label>
                        <input type="number" step="0.0001" name="precio_unitario" id="precio_unitario"
                            value="{{ old('precio_unitario', $producto->precio_unitario) }}" required
                            class="w-full border-gray-300 rounded-md">
                    </div>

                    <div class="mb-4">
                        <label for="peso" class="block text-sm font-medium">Peso (opcional)</label>
                        <input type="number" step="0.01" name="peso" id="peso"
                            value="{{ old('peso', $producto->peso) }}" class="w-full border-gray-300 rounded-md">
                    </div>

                    <div class="mb-4 hidden">
                        <label for="estado" class="block text-sm font-medium">Estado</label>
                        <select name="estado" id="estado" class="w-full border-gray-300 rounded-md">
                            <option value="1" {{ $producto->estado == 1 ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ $producto->estado == 0 ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>Actualizar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
