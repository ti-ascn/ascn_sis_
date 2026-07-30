<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles de la Determinación Mensual') }} de <span
                class="text-blue-500">{{ $empresa->razon_social }}</span>
        </h2>
    </x-slot>
    @php
        $ano = \Carbon\Carbon::parse($determinacion->fecha)->translatedFormat('Y');
        $porcentaje_compras = (float) $determinacion->porcentaje_compras > 0.1 ? '10.5' : '10';
    @endphp
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap gap-3 items-center md:flex-row flex-col">

                {{-- IZQUIERDA: NAVEGACIÓN --}}
                <div class="flex gap-3 flex-wrap">

                    <a href="{{ route('determinacion_mensual.index', ['ano' => $ano]) }}"
                        class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition text-white">
                        <i class="bi bi-arrow-left-circle"></i> Atrás
                    </a>

                    <a href="{{ route('determinacion_mensual.edit', $determinacion->id_determinacion_mensual) }}"
                        class="flex items-center gap-2 bg-yellow-500 px-4 py-2 rounded-md hover:bg-yellow-600 transition text-white">
                        <i class="bi bi-pencil"></i> Editar
                    </a>

                </div>

                {{-- DROPDOWN ARCHIVOS --}}
                <div class="relative">
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition text-white">
                                <i class="bi bi-folder2-open"></i>
                                Archivos
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="p-2">

                                <button type="button" onclick="toggleModal('modalEstadoCuenta')"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-100 rounded-md flex items-center gap-2">
                                    <i class="bi bi-upload text-blue-500"></i>
                                    Agregar Constancia
                                </button>

                                <!-- Ver PDT con submenú elemento -->
                                <div class="relative" x-data="{ abiertoPdt: false }">
                                    <button @mouseenter="abiertoPdt = true" @mouseleave="abiertoPdt = false"
                                        class="w-full text-left px-3 py-2 hover:bg-gray-100 rounded-md flex items-center justify-between gap-2">
                                        <span class="flex items-center gap-2">
                                            <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                            Ver PDT
                                        </span>
                                        <i class="bi bi-chevron-right text-gray-400 text-xs"></i>
                                    </button>

                                    <!-- Submenú PDT subelementos-->
                                    <div x-show="abiertoPdt" @mouseenter="abiertoPdt = true"
                                        @mouseleave="abiertoPdt = false" x-transition
                                        class="absolute left-full top-0 bg-white border border-gray-200 shadow-lg rounded-md min-w-max z-50 p-1">

                                        @if ($determinacion->archivo_pdt)
                                            <a href="{{ asset($determinacion->archivo_pdt) }}" target="_blank"
                                                class="w-full px-3 py-2 hover:bg-gray-100 rounded-md flex items-center gap-2">
                                                <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                                PDT Original
                                            </a>
                                        @endif

                                        @if ($determinacion->archivo_pdt_rectificado)
                                            <a href="{{ asset($determinacion->archivo_pdt_rectificado) }}"
                                                target="_blank"
                                                class="w-full px-3 py-2 hover:bg-gray-100 rounded-md flex items-center gap-2">
                                                <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                                PDT Rectificado 1
                                            </a>
                                        @endif

                                        @for ($i = 2; $i <= 4; $i++)
                                            @php $fieldName = "pdt_rectificatorio_{$i}"; @endphp
                                            @if ($determinacion->$fieldName)
                                                <a href="{{ asset($determinacion->$fieldName) }}" target="_blank"
                                                    class="w-full px-3 py-2 hover:bg-gray-100 rounded-md flex items-center gap-2">
                                                    <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                                    PDT Rectificado {{ $i }}
                                                </a>
                                            @endif
                                        @endfor
                                    </div>
                                </div>

                                <!-- Ver Constancia con submenú -->
                                <div class="relative" x-data="{ abiertoConstancia: false }">
                                    <button @mouseenter="abiertoConstancia = true"
                                        @mouseleave="abiertoConstancia = false"
                                        class="w-full text-left px-3 py-2 hover:bg-gray-100 rounded-md flex items-center justify-between gap-2">
                                        <span class="flex items-center gap-2">
                                            <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                            Ver Constancia
                                        </span>
                                        <i class="bi bi-chevron-right text-gray-400 text-xs"></i>
                                    </button>

                                    <!-- Submenú Constancia -->
                                    <div x-show="abiertoConstancia" @mouseenter="abiertoConstancia = true"
                                        @mouseleave="abiertoConstancia = false" x-transition
                                        class="absolute left-full top-0 bg-white border border-gray-200 shadow-lg rounded-md min-w-max z-50 p-1">

                                        @if ($determinacion->archivo_pdt_constancia)
                                            <a href="{{ asset($determinacion->archivo_pdt_constancia) }}"
                                                target="_blank"
                                                class="w-full px-3 py-2 hover:bg-gray-100 rounded-md flex items-center gap-2">
                                                <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                                Constancia Original
                                            </a>
                                        @endif

                                        @if ($determinacion->archivo_pdt_constancia_rectificado)
                                            <a href="{{ asset($determinacion->archivo_pdt_constancia_rectificado) }}"
                                                target="_blank"
                                                class="w-full px-3 py-2 hover:bg-gray-100 rounded-md flex items-center gap-2">
                                                <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                                Constancia Rectificado 1
                                            </a>
                                        @endif

                                        @for ($i = 2; $i <= 4; $i++)
                                            @php $fieldName = "pdt_constancia_rectificatorio_{$i}"; @endphp
                                            @if ($determinacion->$fieldName)
                                                <a href="{{ asset($determinacion->$fieldName) }}" target="_blank"
                                                    class="w-full px-3 py-2 hover:bg-gray-100 rounded-md flex items-center gap-2">
                                                    <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                                    Constancia Rectificado {{ $i }}
                                                </a>
                                            @endif
                                        @endfor
                                    </div>
                                </div>

                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                {{-- DESCARGAS --}}
                <div class="relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center gap-2 bg-red-600 px-4 py-2 rounded-md hover:bg-red-700 transition text-white">
                                <i class="bi bi-download"></i>
                                PDF Cliente
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="p-2">
                                <a href="{{ route('determinacionmes.pdf', $determinacion->id_determinacion_mensual) }}"
                                    target="_blank"
                                    class="w-full px-3 py-2 hover:bg-gray-100 rounded-md flex items-center gap-2">
                                    <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                    Exportar PDT
                                </a>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                {{-- DEUDAS CONTABILIDAD --}}
                <a href="{{ route('deudas.detalle', [
                    'tipo' => 'contabilidad',
                    'empresa' => $empresa->id_empresas_clientes,
                    'anio' => \Carbon\Carbon::parse($determinacion->fecha)->year,
                    'mes' => \Carbon\Carbon::parse($determinacion->fecha)->month
                ]) }}"
                    class="flex items-center gap-2 bg-green-800 px-4 py-2 rounded-md hover:bg-green-900 transition text-white">
                    <i class="bi bi-cash-coin"></i> Deudas Cont
                </a>

                {{-- ELIMINAR (SEPARADO Y PELIGROSO) --}}
                <form
                    action="{{ route('determinacion_mensual.destroyById', $determinacion->id_determinacion_mensual) }}"
                    method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta determinación?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="flex items-center gap-2 bg-red-500 px-4 py-2 rounded-md hover:bg-red-600 transition text-white">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </form>

            </div>

            @php
                $fechaCarbon = \Carbon\Carbon::parse($determinacion->fecha);
                $mes = strtoupper($fechaCarbon->locale('es')->translatedFormat('F Y'));
                $anio = $fechaCarbon->year;
            @endphp
            <div class="bg-white shadow-lg rounded-lg p-6">
                <x-success-error-bagdes />
                <!-- Tabla de detalles de la determinación mensual -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200" id="myTable-mensual">
                        <thead>
                            <tr class="bg-gray-100">
                                <td class="px-4 py-2 border-b">DETERMINACIÓN MENSUAL DE IMPUESTO {{ $mes }}
                                </td>
                                <td class="px-4 py-2 border-b text-right"></td>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Compras -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $empresa->razon_social }}</td>
                                <td class="px-4 py-2 border-b text-right"></td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">RUC: {{ $empresa->ruc }}</td>
                                <td class="px-4 py-2 border-b text-right"></td>
                            </tr>
                            <!-- Ventas -->
                            @if ($determinacion->base_ventas > 0)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">Base Ventas 18%</td>
                                    <td class="px-4 py-2 border-b text-right">
                                        {{ number_format($determinacion->base_ventas, 2) }}
                                    </td>
                                </tr>
                            @endif
                            @if ($determinacion->base_ventas_10 > 0)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">
                                        Base Ventas {{ $anio >= 2026 ? '10.5%' : '10%' }}
                                    </td>
                                    <td class="px-4 py-2 border-b text-right">
                                        {{ number_format($determinacion->base_ventas_10, 2) }}
                                    </td>
                                </tr>
                            @endif
                            @if ($determinacion->base_ventas > 0)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">IGV Ventas 18%</td>
                                    <td class="px-4 py-2 border-b text-right">
                                        {{ number_format($determinacion->igv_ventas, 2) }}
                                    </td>
                                </tr>
                            @endif
                            @if ($determinacion->base_ventas_10 > 0)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">
                                        IGV Ventas {{ $anio >= 2026 ? '10.5%' : '10%' }}
                                    </td>
                                    <td class="px-4 py-2 border-b text-right">
                                        {{ number_format($determinacion->igv_ventas_10, 2) }}
                                    </td>
                                </tr>
                            @endif
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Descuentos Concedidos y/o Devoluciones de Ventas</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->descuentos_conce_devolu, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Descuento Concedido Tributo</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->descuento_concedido_tributo, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Ventas no Gravadas (Sin Considerar exportaciones)</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->ventas_nogravadas_sinexpor, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">TOTAL VENTAS</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->total_ventas, 2) }}</td>
                            </tr>
                            {{-- compras --}}
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Base Compras 18%</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->base_compras_18, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Base Compras {{ $porcentaje_compras }}%</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->base_compras_10, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Base Compras NG</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->base_compras_ng, 2) }}</td>
                            </tr class="hover:bg-gray-50">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">IGV Compras 18%</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->igv_compras_18, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">IGV Compras {{ $porcentaje_compras }}%</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->igv_compras_10, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Compras Netas Destinadas a Ventas Gravadas y no Gravadas
                                </td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->comprasnetas_gravadas_nogravadas, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Compras Netas Destinada a Ventas no Gravadas
                                    Exclusivamente</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->comprasnetas_nogravadas_exclusivas, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Compras Internas no Gravadas</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->comprasinternas_nogravadas, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">TOTAL COMPRAS</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->total_compras, 2) }}</td>
                            </tr>

                            <!-- IGV -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">
                                    @if ($determinacion->igv_a_pagar < 0)
                                        Crédito a Favor del Mes
                                    @else
                                        IGV a Pagar
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->igv_a_pagar, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Saldo a Favor del Periodo Anterior</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->saldo_a_favor_periodo_anterior, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">
                                    @if ($determinacion->total_igv_a_pagar < 0)
                                        SALDO A FAVOR DEL PERIODO ACTUAL
                                    @else
                                        TOTAL IGV A PAGAR
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->total_igv_a_pagar, 2) }}</td>
                            </tr>

                            <!-- Renta -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Ingresos Netos</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->ingresos_netos, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Calculo Renta a Pagar</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->renta_pagar, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Saldo a Favor Renta del Periodo Anterior</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->saldo_a_favor_renta_periodo_anterior, 2) }}</td>
                            </tr>

                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">TOTAL RENTA A PAGAR</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->total_renta_a_pagar, 2) }}</td>
                            </tr>

                            <!-- Tributos -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">TOTAL A PAGAR TRIBUTOS</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->total_a_pagar_tributos, 2) }}</td>
                            </tr>

                            <!-- Retenciones y Percepciones -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Retenciones del Periodo</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->retenciones_del_periodo, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Retenciones del Periodo Anterior</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->retenciones_periodo_anterior, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">TOTAL RETENCIONES</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->total_retenciones, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Percepciones del Periodo</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->percepciones_del_periodo, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Percepción del Periodo Anterior</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->percepcion_periodo_anterior, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">Percepciones No Aplicadas</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->percepciones_no_aplicadas, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">TOTAL PERCEPCIONES</td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{ number_format($determinacion->total_percepciones, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- Modal: Registrar Estado de Cuenta --}}
