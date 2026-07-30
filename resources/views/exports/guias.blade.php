<table>
    <thead>
        <tr>
            <th colspan="2">{{ $entidad->razon_social ?? 'No especificado' }}</th>
        </tr>
        <tr>
            <th colspan="2">RUC: {{ $entidad->ruc ?? 'No especificado' }}</th>
        </tr>
        <tr>
            <th colspan="2">CONTACTO: {{ $entidad->contacto ?? 'No especificado' }}</th>
        </tr>
        <tr>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th style="width: 20px;">MES</th>
            <th style="width: 45px;">MONTO</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalSoles = 0;
            $totalDolares = 0;
            $totalDolaresEnSoles = 0;

            $meses_es = [
                '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
                '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
                '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
                '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
            ];

            $currentDate = $desde->copy();
            
            // agrupar guias por Y-m para busqueda rapida
            $guiasPorMes = [];
            foreach ($guias as $guia) {
                $m = \Carbon\Carbon::parse($guia->recepcion)->format('Y-m');
                $guiasPorMes[$m] = $guia;
            }
        @endphp

        @while ($currentDate->lte($hasta))
            @php
                $mesKey = $currentDate->format('Y-m');
                $mesNum = $currentDate->format('m');
                $anioNum = $currentDate->format('Y');
                $nombreMes = ($meses_es[$mesNum] ?? '') . ' ' . $anioNum;

                $guia = $guiasPorMes[$mesKey] ?? null;
            @endphp
            <tr>
                <td>{{ $nombreMes }}</td>
                <td>
                    @if($guia)
                        @php
                            if ($guia->moneda === 'USD') {
                                $totalDolares += $guia->monto;
                                $montoSoles = isset($montosSunatPorMes[$mesKey])
                                    ? $montosSunatPorMes[$mesKey]
                                    : $guia->monto * ($guia->tipo_cambio ?? 3.8);
                                $totalDolaresEnSoles += $montoSoles;
                            } else {
                                $totalSoles += $guia->monto;
                            }
                        @endphp
                        
                        @if($guia->moneda === 'USD')
                            $ {{ number_format($guia->monto, 2) }} (S/ {{ number_format($montoSoles, 2) }})
                        @else
                            S/ {{ number_format($guia->monto, 2) }}
                        @endif
                    @else
                        SIN GUÍA
                    @endif
                </td>
            </tr>
            @php
                $currentDate->addMonth();
            @endphp
        @endwhile
    </tbody>
    <tfoot>
        <tr>
            <th></th>
            <th></th>
        </tr>
        @if($totalDolares > 0)
            <tr>
                <th style="text-align: right; font-weight: bold;">TOTAL S/</th>
                <th style="font-weight: bold;">S/ {{ number_format($totalSoles, 2) }}</th>
            </tr>
            <tr>
                <th style="text-align: right; font-weight: bold;">TOTAL $</th>
                <th style="font-weight: bold;">$ {{ number_format($totalDolares, 2) }}</th>
            </tr>
            <tr>
                <th style="text-align: right; font-weight: bold;">TOTAL S/ EN AMBOS</th>
                <th style="font-weight: bold;">S/ {{ number_format($totalSoles + $totalDolaresEnSoles, 2) }}</th>
            </tr>
        @else
            <tr>
                <th style="text-align: right; font-weight: bold;">TOTAL S/</th>
                <th style="font-weight: bold;">S/ {{ number_format($totalSoles, 2) }}</th>
            </tr>
        @endif
    </tfoot>
</table>
