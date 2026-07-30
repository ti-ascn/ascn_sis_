<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('clientes.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="ruc" value="RUC" />
                        <x-text-input id="ruc" name="ruc" type="text" class="mt-1 block w-full" value="{{ old('ruc') }}" required />
                        <x-input-error :messages="$errors->get('ruc')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="razon_social" value="Razón Social" />
                        <x-text-input id="razon_social" name="razon_social" type="text" class="mt-1 block w-full" value="{{ old('razon_social') }}" required />
                        <x-input-error :messages="$errors->get('razon_social')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="rubro" value="Rubro" />
                        <x-text-input id="rubro" name="rubro" type="text" class="mt-1 block w-full" value="{{ old('rubro') }}" />
                        <x-input-error :messages="$errors->get('rubro')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="contacto" value="Contacto" />
                        <x-text-input id="contacto" name="contacto" type="text" class="mt-1 block w-full" value="{{ old('contacto') }}" />
                        <x-input-error :messages="$errors->get('contacto')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="telefono" value="Teléfono" />
                        <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full" value="{{ old('telefono') }}" />
                        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="correo" value="Correo" />
                        <x-text-input id="correo" name="correo" type="email" class="mt-1 block w-full" value="{{ old('correo') }}" />
                        <x-input-error :messages="$errors->get('correo')" class="mt-2" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>Guardar Cliente</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
