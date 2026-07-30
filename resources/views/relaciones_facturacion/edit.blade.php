<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Relaciones de Facturación de: ') }}
            <span class="text-blue-600">{{ $origen->razon_social }}</span>
        </h2>
    </x-slot>

    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow sm:rounded-lg p-6">

                <form method="POST" action="{{ route('relaciones_facturacion.update', $origen->id_empresas_clientes) }}"
                    class="space-y-6" autocomplete="off">

                    @csrf
                    @method('PUT')

                    {{-- Empresa Origen --}}
                    <div>
                        <x-input-label :value="__('Empresa Origen')" />
                        <p class="text-gray-700 font-semibold">
                            {{ $origen->razon_social }} – {{ $origen->ruc }}
                        </p>
                    </div>

                    {{-- Destinos --}}
                    <div>
                        <x-input-label :value="__('Empresas a las que puede facturar')" />

                        <select name="id_empresas_destino[]" class="select-multiple w-full" multiple>
                            @foreach ($empresas as $emp)
                                <option value="{{ $emp->id_empresas_clientes }}"
                                    {{ in_array($emp->id_empresas_clientes, $destinosActuales) ? 'selected' : '' }}>
                                    {{ $emp->razon_social }} – {{ $emp->ruc }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error :messages="$errors->get('id_empresas_destino')" class="mt-2" />
                    </div>

                    {{-- Botón --}}
                    <div class="flex justify-end">
                        <x-primary-button>Actualizar Relaciones</x-primary-button>
                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>

<script>
    $(document).ready(function() {
        $('.select-multiple').select2({
            placeholder: 'Selecciona las empresas destino',
            allowClear: true,
            width: 'resolve'
        });
    });
</script>
