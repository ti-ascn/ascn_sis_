<x-app-layout>
    <section>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Crear cuenta para la empresa: ') . $empresa->nombre }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <div class="max-w-3xl mx-auto">
                        <!-- Título y Descripción -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ __('Información de la Cuenta') }}
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ __('Ingrese los detalles de la cuenta que desea registrar para la empresa: ') . $empresa->nombre }}
                            </p>
                        </div>

                        <div class="mb-4">
                            <a href="{{ url()->previous() }}"
                                class="inline-block bg-gray-300 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-300 text-gray-700">
                                ← Atrás
                            </a>
                        </div>

                        <!-- Formulario -->
                        <form method="POST" action="{{ route('cuentas.store') }}" autocomplete="off" class="space-y-6">
                            @csrf

                            <!-- Campo oculto para ID de Empresa -->
                            <input type="hidden" name="id_empresa" value="{{ $empresa->id_empresa }}">

                            <!-- Contenedor de campos -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Nombre -->
                                <div>
                                    <x-input-label for="nombre" :value="__('Banco')" />
                                    <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                                        required />
                                    <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                                </div>

                                <!-- Número de Cuenta -->
                                <div>
                                    <x-input-label for="numero_cuenta" :value="__('Número de Cuenta')" />
                                    <x-text-input id="numero_cuenta" name="numero_cuenta" type="text"
                                        class="mt-1 block w-full" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('numero_cuenta')" />
                                </div>

                                <!-- Número Interbancario -->
                                <div>
                                    <x-input-label for="numero_interbancario" :value="__('Número Interbancario')" />
                                    <x-text-input id="numero_interbancario" name="numero_interbancario" type="text"
                                        class="mt-1 block w-full" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('numero_interbancario')" />
                                </div>

                                <!-- Tipo -->
                                <div>
                                    <x-input-label for="tipo" :value="__('Tipo de Cuenta')" />
                                    <select id="tipo" name="tipo"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="Soles">{{ __('Soles') }}</option>
                                        <option value="Dolares">{{ __('Dolares') }}</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('tipo')" />
                                </div>

                                <!-- Saldo Actual -->
                                <div class="sm:col-span-2">
                                    <x-input-label for="saldo_actual" :value="__('Saldo Actual')" />
                                    <x-text-input id="saldo_actual" name="saldo_actual" type="number" step="0.01" placeholder="0.00"
                                        class="mt-1 block w-full" />
                                    <x-input-error class="mt-2" :messages="$errors->get('saldo_actual')" />
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