<div>
    <x-modal-show id="modalEstadoCuenta" title="Registrar Estado de Cuenta">
        <form method="POST" action="{{ route('determinacion.archivos', $determinacion->id_determinacion_mensual) }}"
            enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- PDF CONSTANCIA --}}
            <div class="border rounded-md p-4 bg-gray-50 space-y-3">
                <div class="flex items-center justify-between">
                    <x-input-label value="ARCHIVO CONSTANCIA PDT" />
                    @if ($determinacion->archivo_pdt_constancia)
                        <a href="{{ asset($determinacion->archivo_pdt_constancia) }}" target="_blank"
                            class="flex items-center gap-2 bg-green-500 px-3 py-1 rounded-md hover:bg-green-600 transition text-white text-sm">
                            <i class="bi bi-file-earmark-pdf"></i> Ver Constancia
                        </a>
                    @endif
                </div>
                <input type="file" name="archivo_pdt_constancia" accept="application/pdf"
                    class="block w-full border-gray-300 rounded-md shadow-sm">
            </div>

            {{-- PDF CONSTANCIA RECTIFICADO --}}
            <div class="border rounded-md p-4 bg-gray-50 space-y-3">
                <div class="flex items-center justify-between">
                    <x-input-label value="ARCHIVO CONSTANCIA PDT RECTIFICADO" />
                    @if ($determinacion->archivo_pdt_constancia_rectificado)
                        <a href="{{ asset($determinacion->archivo_pdt_constancia_rectificado) }}" target="_blank"
                            class="flex items-center gap-2 bg-green-500 px-3 py-1 rounded-md hover:bg-green-600 transition text-white text-sm">
                            <i class="bi bi-file-earmark-pdf"></i> Ver Constancia Rect.
                        </a>
                    @endif
                </div>
                <input type="file" name="archivo_pdt_constancia_rectificado" accept="application/pdf"
                    class="block w-full border-gray-300 rounded-md shadow-sm">
            </div>

            {{-- CONSTANCIA RECTIFICATORIOS 2, 3, 4 --}}
            @for ($i = 2; $i <= 4; $i++)
                @php $fieldName = "pdt_constancia_rectificatorio_{$i}"; @endphp
                <div class="border rounded-md p-4 bg-gray-50 space-y-3">
                    <div class="flex items-center justify-between">
                        <x-input-label value="ARCHIVO CONSTANCIA PDT RECTIFICADO {{ $i }}" />
                        @if ($determinacion->$fieldName)
                            <a href="{{ asset($determinacion->$fieldName) }}" target="_blank"
                                class="flex items-center gap-2 bg-green-500 px-3 py-1 rounded-md hover:bg-green-600 transition text-white text-sm">
                                <i class="bi bi-file-earmark-pdf"></i> Ver Constancia Rect. {{ $i }}
                            </a>
                        @endif
                    </div>
                    <input type="file" name="{{ $fieldName }}" accept="application/pdf"
                        class="block w-full border-gray-300 rounded-md shadow-sm">
                </div>
            @endfor

            {{-- Acciones --}}
            <div class="flex justify-end pt-4 gap-2">
                <button type="button" onclick="toggleModal('modalEstadoCuenta')"
                    class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white">
                    Cancelar
                </button>
                <x-primary-button class="px-6 py-2">
                    Guardar archivos
                </x-primary-button>
            </div>
        </form>
    </x-modal-show>
