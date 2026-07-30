<x-app-layout>
    <section>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Registrar Pre-Liquidación Renta Anual') }}
            </h2>
        </x-slot>

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <div class="max-w-4xl mx-auto">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ __('RAZÓN SOCIAL:') }} <span class="text-blue-500">{{ $empresa->razon_social }}</span>
                            </h3>
                            <h2 class="text-lg font-semibold text-gray-800">
                                {{ __('RUC: ') }} <span class="text-blue-500">{{ $empresa->ruc }}</span>
                            </h2>
                        </div>

                        <div class="mb-4">
                            <a href="{{ url()->previous() }}"
                                class="inline-block bg-gray-300 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-300 text-gray-700">
                                ← Atrás
                            </a>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('pre-liquidacion.store') }}" autocomplete="off"
                            class="space-y-6">
                            @csrf

                            <input type="hidden" name="id_empresas_clientes"
                                value="{{ $empresa->id_empresas_clientes }}">
                            <!-- SELECCIÓN DEL AÑO -->
                            <div class="border-b pb-6 mb-6">
                                <x-input-label class="text-lg font-semibold text-gray-700 mb-4" for="anio"
                                    :value="__('Seleccionar Año')" />
                                <select id="anio" name="anio"
                                    class="block w-full border-gray-300 rounded-md shadow-sm">
                                    @for ($i = date('Y'); $i >= date('Y') - 10; $i--)
                                        <option value="{{ $i }}" {{ $i == $anio ? 'selected' : '' }}>
                                            {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <input type="hidden" name="id_empresas_clientes"
                                value="{{ $empresa->id_empresas_clientes }}">
                            <input type="hidden" name="id_usuario" value="{{ auth()->user()->id }}">

                            <!-- SECCIÓN 1: VENTAS Y COSTOS -->
                            <div class="border-b pb-6 mb-6">
                                <h4 class="text-lg font-semibold text-gray-700 mb-4">Ventas y Costos</h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <x-input-label for="ventas_netas" :value="__('Ventas Netas')" />
                                    <x-text-input id="ventas_netas" name="ventas_netas" type="number" step="0.01"
                                        class="mt-1 block w-full" required oninput="calcularResultados()"
                                        placeholder="0.00" />

                                    <x-input-label for="costo_ventas" :value="__('( - ) Costo de Ventas')" />
                                    <x-text-input id="costo_ventas" name="costo_ventas" type="number" step="0.01"
                                        class="mt-1 block w-full text-red-500"  oninput="calcularResultados()"
                                        placeholder="-0.00" />

                                    <x-input-label for="resultado_bruto" :value="__('Resultado Bruto')" />
                                    <x-text-input id="resultado_bruto" name="resultado_bruto" type="number"
                                        step="0.01" class="mt-1 block w-full bg-gray-200" readonly />
                                </div>
                            </div>

                            <!-- SECCIÓN 2: GASTOS OPERATIVOS -->
                            <div class="border-b pb-6 mb-6">
                                <h4 class="text-lg font-semibold text-gray-700 mb-4">Gastos Operativos</h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <x-input-label for="gastos_ventas" :value="__('( - ) Gastos de Ventas')" />
                                    <x-text-input id="gastos_ventas" name="gastos_ventas" type="number" step="0.01"
                                        class="mt-1 block w-full text-red-500" oninput="calcularResultados()" placeholder="-0.00" />

                                    <x-input-label for="gastos_administracion" :value="__('( - ) Gastos de Administración')" />
                                    <x-text-input id="gastos_administracion" name="gastos_administracion" type="number"
                                        step="0.01" class="mt-1 block w-full text-red-500" oninput="calcularResultados()"
                                        placeholder="-0.00" />

                                    <x-input-label for="depreciacion" :value="__('( - ) Depreciación')" />
                                    <x-text-input id="depreciacion" name="depreciacion" type="number" step="0.01"
                                        class="mt-1 block w-full text-red-500" oninput="calcularResultados()" placeholder="-0.00" />

                                    <x-input-label for="resultado_operacion" :value="__('Resultado de Operación')" />
                                    <x-text-input id="resultado_operacion" name="resultado_operacion" type="number"
                                        step="0.01" class="mt-1 block w-full bg-gray-200" readonly />
                                </div>
                            </div>

                            <!-- SECCIÓN 3: GASTOS FINANCIEROS -->
                            <div class="border-b pb-6 mb-6">
                                <h4 class="text-lg font-semibold text-gray-700 mb-4">Gastos Financieros</h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <x-input-label for="perdida_tipo_cambio" :value="__('(-) Pérdida por Diferencia de Tipo de Cambio')" />
                                    <x-text-input id="perdida_tipo_cambio" name="perdida_tipo_cambio" type="number"
                                        step="0.01" placeholder="0.00" class="mt-1 block w-full text-red-500"
                                        oninput="calcularResultados()" />

                                    <x-input-label for="intereses_otros_gastos" :value="__('(-) Intereses y Otros Gastos Financieros')" />
                                    <x-text-input id="intereses_otros_gastos" name="intereses_otros_gastos"
                                        type="number" step="0.01" placeholder="0.00" class="mt-1 block w-full text-red-500"
                                        oninput="calcularResultados()" />

                                    <x-input-label for="total_gastos_financieros" :value="__('Total Gastos Financieros')" />
                                    <x-text-input id="total_gastos_financieros" name="total_gastos_financieros"
                                        type="number" step="0.01" class="mt-1 block w-full bg-gray-200"
                                        readonly />
                                </div>
                            </div>

                            <!-- SECCIÓN 4: INGRESOS FINANCIEROS -->
                            <div class="border-b pb-6 mb-6">
                                <h4 class="text-lg font-semibold text-gray-700 mb-4">Ingresos Financieros</h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <x-input-label for="ingresos_financieros_gravados" :value="__('Ingresos Financieros Gravados')" />
                                    <x-text-input id="ingresos_financieros_gravados"
                                        name="ingresos_financieros_gravados" type="number" step="0.01"
                                        placeholder="0.00" class="mt-1 block w-full"
                                        oninput="calcularResultados()" />

                                    <x-input-label for="ingresos_financieros_tipo_cambio" :value="__('Ingresos Financieros por Diferencia de Tipo de Cambio')" />
                                    <x-text-input id="ingresos_financieros_tipo_cambio"
                                        name="ingresos_financieros_tipo_cambio" type="number" step="0.01"
                                        placeholder="0.00" class="mt-1 block w-full"
                                        oninput="calcularResultados()" />

                                    <x-input-label for="total_ingresos_financieros" :value="__('Total Ingresos Financieros')" />
                                    <x-text-input id="total_ingresos_financieros" name="total_ingresos_financieros"
                                        type="number" step="0.01" class="mt-1 block w-full bg-gray-200"
                                        readonly />
                                </div>
                            </div>
                            <!-- SECCIÓN 5: Otros Ingresos Gravados -->
                            <div class="border-b pb-6 mb-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <x-input-label for="otros_ingresos_gravados" :value="__('Otros Ingresos Gravados')" />
                                    <x-text-input id="otros_ingresos_gravados" name="otros_ingresos_gravados"
                                        type="number" step="0.01"  placeholder="0.00"
                                        class="mt-1 block w-full" />
                                </div>
                            </div>
                            <!-- SECCIÓN 6: RESULTADO FINAL -->
                            <div class="border-b pb-6 mb-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <h4 class="text-lg font-semibold text-gray-700 mb-4">Resultados antes del Impuesto
                                    </h4>
                                    {{-- <x-input-label for="total" :value="__('Resultados antes de Participaciones')" /> --}}
                                    <x-text-input id="total" name="total" type="number" step="0.01"
                                        class="mt-1 block w-full bg-gray-200" readonly />
                                </div>
                            </div>

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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const inputs = document.querySelectorAll("input[type='number']");

            inputs.forEach(input => {
                input.addEventListener("input", calcularResultados);
            });

            function calcularResultados() {
                // Obtener valores de ventas y costos
                let ventasNetas = parseFloat(document.getElementById('ventas_netas').value) || 0;
                let costoVentas = parseFloat(document.getElementById('costo_ventas').value) || 0;
                let resultadoBruto = ventasNetas + costoVentas;
                document.getElementById('resultado_bruto').value = resultadoBruto.toFixed(2);

                // Obtener valores de gastos operativos
                let gastosVentas = parseFloat(document.getElementById('gastos_ventas').value) || 0;
                let gastosAdministracion = parseFloat(document.getElementById('gastos_administracion').value) || 0;
                let depreciacion = parseFloat(document.getElementById('depreciacion').value) || 0;
                let resultadoOperacion = resultadoBruto + gastosVentas + gastosAdministracion + depreciacion;
                document.getElementById('resultado_operacion').value = resultadoOperacion.toFixed(2);

                // Obtener valores de gastos financieros
                let perdidaTipoCambio = parseFloat(document.getElementById('perdida_tipo_cambio').value) || 0;
                let interesesGastos = parseFloat(document.getElementById('intereses_otros_gastos').value) || 0;
                let totalGastosFinancieros = perdidaTipoCambio + interesesGastos;
                document.getElementById('total_gastos_financieros').value = totalGastosFinancieros.toFixed(2);

                // Obtener valores de ingresos financieros
                let ingresosFinancieros = parseFloat(document.getElementById('ingresos_financieros_gravados')
                    .value) || 0;
                let ingresosTipoCambio = parseFloat(document.getElementById('ingresos_financieros_tipo_cambio')
                    .value) || 0;
                let totalIngresosFinancieros = ingresosFinancieros + ingresosTipoCambio;
                document.getElementById('total_ingresos_financieros').value = totalIngresosFinancieros.toFixed(2);

                // Calcular resultados antes de participaciones
                let otrosIngresosGravados = parseFloat(document.getElementById('otros_ingresos_gravados').value) ||
                    0;
                let resultadoAntesParticipaciones = resultadoOperacion + totalGastosFinancieros +
                    totalIngresosFinancieros + otrosIngresosGravados;
                document.getElementById('total').value = Math.round(resultadoAntesParticipaciones);
                console.log(resultadoAntesParticipaciones)
            }
        });
    </script>

</x-app-layout>
