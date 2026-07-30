<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Producto') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div id="error-message" class="mb-4 p-4 bg-red-100 text-red-700 border border-red-300 rounded-md">
                    {{ session('error') }}
                </div>
            @endif
            <div class="bg-white shadow p-6 rounded-lg">
                <form method="POST" action="{{ route('productos.store') }}" autocomplete="off">
                    @csrf

                    <div class="mb-4">
                        <input type="hidden" name="id_inventario" value="{{ $inventario->id_inventario }}">
                        <p class="text-sm text-gray-600 mb-2">
                            Inventario: <strong>{{ $inventario->nombre_codigo }}</strong> —
                            {{ $inventario->empresa->razon_social }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="nombre" class="block text-sm font-medium">Nombre</label>
                        <input type="text" name="nombre" id="nombre" required
                            class="w-full border-gray-300 rounded-md">
                    </div>

                    <div class="mb-4">
                        <label for="unidad_medida" class="block text-sm font-medium">Unidad de Medida</label>
                        <select name="unidad_medida" id="unidad_medida" class="w-full border-gray-300 rounded-md"
                            required>
                            <option value="">Seleccione una unidad</option>
                            <option value="UNID" selected>UNID</option>
                            <option value="BOLSAS">BOLSAS</option>
                            <option value="CAJAS">CAJAS</option>
                            <option value="CIENTO">CIENTO</option>
                            <option value="KGM">KGM</option>
                            <option value="KILOS">KILOS</option>
                            <option value="LITROS">LITROS</option>
                            <option value="MTK">MTK</option>
                            <option value="MTR">MTR</option>
                            <option value="NIU">NIU</option>
                            <option value="PAR">PAR</option>
                            <option value="PK">PK</option>
                            <option value="PR">PR</option>
                            <option value="PZA">PZA</option>
                            <option value="RLL">RLL</option>
                            <option value="ROLLO">ROLLO</option>
                            <option value="U.M.">U.M.</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="precio_unitario" class="block text-sm font-medium">Precio Unitario</label>
                        <input type="number" step="0.0001" name="precio_unitario" id="precio_unitario" required
                            class="w-full border-gray-300 rounded-md">
                    </div>

                    <div class="mb-4">
                        <label for="peso" class="block text-sm font-medium">Peso (opcional)</label>
                        <input type="number" step="0.01" name="peso" id="peso"
                            class="w-full border-gray-300 rounded-md">
                    </div>

                    <div class="mb-4 hidden">
                        <label for="estado" class="block text-sm font-medium">Estado</label>
                        <select name="estado" id="estado" class="w-full border-gray-300 rounded-md">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
