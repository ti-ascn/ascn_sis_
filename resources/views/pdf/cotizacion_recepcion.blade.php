<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Cotizaciones de Recepción - {{ strtoupper($empresa->razon_social) }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            line-height: 1.4;
            margin: 0;
            padding: 0 0 80px 0;
            position: relative;
            min-height: 100vh;
        }

        .header {
            border-bottom: 3px solid #c39743;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .logo-block {
            text-align: right;
        }

        .logo-block img {
            height: 100px;
            margin-bottom: -40px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 5px;
        }

        .empresa-info {
            margin-top: -90px;
            font-size: 11px;
            line-height: 1.4;
            text-align: right;
        }

        .empresa-info strong {
            font-size: 13px;
            color: #111827;
        }

        .resumen {
            border: 2px solid #c39743;
            border-radius: 6px;
            padding: 6px 10px;
            background: #fffaf0;
            font-size: 11px;
            min-width: 180px;
            max-width: 200px;
        }

        .resumen strong {
            color: #c39743;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 20px;
            font-size: 11px;
        }

        th {
            background: #c39743;
            color: #fff;
            padding: 6px 5px;
            font-size: 11px;
            text-transform: uppercase;
            text-align: center;
        }

        td {
            border: 1px solid #d1d5db;
            padding: 5px 4px;
            font-size: 11px;
            text-align: center;
            vertical-align: middle;
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

        .estado-parcial {
            background: #fef3c7;
            color: #92400e;
            font-weight: bold;
        }

        .footer-ultima {
            position: absolute;
            bottom: -70px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #c39743;
            padding-top: 4px;
        }

        .info-pago {
            margin-top: 15px;
            page-break-inside: avoid;
            width: 100%;
            font-size: 10px;
            color: #111827;
            background: #fffaf0;
            border: 1px solid #c39743;
            border-radius: 4px;
        }

        .info-pago table {
            width: 100%;
            border: none;
            margin: 0;
        }

        .info-pago td {
            border: none;
            padding: 10px;
            vertical-align: middle;
        }

        .info-pago strong {
            color: #c39743;
        }

        .cotizacion-titulo {
            font-size: 15px;
            font-weight: bold;
            color: #c39743;
            margin-top: 20px;
            margin-bottom: 4px;
            page-break-before: auto;
        }

        .pagos {
            font-size: 11px;
            text-align: left;
            line-height: 1.3;
        }

        .pago-item {
            font-size: 12px;
            margin-bottom: 2px;
            padding-bottom: 2px;
            border-bottom: 0.5px dashed #c39743;
        }

        .pago-item:last-child {
            border-bottom: none;
        }

        .small {
            font-size: 12px;
            color: #666;
        }

        @page {
            size: A4 landscape;
            margin: 10mm 8mm 25mm 8mm;
        }

        /* Evitar que las tablas se dividan entre páginas */
        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    </style>
</head>

<body>
    @php
        $mesesPdf = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre',
        ];
    @endphp

    <div class="header">
        <div class="logo-block">
            <img src="{{ public_path('img/asecon-logo.png') }}" alt="Logo">
        </div>

        <div class="info-row">
            @php
                $totalDeuda = 0;
                foreach ($empresa->todasCotizaciones as $cotizacion) {
                    foreach ($cotizacion->deudas as $deuda) {
                        $totalDeuda += $deuda->monto_pendiente;
                    }
                }
            @endphp
            <div class="resumen">
                <strong>TOTAL DEUDA PENDIENTE:</strong><br>
                S/ {{ number_format($totalDeuda, 2, '.', ',') }}
            </div>

            <div class="empresa-info">
                <strong>{{ $empresa->razon_social }}</strong><br>
                RUC: {{ $empresa->ruc }}<br>
                <strong>Reporte de Cotizaciones de Recepción</strong><br>
                Fecha de reporte: {{ $fechaHoy }}
            </div>
            <strong style="font-weight: normal;font-size: 12px;">A continuación se detalla
                @if ($request->status_pago === 'ambos')
                    <span style="color: #c39743; font-weight: bold; font-size: 12px;">TODAS</span> las
                @else
                    las
                @endif
                facturas
                @if ($request->status_pago === 'pagado')
                    <span style="color: green; font-weight: bold; font-size: 12px;">PAGADO</span>
                @elseif($request->status_pago === 'pendiente-parcial')
                    <span style="color: red; font-weight: bold; font-size: 12px;">PENDIENTE/PARCIAL</span>
                @endif
                por
                @if ($request->tipo_cotizacion === 'ambos')
                    <span style="color: #c39743; font-weight: bold; font-size: 12px;">TODOS</span> los
                @else
                    los
                @endif
                servicios
                @if ($request->tipo_cotizacion === 'contable')
                    <span style="color: #c39743; font-weight: bold; font-size: 12px;">CONTABLES</span>
                @elseif($request->tipo_cotizacion === 'otros')
                    <span style="color: #c39743; font-weight: bold; font-size: 12px;">OTROS</span>
                @endif
                @if ($request->ordenar_por === 'mes_declaracion')
                    del mes <span
                        style="color: #c39743; font-weight: bold; font-size: 12px;">{{ strtoupper($request->mes_declaracion ?? 'todos') }}</span>
                    @if ($request->anio_declaracion)
                        del año <span
                            style="color: #c39743; font-weight: bold; font-size: 12px;">{{ $request->anio_declaracion }}</span>
                    @endif
                @endif
                ,
            </strong>
            <a href="#info-pago"
                style="font-size: 12px; color: #1a73e8; text-decoration: underline; font-weight: bold;">
                Ver detalles de pago
            </a>
        </div>
    </div>

    {{-- view por mes declaracion  --}}

    @if (isset($empresa->deudasOrdenadas))
        <div style="margin-top: 5px;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 6%;">N° COT.</th>
                        @if ($request->tipo_cotizacion === 'ambos')
                            <th style="width: 6%;">TIPO</th>
                        @endif
                        <th style="width: 6%;">N° FACT.</th>
                        <th style="width: 15%;">DESCRIPCIÓN</th>
                        <th style="width: 8%;">MES DECL.</th>
                        <th style="width: 8%;">DEUDA</th>
                        <th style="width: 25%;">PAGOS</th>
                        <th style="width: 15%;">OBSERVACIONES</th>
                        <th style="width: 8%;">SALDO</th>
                        <th style="width: 9%;">ESTADO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($empresa->deudasOrdenadas as $deuda)
                        @php
                            $estadoTexto = match ($deuda->estado) {
                                1 => 'PAGADO',
                                2 => 'PARCIAL',
                                default => 'PENDIENTE',
                            };
                            $estadoClase = match ($deuda->estado) {
                                1 => 'estado-pagada',
                                2 => 'estado-parcial',
                                default => 'estado-pendiente',
                            };
                        @endphp
                        @if ($deuda->pagos->count() > 0)
                            @foreach ($deuda->pagos as $index => $pago)
                                <tr>
                                    @if ($index == 0)
                                        <td rowspan="{{ $deuda->pagos->count() }}">
                                            {{ $deuda->cotizacion->nro_cotizacion }}
                                        </td>
                                        @if ($request->tipo_cotizacion === 'ambos')
                                            <td rowspan="{{ $deuda->pagos->count() }}"
                                                style="font-size: 10px; text-transform: uppercase;">
                                                {{ $deuda->cotizacion->tipo_cotizacion }}
                                            </td>
                                        @endif
                                        <td rowspan="{{ $deuda->pagos->count() }}">{{ $deuda->nro_factura }}</td>
                                        <td rowspan="{{ $deuda->pagos->count() }}">{{ $deuda->descripcion_servicio }}
                                        </td>
                                        <td rowspan="{{ $deuda->pagos->count() }}">
                                            @if ($deuda->tipo_declaracion === 'anual')
                                                <strong>ANUAL</strong>
                                                {{$deuda->anio_declaracion}}
                                            @else
                                                {{ $mesesPdf[\Carbon\Carbon::parse($deuda->mes_declaracion)->format('F')] }}
                                                {{ \Carbon\Carbon::parse($deuda->mes_declaracion)->format('Y') }}
                                            @endif
                                        </td>
                                        <td rowspan="{{ $deuda->pagos->count() }}">S/
                                            {{ number_format($deuda->monto_deuda, 2, '.', ',') }}
                                        </td>
                                    @endif
                                    <td style="text-align: left; font-size: 11px;">
                                        <strong>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</strong>
                                        — S/ {{ number_format($pago->monto_pagado, 2, '.', ',') }}
                                        <span class="small">
                                            ({{ $pago->metodo_pago }}
                                            @if ($pago->metodo_pago != 'Efectivo')
                                                @if ($pago->nro_pago)
                                                    - Nº:{{ $pago->nro_pago }}
                                                @endif
                                            @endif)
                                        </span>
                                    </td>
                                    <td class="small" style="text-align:left; line-height:1.3;">
                                        {{ $pago->observacion ?? '' }}
                                    </td>
                                    @if ($index == 0)
                                        <td rowspan="{{ $deuda->pagos->count() }}">S/
                                            {{ number_format($deuda->monto_pendiente, 2, '.', ',') }}
                                        </td>
                                        <td rowspan="{{ $deuda->pagos->count() }}" class="{{ $estadoClase }}">
                                            {{ $estadoTexto }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td>{{ $deuda->cotizacion->nro_cotizacion }}</td>
                                @if ($request->tipo_cotizacion === 'ambos')
                                    <td style="font-size: 10px; text-transform: uppercase;">
                                        {{ $deuda->cotizacion->tipo_cotizacion }}
                                    </td>
                                @endif
                                <td>{{ $deuda->nro_factura }}</td>
                                <td>{{ $deuda->descripcion_servicio }}</td>
                                <td>
                                    @if ($deuda->tipo_declaracion === 'anual')
                                        <strong>ANUAL</strong>
                                        {{$deuda->anio_declaracion}}
                                    @else
                                        {{ $mesesPdf[\Carbon\Carbon::parse($deuda->mes_declaracion)->format('F')] }}
                                        {{ \Carbon\Carbon::parse($deuda->mes_declaracion)->format('Y') }}
                                    @endif
                                </td>
                                <td>S/ {{ number_format($deuda->monto_deuda, 2, '.', ',') }}</td>
                                <td><em>—</em></td>
                                <td>{{ $deuda->observaciones ?? '—' }}</td>
                                <td>S/ {{ number_format($deuda->monto_pendiente, 2, '.', ',') }}</td>
                                <td class="{{ $estadoClase }}">{{ $estadoTexto }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    @php
                        $totalOriginal = $empresa->deudasOrdenadas->sum('monto_deuda');
                        $totalPendiente = $empresa->deudasOrdenadas->sum('monto_pendiente');
                        $totalPagado = $totalOriginal - $totalPendiente;
                    @endphp
                    <tr style="font-weight: bold; background: #fef9c3;">
                        <td colspan="{{ $request->tipo_cotizacion === 'ambos' ? '5' : '4' }}"
                            style="text-align:right;">TOTAL</td>
                        <td>S/ {{ number_format($totalOriginal, 2, '.', ',') }}</td>
                        <td>S/ {{ number_format($totalPagado, 2, '.', ',') }}</td>
                        <td>—</td>
                        <td>S/ {{ number_format($totalPendiente, 2, '.', ',') }}</td>
                        <td>—</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- view por nº cotizacion --}}
    @else
        @foreach ($empresa->todasCotizaciones as $cotizacion)
            @if ($cotizacion->deudas->count() > 0)
                <div class="cotizacion-titulo">
                    COTIZACIÓN {{ strtoupper($cotizacion->nro_cotizacion) }}
                    — TIPO: {{ ucfirst($cotizacion->tipo_cotizacion) }}
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 6%;">N° FACT.</th>
                            <th style="width: 15%;">DESCRIPCIÓN</th>
                            <th style="width: 8%;">MES DECL.</th>
                            <th style="width: 8%;">DEUDA</th>
                            <th style="width: 25%;">PAGOS</th>
                            <th style="width: 15%;">OBSERVACIONES</th>
                            <th style="width: 8%;">SALDO</th>
                            <th style="width: 9%;">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cotizacion->deudas as $deuda)
                            @php
                                $estadoTexto = match ($deuda->estado) {
                                    1 => 'PAGADO',
                                    2 => 'PARCIAL',
                                    default => 'PENDIENTE',
                                };
                                $estadoClase = match ($deuda->estado) {
                                    1 => 'estado-pagada',
                                    2 => 'estado-parcial',
                                    default => 'estado-pendiente',
                                };
                            @endphp
                            @if ($deuda->pagos->count() > 0)
                                @foreach ($deuda->pagos as $index => $pago)
                                    <tr>
                                        @if ($index == 0)
                                            <td rowspan="{{ $deuda->pagos->count() }}">{{ $deuda->nro_factura }}</td>
                                            <td rowspan="{{ $deuda->pagos->count() }}">
                                                {{ $deuda->descripcion_servicio }}
                                            </td>
                                            <td rowspan="{{ $deuda->pagos->count() }}">
                                                @if ($deuda->tipo_declaracion === 'anual')
                                                    <strong>ANUAL</strong>
                                                    {{$deuda->anio_declaracion}}
                                                @else
                                                    {{ $mesesPdf[\Carbon\Carbon::parse($deuda->mes_declaracion)->format('F')] }}
                                                    {{ \Carbon\Carbon::parse($deuda->mes_declaracion)->format('Y') }}
                                                @endif
                                            </td>
                                            <td rowspan="{{ $deuda->pagos->count() }}">S/
                                                {{ number_format($deuda->monto_deuda, 2, '.', ',') }}
                                            </td>
                                        @endif
                                        <td style="text-align: left; font-size: 11px;">
                                            <strong>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</strong>
                                            — S/ {{ number_format($pago->monto_pagado, 2, '.', ',') }}
                                            <span class="small">
                                                ({{ $pago->metodo_pago }}
                                                @if ($pago->metodo_pago != 'Efectivo')
                                                    @if ($pago->nro_pago)
                                                        - Nº:{{ $pago->nro_pago }}
                                                    @endif
                                                @endif)
                                            </span>
                                        </td>
                                        <td class="small" style="text-align:left; line-height:1.3;">
                                            {{ $pago->observacion ?? '' }}
                                        </td>
                                        @if ($index == 0)
                                            <td rowspan="{{ $deuda->pagos->count() }}">S/
                                                {{ number_format($deuda->monto_pendiente, 2, '.', ',') }}
                                            </td>
                                            <td rowspan="{{ $deuda->pagos->count() }}" class="{{ $estadoClase }}">
                                                {{ $estadoTexto }}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td>{{ $deuda->nro_factura }}</td>
                                    <td>{{ $deuda->descripcion_servicio }}</td>
                                    <td>
                                        @if ($deuda->tipo_declaracion === 'anual')
                                            <strong>ANUAL</strong>
                                            {{$deuda->anio_declaracion}}
                                        @else
                                            {{ $mesesPdf[\Carbon\Carbon::parse($deuda->mes_declaracion)->format('F')] }}
                                            {{ \Carbon\Carbon::parse($deuda->mes_declaracion)->format('Y') }}
                                        @endif
                                    </td>
                                    <td>S/ {{ number_format($deuda->monto_deuda, 2, '.', ',') }}</td>
                                    <td><em>—</em></td>
                                    <td>{{ $deuda->observaciones ?? '—' }}</td>
                                    <td>S/ {{ number_format($deuda->monto_pendiente, 2, '.', ',') }}</td>
                                    <td class="{{ $estadoClase }}">{{ $estadoTexto }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php
                            $totalOriginal = $cotizacion->deudas->sum('monto_deuda');
                            $totalPendiente = $cotizacion->deudas->sum('monto_pendiente');
                            $totalPagado = $totalOriginal - $totalPendiente;
                        @endphp
                        <tr style="font-weight: bold; background: #fef9c3;">
                            <td colspan="3" style="text-align:right;">TOTAL COTIZACIÓN</td>
                            <td>S/ {{ number_format($totalOriginal, 2, '.', ',') }}</td>
                            <td>S/ {{ number_format($totalPagado, 2, '.', ',') }}</td>
                            <td>—</td>
                            <td>S/ {{ number_format($totalPendiente, 2, '.', ',') }}</td>
                            <td>—</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endforeach
    @endif

    @if (
        $empresa->todasCotizaciones->sum(function ($cotizacion) {
            return $cotizacion->deudas->count();
        }) == 0)
        <table>
            <tr>
                <td colspan="9" style="text-align:center; color:#6b7280; background:#f9fafb; font-style:italic;">
                    No se encontraron deudas con los filtros seleccionados.
                </td>
            </tr>
        </table>
    @endif

    <div class="info-pago" id="info-pago">
        <table>
            <tr>
                <td style="width: 85%; text-align: left;">
                    <strong>Ponemos a su disposición nuestro medio de pago, a nombre de: ASESORÍA EMPRESARIAL CONTABLE
                        ASECON S.A.C.</strong><br>
                    <strong>CUENTA SOLES BCP – ASECON</strong><br>
                    N° de Cuenta Corriente BCP: <strong>570-7327550-0-30</strong><br>
                    N° de CCI: <strong>002-570-007327550030-01</strong><br><br style="margin-top: 15px;">
                    <strong style="color: black;">No olvidar enviarnos el voucher de pago, vía pantallazo.</strong><br>
                    Quedamos a la espera de la confirmación del abono POR ESTE ÚNICO MEDIO. ¡Gracias por su confianza!
                </td>
                <td style="width: 15%; text-align: right;">
                    {{-- <img src="{{ public_path('img/qr.png') }}" alt="QR" style="width: 120px; height: 120px;"> --}}
                </td>
            </tr>
        </table>
    </div>

    <strong style="color: black;" class="footer-ultima">
        ASECON PERÚ - Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </strong>
</body>

</html>
