<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Tipo de Pago') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex flex-wrap gap-4 items-center">
                <a href="{{ route('tipos_pagos.index') }}"
                    class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                    {{ __('Volver') }}
                </a>
            </div>
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('tipos_pagos.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 gap-4">
                        <!-- Nombre del Tipo de Pago -->
                        <div>
                            <x-input-label for="nombre" :value="__('Nombre')" />
                            <x-text-input id="nombre" name="nombre" type="text"
                                class="mt-1 block w-full uppercase" required />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        <!-- Descripción del Tipo de Pago -->
                        <div>
                            <x-input-label for="descripcion" :value="__('Descripción')" />
                            <x-text-input id="descripcion" name="descripcion" type="text" class="mt-1 block w-full"
                                placeholder="Opcional" />
                        </div>

                    </div>

                    <div class="flex justify-end mt-2">
                        <x-primary-button>{{ __('Guardar') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
