<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Chofer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('choferes.update', $chofer->id_chofer) }}" class="space-y-6"
                    autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="id_empresas_clientes" :value="__('Empresa')" />
                        <select id="id_empresas_clientes" name="id_empresas_clientes"
                            class="select-empresa mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecciona una empresa</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id_empresas_clientes }}"
                                    {{ $chofer->id_empresas_clientes == $empresa->id_empresas_clientes ? 'selected' : '' }}>
                                    {{ $empresa->razon_social }} – {{ $empresa->ruc }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id_empresas_clientes')" class="mt-2" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="nombre" :value="__('Nombre')" />
                            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                                value="{{ old('nombre', $chofer->nombre) }}" required />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="dni" :value="__('DNI')" />
                            <x-text-input id="dni" name="dni" type="text" class="mt-1 block w-full"
                                value="{{ old('dni', $chofer->dni) }}" required />
                            <x-input-error :messages="$errors->get('dni')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="placa" :value="__('Placa')" />
                            <x-text-input id="placa" name="placa" type="text" class="mt-1 block w-full"
                                value="{{ old('placa', $chofer->placa) }}" />
                        </div>

                        <div>
                            <x-input-label for="marca" :value="__('Marca')" />
                            <x-text-input id="marca" name="marca" type="text" class="mt-1 block w-full"
                                value="{{ old('marca', $chofer->marca) }}" />
                        </div>

                        <div>
                            <x-input-label for="licencia" :value="__('Licencia')" />
                            <x-text-input id="licencia" name="licencia" type="text" class="mt-1 block w-full"
                                value="{{ old('licencia', $chofer->licencia) }}" />
                        </div>

                        <div>
                            <x-input-label for="certificado" :value="__('Certificado de Inscripción')" />
                            <x-text-input id="certificado" name="certificado" type="text" class="mt-1 block w-full"
                                value="{{ old('certificado', $chofer->certificado) }}" />
                        </div>

                        <div>
                            <x-input-label for="config_vehicular" :value="__('Configuración Vehicular')" />
                            <x-text-input id="config_vehicular" name="config_vehicular" type="text"
                                class="mt-1 block w-full"
                                value="{{ old('config_vehicular', $chofer->config_vehicular) }}" />
                        </div>

                        <div>
                            <x-input-label for="cng" :value="__('CNG')" />
                            <x-text-input id="cng" name="cng" type="text" class="mt-1 block w-full"
                                value="{{ old('cng', $chofer->cng) }}" />
                        </div>

                        <div>
                            <x-input-label for="peso" :value="__('Peso (toneladas)')" />
                            <x-text-input id="peso" name="peso" type="number" step="0.01"
                                class="mt-1 block w-full" value="{{ old('peso', $chofer->peso) }}" />
                        </div>

                        <div>
                            <x-input-label for="color" :value="__('Color Identificador')" />
                            <div class="flex items-center gap-2 mt-1">
                                <input id="color" name="color" type="color" value="{{ old('color', $chofer->color ?? '#3b82f6') }}" class="h-10 w-16 border border-gray-300 rounded cursor-pointer" />
                                <span class="text-xs text-gray-500">Color para la grilla de programación</span>
                            </div>
                            <x-input-error :messages="$errors->get('color')" class="mt-2" />
                        </div>

                        <div class="hidden">
                            <x-input-label for="estado" :value="__('Estado')" />
                            <select id="estado" name="estado"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="1" {{ $chofer->estado ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ !$chofer->estado ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Actualizar') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select-empresa').select2({
                placeholder: 'Selecciona una empresa',
                allowClear: true,
                width: 'resolve'
            });
        });
    </script>
@endpush
