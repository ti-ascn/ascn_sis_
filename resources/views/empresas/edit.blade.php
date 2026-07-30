<x-app-layout>
    <section>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Empresa') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-12 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <form method="POST" action="{{ route('empresas.update', $empresa) }}" class="mt-6 space-y-6">
                            @csrf
                            @method('PUT')

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Actualice la información de la empresa.') }}
                            </p>

                            <!-- Nombre de la empresa -->
                            <div>
                                <x-input-label for="nombre" :value="__('Nombre')" />
                                <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" 
                                    value="{{ old('nombre', $empresa->nombre) }}" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                            </div>

                            <!-- RUC -->
                            <div>
                                <x-input-label for="ruc" :value="__('RUC')" />
                                <x-text-input id="ruc" name="ruc" type="text" class="mt-1 block w-full" 
                                    value="{{ old('ruc', $empresa->ruc) }}" required pattern="\d{11}" maxlength="11" />
                                <x-input-error class="mt-2" :messages="$errors->get('ruc')" />
                            </div>

                            <!-- Dirección -->
                            <div>
                                <x-input-label for="direccion" :value="__('Dirección')" />
                                <x-text-input id="direccion" name="direccion" type="text" 
                                    class="mt-1 block w-full" value="{{ old('direccion', $empresa->direccion) }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('direccion')" />
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <x-input-label for="telefono" :value="__('Teléfono')" />
                                <x-text-input id="telefono" name="telefono" type="text" 
                                    class="mt-1 block w-full" value="{{ old('telefono', $empresa->telefono) }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Actualizar') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
