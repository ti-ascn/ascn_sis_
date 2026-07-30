<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Fraccionamiento - {{ $empresa->razon_social }}</title>
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

        /* DESCRIPCIÓN */
        .descripcion {
            margin-top: 5px;
            background: #f9f7f4;
            border-left: 3px solid #c39743;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            color: #555;
        }

        /* TABLA */
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

        tr.pagado {
            background-color: #dcfce7;
        }

        tr.pendiente {
            background-color: #fff;
        }

        .titulo {
            font-size: 13px;
            font-weight: bold;
            color: #c39743;
            margin-top: 10px;
            margin-bottom: 5px;
            border-bottom: 1px solid #c39743;
            display: inline-block;
        }

        .total-final {
            text-align: right;
            font-weight: bold;
            color: #003366;
            margin-top: 10px;
            font-size: 13px;
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

        @page {
            margin-bottom: 60px;
        }
    </style>
</head>

<body>
    {{-- HEADER --}}
    <div class="header">
        <div class="logo-block">
            <img src="{{ public_path('img/asecon-logo.png') }}" alt="Logo ASECON">
        </div>

        <div class="info-row">
            <div class="resumen">
                <strong>TOTAL FRACCIONADO:</strong><br>
                S/ {{ number_format($fraccionamiento->total, 2) }}
            </div>

            <div class="empresa-info">
                <strong>{{ strtoupper($empresa->razon_social) }}</strong><br>
                RUC: {{ $empresa->ruc ?? '—' }}<br>
                Fecha del reporte: {{ $fechaHoy }}
            </div>
        </div>
    </div>

    {{-- DETALLE PRINCIPAL --}}
    <div>
        <span class="titulo">Detalle del
            {{ $fraccionamiento->refinanciamiento ? 'Refinanciamiento del Fraccionamiento' : 'Fraccionamiento' }}</span>
        <p style="margin: 0; font-size: 12px;">
            <strong>Resolución:</strong> {{ $fraccionamiento->nro_resolucion }} |
            <strong>Tributo:</strong> {{ $fraccionamiento->tributo_asociado }} ({{ $fraccionamiento->codigo }}) |
            <strong>Periodo:</strong> {{ $fraccionamiento->periodo }}
        </p>

        @if ($fraccionamiento->descripcion)
            <div class="descripcion">
                <strong>Descripción:</strong> {{ $fraccionamiento->descripcion }}
            </div>
        @endif
    </div>

    {{-- TABLA --}}
    <table>
        <thead>
            <tr>
                <th>N° / Cuota</th>
                <th>Fecha Vencimiento</th>
                <th>Amortización (S/)</th>
                <th>Interés (S/)</th>
                <th>Total (S/)</th>
                <th>Fecha de Pago</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($fraccionamiento->detalles as $detalle)
                <tr class="{{ $detalle->estado }}">
                    <td>{{ $detalle->nombre_cuota }}</td>
                    <td>{{ \Carbon\Carbon::parse($detalle->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td>{{ number_format($detalle->amortizacion, 2) }}</td>
                    <td>{{ number_format($detalle->interes, 2) }}</td>
                    <td>{{ number_format($detalle->total, 2) }}</td>
                    <td>
                        {{ $detalle->fecha_pago ? \Carbon\Carbon::parse($detalle->fecha_pago)->format('d/m/Y') : '-' }}
                    </td>
                    <td style="font-weight: bold; color: {{ $detalle->estado === 'pagado' ? '#166534' : '#a16207' }}">
                        {{ ucfirst($detalle->estado) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total-final">Total Fraccionado: S/ {{ number_format($fraccionamiento->total, 2) }}</p>

    {{-- FOOTER --}}
    <div class="footer-ultima">
        ASECON PERÚ — Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
