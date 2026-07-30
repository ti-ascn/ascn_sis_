<x-app-layout>
    <section>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar PDT') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <div class="max-w-3xl mx-auto">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ __('Editar Registro PDT') }}
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

                        <form method="POST" action="{{ route('pdt_empresas_clientes.update', $pdtCliente->id_pdt) }}" class="space-y-6">
                            @csrf
                            @method('PUT') <!-- Usamos PUT para la actualización -->

                            <!-- Campo oculto para el ID del usuario -->
                            <input type="hidden" name="id_usuario" value="{{ auth()->id() }}">

                            <!-- Campo "si/no" -->
                            <div>
                                <x-input-label :value="__('¿Aplica PDT?')" />
                                <div class="mt-2 space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="opcion_si_no" value="si" class="form-radio" {{ $pdtCliente->opcion_si_no == 'si' ? 'checked' : '' }} required>
                                        <span class="ml-2">Sí</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="opcion_si_no" value="no" class="form-radio" {{ $pdtCliente->opcion_si_no == 'no' ? 'checked' : '' }} required>
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('opcion_si_no')" />
                            </div>

                            <!-- Estado -->
                            <div style="display: none;">
                                <x-input-label :value="__('Estado')" />
                                <div class="mt-2 space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="estado" value="dentro" class="form-radio" {{ $pdtCliente->estado == 'dentro' ? 'checked' : '' }} required>
                                        <span class="ml-2">Dentro</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="estado" value="fuera" class="form-radio" {{ $pdtCliente->estado == 'fuera' ? 'checked' : '' }} required>
                                        <span class="ml-2">Fuera</span>
                                    </label>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('estado')" />
                            </div>

                            <!-- Observación -->
                            <div>
                                <x-input-label for="observacion" :value="__('Observación')" />
                                <x-text-input id="observacion" name="observacion" type="text" class="mt-1 block w-full" value="{{ $pdtCliente->observacion }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('observacion')" />
                            </div>

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