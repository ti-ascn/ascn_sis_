<x-app-layout>
    <section>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Registrar transacción para la cuenta: ') . $cuenta->nombre . (' ') . $cuenta->tipo }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <div class="max-w-3xl mx-auto">
                        <!-- Título y Descripción -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ __('Detalles de la Transacción') }}
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ __('Ingrese los detalles de la transacción para la cuenta: ') . $cuenta->nombre }}
                            </p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-4">
                            <a href="{{ url()->previous() }}"
                                class="inline-block bg-gray-300 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-300 text-gray-700">
                                ← Atrás
                            </a>
                        </div>

                        <!-- Mostrar saldo actual antes del formulario -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-800">
                                {{ __('Saldo actual de la cuenta: ') }}
                                <span
                                    class="font-extrabold text-2xl 
                    @if ($cuenta->saldo_actual == 0) text-red-600 
                    @else text-green-600 @endif">
                                    @if ($cuenta->tipo == 'Soles')
                                        S/ {{ number_format($cuenta->saldo_actual, 2) }}
                                    @elseif ($cuenta->tipo == 'Dolares')
                                        $ {{ number_format($cuenta->saldo_actual, 2) }}
                                    @endif
                                </span>
                            </h4>
                        </div>


                        <!-- Formulario -->
                        <form method="POST" action="{{ route('transacciones.store') }}" autocomplete="off"
                            class="space-y-6">
                            @csrf

                            <!-- Campo oculto para ID de Cuenta -->
                            <input type="hidden" name="id_cuenta" value="{{ $cuenta->id_cuenta }}">

                            <!-- Contenedor de campos -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Descripción -->
                                <div>
                                    <x-input-label for="descripcion" :value="__('Descripción')" />
                                    <x-text-input id="descripcion" name="descripcion" type="text"
                                        class="mt-1 block w-full" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('descripcion')" />
                                </div>

                                <!-- Monto -->
                                <div>
                                    <x-input-label for="monto" :value="__('Monto')" />
                                    <x-text-input id="monto" name="monto" type="number" step="0.01"
                                        placeholder="0.00" class="mt-1 block w-full" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('monto')" />
                                </div>

                                <!-- Tipo de Transacción -->
                                <div>
                                    <x-input-label for="tipo" :value="__('Tipo de Transacción')" />
                                    <select id="tipo" name="tipo"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="egreso">{{ __('Egreso') }}</option>
                                        <option value="ingreso">{{ __('Ingreso') }}</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('tipo')" />
                                </div>
                            </div>

                            <!-- Botón Guardar -->
                            <div class="flex justify-end mt-6">
                                <x-primary-button class="px-6 py-2">
                                    {{ __('Registrar Transacción') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
