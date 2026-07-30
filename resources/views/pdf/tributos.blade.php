<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Tributos de {{ strtoupper($empresa->razon_social) }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11.5px;
            color: #111827;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* HEADER */
        .header {
            border-bottom: 3px solid #c39743;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .logo-block {
            text-align: right;
        }

        .logo-block img {
            height: 135px;
            margin-bottom: -50px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 10px;
        }

        .empresa-info {
            margin-top: -130px;
            font-size: 12.5px;
            line-height: 1.5;
            text-align: right;
        }

        .empresa-info strong {
            font-size: 15px;
            color: #111827;
        }

        .resumen {
            border: 2px solid #c39743;
            border-radius: 6px;
            padding: 8px 12px;
            background: #fffaf0;
            font-size: 12.5px;
            min-width: 200px;
            max-width: 240px;
        }

        .resumen strong {
            color: #c39743;
        }

        /* TABLAS */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            /* un poco menos para equilibrar el espacio */
            margin-bottom: 20px;
            page-break-inside: avoid;
            /* evita que se corte una tabla en dos páginas */
        }

        th {
            background: #c39743;
            color: #fff;
            padding: 7px;
            font-size: 11.5px;
            text-transform: uppercase;
        }

        td {
            border: 1px solid #d1d5db;
            padding: 6px;
            font-size: 11px;
            text-align: center;
        }


        .estado-pagada {
            background: #dcfce7;
            color: #166534;
            font-weight: bold;
        }

        .estado-pendiente {
            background: #fee2e2;
            color: #991b1b;
            font-weight: bold;
        }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: right;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #c39743;
            padding: 4px 10px;
        }

        @page {
            margin-bottom: 60px;
            /* espacio para el footer */
        }

        .footer-ultima {
            position: absolute;
            bottom: -20px;
            right: 10px;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #c39743;
            padding-top: 4px;
            text-align: right;
            width: 100%;
            /* truco para que se dibuje solo una vez, en la última hoja */
            display: none;
        }

        body:last-child .footer-ultima {
            display: block;
        }


        .periodo-titulo {
            font-size: 13px;
            font-weight: bold;
            color: #c39743;
            margin-top: 25px;
            margin-bottom: 8px;
            /* separa la tabla siguiente */
            page-break-inside: avoid;
            /* evita que el título se parta en un salto de página */
        }

        .pagos {
            text-align: left;
            line-height: 1.4em;
        }

        .pago-item {
            margin-bottom: 4px;
            padding-bottom: 3px;
            border-bottom: 0.5px dashed #c39743;
        }

        .pago-item:last-child {
            border-bottom: none;
        }

        .small {
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <div class="header">
        <div class="logo-block">
            <img src="{{ public_path('img/asecon-logo.png') }}" alt="Logo">
        </div>

        <div class="info-row">
            @php
                $totalDeuda = $deudas->sum('monto_pendiente');
            @endphp
            <div class="resumen">
                <strong>TOTAL DEUDA PENDIENTE:</strong><br>
                S/ {{ number_format($totalDeuda, 2, '.', ',') }}
            </div>

            <div class="empresa-info">
                <strong>{{ $empresa->razon_social }}</strong><br>
                RUC: {{ $empresa->ruc }}<br>
                <strong>Período hasta:</strong> {{ $periodoHasta }}<br>
                Fecha de reporte: {{ $fechaHoy }}
            </div>
        </div>
    </div>

    {{-- Agrupación por período --}}

    @foreach ($deudas->groupBy('periodo') as $periodo => $items)
        @php
            $itemsValidos = $items->where('monto_deuda', '>', 0);
            $fechaVencimientoPeriodo = optional($items->first())->fecha_vencimiento
                ? \Carbon\Carbon::parse($items->first()->fecha_vencimiento)->format('d/m/Y')
                : '-';
        @endphp

        <div class="periodo-titulo">
            PERÍODO: {{ strtoupper($periodo) }}
            <span style="font-weight: normal; color:#444; font-size:12px;">
                (Vencimiento: {{ $fechaVencimientoPeriodo }})
            </span>
        </div>

        @if ($itemsValidos->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>TRIBUTO</th>
                        <th>MONTO ORIGINAL</th>
                        <th>INTERESES</th>
                        <th>DEUDA TOTAL</th>
                        <th>PAGOS REALIZADOS</th>
                        <th>SALDO</th>
                        <th>ESTADO</th>
                        <th>OBSERVACIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemsValidos as $d)
                        @php
                            $estadoTexto = match ($d->estado) {
                                1 => 'PAGADA',
                                2 => 'SIN MOVIMIENTO',
                                default => 'PENDIENTE',
                            };

                            $estadoClase = match ($d->estado) {
                                1 => 'estado-pagada',
                                2 => '',
                                default => 'estado-pendiente',
                            };
                        @endphp
                        <tr>
                            <td>{{ $d->tributo->nombre }} ({{ $d->tributo->codigo }})</td>
                            <td>S/ {{ number_format($d->monto_deuda, 2, '.', ',') }}</td>
                            <td>S/ {{ number_format($d->interes_total, 2, '.', ',') }}</td>
                            <td>S/ {{ number_format($d->monto_deuda + $d->interes_total, 2, '.', ',') }}</td>
                            <td class="pagos">
                                @if ($d->pagos->count() > 0)
                                    @foreach ($d->pagos as $p)
                                        <div class="pago-item">
                                            <strong>{{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}</strong>
                                            —
                                            S/ {{ number_format($p->monto_total, 2, '.', ',') }}
                                            <span class="small">(Int: S/.
                                                {{ number_format($p->interes, 2, '.', ',') }})</span>
                                        </div>
                                    @endforeach
                                @else
                                    <em>Sin pagos</em>
                                @endif
                            </td>
                            <td>S/ {{ number_format($d->monto_pendiente, 2, '.', ',') }}</td>
                            <td class="{{ $estadoClase }}">{{ $estadoTexto }}</td>
                            <td>{{ $d->observaciones ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @php
                        $totalOriginal = $itemsValidos->sum('monto_deuda');
                        $totalPendiente = $itemsValidos->sum('monto_pendiente');
                        $totalPagado = $itemsValidos->sum('monto_pagado');
                        $totalIntereses = $itemsValidos->sum('interes_total');
                    @endphp
                    <tr style="font-weight: bold; background: #fef9c3;">
                        <td style="text-align:right;">TOTAL</td>
                        <td>S/ {{ number_format($totalOriginal, 2, '.', ',') }}</td>
                        <td>S/ {{ number_format($totalIntereses, 2, '.', ',') }}</td>
                        <td>S/ {{ number_format($totalOriginal + $totalIntereses, 2, '.', ',') }}</td>
                        <td>S/ {{ number_format($totalPagado, 2, '.', ',') }}</td>
                        <td>S/ {{ number_format($totalPendiente, 2, '.', ',') }}</td>
                        <td colspan="2">--</td>
                    </tr>
                </tfoot>
            </table>
        @else
            <table>
                <tr>
                    <td colspan="8" style="text-align:center; color:#6b7280; background:#f9fafb; font-style:italic;">
                        Sin movimiento en este período.
                    </td>
                </tr>
            </table>
        @endif
    @endforeach


    {{-- Footer solo en última página --}}
    <div class="footer-ultima">
        ASECON PERÚ - Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
