<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Deudas de {{ strtoupper($empresa->razon_social) }}</title>
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
            margin-bottom: 20px;
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
            margin-top: 15px;
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
            vertical-align: top;
        }

        td.left {
            text-align: left;
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

        .pagos {
            text-align: left;
            line-height: 1.5;
        }

        .pago-item {
            margin-bottom: 4px;
            padding-bottom: 3px;
            border-bottom: 0.5px dashed #c39743;
            /* línea dorada sutil */
        }

        .pago-item:last-child {
            border-bottom: none;
            /* no dibujar línea en el último */
        }


        .pagos div {
            margin-bottom: 2px;
        }

        .small {
            font-size: 10px;
            color: #666;
        }

        .observaciones {
            text-align: left;
            font-size: 10.8px;
            color: #444;
            line-height: 1.3;
        }

        .total-row {
            background: #fef9c3;
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
        }

        .periodo-titulo {
            font-size: 13px;
            font-weight: bold;
            color: #c39743;
            margin-top: 25px;
            margin-bottom: 8px;
            page-break-inside: avoid;
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
                $saldoTotal = $deudas->sum(fn($x) => $x->saldo_total);
            @endphp
            <div class="resumen">
                <strong>TOTAL DEUDA PENDIENTE:</strong><br>
                S/ {{ number_format($saldoTotal, 2, '.', ',') }}
            </div>

            <div class="empresa-info">
                <strong>{{ strtoupper($empresa->razon_social) }}</strong><br>
                RUC: {{ $empresa->ruc }}<br>
                <strong>Período:</strong> {{ strtoupper($periodo) }}<br>
                <strong>Vencimiento (PDT):</strong> {{ $vencimientoMes }}<br>
                Fecha de reporte: {{ $fechaHoy }}
            </div>
        </div>
    </div>

    {{-- Tabla principal --}}
    <table>
        <thead>
            <tr>
                <th>TRIBUTO</th>
                <th>MONTO ORIGINAL</th>
                <th>INTERÉS</th>
                <th>DEUDA TOTAL</th>
                <th>FECHA VENC.</th>
                <th>PAGOS REALIZADOS</th>
                <th>SALDO</th>
                <th>ESTADO</th>
                <th>OBSERVACIONES</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($deudas as $d)
                @php
                    $estadoClase = $d->estado == 1 ? 'estado-pagada' : 'estado-pendiente';
                    $estadoTexto = $d->estado == 1 ? 'PAGADA' : 'PENDIENTE';
                @endphp
                <tr>
                    <td class="left">{{ $d->tributo->nombre ?? '—' }} ({{ $d->tributo->codigo ?? '—' }})</td>
                    <td>S/ {{ number_format($d->monto_deuda, 2, '.', ',') }}</td>
                    <td>S/ {{ number_format($d->interes_total, 2, '.', ',') }}</td>
                    <td>S/ {{ number_format($d->monto_deuda + $d->interes_total, 2, '.', ',') }}</td>
                    <td>{{ \Carbon\Carbon::parse($d->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td class="pagos">
                        @if ($d->pagos->count() > 0)
                            @foreach ($d->pagos as $p)
                                <div class="pago-item">
                                    <strong>{{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}</strong> —
                                    S/ {{ number_format($p->monto_total, 2, '.', ',') }}
                                    <span class="small">(Int: S/.
                                        {{ number_format($p->interes, 2, '.', ',') }})</span>
                                </div>
                            @endforeach
                        @else
                            <em>Sin pagos</em>
                        @endif
                    </td>
                    <td>S/ {{ number_format($d->saldo_total, 2, '.', ',') }}</td>
                    <td class="{{ $estadoClase }}">{{ $estadoTexto }}</td>
                    <td class="observaciones">{{ $d->observaciones ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; color:#6b7280; background:#f9fafb; font-style:italic;">
                        Sin movimiento en este período.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            @php
                $totalDeuda = $deudas->sum(fn($x) => $x->monto_deuda);
                $totalInteres = $deudas->sum(fn($x) => $x->interes_total);
                $totalGeneral = $totalDeuda + $totalInteres;
                $totalPagado = $deudas->sum(fn($x) => $x->pagos->sum('monto_total'));
                $saldoFinal = $deudas->sum(fn($x) => $x->saldo_total);
            @endphp

            <tr class="total-row">
                <td style="text-align:right;">TOTAL</td>
                <td>S/ {{ number_format($totalDeuda, 2, '.', ',') }}</td>
                <td>S/ {{ number_format($totalInteres, 2, '.', ',') }}</td>
                <td>S/ {{ number_format($totalGeneral, 2, '.', ',') }}</td>
                <td></td>
                <td>S/ {{ number_format($totalPagado, 2, '.', ',') }}</td>
                <td>S/ {{ number_format($saldoFinal, 2, '.', ',') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        ASECON PERÚ · Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
