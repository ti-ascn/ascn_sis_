<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Contacto y/o Información de Cuentas - <span class="text-blue-500">{{ $empresa->razon_social }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            <div class="flex gap-4 mb-4">
                <a href="{{ route('empresas_clientes_auditoria.all') }}"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">← Volver a Empresas</a>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST"
                    action="{{ route('empresas_clientes.actualizar_contacto', $empresa->id_empresas_clientes) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <x-input-label for="contacto" :value="__('Contacto')" />
                        <x-text-input id="contacto" name="contacto" type="text" class="mt-1 block w-full"
                            value="{{ old('contacto', $empresa->contacto) }}" />
                        <x-input-error :messages="$errors->get('contacto')" class="mt-2" />
                    </div>
                    <div class="mb-4 hidden">
                        <x-input-label for="info_cuentas" :value="__('Información de Cuentas')" />
                        <textarea id="info_cuentas" name="info_cuentas" rows="6"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('info_cuentas', $empresa->info_cuentas) }}</textarea>
                        <x-input-error :messages="$errors->get('info_cuentas')" class="mt-2" />
                    </div>
                    <div class="mb-4">
                        <x-input-label for="pagina_web" :value="__('Página Web')" />
                        <x-text-input id="pagina_web" name="pagina_web" type="text" class="mt-1 block w-full"
                            value="{{ old('pagina_web', $empresa->pagina_web) }}" />
                        <x-input-error :messages="$errors->get('pagina_web')" class="mt-2" />
                    </div>
                    <div class="mb-4">
                        <x-input-label for="correo" :value="__('Correo')" />
                        <x-text-input id="correo" name="correo" type="email" class="mt-1 block w-full"
                            value="{{ old('correo', $empresa->correo) }}" />
                        <x-input-error :messages="$errors->get('correo')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="nro_celular" :value="__('Número de Celular')" />
                        <x-text-input id="nro_celular" name="nro_celular" type="text" class="mt-1 block w-full"
                            value="{{ old('nro_celular', $empresa->nro_celular) }}" maxlength="9" pattern="[0-9]{9}"
                            inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,9);" />
                        <x-input-error :messages="$errors->get('nro_celular')" class="mt-2" />
                    </div>


                    <div class="flex justify-end">
                        <x-primary-button>
                            {{ __('Actualizar Contacto') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
