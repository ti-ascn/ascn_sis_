<x-app-layout>
    <section>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Empresa Cliente') }}
            </h2>
        </x-slot>

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <div class="max-w-4xl mx-auto">
                        <!-- Título y Descripción -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ __('Información de la Empresa Cliente') }}
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ __('Modifique los detalles de la empresa cliente que desea actualizar.') }}
                            </p>
                        </div>

                        <div class="mb-4">
                            <a href="{{ url()->previous() }}"
                                class="inline-block bg-gray-300 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-300 text-gray-700">
                                ← Atrás
                            </a>
                        </div>

                        <!-- Formulario -->
                        <form method="POST"
                            action="{{ route('empresas_clientes.update', $empresaCliente->id_empresas_clientes) }}"
                            autocomplete="off" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <!-- Contenedor de campos (tres columnas en pantallas grandes) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- Estado -->
                                <div>
                                    <x-input-label for="estado" :value="__('Estado')" />
                                    <select id="estado" name="estado"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="A1"
                                            {{ old('estado', $empresaCliente->estado) == 'A1' ? 'selected' : '' }}>A1
                                        </option>
                                        <option value="C1"
                                            {{ old('estado', $empresaCliente->estado) == 'C1' ? 'selected' : '' }}>C1
                                        </option>
                                        <option value="R1"
                                            {{ old('estado', $empresaCliente->estado) == 'R1' ? 'selected' : '' }}>R1
                                        </option>
                                        <option value="R2"
                                            {{ old('estado', $empresaCliente->estado) == 'R2' ? 'selected' : '' }}>R2
                                        </option>
                                        <option value="I1"
                                            {{ old('estado', $empresaCliente->estado) == 'I1' ? 'selected' : '' }}>I1
                                        </option>
                                        <option value="P1"
                                            {{ old('estado', $empresaCliente->estado) == 'P1' ? 'selected' : '' }}>P1
                                        </option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('estado')" />
                                </div>

                                <!-- Razón Social -->
                                <div>
                                    <x-input-label for="razon_social" :value="__('Razón Social')" />
                                    <x-text-input id="razon_social" name="razon_social" type="text"
                                        class="mt-1 block w-full"
                                        value="{{ old('razon_social', $empresaCliente->razon_social) }}" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('razon_social')" />
                                </div>

                                <!-- RUC -->
                                <div>
                                    <x-input-label for="ruc" :value="__('RUC')" />
                                    <x-text-input id="ruc" name="ruc" type="text" class="mt-1 block w-full"
                                        value="{{ old('ruc', $empresaCliente->ruc) }}" required maxlength="11"
                                        pattern="\d{11}" inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);" />
                                    <x-input-error class="mt-2" :messages="$errors->get('ruc')" />
                                </div>

                                <!-- Usuario Sol -->
                                <div>
                                    <x-input-label for="sol_usuario" :value="__('Usuario Sol')" />
                                    <x-text-input id="sol_usuario" name="sol_usuario" type="text"
                                        class="mt-1 block w-full"
                                        value="{{ old('sol_usuario', $empresaCliente->sol_usuario) }}" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('sol_usuario')" />
                                </div>

                                <!-- Clave Sol -->
                                <div>
                                    <x-input-label for="sol_clave" :value="__('Clave Sol')" />
                                    <x-text-input id="sol_clave" name="sol_clave" type="text"
                                        class="mt-1 block w-full"
                                        value="{{ old('sol_clave', $empresaCliente->sol_clave) }}" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('sol_clave')" />
                                </div>

                                <!-- Usuario Sol 2 -->
                                <div>
                                    <x-input-label for="sol2_usuario" :value="__('Usuario Sol 2')" />
                                    <x-text-input id="sol2_usuario" name="sol2_usuario" type="text"
                                        class="mt-1 block w-full"
                                        value="{{ old('sol2_usuario', $empresaCliente->sol2_usuario) }}"
                                        placeholder="Opcional" />
                                    <x-input-error class="mt-2" :messages="$errors->get('sol2_usuario')" />
                                </div>

                                <!-- Clave Sol 2 -->
                                <div>
                                    <x-input-label for="sol2_clave" :value="__('Clave Sol 2')" />
                                    <x-text-input id="sol2_clave" name="sol2_clave" type="text"
                                        class="mt-1 block w-full"
                                        value="{{ old('sol2_clave', $empresaCliente->sol2_clave) }}"
                                        placeholder="Opcional" />
                                    <x-input-error class="mt-2" :messages="$errors->get('sol2_clave')" />
                                </div>

                                <!-- Detracción -->
                                <div>
                                    <x-input-label for="detraccion" :value="__('Detracción')" />
                                    <select id="detraccion" name="detraccion"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="SI"
                                            {{ old('detraccion', $empresaCliente->detraccion) == 'SI' ? 'selected' : '' }}>
                                            SI</option>
                                        <option value="NO"
                                            {{ old('detraccion', $empresaCliente->detraccion) == 'NO' ? 'selected' : '' }}>
                                            NO</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('detraccion')" />
                                </div>

                                <!-- Retención -->
                                <div>
                                    <x-input-label for="retencion" :value="__('Retención')" />
                                    <select id="retencion" name="retencion"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="SI"
                                            {{ old('retencion', $empresaCliente->retencion) == 'SI' ? 'selected' : '' }}>
                                            SI</option>
                                        <option value="NO"
                                            {{ old('retencion', $empresaCliente->retencion) == 'NO' ? 'selected' : '' }}>
                                            NO</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('retencion')" />
                                </div>

                                <!-- Rubro -->
                                <div>
                                    <x-input-label for="rubro" :value="__('Rubro')" />
                                    <x-text-input id="rubro" name="rubro" type="text" class="mt-1 block w-full"
                                        value="{{ old('rubro', $empresaCliente->rubro) }}" placeholder="Opcional" />
                                    <x-input-error class="mt-2" :messages="$errors->get('rubro')" />
                                </div>

                                <!-- Usuario AFP -->
                                <div>
                                    <x-input-label for="afp_usuario" :value="__('Usuario AFP')" />
                                    <x-text-input id="afp_usuario" name="afp_usuario" type="text"
                                        class="mt-1 block w-full"
                                        value="{{ old('afp_usuario', $empresaCliente->afp_usuario) }}"
                                        placeholder="Opcional" />
                                    <x-input-error class="mt-2" :messages="$errors->get('afp_usuario')" />
                                </div>

                                <!-- Clave AFP -->
                                <div>
                                    <x-input-label for="afp_clave" :value="__('Clave AFP')" />
                                    <x-text-input id="afp_clave" name="afp_clave" type="text"
                                        class="mt-1 block w-full"
                                        value="{{ old('afp_clave', $empresaCliente->afp_clave) }}"
                                        placeholder="Opcional" />
                                    <x-input-error class="mt-2" :messages="$errors->get('afp_clave')" />
                                </div>

                                <!-- Usuario BN -->
                                <div>
                                    <x-input-label for="bn_usuario" :value="__('Usuario BN')" />
                                    <x-text-input id="bn_usuario" name="bn_usuario" type="text"
                                        class="mt-1 block w-full"
                                        value="{{ old('bn_usuario', $empresaCliente->bn_usuario) }}"
                                        placeholder="Opcional" />
                                    <x-input-error class="mt-2" :messages="$errors->get('bn_usuario')" />
                                </div>

                                <!-- Clave BN -->
                                <div>
                                    <x-input-label for="bn_clave" :value="__('Clave BN')" />
                                    <x-text-input id="bn_clave" name="bn_clave" type="text"
                                        class="mt-1 block w-full"
                                        value="{{ old('bn_clave', $empresaCliente->bn_clave) }}"
                                        placeholder="Opcional" />
                                    <x-input-error class="mt-2" :messages="$errors->get('bn_clave')" />
                                </div>

                                <!-- Clave Extranet --> 
                                <div>
                                    <x-input-label for="clave_extranet" :value="__('Clave Extranet')" />
                                    <x-text-input id="clave_extranet" name="clave_extranet" type="text"
                                        class="mt-1 block w-full"
                                        value="{{ old('clave_extranet', $empresaCliente->clave_extranet) }}"
                                        placeholder="Opcional" />
                                    <x-input-error class="mt-2" :messages="$errors->get('clave_extranet')" />
                                </div>
                                
                                <!-- Estado Actual -->
                                <div>
                                    <x-input-label for="estado_actual" :value="__('Estado Actual')" />
                                    <select required id="estado_actual" name="estado_actual"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="1"
                                            {{ old('estado_actual', $empresaCliente->estado_actual) == 1 ? 'selected' : '' }}>
                                            Activo
                                        </option>
                                        <option value="0"
                                            {{ old('estado_actual', $empresaCliente->estado_actual) == 0 ? 'selected' : '' }}>
                                            Retirado
                                        </option>
                                        <option value="2"
                                            {{ old('estado_actual', $empresaCliente->estado_actual) == 2 ? 'selected' : '' }}>
                                            Suspendido
                                        </option>
                                        <option value="3"
                                            {{ old('estado_actual', $empresaCliente->estado_actual) == 3 ? 'selected' : '' }}>
                                            Pendiente
                                        </option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('estado_actual')" />
                                </div>
                                <!-- Fecha Ingreso -->
                                <div>
                                    <x-input-label for="fecha_ingreso" :value="__('Fecha de Ingreso')" />
                                    <x-text-input id="fecha_ingreso" name="fecha_ingreso" type="date"
                                        class="mt-1 block w-full"
                                        value="{{ old('fecha_ingreso', $empresaCliente->fecha_ingreso) }}" />
                                    <x-input-error class="mt-2" :messages="$errors->get('fecha_ingreso')" />
                                </div>

                                <!-- Mes Inicio Declaración -->
                                <div>
                                    <x-input-label for="mes_inicio_declaracion" :value="__('Mes Inicio Declaración')" />
                                    <x-text-input id="mes_inicio_declaracion" name="mes_inicio_declaracion"
                                        type="month" class="mt-1 block w-full"
                                        value="{{ old('mes_inicio_declaracion', isset($empresaCliente) ? $empresaCliente->mes_inicio_declaracion : '') }}" />
                                    <x-input-error class="mt-2" :messages="$errors->get('mes_inicio_declaracion')" />
                                </div>

                                <!-- Mes Inicio Planilla -->
                                <div>
                                    <x-input-label for="mes_inicio_planilla" :value="__('Mes Inicio Planilla')" />
                                    <x-text-input id="mes_inicio_planilla" name="mes_inicio_planilla" type="month"
                                        class="mt-1 block w-full"
                                        value="{{ old('mes_inicio_planilla', isset($empresaCliente) ? $empresaCliente->mes_inicio_planilla : '') }}" />
                                    <x-input-error class="mt-2" :messages="$errors->get('mes_inicio_planilla')" />
                                </div>
                            </div>
                            <div class="grid-cols-1 sm:grid-cols-1 lg:grid-cols-1 gap-6">
                                <!-- Campo oculto para ID de Empresa -->
                                <input type="hidden" name="id_empresas_clientes"
                                    value="{{ $empresaCliente->id_empresas_clientes }}">
                            </div>

                            <!-- Botón Guardar -->
                            <div class="flex justify-end mt-6">
                                <x-primary-button class="px-6 py-2">
                                    {{ __('Actualizar') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