</div>

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
        toggleModal('modalEstadoCuenta');
    });
</script>

@if ($determinacion->base_ventas_10 > 0 && $determinacion->base_ventas > 0)
    <script>
        $(document).ready(function() {
            // Obtener el mes desde Blade
            const mes =
                '{{ strtoupper(\Carbon\Carbon::parse($determinacion->fecha)->locale('es')->translatedFormat('F Y')) }}';

            // Reemplazar espacios por guiones bajos
            const mesFormateado = mes.replace(/ /g, '_'); // "Enero_2024"

            $('#myTable-mensual').DataTable({
                responsive: false,
                paging: false,
                ordering: false,
                searching: false,
                info: false,
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excel',
                    text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                    title: '{{ $empresa->razon_social }}_DETERMINACION_MENSUAL_' + mesFormateado,
                    customize: function(xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        $('row:first', sheet).remove();
                        //MEZCLAR CELDAS
                        $('mergeCells', sheet).append('<mergeCell ref="A2:B2"/>');
                        $('mergeCells', sheet).append('<mergeCell ref="A3:B3"/>');
                        $('mergeCells', sheet).append('<mergeCell ref="A4:B4"/>');

                        //NEGRITA
                        $('row c[r="A3"], row c[r="A4"]', sheet).attr('s',
                            '2');
                        $('row c[r="b3"], row c[r="B4"]', sheet).attr('s',
                            '2');
                        //MORADO CON NEGRITA
                        $('row c[r="A10"], row c[r="A15"]', sheet).attr('s',
                            '22');
                        $('row c[r="B10"], row c[r="B15"]', sheet).attr('s',
                            '22');
                        //GRIS CON NEGRITA
                        $('row c[r="A22"], row c[r="B22"]', sheet).attr('s', '7');
                        //VERDE CON NEGRITA
                        $('row c[r="A25"], row c[r="A29"]', sheet).attr('s',
                            '17');
                        $('row c[r="B25"], row c[r="B29"]', sheet).attr('s',
                            '17');
                        //ROJO CON NEGRITA
                        $('row c[r="A21"], row c[r="B21"]', sheet).attr('s',
                            '12');
                        //SI ES TOTAL IGV QUE SEA ROJO Y SI ES A FAVOR QUE SEA VERDE
                        // Obtener el valor de total_igv_a_pagar desde el HTML
                        var totalIgvAPagar = parseFloat(
                            "{{ $determinacion->total_igv_a_pagar }}");

                        // Determinar el estilo según el valor de total_igv_a_pagar
                        var estiloTotalIgv = (totalIgvAPagar < 0) ? '17' :
                            '12'; // 17: verde, 12: rojo

                        // Aplicar el estilo a las celdas correspondientes (fila 20 en este ejemplo)
                        $('row c[r="A18"], row c[r="B18"]', sheet).attr('s', estiloTotalIgv);

                    }
                }],
                language: {
                    url: "/js/datatables/lang/es-ES.json" // Textos en español
                },
                order: [
                    [0, 'desc']
                ]
            });
        });
    </script>
