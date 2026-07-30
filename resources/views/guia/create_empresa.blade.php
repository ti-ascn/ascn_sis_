<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Guía – <span class="text-blue-500">Empresa</span>
            </h2>

            <a href="{{ route('guias.create_cliente') }}"
                class="inline-flex items-center gap-1 text-green-600 text-sm font-semibold hover:underline"
                title="¿Es para un cliente? Haz clic en Crear Guía Cliente.">
                <i class="bi bi-person"></i> Guía para cliente
            </a>
        </div>
    </x-slot>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('guias.store') }}" autocomplete="off">
                    @csrf

                    <input type="hidden" name="id_usuario" value="{{ Auth::id() }}">
                    <input type="hidden" name="id_cliente" value="">

                    {{-- Empresa --}}
                    <div class="mb-6">
                        <x-input-label for="id_empresas_clientes" :value="__('Empresa')" />
                        <select id="id_empresas_clientes" name="id_empresas_clientes"
                            class="select-empresa mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Selecciona una empresa</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id_empresas_clientes }}"
                                    {{ old('id_empresas_clientes', $empresaId) == $empresa->id_empresas_clientes ? 'selected' : '' }}>
                                    {{ $empresa->razon_social }} – {{ $empresa->ruc }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id_empresas_clientes')" class="mt-2" />
                    </div>

                    {{-- Campos en columnas --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                        <div>
                            <x-input-label for="moneda" value="Moneda" />
                            <select id="moneda" name="moneda"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="PEN" selected>Soles (PEN)</option>
                                <option value="USD">Dólares (USD)</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="monto" :value="__('Monto')" />
                            <x-text-input id="monto" name="monto" type="number" step="0.01"
                                class="mt-1 block w-full" value="{{ old('monto') }}" />
                            <x-input-error :messages="$errors->get('monto')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="recepcion" :value="__('Recepción')" />
                            <x-text-input id="recepcion" name="recepcion" type="date" class="mt-1 block w-full"
                                value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required />
                            <x-input-error :messages="$errors->get('recepcion')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="programado" :value="__('Programado')" />
                            <select id="programado" name="programado"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="listo">Listo</option>
                                <option value="pendiente" selected>Pendiente</option>
                            </select>
                            <x-input-error :messages="$errors->get('programado')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="emitido" :value="__('Emitido')" />
                            <select id="emitido" name="emitido"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="listo">Listo</option>
                                <option value="pendiente" selected>Pendiente</option>
                            </select>
                            <x-input-error :messages="$errors->get('emitido')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="grr" :value="__('GRR')" />
                            <select id="grr" name="grr"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="-">-</option>
                                <option value="si">Sí</option>
                                <option value="no">No</option>
                            </select>
                            <x-input-error :messages="$errors->get('grr')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="grt" :value="__('GRT')" />
                            <select id="grt" name="grt"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="-">-</option>
                                <option value="si">Sí</option>
                                <option value="no">No</option>
                            </select>
                            <x-input-error :messages="$errors->get('grt')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="bancarizacion" :value="__('Bancarización')" />
                            <select id="bancarizacion" name="bancarizacion"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="-">-</option>
                                <option value="si">Sí</option>
                                <option value="no">No</option>
                                <option value="entregado">Entregado</option>
                                <option value="bancarizó">Cliente Bancarizó</option>
                                <option value="archivado">Archivado</option>
                            </select>
                            <x-input-error :messages="$errors->get('bancarizacion')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="entregado" :value="__('Entregado')" />
                            <textarea id="entregado" name="entregado" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('entregado') }}</textarea>
                            <x-input-error :messages="$errors->get('entregado')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-between items-center">
                        <div class="flex items-center">
                            <label for="contingencia" class="inline-flex items-center">
                                <input type="checkbox" name="contingencia" id="contingencia"
                                    class="h-5 w-5 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    {{ old('contingencia') ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700 font-medium">CONTINGENCIA</span>
                            </label>
                        </div>
                    </div>

                    {{-- Textareas en una sola columna --}}
                    <div class="mt-6 grid grid-cols-1 gap-6">
                        <div>
                            <x-input-label for="observaciones" :value="__('Observaciones')" />
                            <textarea id="observaciones" name="observaciones" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('observaciones') }}</textarea>
                            <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Botón --}}
                    <div class="flex justify-end mt-6">
                        <x-primary-button>{{ __('Guardar Guía') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    $(document).ready(function() {
        $('.select-empresa').select2({
            placeholder: 'Selecciona una empresa',
            allowClear: true,
            width: 'resolve'
        });
    });
</script>
