<x-app-layout>
    <section>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Crear empresa') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <div class="max-w-3xl mx-auto">
                        <!-- Título y Descripción -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ __('Información de la Empresa') }}
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ __('Ingrese los detalles de la empresa que desea registrar.') }}
                            </p>
                        </div>

                        <div class="mb-4">
                            <a href="{{ url()->previous() }}"
                                class="inline-block bg-gray-300 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-300 text-gray-700">
                                ← Atrás
                            </a>
                        </div>
                        <!-- Formulario -->
                        {{-- ! esto sirve para que no se autocomplete el formulario autocomplete="off" --}}
                        <form method="POST" action="{{ route('empresas.store') }}" autocomplete="off"
                            class="space-y-6">
                            @csrf

                            <!-- Contenedor de campos (dos columnas en pantallas grandes) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Nombre -->
                                <div>
                                    <x-input-label for="nombre" :value="__('Nombre')" />
                                    <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                                        required autofocus />
                                    <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                                </div>

                                <!-- RUC -->
                                <div>
                                    <x-input-label for="ruc" :value="__('RUC')" />
                                    <x-text-input id="ruc" name="ruc" type="text" class="mt-1 block w-full"
                                        required pattern="\d{11}" maxlength="11" inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);" />
                                    <x-input-error class="mt-2" :messages="$errors->get('ruc')" />
                                </div>

                                <!-- Dirección -->
                                <div class="sm:col-span-2">
                                    <x-input-label for="direccion" :value="__('Dirección')" />
                                    <x-text-input id="direccion" name="direccion" type="text" placeholder="Opcional"
                                        class="mt-1 block w-full" />
                                    <x-input-error class="mt-2" :messages="$errors->get('direccion')" />
                                </div>

                                <!-- Teléfono -->
                                <div class="sm:col-span-2">
                                    <x-input-label for="telefono" :value="__('Teléfono')" />
                                    <x-text-input id="telefono" name="telefono" type="text" placeholder="Opcional"
                                        class="mt-1 block w-full" />
                                    <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
                                </div>
                            </div>

                            <!-- Botón Guardar -->
                            <div class="flex justify-end mt-6">
                                <x-primary-button class="px-6 py-2">
                                    {{ __('Guardar') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
