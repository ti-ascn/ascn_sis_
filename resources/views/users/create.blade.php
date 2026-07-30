<x-app-layout>
    <section>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Crear Usuario') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <div class="max-w-3xl mx-auto">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ __('Información del Usuario') }}
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
                            <p class="text-sm text-gray-600">
                                {{ __('Ingrese los detalles del usuario que desea registrar.') }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-6">
                            @csrf

                            <!-- Nombre -->
                            <div>
                                <x-input-label for="name" :value="__('Nombre y Apellido')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                    required />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <!-- Correo Electrónico -->
                            <div>
                                <x-input-label for="email" :value="__('Correo Electrónico')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                    required />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <x-input-label for="celular" :value="__('Celular')" />
                                <x-text-input id="celular" name="celular" type="text" class="mt-1 block w-full"
                                    required maxlength="9" pattern="[0-9]{9}" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);" />
                                <x-input-error class="mt-2" :messages="$errors->get('celular')" />
                            </div>

                            <!-- Contraseña -->
                            <div>
                                <x-input-label for="password" :value="__('Contraseña')" />
                                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                    required />
                                <x-input-error class="mt-2" :messages="$errors->get('password')" />
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div>
                                <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
                                <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                                    class="mt-1 block w-full" required />
                                <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                            </div>

                            <!-- Rol -->
                            <div>
                                <x-input-label for="tipo_usuario" :value="__('Rol')" />
                                <select id="tipo_usuario" name="tipo_usuario" class="mt-1 block w-full" required>
                                    <option value="" disabled selected>Seleccionar rol</option>
                                    <option value="1">Administrador</option>
                                    <option value="2">Jefe</option>
                                    <option value="3">Asistente</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('role')" />
                            </div>

                            <!-- Área -->
                            <div>
                                <x-input-label for="id_area" :value="__('Área')" />
                                <select id="id_area" name="id_area" class="mt-1 block w-full">
                                    <option value="" disabled selected>Seleccionar área</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id_area }}">{{ $area->nombre }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('id_area')" />
                            </div>

                            <!-- Estado (Oculto, siempre en activo) -->
                            <input type="hidden" name="estado" value="1">

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
