<x-app-layout>
    <section>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Determinación de Impuesto a la Renta 3CA del') }} <span
                    class="text-blue-500">{{ $preLiquidacion->anio }}</span>
                de la empresa <span class="text-blue-500">{{ $preLiquidacion->empresaCliente->razon_social }}</span>
            </h2>
        </x-slot>

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <div class="max-w-4xl mx-auto">
                        <!-- Botón Atrás -->
                        {{-- <div class="mb-4">
                            <a href="{{ url()->previous() }}"
                                class="inline-block bg-gray-300 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-300 text-gray-700">
                                ← Atrás
                            </a>
                        </div> --}}
                        <x-success-error-bagdes />

                        <!-- Formulario -->
                        <form
                            action="{{ route('determinacion-renta.update', ['id_pre_liquidacion' => $preLiquidacion->id_pre_liquidacion]) }}"
                            method="POST" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="id_pre_liquidacion"
                                value="{{ $preLiquidacion->id_pre_liquidacion }}">
                            <input type="hidden" name="id_usuario" value="{{ auth()->user()->id }}">
                            {{-- !CALCULAR MEJOR COMO OBTIENE LA UIT --}}
                            <!-- Sección 1: Valor UIT -->
                            @if ($preLiquidacion->total > 0)
                                <div class="border-b pb-6 mb-6">
                                    <h4 class="text-lg font-semibold text-gray-700 mb-4">Valor UIT</h4>
                                    <div class="grid grid-cols-2 gap-6">
                                        <x-input-label for="valor_uit" :value="__('Valor UIT')" />
                                        @php
                                            $valorUIT = '';
                                            switch ($preLiquidacion->anio) {
                                                case 2024:
                                                    $valorUIT = 5150;
                                                    break;
                                                case 2025:
                                                    $valorUIT = 5350;
                                                    break;
                                                case 2023:
                                                    $valorUIT = 4950;
                                                    break;
                                                default:
                                                    $valorUIT = 'INGRESAR VALOR UIT o ACTUALIZAR SISTEMA';
                                                    break;
                                            }
                                        @endphp
                                        <x-text-input id="valor_uit" name="valor_uit" type="number" step="0.01"
                                            class="mt-1 block w-full" value="{{ $valorUIT }}"
                                            placeholder="{{ $valorUIT === 'INGRESAR VALOR UIT o ACTUALIZAR SISTEMA' ? $valorUIT : '' }}" />
                                    </div>
                                </div>
                            @else
                                <input type="hidden" id="valor_uit" name="valor_uit" value="0">
                            @endif

                            <!-- Sección 2: Utilidad Antes -->
                            <div class="border-b pb-6 mb-6">
                                <h4 class="text-lg font-semibold text-gray-700 mb-4">Utilidad Antes</h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <x-input-label for="utilidad_antes" :value="__('Utilidad Antes')" />
                                    <x-text-input id="utilidad_antes" name="utilidad_antes" type="number"
                                        step="1" class="mt-1 block w-full bg-gray-200 " readonly
                                        value="{{ old('utilidad_antes', round($preLiquidacion->total)) }}" />
                                </div>
                            </div>

                            <!-- Sección 3: Régimen -->
                            @if ($preLiquidacion->total > 0)
                                <div class="border-b pb-6 mb-6">
                                    <h4 class="text-lg font-semibold text-gray-700 mb-4">Régimen</h4>
                                    <div class="grid grid-cols-2 gap-6">
                                        <x-input-label for="regimen" :value="__('Seleccionar Régimen')" />
                                        <select id="regimen" name="regimen"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                            <option value="" disabled>Seleccionar régimen</option>
                                            @if ($determinacionRenta->impuesto_hasta_15_uit > 0)
                                                <option value="1"selected>Mype Tributario</option>
                                                <option value="2">Régimen General</option>
                                            @else
                                                <option value="1">Mype Tributario</option>
                                                <option value="2"selected>Régimen General</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <!-- Sección 4: Adiciones -->
                            @if ($preLiquidacion->total > 0)
                                <div class="border-b pb-6 mb-6">
                                    <h4 class="text-lg font-semibold text-gray-700 mb-4">Adiciones</h4>
                                    <div class="grid grid-cols-2 gap-6">
                                        <x-input-label for="adicion_depreciacion" :value="__('Depreciación de Activo Fijo en Exceso')" />
                                        <x-text-input id="adicion_depreciacion" name="adicion_depreciacion"
                                            type="number" step="0.01" class="mt-1 block w-full" placeholder="0.00"
                                            value="{{ old('adicion_depreciacion', $determinacionRenta->adicion_depreciacion) }}" />

                                        <x-input-label for="adicion_gastos_personales" :value="__(
                                            'Gastos personales y de sustento del contribuyente y sus familiares',
                                        )" />
                                        <x-text-input id="adicion_gastos_personales" name="adicion_gastos_personales"
                                            type="number" step="0.01" class="mt-1 block w-full" placeholder="0.00"
                                            value="{{ old('adicion_gastos_personales', $determinacionRenta->adicion_gastos_personales) }}" />

                                        <x-input-label for="adicion_multas_cargos" :value="__(' Multas, Recargos, Intereses moratorios y sanciones')" />
                                        <x-text-input id="adicion_multas_cargos" name="adicion_multas_cargos"
                                            type="number" step="0.01" class="mt-1 block w-full" placeholder="0.00"
                                            value="{{ old('adicion_multas_cargos', $determinacionRenta->adicion_multas_cargos) }}" />

                                        <x-input-label for="adicion_gastos_rcp" :value="__('Gastos cuya documentacion sustentatoria no cumplan con el RCP')" />
                                        <x-text-input id="adicion_gastos_rcp" name="adicion_gastos_rcp" type="number"
                                            step="0.01" class="mt-1 block w-full" placeholder="0.00"
                                            value="{{ old('adicion_gastos_rcp', $determinacionRenta->adicion_gastos_rcp) }}" />

                                        <x-input-label for="adicion_otros" :value="__('Otros')" />
                                        <x-text-input id="adicion_otros" name="adicion_otros" type="number"
                                            step="0.01" class="mt-1 block w-full" placeholder="0.00"
                                            value="{{ old('adicion_otros', $determinacionRenta->adicion_otros) }}" />

                                        <x-input-label for="adicion_total" :value="__('Total')" />
                                        <x-text-input id="adicion_total" name="adicion_total" type="number"
                                            step="0.01" class="mt-1 block w-full bg-gray-200" readonly
                                            value="{{ old('adicion_total', $determinacionRenta->adicion_multasadicion_total_cargos) }}" />
                                    </div>
                                </div>
                            @endif

                            <!-- Sección 5: Deducciones -->
                            @if ($preLiquidacion->total > 0)
                                <div class="border-b pb-6 mb-6">
                                    <h4 class="text-lg font-semibold text-gray-700 mb-4">Deducciones ( - )</h4>
                                    <div class="grid grid-cols-2 gap-6">
                                        <x-input-label for="deduccion_vaciones" :value="__('( - ) Vacaciones')" />
                                        <x-text-input id="deduccion_vaciones" name="deduccion_vaciones"
                                            type="number" step="0.01" class="mt-1 block w-full text-red-500"
                                            placeholder="-0.00"
                                            value="{{ old('deduccion_vaciones', $determinacionRenta->deduccion_vaciones) }}" />

                                        <x-input-label for="deduccion_depreciacion" :value="__('( - ) Depreciación acelerada por contrato de leasing')" />
                                        <x-text-input id="deduccion_depreciacion" name="deduccion_depreciacion"
                                            type="number" step="0.01" class="mt-1 block w-full text-red-500"
                                            placeholder="-0.00"
                                            value="{{ old('deduccion_depreciacion', $determinacionRenta->deduccion_depreciacion) }}" />

                                        <x-input-label for="deduccion_otros" :value="__('( - ) Otros')" />
                                        <x-text-input id="deduccion_otros" name="deduccion_otros" type="number"
                                            step="0.01" class="mt-1 block w-full text-red-500" placeholder="-0.00"
                                            value="{{ old('deduccion_otros', $determinacionRenta->deduccion_otros) }}" />

                                        <x-input-label for="deduccion_total" :value="__('Total')" />
                                        <x-text-input id="deduccion_total" name="deduccion_total" type="number"
                                            step="0.01" class="mt-1 block w-full bg-gray-200"
                                            value="{{ old('deduccion_total', $determinacionRenta->deduccion_total) }}"
                                            readonly />
                                    </div>
                                </div>
                            @endif

                            <!-- Sección 6: Utilidad Tributaria -->
                            @if ($preLiquidacion->total > 0)
                                <div class="border-b pb-6 mb-6">
                                    <h4 class="text-lg font-semibold text-gray-700 mb-4">Utilidad Tributaria</h4>
                                    <div class="grid grid-cols-2 gap-6">
                                        <x-input-label for="utilidad_tributaria" :value="__('UTILIDAD DEL EJERCICIO (Tributario)')" />
                                        <x-text-input id="utilidad_tributaria" name="utilidad_tributaria"
                                            type="number" step="0.01" class="w-full bg-gray-200"
                                            value="{{ old('deduccion_total', $determinacionRenta->deduccion_total) }}"
                                            readonly />
                                    </div>
                                </div>
                            @endif

                            <!-- Sección 7: Impuesto a la Renta -->
                            @if ($preLiquidacion->total > 0)
                                <div class="border-b pb-6 mb-6">
                                    <h4 class="text-lg font-semibold text-gray-700 mb-4">Impuesto a la Renta</h4>
                                    <div class="grid grid-cols-2 gap-6">
                                        <x-input-label for="impuesto_hasta_15_uit" :value="__('Hasta 15 UIT (la tasa del 10%)')" />
                                        <x-text-input id="impuesto_hasta_15_uit" name="impuesto_hasta_15_uit"
                                            placeholder="Hasta 15 UIT" type="number" step="0.01"
                                            class="w-full bg-gray-200"
                                            value="{{ old('impuesto_hasta_15_uit', $determinacionRenta->impuesto_hasta_15_uit) }}"
                                            readonly />

                                        <x-input-label for="impuesto_exceso_15_uit" :value="__('Por el exceso (la tasa del 29.5%, UTILIDAD-15UIT)')" />
                                        <x-text-input id="impuesto_exceso_15_uit" placeholder="Exceso de 15 UIT"
                                            type="number" step="0.01" name="impuesto_exceso_15_uit"
                                            class="w-full bg-gray-200"
                                            value="{{ old('impuesto_exceso_15_uit', $determinacionRenta->impuesto_exceso_15_uit) }}"
                                            readonly />

                                        <x-input-label for="impuesto_total" :value="__('Total Impuesto a la Renta a pagar')" />
                                        <x-text-input id="impuesto_total" name="impuesto_total" placeholder="Total"
                                            type="number" step="0.01" class="w-full bg-gray-200"
                                            value="{{ old('impuesto_total', $determinacionRenta->impuesto_total) }}"
                                            readonly />
                                    </div>
                                </div>
                            @endif

                            <!-- Sección 8: Pagos a Cuenta -->
                            <div class="border-b pb-6 mb-6">
                                <h4 class="text-lg font-semibold text-gray-700 mb-4">Pagos a Cuenta</h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <x-input-label for="pagos_cuenta" :value="__('( - ) Pagos a cuenta del ejercicio')" />
                                    <x-text-input id="pagos_cuenta" placeholder="-0.00" name="pagos_cuenta"
                                        type="number" step="0.01" class="w-full text-red-500"
                                        value="{{ old('pagos_cuenta', $determinacionRenta->pagos_cuenta) }}" />

                            <!-- Sección 8: Pagos a Cuenta ITAN-->
                                    <x-input-label for="pago_itan" :value="__('( - ) Pagos a cuenta ITAN')" />
                                    <x-text-input id="pago_itan" placeholder="-0.00" name="pago_itan"
                                        type="number" step="0.01" class="w-full text-red-500"
                                        value="{{ old('pago_itan', $determinacionRenta->pago_itan) }}" />
                                </div>
                            </div>

                            <!-- Sección 9: Saldos a Favor No Aplicados -->
                            <div class="border-b pb-6 mb-6">
                                <h4 class="text-lg font-semibold text-gray-700 mb-4">Saldos a Favor No Aplicados</h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <x-input-label for="saldos_no_aplicados" :value="__('( - ) Saldos a favor no aplicados')" />
                                    <x-text-input id="saldos_no_aplicados" placeholder="-0.00"
                                        name="saldos_no_aplicados" type="number" step="0.01"
                                        class="w-full text-red-500"
                                        value="{{ old('saldos_no_aplicados', $determinacionRenta->saldos_no_aplicados) }}" />
                                </div>
                            </div>

                            <!-- Sección 10: Total por Pagar o Crédito a Favor -->
                            @if ($preLiquidacion->total > 0)
                                <div class="border-b pb-6 mb-6">
                                    <h4 class="text-lg font-semibold text-gray-700 mb-4">Por pagar renta 3CA
                                        {{ $preLiquidacion->anio }}</h4>
                                    <div class="grid grid-cols-2 gap-6">
                                        <x-input-label for="total_por_pagar" :value="__('POR PAGAR RENTA 3CA ')" />
                                        <x-text-input name="total_por_pagar" placeholder="Total por Pagar"
                                            type="number" step="0.01" class="w-full bg-gray-200"
                                            value="{{ old('total_por_pagar', $determinacionRenta->total_por_pagar) }}" />
                                    </div>
                                </div>
                            @else
                                <div class="border-b pb-6 mb-6">
                                    <h4 class="text-lg font-semibold text-gray-700 mb-4">Crédito a favor renta 3CA
                                        {{ $preLiquidacion->anio }}</h4>
                                    <div class="grid grid-cols-2 gap-6">
                                        <x-input-label for="total_a_favor" :value="__('CREDITO A FAVOR DE RENTA 3CA')" />
                                        <x-text-input name="total_a_favor" placeholder="Total a Favor" type="number"
                                            step="0.01" class="w-full bg-gray-200"
                                            value="{{ old('total_a_favor', $determinacionRenta->total_a_favor) }}" />
                                    </div>
                                </div>
                            @endif

                            <!-- Botón de Guardar -->
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
    @if ($preLiquidacion->total > 0)
        <script>
            //!LO QUITE PORQUE QUIERE EDITAR EL BOTON
            /*document.addEventListener("DOMContentLoaded", function() {
                // Obtener los elementos
                const utilidadAntesInput = document.getElementById("utilidad_antes");
                const valorUitInput = document.getElementById("valor_uit");
                const regimenSelect = document.getElementById("regimen");

                function actualizarEstadoRegimen() {
                    const utilidadAntes = parseFloat(utilidadAntesInput.value) || 0;
                    const valorUit = parseFloat(valorUitInput.value) || 0;
                    const limite = 15 * valorUit; // 15 veces la UIT

                    if (utilidadAntes < limite) {
                        regimenSelect.disabled = true;
                        regimenSelect.classList.add("bg-gray-200");
                    } else {
                        regimenSelect.disabled = false;
                        regimenSelect.classList.remove("bg-gray-200");
                    }
                }

                // Escuchar cambios en los inputs
                utilidadAntesInput.addEventListener("input", actualizarEstadoRegimen);
                valorUitInput.addEventListener("input", actualizarEstadoRegimen);

                // Ejecutar la función al cargar la página
                actualizarEstadoRegimen();
            }); */
        </script>
        <script>
            function calcular() {
                let valorUIT = parseFloat(document.getElementById('valor_uit').value);

                let utilidadAntes = parseFloat(document.getElementById('utilidad_antes').value) || 0;
                let adicionDepreciacion = parseFloat(document.getElementById('adicion_depreciacion').value) || 0;
                let adicionGastosPersonales = parseFloat(document.getElementById('adicion_gastos_personales').value) || 0;
                let adicionMultasCargos = parseFloat(document.getElementById('adicion_multas_cargos').value) || 0;
                let adicionGastosRcp = parseFloat(document.getElementById('adicion_gastos_rcp').value) || 0;
                let adicionOtros = parseFloat(document.getElementById('adicion_otros').value) || 0;

                let adicionTotal = adicionDepreciacion + adicionGastosPersonales + adicionMultasCargos +
                    adicionGastosRcp + adicionOtros;
                document.getElementById('adicion_total').value = Math.round(adicionTotal * 100) / 100;

                let deduccionVacaciones = parseFloat(document.getElementById('deduccion_vaciones').value) || 0;
                let deduccionDepreciacion = parseFloat(document.getElementById('deduccion_depreciacion').value) || 0;
                let deduccionOtros = parseFloat(document.getElementById('deduccion_otros').value) || 0;

                let deduccionTotal = deduccionVacaciones + deduccionDepreciacion + deduccionOtros;
                document.getElementById('deduccion_total').value = Math.round(deduccionTotal * 100) / 100;

                let utilidadTributaria = Math.round(utilidadAntes + deduccionTotal + adicionTotal);
                document.getElementById('utilidad_tributaria').value = utilidadTributaria;

                let umbral15UIT = valorUIT * 15;
                let impuestoHasta15UIT = 0;
                let impuestoExceso15UIT = 0;
                let impuestoTotal = 0;

                let regimen = document.getElementById('regimen').value;

                if (regimen === "1") { // MYPE Tributario
                    if (utilidadTributaria > umbral15UIT) {
                        // Si hay exceso, aplica 10% hasta el umbral y 29.5% al exceso
                        impuestoHasta15UIT = umbral15UIT * 0.10;
                        impuestoExceso15UIT = (utilidadTributaria - umbral15UIT) * 0.295;
                    } else {
                        // Si no hay exceso, solo aplica 10% a la utilidadTributaria
                        impuestoHasta15UIT = utilidadTributaria * 0.10;
                        impuestoExceso15UIT = 0; // Asegúrate de que el exceso sea 0
                    }
                    impuestoTotal = impuestoHasta15UIT + impuestoExceso15UIT;
                } else if (regimen === "2") { // Régimen General
                    impuestoTotal = utilidadTributaria * 0.295;
                    document.getElementById('impuesto_exceso_15_uit').value = Math.round(impuestoTotal);
                    document.getElementById('impuesto_hasta_15_uit').value = 0; // No hay impuesto hasta 15 UIT en este régimen
                    document.getElementById('impuesto_total').value = Math.round(impuestoTotal);
                }

                // Solo actualiza estos campos si no es Régimen General
                if (regimen !== "2") {
                    document.getElementById('impuesto_hasta_15_uit').value = Math.round(impuestoHasta15UIT);
                    document.getElementById('impuesto_exceso_15_uit').value = Math.round(impuestoExceso15UIT);
                    document.getElementById('impuesto_total').value = Math.round((impuestoHasta15UIT + impuestoExceso15UIT));
                }

                let saldos_no_aplicados = parseFloat(document.getElementById('saldos_no_aplicados').value) || 0;
                let pagosCuenta = parseFloat(document.getElementById('pagos_cuenta').value) || 0;
                let pagosItan = parseFloat(document.getElementById('pago_itan').value) || 0;
                let totalPorPagar = (Math.round(impuestoTotal * 100) / 100) + pagosCuenta + pagosItan + saldos_no_aplicados;
                let totalAFavor = (Math.round(impuestoTotal * 100) / 100) + pagosCuenta + pagosItan + saldos_no_aplicados;

                let totalInput = document.querySelector('input[name="total_por_pagar"]') || document.querySelector(
                    'input[name="total_a_favor"]');

                if (totalInput) {
                    if (totalInput.name === 'total_a_favor') {
                        // Si es "total_a_favor", se calcula el totalAFavor
                        totalInput.value = Math.round(totalAFavor); 
                    } else {
                        // Si es "total_por_pagar", se calcula el totalPorPagar
                        totalInput.value = Math.round(totalPorPagar);
                    }
                }
            }

            // Agrega eventos para recalcular cuando cambian los valores
            document.querySelectorAll('input[type="number"]').forEach(input => {
                input.addEventListener('input', calcular);
            });
            document.getElementById('regimen').addEventListener('change', calcular);

            // Ejecuta el cálculo al cargar la página
            window.onload = calcular;
        </script>
    @else
        <script>
            function calcular() {
                let valorUIT = parseFloat(document.getElementById('valor_uit').value) || 0;

                let utilidadAntes = parseFloat(document.getElementById('utilidad_antes').value) || 0;
                let saldos_no_aplicados = parseFloat(document.getElementById('saldos_no_aplicados').value) || 0;
                let pagosCuenta = parseFloat(document.getElementById('pagos_cuenta').value) || 0;
                let totalAFavor = pagosCuenta + saldos_no_aplicados;

                let totalInput = document.querySelector('input[name="total_por_pagar"]') || document.querySelector(
                    'input[name="total_a_favor"]');

                if (totalInput) {
                    if (totalInput.name === 'total_a_favor') {
                        // Si es "total_a_favor", se calcula el totalAFavor
                        totalInput.value = Math.round(totalAFavor); 
                    } else {
                        // Si es "total_por_pagar", se calcula el totalPorPagar
                        totalInput.value = Math.round(totalPorPagar);
                    }
                }
            }

            document.querySelectorAll('input[type="number"]').forEach(input => {
                input.addEventListener('input', calcular);
            });

            window.onload = calcular;
        </script>
    @endif
</x-app-layout>
