<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($direccion->id_cliente)
                {{ __('Editar dirección del cliente:') }} <span
                    class="text-blue-500">{{ $direccion->cliente->razon_social ?? 'Cliente' }}</span>
            @elseif ($direccion->id_empresas_clientes)
                {{ __('Editar dirección de la empresa:') }} <span
                    class="text-blue-500">{{ $direccion->empresa->razon_social ?? 'Empresa' }}</span>
            @else
                {{ __('Editar Dirección') }}
            @endif
        </h2>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('direcciones.update', $direccion->id_direccion) }}" class="space-y-6"
                    autocomplete="off">
                    @csrf
                    @method('PUT')

                    {{-- Cliente o Empresa --}}
                    <div class="hidden">
                        @if ($direccion->id_empresas_clientes)
                            <x-input-label :value="__('Empresa')" />
                            <input type="hidden" name="id_empresas_clientes"
                                value="{{ $direccion->id_empresas_clientes }}">
                            <p class="mt-2 text-gray-700">
                                {{ $direccion->empresa->razon_social }} – {{ $direccion->empresa->ruc }}
                            </p>
                        @elseif ($direccion->id_cliente)
                            <x-input-label :value="__('Cliente')" />
                            <input type="hidden" name="id_cliente" value="{{ $direccion->id_cliente }}">
                            <p class="mt-2 text-gray-700">
                                {{ $direccion->cliente->razon_social ?? 'Cliente sin nombre' }}
                            </p>
                        @endif
                    </div>

                    {{-- Distribución en columnas --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <div class="md:col-span-2">
                            <x-input-label for="nombre" :value="__('Nombre de la Dirección')" />
                            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                                value="{{ old('nombre', $direccion->nombre) }}" required />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tipo_domicilio" :value="__('Tipo de Domicilio')" />
                            <select id="tipo_domicilio" name="tipo_domicilio"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Selecciona tipo --</option>
                                <option value="Fiscal" {{ $direccion->tipo_domicilio == 'Fiscal' ? 'selected' : '' }}>
                                    Fiscal</option>
                                <option value="Sucursal"
                                    {{ $direccion->tipo_domicilio == 'Sucursal' ? 'selected' : '' }}>Sucursal</option>
                                <option value="Punto de Llegada"
                                    {{ $direccion->tipo_domicilio == 'Punto de Llegada' ? 'selected' : '' }}>Punto de
                                    Llegada</option>
                                <option value="Emision"
                                    {{ $direccion->tipo_domicilio == 'Emision' ? 'selected' : '' }}>Lugar
                                    Emision
                                </option>
                            </select>
                            <x-input-error :messages="$errors->get('tipo_domicilio')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="distrito" :value="__('Distrito')" />
                            <x-text-input id="distrito" name="distrito" type="text"
                                value="{{ old('distrito', $direccion->distrito) }}" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('distrito')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="provincia" :value="__('Provincia')" />
                            <x-text-input id="provincia" name="provincia" type="text"
                                value="{{ old('provincia', $direccion->provincia) }}" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('provincia')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="region" :value="__('Región')" />
                            <x-text-input id="region" name="region" type="text"
                                value="{{ old('region', $direccion->region) }}" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('region')" class="mt-2" />
                        </div>

                        <div class="hidden">
                            <x-input-label for="estado" :value="__('Estado')" />
                            <select id="estado" name="estado"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="1" {{ $direccion->estado == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ $direccion->estado == 0 ? 'selected' : '' }}>Inactivo
                                </option>
                            </select>
                            <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Botón --}}
                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Actualizar Dirección') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
