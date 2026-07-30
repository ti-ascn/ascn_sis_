<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de AFP - {{ strtoupper($empresa->razon_social) }}</title>
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
            margin-bottom: 20px;
            page-break-inside: avoid;
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
            page-break-inside: avoid;
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
                $totalPendiente = $deudas->where('estado', 'pendiente')->sum('total');
            @endphp
            <div class="resumen">
                <strong>TOTAL DEUDA PENDIENTE:</strong><br>
                S/ {{ number_format($totalPendiente, 2, '.', ',') }}
            </div>

            <div class="empresa-info">
                <strong>{{ strtoupper($empresa->razon_social) }}</strong><br>
                RUC: {{ $empresa->ruc }}<br>
                <strong>Período hasta:</strong> {{ strtoupper($periodoHasta) }}<br>
                Fecha de reporte: {{ $fechaHoy }}
            </div>
        </div>
    </div>
    {{-- ALERTA: Monto pendiente sin intereses --}}
    <div
        style="
    border: 1.5px solid #c39743;
    background: #fff8e1;
    color: #92400e;
    font-size: 12px;
    padding: 8px 12px;
    border-radius: 6px;
    margin-top: -5px;
    margin-bottom: 15px;
    ">
        <strong>Nota:</strong> Los montos pendientes mostrados no incluyen intereses generados hasta la fecha actual.
    </div>

    {{-- Agrupación por período --}}
    @foreach ($deudas->groupBy(fn($d) => \Carbon\Carbon::parse($d->periodo)->locale('es')->translatedFormat('F Y')) as $periodo => $items)
        <div class="periodo-titulo">
            PERÍODO: {{ strtoupper($periodo) }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>AFP</th>
                    <th>MONTO DEUDA</th>
                    <th>INTERESES</th>
                    <th>TOTAL</th>
                    <th>FECHA VCTO.</th>
                    <th>FECHA PAGO</th>
                    <th>ESTADO</th>
                    <th>OBSERVACIONES</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $d)
                    @php
                        $estadoTexto = strtoupper($d->estado);
                        $estadoClase = match ($d->estado) {
                            'pagada' => 'estado-pagada',
                            'pendiente' => 'estado-pendiente',
                            default => '',
                        };
                    @endphp
                    <tr>
                        <td>{{ $d->afp->nombre ?? '-' }}</td>
                        <td>S/ {{ number_format($d->monto_deuda, 2, '.', ',') }}</td>
                        <td>S/ {{ number_format($d->intereses, 2, '.', ',') }}</td>
                        <td>S/ {{ number_format($d->total, 2, '.', ',') }}</td>
                        <td>{{ $d->fecha_vencimiento ? \Carbon\Carbon::parse($d->fecha_vencimiento)->format('d/m/Y') : '-' }}
                        </td>
                        <td>{{ $d->fecha_pago ? \Carbon\Carbon::parse($d->fecha_pago)->format('d/m/Y') : '-' }}</td>
                        <td class="{{ $estadoClase }}">{{ $estadoTexto }}</td>
                        <td>{{ $d->observaciones ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php
                    $totalDeuda = $items->sum('monto_deuda');
                    $totalInteres = $items->sum('intereses');
                    $totalGeneral = $items->sum('total');
                @endphp
                <tr style="font-weight: bold; background: #fef9c3;">
                    <td style="text-align:right;">TOTAL</td>
                    <td>S/ {{ number_format($totalDeuda, 2, '.', ',') }}</td>
                    <td>S/ {{ number_format($totalInteres, 2, '.', ',') }}</td>
                    <td>S/ {{ number_format($totalGeneral, 2, '.', ',') }}</td>
                    <td colspan="4"></td>
                </tr>
                {{-- <tr>
                    <td colspan="8"
                        style="font-size:10.5px; color:#92400e; background:#fff8e1; text-align:left; padding:6px;">
                        * Este total no considera intereses adicionales acumulados pasado la fecha de vencimiento.
                    </td>
                </tr> --}}
            </tfoot>
        </table>
    @endforeach

    {{-- Footer --}}
    <div class="footer-ultima">
        ASECON PERÚ - Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
