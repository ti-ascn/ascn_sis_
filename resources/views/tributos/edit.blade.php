<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Tributo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('tributos.update', $tributo->id_tributo) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="nombre" :value="__('Nombre')" />
                        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                            value="{{ old('nombre', $tributo->nombre) }}" required />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="codigo" :value="__('Código')" />
                        <x-text-input id="codigo" name="codigo" type="text" class="mt-1 block w-full"
                            value="{{ old('codigo', $tributo->codigo) }}" required />
                        <x-input-error :messages="$errors->get('codigo')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="tipo" :value="__('Tipo')" />
                        <select id="tipo" name="tipo"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="contabilidad" {{ $tributo->tipo == 'contabilidad' ? 'selected' : '' }}>
                                Contabilidad</option>
                            <option value="recursos" {{ $tributo->tipo == 'recursos' ? 'selected' : '' }}>Recursos
                            </option>
                        </select>
                        <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="estado" :value="__('Estado')" />
                        <select id="estado" name="estado"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="1" {{ $tributo->estado ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ !$tributo->estado ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Actualizar') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
