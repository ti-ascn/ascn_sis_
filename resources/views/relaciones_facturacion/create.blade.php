<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Relaciones de Facturación') }}
        </h2>
    </x-slot>

    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow sm:rounded-lg p-6">

                <form method="POST" action="{{ route('relaciones_facturacion.store') }}" class="space-y-6"
                    autocomplete="off">

                    @csrf

                    {{-- Empresa Origen --}}
                    <div>
                        <x-input-label for="id_empresa_origen" :value="__('Empresa que factura (Origen)')" />
                        <select name="id_empresa_origen" class="select-empresa w-full">
                            <option value="">Selecciona una empresa</option>

                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id_empresas_clientes }}">
                                    {{ $empresa->razon_social }} – {{ $empresa->ruc }}
                                </option>
                            @endforeach

                        </select>
                        <x-input-error :messages="$errors->get('id_empresa_origen')" class="mt-2" />
                    </div>

                    {{-- Empresas Destino (MULTISELECT) --}}
                    <div>
                        <x-input-label for="id_empresas_destino" :value="__('Empresas a las que puede facturar (Destino)')" />

                        <select name="id_empresas_destino[]" class="select-multiple w-full" multiple="multiple">

                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id_empresas_clientes }}">
                                    {{ $empresa->razon_social }} – {{ $empresa->ruc }}
                                </option>
                            @endforeach

                        </select>

                        <x-input-error :messages="$errors->get('id_empresas_destino')" class="mt-2" />
                    </div>

                    {{-- Botón --}}
                    <div class="flex justify-end">
                        <x-primary-button>Guardar Relaciones</x-primary-button>
                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>


{{-- Select2 Scripts --}}
<script>
    $(document).ready(function() {
        $('.select-empresa').select2({
            placeholder: 'Selecciona una empresa',
            allowClear: true,
            width: 'resolve'
        });

        $('.select-multiple').select2({
            placeholder: 'Selecciona las empresas destino',
            allowClear: true,
            width: 'resolve'
        });
    });
</script>
