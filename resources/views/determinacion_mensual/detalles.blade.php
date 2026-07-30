<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <span class="text-blue-500">{{ $empresa->razon_social }}</span> - Determinaciones Mensuales <span
                class="text-blue-500">{{ $ano }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">

            <div class="mb-4 flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('determinacion_mensual.index', ['ano' => $ano]) }}"
                    class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white">
                    <i class="bi bi-arrow-left-circle"></i> Atrás
                </a>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <table class="min-w-[1200px] bg-white" id="myTable-mensual-anual">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border-b">Campo</th>
                                @for ($mes = 1; $mes <= 12; $mes++)
                                    <th class="px-4 py-2 border-b">
                                        {{ strtoupper(\Carbon\Carbon::create()->month($mes)->locale('es')->monthName) }}
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        @php
                            $porcentaje10 = $ano >= 2026 ? '10.5%' : '10%';
                        @endphp
                        <tbody>
                            @php
                                $campos = [
                                    'base_compras_18' => 'Base Compras (18%)',
                                    'base_compras_10' => 'Base Compras (10%)',
                                    'base_compras_ng' => 'Base Compras NG',
                                    'igv_compras_18' => 'IGV Compras (18%)',
                                    'igv_compras_10' => 'IGV Compras (10%)',
                                    'comprasnetas_gravadas_nogravadas' => 'Compras Netas Gravadas y No Gravadas',
                                    'comprasnetas_nogravadas_exclusivas' => 'Compras netas destinada a ventas no gravadas exclusivamente',
                                    'comprasinternas_nogravadas' => 'Compras internas no gravadas',
                                    'total_compras' => 'Total Compras',
                                    'base_ventas' => 'Base Ventas 18%',
                                    'base_ventas_10' => 'Base Ventas ' . $porcentaje10,
                                    'igv_ventas' => 'IGV Ventas 18%',
                                    'igv_ventas_10' => 'IGV Ventas ' . $porcentaje10,
                                    'descuentos_conce_devolu' => 'Descuentos Concedidos y/o devoluciones de Ventas',
                                    'descuento_concedido_tributo' => 'Descuento Concedido por Tributos',
                                    'ventas_nogravadas_sinexpor' => 'Ventas no Gravadas (Sin Considerar exportaciones)',
                                    'total_ventas' => 'Total Ventas',
                                    'igv_a_pagar' => 'IGV A Pagar / Crédito',
                                    'saldo_a_favor_periodo_anterior' => 'Saldo a Favor del Periodo Anterior',
                                    'total_igv_a_pagar' => 'Total IGV a Pagar o Saldo a Favor',
                                    'ingresos_netos' => 'Ingresos Netos',
                                    'renta_pagar' => 'Calculo Renta a Pagar',
                                    'saldo_a_favor_renta_periodo_anterior' =>
                                        'Saldo a Favor Renta del Periodo Anterior',
                                    'total_renta_a_pagar' => 'Total Renta a Pagar',
                                    'total_a_pagar_tributos' => 'Total a Pagar Tributos',
                                    'retenciones_del_periodo' => 'Retenciones del Periodo',
                                    'retenciones_periodo_anterior' => 'Retenciones de Periodo Anterior',
                                    'total_retenciones' => 'Total Retenciones',
                                    'percepciones_del_periodo' => 'Percepciones del Periodo',
                                    'percepcion_periodo_anterior' => 'Percepción de Periodo Anterior',
                                    'percepciones_no_aplicadas' => 'Percepciones No Aplicadas',
                                    'total_percepciones' => 'Total Percepciones',
                                ];
                            @endphp

                            @foreach ($campos as $campo => $nombre)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">
                                        {{-- @if ($nombre == 'Saldo a Favor del Periodo Anterior' || $nombre == 'Saldo a Favor Renta del Periodo Anterior')
                                        <span class="text-red-500">{{$nombre}}</span>
                                        @else --}}
                                        {{ $nombre }}
                                        {{-- @endif --}}
                                    </td>
                                    @for ($mes = 1; $mes <= 12; $mes++)
                                        <td class="px-4 py-2 border-b">
                                            {{ isset($datosTabla[$mes]) && $datosTabla[$mes] !== 'Vacío' ? number_format($datosTabla[$mes]->$campo, 2) : 'Vacío' }}
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    $(document).ready(function() {
        $('#myTable-mensual-anual').DataTable({
            responsive: false,
            paging: false,
            ordering: false,
            searching: false,
            info: false,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: '{{ $empresa->razon_social }}_DETERMINACION_MENSUAL_TODO_EL_AÑO_{{ $ano }}'
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
