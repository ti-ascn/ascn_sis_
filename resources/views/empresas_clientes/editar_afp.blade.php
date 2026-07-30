<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Usuario y Clave AFP') }} de <span class="text-blue-600 font-semibold">
                {{ $empresaCliente->razon_social }}
            </span>
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-8 border border-gray-100">
                <form method="POST" action="{{ route('empresas_clientes.actualizar_afp', $empresaCliente->id_empresas_clientes) }}" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Usuario AFP -->
                        <div>
                            <x-input-label for="afp_usuario" :value="__('Usuario AFP')" />
                            <x-text-input id="afp_usuario" name="afp_usuario" type="text" class="mt-1 block w-full" value="{{ old('afp_usuario', $empresaCliente->afp_usuario) }}" />
                            <x-input-error :messages="$errors->get('afp_usuario')" class="mt-2" />
                        </div>

                        <!-- Clave AFP -->
                        <div>
                            <x-input-label for="afp_clave" :value="__('Clave AFP')" />
                            <x-text-input id="afp_clave" name="afp_clave" type="text" class="mt-1 block w-full" value="{{ old('afp_clave', $empresaCliente->afp_clave) }}" />
                            <x-input-error :messages="$errors->get('afp_clave')" class="mt-2" />
                        </div>

                    </div>
                    <!-- Mes Inicio Planilla -->
                    <div class="grid grid-cols-1 md:grid-cols-1 mt-4">
                        <div>
                            <x-input-label for="mes_inicio_planilla" :value="__('Mes Inicio Planilla')" />
                            <x-text-input id="mes_inicio_planilla" name="mes_inicio_planilla" type="month" class="mt-1 block w-full" value="{{ old('mes_inicio_planilla', $empresaCliente->mes_inicio_planilla) }}" />
                            <x-input-error :messages="$errors->get('mes_inicio_planilla')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Estado Recursos -->
                    <div class="grid grid-cols-1 md:grid-cols-1 mt-4">
                        <div>
                            <x-input-label for="estado_recursos" :value="__('Estado Recursos')" />
                            <select name="estado_recursos" id="estado_recursos" class="block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="C1" {{ old('estado_recursos', $empresaCliente->estado_recursos) == 'C1' ? 'selected' : '' }}>C1</option>
                                <option value="A1" {{ old('estado_recursos', $empresaCliente->estado_recursos) == 'A1' ? 'selected' : '' }}>A1</option>
                                <option value="R1" {{ old('estado_recursos', $empresaCliente->estado_recursos) == 'R1' ? 'selected' : '' }}>R1</option>
                            </select>
                            <x-input-error :messages="$errors->get('estado_recursos')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Botón de actualizar -->
                    <div class="mt-8 flex justify-end">
                        <x-primary-button>
                            {{ __('Actualizar') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
