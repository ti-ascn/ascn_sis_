@php
    $anoActual = strtoupper($fecha->locale('es')->isoFormat('YYYY'));
    $porcentaje10 = $anoActual >= 2026 ? '10.5%' : '10%';
    $factorVentas10 = $anoActual >= 2026 ? 0.105 : 0.1;
@endphp
<x-app-layout>
    <section>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Determinación Mensual') }}
            </h2>
        </x-slot>

        <div class="py-6">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-5">

                {{-- HEADER EMPRESA --}}
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="space-y-1">
                            <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest">Empresa</p>
                            <h3 class="text-xl font-bold leading-tight">{{ $empresa->razon_social }}</h3>
                            <div class="flex flex-wrap items-center gap-4 mt-1">
                                <span class="flex items-center gap-1 text-blue-100 text-sm">
                                    <i class="bi bi-building"></i>
                                    RUC: <strong class="text-white ml-1">{{ $empresa->ruc }}</strong>
                                </span>
                                <span class="flex items-center gap-1 text-blue-100 text-sm">
                                    <i class="bi bi-calendar3"></i>
                                    <strong
                                        class="text-white ml-1">{{ strtoupper($fecha->locale('es')->isoFormat('MMMM - YYYY')) }}</strong>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <a href="{{ route('determinacion_mensual.show', $determinacion->id_determinacion_mensual) }}"
                                class="flex items-center gap-2 bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition duration-200 text-white text-sm font-medium">
                                <i class="bi bi-arrow-left"></i> Atrás
                            </a>
                            <button type="button" onclick="toggleModal('modalpdfdeterminaciones')"
                                class="flex items-center gap-2 bg-white text-blue-700 hover:bg-blue-50 px-4 py-2 rounded-lg transition duration-200 text-sm font-semibold shadow">
                                <i class="bi bi-filetype-pdf"></i> Agregar PDF
                            </button>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-red-700 font-semibold mb-2">
                            <i class="bi bi-exclamation-triangle-fill"></i> Errores en el formulario
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                    action="{{ route('determinacion_mensual.update', $determinacion->id_determinacion_mensual) }}"
                    autocomplete="off" class="space-y-5" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id_empresas_clientes" value="{{ $empresa->id_empresas_clientes }}">
                    <input type="hidden" name="id_usuario" value="{{ auth()->user()->id }}">
                    <input type="hidden" name="fecha" value="{{ $determinacion->fecha }}">

                    {{-- VENTAS --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-emerald-50 border-b border-emerald-100 px-6 py-4 flex items-center gap-3">
                            <div class="bg-emerald-500 text-white rounded-lg p-2">
                                <i class="bi bi-graph-up-arrow text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-emerald-800">Ventas</h4>
                                <p class="text-xs text-emerald-600">Registro de ingresos y ventas del periodo</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-x-8 gap-y-5">
                                <div>
                                    <x-input-label for="base_ventas" :value="__('Base Ventas 18%')" />
                                    <x-text-input id="base_ventas" name="base_ventas" type="number" step="0.01"
                                        class="mt-1 block w-full rounded-lg" oninput="calcularDeterminacion()"
                                        value="{{ $determinacion->base_ventas }}" placeholder="0.00" />
                                </div>
                                <div>
                                    <x-input-label for="base_ventas_10" :value="__('Base Ventas ' . $porcentaje10)" />
                                    <x-text-input id="base_ventas_10" name="base_ventas_10" type="number"
                                        step="0.01" class="mt-1 block w-full rounded-lg"
                                        oninput="calcularDeterminacion()" value="{{ $determinacion->base_ventas_10 }}"
                                        placeholder="0.00" />
                                </div>
                                <div>
                                    <x-input-label for="igv_ventas" :value="__('IGV Ventas 18%')" />
                                    <x-text-input id="igv_ventas" name="igv_ventas" type="number" step="0.01"
                                        class="mt-1 block w-full rounded-lg bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed"
                                        readonly value="{{ $determinacion->igv_ventas }}" />
                                </div>
                                <div>
                                    <x-input-label for="igv_ventas_10" :value="__('IGV Ventas ' . $porcentaje10)" />
                                    <x-text-input id="igv_ventas_10" name="igv_ventas_10" type="number" step="0.01"
                                        class="mt-1 block w-full rounded-lg bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed"
                                        readonly value="{{ $determinacion->igv_ventas_10 }}" />
                                </div>
                                <div>
                                    <x-input-label for="descuentos_conce_devolu" :value="__('Descuentos Concedidos y/o Devoluciones de Ventas')" />
                                    <x-text-input id="descuentos_conce_devolu" name="descuentos_conce_devolu"
                                        type="number" step="0.01"
                                        class="mt-1 block w-full rounded-lg text-red-600 border-red-200 focus:border-red-400"
                                        oninput="calcularDeterminacion()"
                                        value="{{ $determinacion->descuentos_conce_devolu }}" placeholder="0.00" />
                                </div>
                                <div>
                                    <x-input-label for="descuento_concedido_tributo" :value="__('Descuentos Concedidos Tributo')" />
                                    <x-text-input id="descuento_concedido_tributo" name="descuento_concedido_tributo"
                                        type="number" step="0.01"
                                        class="mt-1 block w-full rounded-lg text-red-600 border-red-200 focus:border-red-400"
                                        oninput="calcularDeterminacion()"
                                        value="{{ $determinacion->descuento_concedido_tributo }}" placeholder="0.00" />
                                </div>
                                <div>
                                    <x-input-label for="ventas_nogravadas_sinexpor" :value="__('Ventas no Gravadas (Sin Considerar Exportaciones)')" />
                                    <x-text-input id="ventas_nogravadas_sinexpor" name="ventas_nogravadas_sinexpor"
                                        type="number" step="0.01" class="mt-1 block w-full rounded-lg"
                                        oninput="calcularDeterminacion()"
                                        value="{{ $determinacion->ventas_nogravadas_sinexpor }}"
                                        placeholder="0.00" />
                                </div>
                                <div class="col-span-2">
                                    <div
                                        class="bg-emerald-50 rounded-lg p-4 border border-emerald-200 flex items-center justify-between">
                                        <x-input-label for="total_ventas" :value="__('Total Ventas')"
                                            class="!mb-0 font-bold text-emerald-800" />
                                        <x-text-input id="total_ventas" name="total_ventas" type="number"
                                            step="0.01"
                                            class="w-52 bg-transparent border-0 border-b-2 border-emerald-500 text-emerald-800 font-bold text-right text-lg rounded-none focus:ring-0 cursor-not-allowed"
                                            readonly value="{{ $determinacion->total_ventas }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- COMPRAS --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-orange-50 border-b border-orange-100 px-6 py-4 flex items-center gap-3">
                            <div class="bg-orange-500 text-white rounded-lg p-2">
                                <i class="bi bi-cart3 text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-orange-800">Compras</h4>
                                <p class="text-xs text-orange-600">Registro de compras y adquisiciones del periodo</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-x-8 gap-y-5">
                                <div>
                                    <x-input-label for="base_compras_18" :value="__('Base Compras 18%')" />
                                    <x-text-input id="base_compras_18" name="base_compras_18" type="number"
                                        step="0.01" class="mt-1 block w-full rounded-lg"
                                        oninput="calcularDeterminacion()"
                                        value="{{ $determinacion->base_compras_18 }}" placeholder="0.00" />
                                </div>
                                <div>
                                    <x-input-label for="base_compras_10" :value="__('Base Compras')" />
                                    <x-text-input id="base_compras_10" name="base_compras_10" type="number"
                                        step="0.01" class="mt-1 block w-full rounded-lg"
                                        oninput="calcularDeterminacion()"
                                        value="{{ $determinacion->base_compras_10 }}" placeholder="0.00" />
                                </div>
                                <div>
                                    <x-input-label for="igv_compras_18" :value="__('IGV Compras 18%')" />
                                    <x-text-input id="igv_compras_18" name="igv_compras_18" type="number"
                                        step="0.01"
                                        class="mt-1 block w-full rounded-lg bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed"
                                        readonly value="{{ $determinacion->igv_compras_18 }}" />
                                </div>
                                <div class="grid grid-cols-6 gap-4">
                                    <div class="col-span-2">
                                        <x-input-label for="porcentaje_compras" :value="__('%')" />
                                        <select id="porcentaje_compras" name="porcentaje_compras"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm h-10"
                                            onchange="calcularDeterminacion()">
                                            <option value="0.10"
                                                {{ (float)$determinacion->porcentaje_compras <= 0.1 ? 'selected' : '' }}>
                                                10%</option>
                                            <option value="0.105"
                                                {{ (float)$determinacion->porcentaje_compras > 0.1 ? 'selected' : '' }}>10.5%
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-span-4">
                                        <x-input-label for="igv_compras_10" :value="__('IGV Compras')" />
                                        <x-text-input id="igv_compras_10" name="igv_compras_10" type="number"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-lg bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed"
                                            readonly value="{{ $determinacion->igv_compras_10 }}" />
                                    </div>
                                </div>
                                <div>
                                    <x-input-label for="base_compras_ng" :value="__('Base Compras NG')" />
                                    <x-text-input id="base_compras_ng" name="base_compras_ng" type="number"
                                        step="0.01" class="mt-1 block w-full rounded-lg"
                                        oninput="calcularDeterminacion()"
                                        value="{{ $determinacion->base_compras_ng }}" placeholder="0.00" />
                                </div>
                                <div>
                                    <x-input-label for="comprasnetas_gravadas_nogravadas" :value="__('Compras netas destinadas a ventas gravadas y no gravadas')" />
                                    <x-text-input id="comprasnetas_gravadas_nogravadas"
                                        name="comprasnetas_gravadas_nogravadas" type="number" step="0.01"
                                        class="mt-1 block w-full rounded-lg" oninput="calcularDeterminacion()"
                                        value="{{ $determinacion->comprasnetas_gravadas_nogravadas }}"
                                        placeholder="0.00" />
                                </div>
                                <div>
                                    <x-input-label for="comprasnetas_nogravadas_exclusivas" :value="__('Compras netas destinadas a ventas no gravadas exclusivamente')" />
                                    <x-text-input id="comprasnetas_nogravadas_exclusivas"
                                        name="comprasnetas_nogravadas_exclusivas" type="number" step="0.01"
                                        class="mt-1 block w-full rounded-lg" oninput="calcularDeterminacion()"
                                        value="{{ $determinacion->comprasnetas_nogravadas_exclusivas }}"
                                        placeholder="0.00" />
                                </div>
                                <div>
                                    <x-input-label for="comprasinternas_nogravadas" :value="__('Compras internas no gravadas')" />
                                    <x-text-input id="comprasinternas_nogravadas" name="comprasinternas_nogravadas"
                                        type="number" step="0.01" class="mt-1 block w-full rounded-lg"
                                        oninput="calcularDeterminacion()"
                                        value="{{ $determinacion->comprasinternas_nogravadas }}"
                                        placeholder="0.00" />
                                </div>
                                <div class="col-span-2">
                                    <div
                                        class="bg-orange-50 rounded-lg p-4 border border-orange-200 flex items-center justify-between">
                                        <x-input-label for="total_compras" :value="__('Total Compras')"
                                            class="!mb-0 font-bold text-orange-800" />
                                        <x-text-input id="total_compras" name="total_compras" type="number"
                                            step="0.01"
                                            class="w-52 bg-transparent border-0 border-b-2 border-orange-500 text-orange-800 font-bold text-right text-lg rounded-none focus:ring-0 cursor-not-allowed"
                                            readonly value="{{ $determinacion->total_compras }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- IGV A PAGAR --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-blue-50 border-b border-blue-100 px-6 py-4 flex items-center gap-3">
                            <div class="bg-blue-500 text-white rounded-lg p-2">
                                <i class="bi bi-receipt text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-blue-800">IGV a Pagar</h4>
                                <p class="text-xs text-blue-600">Cálculo del IGV resultante del periodo</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-x-8 gap-y-5">
                                <div>
                                    <x-input-label for="igv_a_pagar" :value="__('IGV a Pagar / Crédito')" />
                                    <x-text-input id="igv_a_pagar" name="igv_a_pagar" type="number" step="0.01"
                                        class="mt-1 block w-full rounded-lg bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed"
                                        readonly value="{{ $determinacion->igv_a_pagar }}" />
                                </div>
                                <div>
                                    <x-input-label for="saldo_a_favor_periodo_anterior" :value="__('( − ) Saldo a Favor del Periodo Anterior')" />
                                    <x-text-input id="saldo_a_favor_periodo_anterior"
                                        name="saldo_a_favor_periodo_anterior" type="text" step="0.01"
                                        class="mt-1 block w-full rounded-lg text-red-600 border-red-200 focus:border-red-400"
                                        oninput="calcularDeterminacion()"
                                        value="{{ $determinacion->saldo_a_favor_periodo_anterior }}" />
                                </div>
                                <div class="col-span-2">
                                    <div
                                        class="bg-blue-50 rounded-lg p-4 border border-blue-200 flex items-center justify-between">
                                        <x-input-label for="total_igv_a_pagar" :value="__('Total IGV a Pagar o Saldo a Favor')"
                                            class="!mb-0 font-bold text-blue-800" />
                                        <x-text-input id="total_igv_a_pagar" name="total_igv_a_pagar" type="number"
                                            step="0.01"
                                            class="w-52 bg-transparent border-0 border-b-2 border-blue-500 text-blue-800 font-bold text-right text-lg rounded-none focus:ring-0 cursor-not-allowed"
                                            readonly value="{{ $determinacion->total_igv_a_pagar }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PORCENTAJE MYPE --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-violet-50 border-b border-violet-100 px-6 py-4 flex items-center gap-3">
                            <div class="bg-violet-500 text-white rounded-lg p-2">
                                <i class="bi bi-percent text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-violet-800">Porcentaje MYPE</h4>
                                <p class="text-xs text-violet-600">Categoría y tasa aplicable a la empresa</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-x-8 gap-y-5">
                                <div>
                                    <x-input-label for="mype" :value="__('MYPE (UIT)')" />
                                    <select id="mype" name="mype"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-400 focus:ring focus:ring-violet-200 focus:ring-opacity-50"
                                        onchange="actualizarPorcentajeMype()">
                                        <option value="menor_300"
                                            {{ $determinacion->porcentaje_mype == 0.01 ? 'selected' : '' }}>Menos de
                                            300 UIT (0.010)</option>
                                        <option value="mayor_300"
                                            {{ $determinacion->porcentaje_mype == 0.015 ? 'selected' : '' }}>Más de 300
                                            UIT (0.015)</option>
                                        <option value="coeficiente"
                                            {{ $determinacion->porcentaje_mype != 0.01 && $determinacion->porcentaje_mype != 0.015 ? 'selected' : '' }}>
                                            Personalizado</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="porcentaje_mype" :value="__('Porcentaje MYPE')" />
                                    <x-text-input id="porcentaje_mype" name="porcentaje_mype" type="number"
                                        step="0.0001" class="mt-1 block w-full rounded-lg"
                                        oninput="manejarCambioManual()" value="{{ $determinacion->porcentaje_mype }}"
                                        placeholder="Ej: 0.0105" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RENTA --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-amber-50 border-b border-amber-100 px-6 py-4 flex items-center gap-3">
                            <div class="bg-amber-500 text-white rounded-lg p-2">
                                <i class="bi bi-bank text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-amber-800">Renta</h4>
                                <p class="text-xs text-amber-600">Cálculo del impuesto a la renta del periodo</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-x-8 gap-y-5">
                                <div>
                                    <x-input-label for="ingresos_netos" :value="__('Ingresos Netos')" />
                                    <x-text-input id="ingresos_netos" name="ingresos_netos" type="number"
                                        step="0.01"
                                        class="mt-1 block w-full rounded-lg bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed"
                                        value="{{ $determinacion->ingresos_netos }}" readonly />
                                </div>
                                <div>
                                    <x-input-label for="renta_pagar" :value="__('Cálculo Renta a Pagar')" />
                                    <x-text-input id="renta_pagar" name="renta_pagar" type="number" step="0.01"
                                        class="mt-1 block w-full rounded-lg bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed"
                                        value="{{ $determinacion->renta_pagar }}" readonly />
                                </div>
                                <div>
                                    <x-input-label for="saldo_a_favor_renta_periodo_anterior" :value="__('( − ) Saldo a Favor Renta del Periodo Anterior')" />
                                    <x-text-input id="saldo_a_favor_renta_periodo_anterior"
                                        name="saldo_a_favor_renta_periodo_anterior" type="number" step="0.01"
                                        class="mt-1 block w-full rounded-lg text-red-600 border-red-200 focus:border-red-400"
                                        oninput="calcularDeterminacion()"
                                        value="{{ $determinacion->saldo_a_favor_renta_periodo_anterior }}"
                                        placeholder="-0.00" />
                                </div>
                                <div class="col-span-2">
                                    <div
                                        class="bg-amber-50 rounded-lg p-4 border border-amber-200 flex items-center justify-between">
                                        <x-input-label for="total_renta_a_pagar" :value="__('Total Renta a Pagar')"
                                            class="!mb-0 font-bold text-amber-800" />
                                        <x-text-input id="total_renta_a_pagar" name="total_renta_a_pagar"
                                            type="number" step="0.01"
                                            class="w-52 bg-transparent border-0 border-b-2 border-amber-500 text-amber-800 font-bold text-right text-lg rounded-none focus:ring-0 cursor-not-allowed"
                                            readonly value="{{ $determinacion->total_renta_a_pagar }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TRIBUTOS (destacado oscuro) --}}
                    <div class="bg-gradient-to-r from-slate-700 to-slate-800 rounded-xl shadow-lg overflow-hidden">
                        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-white/20 text-white rounded-lg p-2">
                                    <i class="bi bi-cash-stack text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-base font-bold text-white">Total a Pagar Tributos</h4>
                                    <p class="text-xs text-slate-400">Suma total de obligaciones tributarias del
                                        periodo</p>
                                </div>
                            </div>
                            <x-text-input id="total_a_pagar_tributos" name="total_a_pagar_tributos" type="number"
                                step="0.01"
                                class="w-full sm:w-56 bg-white/10 border-0 border-b-2 border-white/50 text-white font-bold text-right text-2xl rounded-none focus:ring-0 cursor-not-allowed"
                                readonly value="{{ $determinacion->total_a_pagar_tributos }}" />
                        </div>
                    </div>

                    {{-- RETENCIONES Y PERCEPCIONES --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-teal-50 border-b border-teal-100 px-6 py-4 flex items-center gap-3">
                            <div class="bg-teal-500 text-white rounded-lg p-2">
                                <i class="bi bi-shield-check text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-teal-800">Retenciones y Percepciones</h4>
                                <p class="text-xs text-teal-600">Importes retenidos y percibidos del periodo</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <p
                                    class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <span class="w-6 h-px bg-teal-300 inline-block"></span> Retenciones
                                </p>
                                <div class="grid grid-cols-2 gap-x-8 gap-y-5">
                                    <div>
                                        <x-input-label for="retenciones_del_periodo" :value="__('Retenciones del Periodo')" />
                                        <x-text-input id="retenciones_del_periodo" name="retenciones_del_periodo"
                                            type="number" step="0.01" class="mt-1 block w-full rounded-lg"
                                            oninput="calcularDeterminacion()"
                                            value="{{ $determinacion->retenciones_del_periodo }}"
                                            placeholder="0.00" />
                                    </div>
                                    <div>
                                        <x-input-label for="retenciones_periodo_anterior" :value="__('Retenciones del Periodo Anterior')" />
                                        <x-text-input id="retenciones_periodo_anterior"
                                            name="retenciones_periodo_anterior" type="number" step="0.01"
                                            class="mt-1 block w-full rounded-lg" oninput="calcularDeterminacion()"
                                            value="{{ $determinacion->retenciones_periodo_anterior }}"
                                            placeholder="0.00" />
                                    </div>
                                    <div class="col-span-2">
                                        <div
                                            class="bg-teal-50 rounded-lg p-3 border border-teal-200 flex items-center justify-between">
                                            <x-input-label for="total_retenciones" :value="__('Total Retenciones')"
                                                class="!mb-0 font-bold text-teal-800" />
                                            <x-text-input id="total_retenciones" name="total_retenciones"
                                                type="number" step="0.01"
                                                class="w-44 bg-transparent border-0 border-b-2 border-teal-500 text-teal-800 font-bold text-right rounded-none focus:ring-0 cursor-not-allowed"
                                                readonly value="{{ $determinacion->total_retenciones }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <p
                                    class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <span class="w-6 h-px bg-teal-300 inline-block"></span> Percepciones
                                </p>
                                <div class="grid grid-cols-2 gap-x-8 gap-y-5">
                                    <div>
                                        <x-input-label for="percepciones_del_periodo" :value="__('Percepciones del Periodo')" />
                                        <x-text-input id="percepciones_del_periodo" name="percepciones_del_periodo"
                                            type="number" step="0.01" class="mt-1 block w-full rounded-lg"
                                            oninput="calcularDeterminacion()"
                                            value="{{ $determinacion->percepciones_del_periodo }}"
                                            placeholder="0.00" />
                                    </div>
                                    <div>
                                        <x-input-label for="percepcion_periodo_anterior" :value="__('Percepción del Periodo Anterior')" />
                                        <x-text-input id="percepcion_periodo_anterior"
                                            name="percepcion_periodo_anterior" type="number" step="0.01"
                                            class="mt-1 block w-full rounded-lg" oninput="calcularDeterminacion()"
                                            value="{{ $determinacion->percepcion_periodo_anterior }}"
                                            placeholder="0.00" />
                                    </div>
                                    <div>
                                        <x-input-label for="percepciones_no_aplicadas" :value="__('Percepciones No Aplicadas')" />
                                        <x-text-input id="percepciones_no_aplicadas" name="percepciones_no_aplicadas"
                                            type="number" step="0.01" class="mt-1 block w-full rounded-lg"
                                            oninput="calcularDeterminacion()"
                                            value="{{ $determinacion->percepciones_no_aplicadas }}"
                                            placeholder="0.00" />
                                    </div>
                                    <div class="flex items-end">
                                        <div
                                            class="w-full bg-teal-50 rounded-lg p-3 border border-teal-200 flex items-center justify-between">
                                            <x-input-label for="total_percepciones" :value="__('Total Percepciones')"
                                                class="!mb-0 font-bold text-teal-800" />
                                            <x-text-input id="total_percepciones" name="total_percepciones"
                                                type="number" step="0.01"
                                                class="w-44 bg-transparent border-0 border-b-2 border-teal-500 text-teal-800 font-bold text-right rounded-none focus:ring-0 cursor-not-allowed"
                                                readonly value="{{ $determinacion->total_percepciones }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="temp_pdt_path" id="temp_pdt_path">

                    <div class="flex justify-end pb-4">
                        <x-primary-button class="px-8 py-3 text-base font-semibold">
                            <i class="bi bi-check-lg mr-2"></i> {{ __('Guardar Cambios') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        const FACTOR_VENTAS_10 = {{ $factorVentas10 }};
    </script>

    <script>
        let esCoeficiente = false;
        let coeficienteOriginal = null;

        function actualizarPorcentajeMype() {
            const mypeSelect = document.getElementById('mype');
            const porcentajeMypeInput = document.getElementById('porcentaje_mype');

            //  COEFICIENTE
            if (mypeSelect.value === 'coeficiente') {
                esCoeficiente = true;
                if (coeficienteOriginal !== null) {
                    porcentajeMypeInput.value = coeficienteOriginal;
                }
                calcularDeterminacion();
                return;
            }
            esCoeficiente = false;

            if (mypeSelect.value === 'menor_300') {
                porcentajeMypeInput.value = '0.010';
            } else if (mypeSelect.value === 'mayor_300') {
                porcentajeMypeInput.value = '0.015';
            }
            calcularDeterminacion();
        }

        function manejarCambioManual() {
            const mypeSelect = document.getElementById('mype');
            const porcentajeMypeInput = document.getElementById('porcentaje_mype');
            const valStr = porcentajeMypeInput.value;
            const valNum = parseFloat(valStr);
            // Comparación numérica usando parseFloat
            if (valNum === 0.01) {
                mypeSelect.value = 'menor_300';
                esCoeficiente = false;
            } else if (valNum === 0.015) {
                mypeSelect.value = 'mayor_300';
                esCoeficiente = false;
            } else if (!isNaN(valNum)) {
                mypeSelect.value = 'coeficiente';
                coeficienteOriginal = valStr;
                esCoeficiente = true;
            }

            calcularDeterminacion();
        }

        // Inicialización al cargar la página
        document.addEventListener("DOMContentLoaded", function() {
            const porcentajeMypeInput = document.getElementById('porcentaje_mype');
            const mypeSelect = document.getElementById('mype');
            const valStr = porcentajeMypeInput.value;
            const valNum = parseFloat(valStr);
            if (!isNaN(valNum)) {
                if (valNum === 0.01) {
                    mypeSelect.value = 'menor_300';
                    esCoeficiente = false;
                } else if (valNum === 0.015) {
                    mypeSelect.value = 'mayor_300';
                    esCoeficiente = false;
                } else {
                    esCoeficiente = true;
                    coeficienteOriginal = valStr;
                    mypeSelect.value = 'coeficiente';
                }
            }
        });

        function calcularDeterminacion() {
            // Obtener valores de compras
            let baseCompras18 = Math.round(parseFloat(document.getElementById('base_compras_18').value) || 0);
            let baseCompras10 = Math.round(parseFloat(document.getElementById('base_compras_10').value) || 0);
            let porcentaje_compras = parseFloat(document.getElementById('porcentaje_compras').value) || 0.10;
            let baseComprasNG = Math.round(parseFloat(document.getElementById('base_compras_ng').value) || 0);
            let comprasnetas_gravadas_nogravadas = Math.round(parseFloat(document.getElementById(
                'comprasnetas_gravadas_nogravadas').value) || 0);
            let comprasnetas_nogravadas_exclusivas = Math.round(parseFloat(document.getElementById(
                'comprasnetas_nogravadas_exclusivas').value) || 0);
            let comprasinternas_nogravadas = Math.round(parseFloat(document.getElementById('comprasinternas_nogravadas')
                .value) || 0);

            // Calcular IGV Compras
            let igvCompras18 = Math.round(baseCompras18 * 0.18);
            let igvCompras10 = Math.round(baseCompras10 * porcentaje_compras);

            // Calcular Total Compras
            let totalCompras = baseCompras18 + baseCompras10 + igvCompras18 + igvCompras10 + baseComprasNG +
                comprasnetas_gravadas_nogravadas + comprasnetas_nogravadas_exclusivas + comprasinternas_nogravadas;

            // Actualizar campos de IGV Compras y Total Compras
            document.getElementById('igv_compras_18').value = igvCompras18.toFixed(2);
            document.getElementById('igv_compras_10').value = igvCompras10.toFixed(2);
            document.getElementById('total_compras').value = totalCompras.toFixed(2);

            // Obtener valores de ventas
            let baseVentas = parseFloat(document.getElementById('base_ventas').value) || 0;
            let baseVentas10 = parseFloat(document.getElementById('base_ventas_10').value) || 0;

            // Calcular IGV Ventas
            let igvVentas = Math.round(baseVentas * 0.18);
            let igvVentas10 = Math.round(baseVentas10 * FACTOR_VENTAS_10);

            // Obtener valores de pdf
            let descuento_concedido_tributo = parseFloat(document.getElementById('descuento_concedido_tributo').value) || 0;
            let descuentos_conce_devolu = parseFloat(document.getElementById('descuentos_conce_devolu').value) || 0;
            let ventas_nogravadas_sinexpor = parseFloat(document.getElementById('ventas_nogravadas_sinexpor').value) || 0;

            // Calcular Total Ventas
            let totalVentas = baseVentas + igvVentas + baseVentas10 + igvVentas10 - (-descuentos_conce_devolu) +
                ventas_nogravadas_sinexpor;

            // Actualizar campos de IGV Ventas y Total Ventas
            document.getElementById('igv_ventas').value = igvVentas.toFixed(2);
            document.getElementById('igv_ventas_10').value = igvVentas10.toFixed(2);
            document.getElementById('total_ventas').value = totalVentas.toFixed(2);

            // Calcular IGV a Pagar
            let igvAPagar = Math.round((igvVentas + igvVentas10 - (-descuento_concedido_tributo)) - (igvCompras18 +
                igvCompras10));

            // Obtener Saldo a Favor del Periodo Anterior
            let saldoAFavorPeriodoAnterior = parseFloat(document.getElementById('saldo_a_favor_periodo_anterior').value) ||
                0;

            // Calcular Total IGV a Pagar o Saldo a Favor
            let totalIgvAPagar = igvAPagar + saldoAFavorPeriodoAnterior;

            // Actualizar campos de IGV a Pagar y Total IGV a Pagar
            document.getElementById('igv_a_pagar').value = igvAPagar.toFixed(2);
            document.getElementById('total_igv_a_pagar').value = totalIgvAPagar.toFixed(2);

            // Obtener Saldo a Favor Renta del Periodo Anterior
            let saldoAFavorRentaPeriodoAnterior = parseFloat(document.getElementById('saldo_a_favor_renta_periodo_anterior')
                .value) || 0;

            //Calculo Ingresos Netos
            let ingresosnetos = Math.round((baseVentas + baseVentas10 - (-descuentos_conce_devolu) +
                ventas_nogravadas_sinexpor));
            document.getElementById('ingresos_netos').value = ingresosnetos.toFixed(2);

            // Calcular Total Renta a Pagar
            let porcentajeMype = parseFloat(document.getElementById('porcentaje_mype').value) || 0;
            let saldofavorperiodoant = parseFloat(document.getElementById('saldo_a_favor_renta_periodo_anterior').value) ||
                0;

            //Calcular renta a pagar
            let rentaAPagar = Math.round(ingresosnetos * porcentajeMype);
            document.getElementById('renta_pagar').value = rentaAPagar.toFixed(2);

            // Calcular Total Renta a Pagar 2.0
            let totalRentaAPagar = Math.round(rentaAPagar - (-saldofavorperiodoant));

            // Actualizar campo de Total Renta a Pagar
            document.getElementById('total_renta_a_pagar').value = totalRentaAPagar.toFixed(2);

            // Calcular Total a Pagar Tributos
            let totalAPagarTributos = (totalIgvAPagar > 0 ? totalIgvAPagar : 0) + (totalRentaAPagar > 0 ? totalRentaAPagar :
                0);

            // Actualizar campo de Total a Pagar Tributos
            document.getElementById('total_a_pagar_tributos').value = totalAPagarTributos.toFixed(2);

            // Obtener valores de Retenciones y Percepciones
            let retencionesDelPeriodo = parseFloat(document.getElementById('retenciones_del_periodo').value) || 0;
            let retencionesPeriodoAnterior = parseFloat(document.getElementById('retenciones_periodo_anterior').value) || 0;
            let totalRetenciones = retencionesDelPeriodo + retencionesPeriodoAnterior;

            let percepcionesDelPeriodo = parseFloat(document.getElementById('percepciones_del_periodo').value) || 0;
            let percepcionPeriodoAnterior = parseFloat(document.getElementById('percepcion_periodo_anterior').value) || 0;
            let percepcionesNoAplicadas = parseFloat(document.getElementById('percepciones_no_aplicadas').value) || 0;
            let totalPercepciones = percepcionesDelPeriodo + percepcionPeriodoAnterior + percepcionesNoAplicadas;

            // Actualizar campos de Total Retenciones y Total Percepciones
            document.getElementById('total_retenciones').value = totalRetenciones.toFixed(2);
            document.getElementById('total_percepciones').value = totalPercepciones.toFixed(2);
        }

        /* Función para subir el PDF y extraer los datos automáticamente*/
        function uploadAndExtractPdf() {
            const fileInput = document.getElementById('pdf_file_input');
            const file = fileInput.files[0];

            if (!file) {
                showPdfMessage('Por favor selecciona un archivo PDF', 'error');
                return;
            }

            if (file.type !== 'application/pdf') {
                showPdfMessage('El archivo debe ser un PDF', 'error');
                return;
            }

            // Mostrar estado de carga
            document.getElementById('upload_text').classList.add('hidden');
            document.getElementById('upload_loading').classList.remove('hidden');
            document.getElementById('btn_upload_pdf').disabled = true;

            // Crear FormData para enviar el archivo
            const formData = new FormData();
            formData.append('pdf_file', file);
            formData.append('_token', '{{ csrf_token() }}');

            // Enviar el archivo al servidor
            fetch('{{ route('determinacion_mensual.extractPdfData') }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Verificar si la respuesta es JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('El servidor no retornó una respuesta JSON válida');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Auto-completar los campos del formulario
                        fillFormFields(data.data);

                        // Asignar ruta temporal al input oculto
                        if (data.temp_path) {
                            document.getElementById('temp_pdt_path').value = data.temp_path;
                        }

                        showPdfMessage('Datos extraídos correctamente. Los campos han sido completados.',
                            'success');

                        // Cerrar el modal después de 2 segundos
                        setTimeout(() => {
                            toggleModal('modalpdfdeterminaciones');
                            // Limpiar el input de archivo
                            fileInput.value = '';
                            document.getElementById('pdf_upload_message').classList.add('hidden');
                        }, 2000);
                    } else {
                        showPdfMessage(data.message || 'Error desconocido al procesar el PDF', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    showPdfMessage('Error al procesar el PDF: ' + error.message, 'error');
                })
                .finally(() => {
                    // Restaurar el botón
                    document.getElementById('upload_text').classList.remove('hidden');
                    document.getElementById('upload_loading').classList.add('hidden');
                    document.getElementById('btn_upload_pdf').disabled = false;
                });


            /* Función para completar los campos del formulario con los datos extraídos  */
            function fillFormFields(data) {
                // Mapeo de campos
                const fieldMapping = {
                    'base_compras_18': 'base_compras_18',
                    'base_compras_10': 'base_compras_10',
                    'base_compras_ng': 'base_compras_ng',
                    'comprasnetas_gravadas_nogravadas': 'comprasnetas_gravadas_nogravadas',
                    'comprasnetas_nogravadas_exclusivas': 'comprasnetas_nogravadas_exclusivas',
                    'comprasinternas_nogravadas': 'comprasinternas_nogravadas',
                    'base_ventas': 'base_ventas',
                    'base_ventas_10': 'base_ventas_10',
                    'descuentos_conce_devolu': 'descuentos_conce_devolu',
                    'descuento_concedido_tributo': 'descuento_concedido_tributo',
                    'ventas_nogravadas_sinexpor': 'ventas_nogravadas_sinexpor',
                    'saldo_a_favor_periodo_anterior': 'saldo_a_favor_periodo_anterior',
                    'porcentaje_mype': 'porcentaje_mype',
                    'retenciones_del_periodo': 'retenciones_del_periodo',
                    'retenciones_periodo_anterior': 'retenciones_periodo_anterior',
                    'percepciones_del_periodo': 'percepciones_del_periodo',
                    'percepcion_periodo_anterior': 'percepcion_periodo_anterior',
                    'percepciones_no_aplicadas': 'percepciones_no_aplicadas'
                };

                // Primero, vaciar todos los campos del mapeo
                for (const fieldId of Object.values(fieldMapping)) {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.value = '';
                    }
                }

                // Luego, completar cada campo si hay datos extraídos
                for (const [key, fieldId] of Object.entries(fieldMapping)) {
                    if (data[key] !== undefined && data[key] !== null) {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            let value = data[key];
                            // Asegurar que saldo_a_favor_periodo_anterior siempre tenga signo negativo
                            if (fieldId === 'saldo_a_favor_periodo_anterior' || fieldId === 'descuentos_conce_devolu' ||
                                fieldId === 'descuento_concedido_tributo' &&
                                value) {
                                let numValue = parseFloat(value);
                                if (!isNaN(numValue)) {
                                    value = '-' + Math.abs(numValue).toFixed(2);
                                }
                            }
                            field.value = value;
                        }
                    }
                }
                // ===============================
                // LÓGICA MYPE / COEFICIENTE
                // ===============================
                const mypeSelect = document.getElementById('mype');
                const porcentajeInput = document.getElementById('porcentaje_mype');

                const porcentaje = parseFloat(data.porcentaje_mype ?? 0);
                const tipo = data.mype_tipo ?? null;

                // Reset control
                esCoeficiente = false;
                coeficienteOriginal = null;

                // 🟢 CASO 1: COEFICIENTE REAL (≠ 000)
                if (tipo === 'coeficiente' && porcentaje > 0) {
                    esCoeficiente = true;
                    coeficienteOriginal = porcentaje.toString();

                    mypeSelect.value = 'coeficiente';
                    porcentajeInput.value = coeficienteOriginal;
                }

                // 🟡 CASO 2: COEFICIENTE = 000 → priorizar porcentaje
                else if (porcentaje === 1.0) {
                    mypeSelect.value = 'menor_300';
                    porcentajeInput.value = '0.01';
                } else if (porcentaje === 1.5) {
                    mypeSelect.value = 'mayor_300';
                    porcentajeInput.value = '0.015';
                } else {
                    porcentajeInput.value = '';
                }

                // ===============================
                // RECÁLCULO FINAL (SIEMPRE)
                // ===============================
                calcularDeterminacion();
            }

            /*Función para mostrar mensajes de éxito o error*/
            function showPdfMessage(message, type) {
                const messageDiv = document.getElementById('pdf_upload_message');
                const contentDiv = document.getElementById('pdf_message_content');

                messageDiv.classList.remove('hidden');

                if (type === 'success') {
                    contentDiv.className = 'p-3 rounded-md bg-green-100 text-green-800';
                    contentDiv.innerHTML = '<i class="bi bi-check-circle"></i> ' + message;
                } else {
                    contentDiv.className = 'p-3 rounded-md bg-red-100 text-red-800';
                    contentDiv.innerHTML = '<i class="bi bi-exclamation-circle"></i> ' + message;
                }
            }
        }
    </script>
</x-app-layout>

{{-- Modal para subir PDF y extraer datos --}}
<x-modal-show id="modalpdfdeterminaciones" title="Cargar datos desde PDF">
    <div class="space-y-4">
        <div class="border rounded-md p-4 bg-gray-50">
            <x-input-label value="Seleccionar archivo PDF" />
            <input type="file" id="pdf_file_input" accept="application/pdf"
                class="mt-2 block w-full border-gray-300 rounded-md shadow-sm">
            <p class="text-xs text-gray-500 mt-2">
                Sube el PDF que contiene los datos de la determinación mensual. Los campos se completarán
                automáticamente.
            </p>
        </div>

        <div id="pdf_upload_message" class="hidden">
            <div class="p-3 rounded-md" id="pdf_message_content"></div>
        </div>

        <div class="flex justify-end gap-2">
            <button type="button" onclick="toggleModal('modalpdfdeterminaciones')"
                class="bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white">
                Cancelar
            </button>
            <button type="button" id="btn_upload_pdf" onclick="uploadAndExtractPdf()"
                class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                <span id="upload_text">Cargar y Extraer Datos</span>
                <span id="upload_loading" class="hidden">
                    <i class="bi bi-arrow-repeat animate-spin"></i> Procesando...
                </span>
            </button>
        </div>
    </div>
</x-modal-show>

<script>
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-open-estado');
        if (!btn) return;
        const pdtLink = document.getElementById('ec-pdt-link');
        const pdtConstanciaLink = document.getElementById('ec-pdt-constancia-link');
        if (pdtLink) {
            if (btn.dataset.pdf) {
                pdtLink.href = btn.dataset.pdf;
                pdtLink.classList.remove('hidden');
            } else {
                pdtLink.classList.add('hidden');
            }
        }
        if (pdtConstanciaLink) {
            if (btn.dataset.pdfConstancia) {
                pdtConstanciaLink.href = btn.dataset.pdfConstancia;
                pdtConstanciaLink.classList.remove('hidden');
            } else {
                pdtConstanciaLink.classList.add('hidden');
            }
        }
        toggleModal('modalpdfdeterminacionestadoCuenta');
    });
</script>
