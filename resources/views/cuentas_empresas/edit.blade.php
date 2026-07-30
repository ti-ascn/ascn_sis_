<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Cuenta de Empresa:') }}
            <span class="text-blue-500">{{ $cuenta->empresa->razon_social ?? 'Empresa' }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <!-- Botón Atrás -->
                <div class="mb-4">
                    <a href="{{ url()->previous() }}"
                        class="inline-block bg-gray-300 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-300 text-gray-700">
                        ← Atrás
                    </a>
                </div>

                <form method="POST" action="{{ route('cuentas_empresas.update', $cuenta->id_cuentas_empresa) }}"
                    class="space-y-6" autocomplete="off">
                    @csrf
                    @method('PUT')

                    {{-- Empresa --}}
                    <div>
                        <x-input-label for="id_empresa_cliente" :value="__('Empresa')" />
                        <select name="id_empresa_cliente" class="select-empresa w-full" required>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id_empresas_clientes }}"
                                    {{ $cuenta->id_empresa_cliente == $empresa->id_empresas_clientes ? 'selected' : '' }}>
                                    {{ $empresa->razon_social }} – {{ $empresa->ruc }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id_empresa_cliente')" class="mt-2" />
                    </div>

                    {{-- Campos --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="nombre" :value="__('Banco')" />
                            <select id="nombre" name="nombre"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="BCP" {{ old('nombre', $cuenta->nombre) == 'BCP' ? 'selected' : '' }}>
                                    BCP</option>
                                <option value="BBVA" {{ old('nombre', $cuenta->nombre) == 'BBVA' ? 'selected' : '' }}>
                                    BBVA</option>
                                <option value="INTERBANK"
                                    {{ old('nombre', $cuenta->nombre) == 'INTERBANK' ? 'selected' : '' }}>INTERBANK
                                </option>
                                <option value="CAJA TRUJILLO"
                                    {{ old('nombre', $cuenta->nombre) == 'CAJA TRUJILLO' ? 'selected' : '' }}>CAJA
                                    TRUJILLO</option>
                                <option value="SCOTIABANK"
                                    {{ old('nombre', $cuenta->nombre) == 'SCOTIABANK' ? 'selected' : '' }}>SCOTIABANK
                                </option>
                            </select>
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="nro_cuenta" :value="__('Nro Cuenta')" />
                            <x-text-input id="nro_cuenta" name="nro_cuenta" type="text" class="mt-1 block w-full"
                                value="{{ old('nro_cuenta', $cuenta->nro_cuenta) }}" />
                            <x-input-error :messages="$errors->get('nro_cuenta')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="nro_interbancario" :value="__('CCI')" />
                            <x-text-input id="nro_interbancario" name="nro_interbancario" type="text"
                                class="mt-1 block w-full"
                                value="{{ old('nro_interbancario', $cuenta->nro_interbancario) }}" />
                            <x-input-error :messages="$errors->get('nro_interbancario')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tipo" :value="__('Tipo de Cuenta')" />
                            <select id="tipo" name="tipo"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="0" {{ $cuenta->tipo == 0 ? 'selected' : '' }}>Soles</option>
                                <option value="1" {{ $cuenta->tipo == 1 ? 'selected' : '' }}>Dólares</option>
                            </select>
                            <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="sede" :value="__('Sede')" />
                            <select id="sede" name="sede"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                @php($sedeVal = old('sede', $cuenta->sede ?? 'Trujillo'))
                                <option value="Trujillo" {{ $sedeVal === 'Trujillo' ? 'selected' : '' }}>Trujillo
                                </option>
                                <option value="Lima" {{ $sedeVal === 'Lima' ? 'selected' : '' }}>Lima</option>
                            </select>
                            <x-input-error :messages="$errors->get('sede')" class="mt-2" />
                        </div>

                        <div class="hidden">
                            <x-input-label for="estado" :value="__('Estado')" />
                            <select id="estado" name="estado"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="1" {{ $cuenta->estado == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ $cuenta->estado == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="fecha_creacion" :value="__('Fecha de Creación')" />
                        <x-text-input id="fecha_creacion" name="fecha_creacion" type="date"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            value="{{ old('fecha_creacion', $cuenta->fecha_creacion ? date('Y-m-d', strtotime($cuenta->fecha_creacion)) : '') }}" />
                        <x-input-error :messages="$errors->get('fecha_creacion')" class="mt-2" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="observaciones" :value="__('Observaciones')" />
                        <textarea id="observaciones" name="observaciones" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('observaciones', $cuenta->observaciones) }}</textarea>
                        <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                    </div>

                    {{-- Manejo de Estado de Cuenta --}}
                    <div>
                        <x-input-label value="¿Maneja estado de cuenta?" />
                        <select name="manejo_estado_cuenta"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="0" {{ $cuenta->manejo_estado_cuenta == 0 ? 'selected' : '' }}>
                                No
                            </option>
                            <option value="1" {{ $cuenta->manejo_estado_cuenta == 1 ? 'selected' : '' }}>
                                Sí
                            </option>
                        </select>
                        <small class="text-gray-500">
                            Controla si esta cuenta participa en el módulo de estados de cuenta.
                        </small>
                    </div>

                    {{-- Botón --}}
                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Actualizar Cuenta') }}</x-primary-button>
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
