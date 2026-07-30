<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($guia->id_cliente)
                {{ __('Editar guía del cliente:') }} <span
                    class="text-blue-500">{{ $guia->cliente->razon_social ?? 'Cliente' }}</span>
            @elseif ($guia->id_empresas_clientes)
                {{ __('Editar guía de la empresa:') }} <span
                    class="text-blue-500">{{ $guia->empresa->razon_social ?? 'Empresa' }}</span>
            @else
                {{ __('Editar Guía') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">

                {{-- Botón Atrás --}}
                <div class="mb-4">
                    <a href="{{ url()->previous() }}"
                        class="inline-block bg-gray-300 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-300 text-gray-700">
                        ← Atrás
                    </a>
                </div>

                <form method="POST" action="{{ route('guias.update', $guia->id_guia) }}" class="space-y-6"
                    autocomplete="off">
                    @csrf
                    @method('PUT')

                    {{-- Cliente o Empresa --}}
                    <div class="hidden">
                        @if ($guia->id_empresas_clientes)
                            <x-input-label :value="__('Empresa')" />
                            <input type="hidden" name="id_empresas_clientes" value="{{ $guia->id_empresas_clientes }}">
                            <p class="mt-2 text-gray-700">
                                {{ $guia->empresa->razon_social }} – {{ $guia->empresa->ruc }}
                            </p>
                        @elseif ($guia->id_cliente)
                            <x-input-label :value="__('Cliente')" />
                            <input type="hidden" name="id_cliente" value="{{ $guia->id_cliente }}">
                            <p class="mt-2 text-gray-700">
                                {{ $guia->cliente->razon_social ?? 'Cliente sin nombre' }}
                            </p>
                        @endif
                    </div>

                    {{-- Campos --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="moneda" value="Moneda" />
                            <select name="moneda" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="PEN" {{ old('moneda', $guia->moneda ?? 'PEN') == 'PEN' ? 'selected' : '' }}>Soles (PEN)</option>
                                <option value="USD" {{ old('moneda', $guia->moneda ?? 'PEN') == 'USD' ? 'selected' : '' }}>Dólares (USD)</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="monto" :value="__('Monto')" />
                            <x-text-input name="monto" type="number" step="0.01" class="mt-1 block w-full"
                                value="{{ old('monto', $guia->monto) }}" />
                            <x-input-error :messages="$errors->get('monto')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tipo_cambio" :value="__('Tipo de Cambio')" />
                            <x-text-input name="tipo_cambio" type="number" step="0.001" class="mt-1 block w-full"
                                value="{{ old('tipo_cambio', $guia->tipo_cambio) }}" />
                            <small class="text-gray-500">Solo para USD</small>
                            <x-input-error :messages="$errors->get('tipo_cambio')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="recepcion" :value="__('Recepción')" />
                            <x-text-input name="recepcion" type="date" class="mt-1 block w-full"
                                value="{{ old('recepcion', $guia->recepcion) }}" required />
                            <x-input-error :messages="$errors->get('recepcion')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="programado" :value="__('Programado')" />
                            <select name="programado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="listo" {{ $guia->programado == 'listo' ? 'selected' : '' }}>Listo
                                </option>
                                <option value="pendiente" {{ $guia->programado == 'pendiente' ? 'selected' : '' }}>
                                    Pendiente</option>
                            </select>
                            <x-input-error :messages="$errors->get('programado')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="emitido" :value="__('Emitido')" />
                            <select name="emitido" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="listo" {{ $guia->emitido == 'listo' ? 'selected' : '' }}>Listo</option>
                                <option value="pendiente" {{ $guia->emitido == 'pendiente' ? 'selected' : '' }}>
                                    Pendiente</option>
                            </select>
                            <x-input-error :messages="$errors->get('emitido')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="grr" :value="__('GRR')" />
                            <select name="grr" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="-" {{ $guia->grr == '-' ? 'selected' : '' }}>-</option>
                                <option value="si" {{ $guia->grr == 'si' ? 'selected' : '' }}>Sí</option>
                                <option value="no" {{ $guia->grr == 'no' ? 'selected' : '' }}>No</option>
                            </select>
                            <x-input-error :messages="$errors->get('grr')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="grt" :value="__('GRT')" />
                            <select name="grt" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="-" {{ $guia->grt == '-' ? 'selected' : '' }}>-</option>
                                <option value="si" {{ $guia->grt == 'si' ? 'selected' : '' }}>Sí</option>
                                <option value="no" {{ $guia->grt == 'no' ? 'selected' : '' }}>No</option>
                            </select>
                            <x-input-error :messages="$errors->get('grt')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="bancarizacion" :value="__('Bancarización')" />
                            <select name="bancarizacion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="-" {{ $guia->bancarizacion == '-' ? 'selected' : '' }}>-</option>
                                <option value="si" {{ $guia->bancarizacion == 'si' ? 'selected' : '' }}>Sí</option>
                                <option value="no" {{ $guia->bancarizacion == 'no' ? 'selected' : '' }}>No</option>
                                <option value="entregado" {{ $guia->bancarizacion == 'entregado' ? 'selected' : '' }}>Entregado</option>
                                <option value="bancarizó" {{ $guia->bancarizacion == 'bancarizó' ? 'selected' : '' }}>Cliente Bancarizó</option>
                                <option value="archivado" {{ $guia->bancarizacion == 'archivado' ? 'selected' : '' }}>Archivado</option>
                            </select>
                            <x-input-error :messages="$errors->get('bancarizacion')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="entregado" :value="__('Entregado')" />
                            <textarea name="entregado" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('entregado', $guia->entregado) }}</textarea>
                            <x-input-error :messages="$errors->get('entregado')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center">
                        <label for="contingencia" class="inline-flex items-center">
                            <input type="checkbox" name="contingencia" id="contingencia"
                                class="h-5 w-5 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                {{ old('contingencia', $guia->contingencia) ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700 font-medium">CONTINGENCIA</span>
                        </label>
                    </div>

                    <div class="mt-6">
                        <x-input-label for="observaciones" :value="__('Observaciones')" />
                        <textarea name="observaciones" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('observaciones', $guia->observaciones) }}</textarea>
                        <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                    </div>

                    <div class="flex justify-end mt-6">
                        <x-primary-button>{{ __('Actualizar Guía') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
