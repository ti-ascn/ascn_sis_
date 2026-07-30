<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Dirección') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('direcciones.store') }}" class="space-y-6" autocomplete="off">
                    @csrf

                    {{-- Empresa --}}
                    <div>
                        <x-input-label for="id_cliente" :value="__('Cliente')" />
                        <select name="id_cliente" class="select-cliente w-full">
                            <option value="">Selecciona un cliente</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id_cliente }}"
                                    {{ old('id_cliente', $clienteId) == $cliente->id_cliente ? 'selected' : '' }}>
                                    {{ $cliente->razon_social }} - {{ $cliente->ruc }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="id_empresas_clientes" value="">
                        <x-input-error :messages="$errors->get('id_cliente')" class="mt-2" />
                    </div>

                    {{-- Distribución en columnas --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <div class="md:col-span-2">
                            <x-input-label for="nombre" :value="__('Nombre de la Dirección')" />
                            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                                value="{{ old('nombre') }}" required />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tipo_domicilio" :value="__('Tipo de Domicilio')" />
                            <select id="tipo_domicilio" name="tipo_domicilio"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Selecciona tipo --</option>
                                <option value="Punto de Llegada" {{ old('tipo_domicilio') == 'Punto de Llegada' ? 'selected' : '' }}>Punto de Llegada
                                </option>
                                <option value="Fiscal" {{ old('tipo_domicilio') == 'Fiscal' ? 'selected' : '' }}>Fiscal
                                </option>
                                <option value="Emision" {{ old('lugar_emision') == 'Emision' ? 'selected' : '' }}>Lugar Emision
                                </option>
                            </select>
                            <x-input-error :messages="$errors->get('tipo_domicilio')" class="mt-2" />
                        </div>

                        <div class="hidden">
                            <x-input-label for="estado" :value="__('Estado')" />
                            <select id="estado" name="estado"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="1" {{ old('estado') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="distrito" :value="__('Distrito')" />
                            <x-text-input id="distrito" name="distrito" type="text" value="{{ old('distrito') }}"
                                class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('distrito')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="provincia" :value="__('Provincia')" />
                            <x-text-input id="provincia" name="provincia" type="text" value="{{ old('provincia') }}"
                                class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('provincia')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="region" :value="__('Región')" />
                            <x-text-input id="region" name="region" type="text" value="{{ old('region') }}"
                                class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('region')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Botón --}}
                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Guardar Dirección') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>

<script>
    $(document).ready(function() {
        $('.select-cliente').select2({
            placeholder: 'Selecciona un cliente',
            allowClear: true,
            width: 'resolve'
        });
    });
</script>
