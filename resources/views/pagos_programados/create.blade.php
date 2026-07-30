<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Pago Programado') }}
        </h2>
    </x-slot>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('pagos_programados.index') }}"
                    class="block bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                    {{ __('Volver') }}
                </a>
            </div>
            <div class="p-6 bg-white shadow sm:rounded-lg">
                <form method="POST" action="{{ route('pagos_programados.store') }}" autocomplete="off" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="descripcion" :value="__('Descripción')" />
                            <x-text-input id="descripcion" name="descripcion" type="text" class="mt-1 block w-full"
                                required />
                            <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="monto" :value="__('Monto (S/)')" />
                            <x-text-input id="monto" name="monto" type="number" step="0.01"
                                class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('monto')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="fecha_inicio" :value="__('Fecha de Inicio')" />
                            <x-text-input id="fecha_inicio" name="fecha_inicio" type="date" class="mt-1 block w-full"
                                required />
                            <x-input-error :messages="$errors->get('fecha_inicio')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="fecha_fin" :value="__('Fecha de Fin')" />
                            <x-text-input id="fecha_fin" name="fecha_fin" type="date" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('fecha_fin')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="frecuencia" :value="__('Frecuencia')" />
                        <select id="frecuencia" name="frecuencia"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="una_vez">Una vez</option>
                            <option value="diario">Diario</option>
                            <option value="semanal">Semanal</option>
                            <option value="mensual">Mensual</option>
                            <option value="anual">Anual</option>
                        </select>
                        <x-input-error :messages="$errors->get('frecuencia')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="id_tipo_pago" :value="__('Tipo de Pago')" />
                        <select id="id_tipo_pago" name="id_tipo_pago"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            @foreach ($tiposPago as $tipo)
                                <option value="{{ $tipo->id_tipo_pago }}"
                                    {{ old('id_tipo_pago') == $tipo->id_tipo_pago ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id_tipo_pago')" class="mt-2" />
                        <a href="{{ route('tipos_pagos.create') }}" class="text-blue-600 underline text-sm mt-4 block">
                            <i class="bi bi-box-arrow-up-right"></i> Crear nuevo tipo de pago
                        </a>
                    </div>


                    <div class="flex justify-end mt-6">
                        <x-primary-button>{{ __('Guardar') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