@else
    <script>
        $(document).ready(function() {
            // Obtener el mes desde Blade
            const mes =
                '{{ strtoupper(\Carbon\Carbon::parse($determinacion->fecha)->locale('es')->translatedFormat('F Y')) }}';

            // Reemplazar espacios por guiones bajos
            const mesFormateado = mes.replace(/ /g, '_'); // "Enero_2024"

            $('#myTable-mensual').DataTable({
                responsive: false,
                paging: false,
                ordering: false,
                searching: false,
                info: false,
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excel',
                    text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                    title: '{{ $empresa->razon_social }}_DETERMINACION_MENSUAL_' + mesFormateado,
                    customize: function(xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        $('row:first', sheet).remove();
                        //MEZCLAR CELDAS
                        $('mergeCells', sheet).append('<mergeCell ref="A2:B2"/>');
                        $('mergeCells', sheet).append('<mergeCell ref="A3:B3"/>');
                        $('mergeCells', sheet).append('<mergeCell ref="A4:B4"/>');

                        //NEGRITA
                        $('row c[r="A3"], row c[r="A4"]', sheet).attr('s',
                            '2');
                        $('row c[r="b3"], row c[r="B4"]', sheet).attr('s',
                            '2');
                        //MORADO CON NEGRITA
                        $('row c[r="A10"], row c[r="A13"]', sheet).attr('s',
                            '22');
                        $('row c[r="B10"], row c[r="B13"]', sheet).attr('s',
                            '22');
                        //GRIS CON NEGRITA
                        $('row c[r="A20"], row c[r="B20"]', sheet).attr('s', '7');
                        //VERDE CON NEGRITA
                        $('row c[r="A23"], row c[r="A27"]', sheet).attr('s',
                            '17');
                        $('row c[r="B23"], row c[r="B27"]', sheet).attr('s',
                            '17');
                        //ROJO CON NEGRITA
                        $('row c[r="A19"], row c[r="B19"]', sheet).attr('s',
                            '12');
                        //SI ES TOTAL IGV QUE SEA ROJO Y SI ES A FAVOR QUE SEA VERDE
                        // Obtener el valor de total_igv_a_pagar desde el HTML
                        var totalIgvAPagar = parseFloat(
                            "{{ $determinacion->total_igv_a_pagar }}");

                        // Determinar el estilo según el valor de total_igv_a_pagar
                        var estiloTotalIgv = (totalIgvAPagar < 0) ? '17' :
                            '12'; // 17: verde, 12: rojo

                        // Aplicar el estilo a las celdas correspondientes (fila 20 en este ejemplo)
                        $('row c[r="A16"], row c[r="B16"]', sheet).attr('s', estiloTotalIgv);

                    }
                }],
                language: {
                    url: "/js/datatables/lang/es-ES.json" // Textos en español
                },
                order: [
                    [0, 'desc']
                ]
            });
        });
    </script>
@endif
