<x-app-layout>
    <section>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Compras') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <div class="max-w-3xl mx-auto">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ __('Editar Registro de Compras') }}
                            </h3>
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <form method="POST"
                            action="{{ route('compras_empresas_clientes.update', $comprasCliente->id_compras) }}"
                            class="space-y-6">
                            @csrf
                            @method('PUT') <!-- Usamos PUT para la actualización -->

                            <!-- Campo oculto para el ID del usuario -->
                            <input type="hidden" name="id_usuario" value="{{ auth()->id() }}">

                            <!-- Selección de Registro -->
                            <div>
                                <x-input-label for="registro" :value="__('Registro')" />
                                <div class="mt-2 space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="registro" value="si" class="form-radio"
                                            {{ $comprasCliente->registro == 'si' ? 'checked' : '' }} required>
                                        <span class="ml-2">Sí</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="registro" value="no" class="form-radio"
                                            {{ $comprasCliente->registro == 'no' ? 'checked' : '' }} required>
                                        <span class="ml-2">No</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="registro" value="na" class="form-radio"
                                            {{ $comprasCliente->registro == 'na' ? 'checked' : '' }} required>
                                        <span class="ml-2">No Aplica</span>
                                    </label>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('registro')" />
                            </div>

                            <!-- Observación -->
                            <div>
                                <x-input-label for="observacion" :value="__('Observación')" />
                                <x-text-input id="observacion" name="observacion" type="text"
                                    class="mt-1 block w-full" value="{{ $comprasCliente->observacion }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('observacion')" />
                            </div>

                            <!-- Fecha (solo lectura) -->
                            {{-- <div>
                                <x-input-label for="fecha" :value="__('Fecha')" />
                                <x-text-input id="fecha" name="fecha" type="text"
                                    class="mt-1 block w-full bg-gray-100" readonly
                                    value="{{ $comprasCliente->fecha }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('fecha')" />
                            </div> --}}

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
