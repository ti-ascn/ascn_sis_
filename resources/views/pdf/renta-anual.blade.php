<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }} - {{ strtoupper($empresa->razon_social) }}</title>
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
            margin-bottom: 80px;
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
            text-align: right;
        }

        td:first-child {
            text-align: left;
        }

        .section-title {
            background: #fef9c3;
            color: #92400e;
            font-weight: bold;
            padding: 8px;
            border-radius: 4px;
            margin-top: 20px;
            margin-bottom: 6px;
            text-transform: uppercase;
            page-break-inside: avoid;
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
            display: none;
        }

        body:last-child .footer-ultima {
            display: block;
        }

        .small {
            font-size: 10px;
            color: #666;
        }

        /* NUEVA HOJA */
        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    {{-- HEADER --}}
    <div class="header">
        <div class="logo-block">
            <img src="{{ public_path('img/asecon-logo.png') }}" alt="Logo">
        </div>

        <div class="info-row">
            @php
                $impuestoTotal = $determinacionRenta->impuesto_total ?? 0;
                $pagosCuenta = abs($determinacionRenta->pagos_cuenta ?? 0);
                $saldosNoAplicados = abs($determinacionRenta->saldos_no_aplicados ?? 0);
                $pagoItan = abs($determinacionRenta->pago_itan ?? 0);
                $totalPorPagar = $impuestoTotal - $pagosCuenta - $saldosNoAplicados - $pagoItan;
                $saldoAFavor = $totalPorPagar;
            @endphp
            <div class="resumen">
                @if ($totalPorPagar > 0)
                    <strong>TOTAL A PAGAR RENTA ANUAL {{ $anio }}:</strong><br>
                    S/ {{ number_format($totalPorPagar, 2, '.', ',') }}
                @else
                    <strong>CRÉDITO RENTA ANUAL {{ $anio }}:</strong><br>
                    <span style="color:#b91c1c;">S/ {{ number_format($saldoAFavor, 2, '.', ',') }}</span>
                @endif
            </div>

            <div class="empresa-info">
                <strong>{{ strtoupper($empresa->razon_social) }}</strong><br>
                RUC: {{ $empresa->ruc }}<br>
                <strong>{{ strtoupper($titulo) }}</strong><br>
                Fecha de reporte: {{ $fechaHoy }}
            </div>
        </div>
    </div>

    {{-- SECCIÓN PRE-LIQUIDACIÓN --}}
    <div class="section-title">
        PRE-LIQUIDACIÓN RENTA ANUAL {{ $anio }}
    </div>
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Monto (S/)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Ventas Netas</td>
                <td>{{ number_format($preLiquidacion->ventas_netas, 2) }}</td>
            </tr>
            <tr>
                <td>Costo de Ventas</td>
                <td>{{ number_format($preLiquidacion->costo_ventas, 2) }}</td>
            </tr>
            <tr>
                <td>Resultado Bruto</td>
                <td>{{ number_format($preLiquidacion->resultado_bruto, 2) }}</td>
            </tr>
            <tr>
                <td>Gastos de Ventas</td>
                <td>{{ number_format($preLiquidacion->gastos_ventas, 2) }}</td>
            </tr>
            <tr>
                <td>Gastos de Administración</td>
                <td>{{ number_format($preLiquidacion->gastos_administracion, 2) }}</td>
            </tr>
            <tr>
                <td>Depreciación</td>
                <td>{{ number_format($preLiquidacion->depreciacion, 2) }}</td>
            </tr>
            <tr>
                <td>Resultado de Operación</td>
                <td>{{ number_format($preLiquidacion->resultado_operacion, 2) }}</td>
            </tr>
            <tr>
                <td>Pérdida por Tipo de Cambio</td>
                <td>{{ number_format($preLiquidacion->perdida_tipo_cambio, 2) }}</td>
            </tr>
            <tr>
                <td>Intereses y Otros Gastos</td>
                <td>{{ number_format($preLiquidacion->intereses_otros_gastos, 2) }}</td>
            </tr>
            <tr>
                <td>Total Gastos Financieros</td>
                <td>{{ number_format($preLiquidacion->total_gastos_financieros, 2) }}</td>
            </tr>
            <tr>
                <td>Ingresos Financieros Gravados</td>
                <td>{{ number_format($preLiquidacion->ingresos_financieros_gravados, 2) }}</td>
            </tr>
            <tr>
                <td>Ingresos Financieros por Tipo de Cambio</td>
                <td>{{ number_format($preLiquidacion->ingresos_financieros_tipo_cambio, 2) }}</td>
            </tr>
            <tr>
                <td>Total Ingresos Financieros</td>
                <td>{{ number_format($preLiquidacion->total_ingresos_financieros, 2) }}</td>
            </tr>
            <tr>
                <td>Otros Ingresos Gravados</td>
                <td>{{ number_format($preLiquidacion->otros_ingresos_gravados, 2) }}</td>
            </tr>
            <tr style="background:#fef3c7;font-weight:bold;">
                <td>UTILIDAD ANTES DE IMPUESTOS</td>
                <td>{{ number_format($preLiquidacion->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- SECCIÓN DETERMINACIÓN DE RENTA - NUEVA HOJA --}}
    <div class="page-break"></div>

    <div class="section-title">
        DETERMINACIÓN DE IMPUESTO A LA RENTA {{ $anio }}
    </div>
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Monto (S/)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Utilidad Antes</td>
                <td>{{ number_format($determinacionRenta->utilidad_antes, 2) }}</td>
            </tr>
            <tr>
                <td>Adición Depreciación</td>
                <td>{{ number_format($determinacionRenta->adicion_depreciacion, 2) }}</td>
            </tr>
            <tr>
                <td>Adición Gastos Personales</td>
                <td>{{ number_format($determinacionRenta->adicion_gastos_personales, 2) }}</td>
            </tr>
            <tr>
                <td>Adición Multas y Cargos</td>
                <td>{{ number_format($determinacionRenta->adicion_multas_cargos, 2) }}</td>
            </tr>
            <tr>
                <td>Adición Gastos RCP</td>
                <td>{{ number_format($determinacionRenta->adicion_gastos_rcp, 2) }}</td>
            </tr>
            <tr>
                <td>Adición Otros</td>
                <td>{{ number_format($determinacionRenta->adicion_otros, 2) }}</td>
            </tr>
            <tr>
                <td>Total Adiciones</td>
                <td>{{ number_format($determinacionRenta->adicion_total, 2) }}</td>
            </tr>
            <tr>
                <td>Deducción Vacaciones</td>
                <td>{{ number_format($determinacionRenta->deduccion_vaciones, 2) }}</td>
            </tr>
            <tr>
                <td>Deducción Depreciación</td>
                <td>{{ number_format($determinacionRenta->deduccion_depreciacion, 2) }}</td>
            </tr>
            <tr>
                <td>Deducción Otros</td>
                <td>{{ number_format($determinacionRenta->deduccion_otros, 2) }}</td>
            </tr>
            <tr>
                <td>Total Deducciones</td>
                <td>{{ number_format($determinacionRenta->deduccion_total, 2) }}</td>
            </tr>
            <tr>
                <td>Utilidad Tributaria</td>
                <td>{{ number_format($determinacionRenta->utilidad_tributaria, 2) }}</td>
            </tr>
            <tr>
                <td>Impuesto Hasta 15 UIT</td>
                <td>{{ number_format($determinacionRenta->impuesto_hasta_15_uit, 2) }}</td>
            </tr>
            <tr>
                <td>Impuesto Exceso 15 UIT</td>
                <td>{{ number_format($determinacionRenta->impuesto_exceso_15_uit, 2) }}</td>
            </tr>
            <tr>
                <td>Impuesto Total</td>
                <td>{{ number_format($determinacionRenta->impuesto_total, 2) }}</td>
            </tr>
            <tr>
                <td>Pagos a Cuenta</td>
                <td>-{{ number_format(abs($determinacionRenta->pagos_cuenta), 2) }}</td>
            </tr>
            <tr>
                <td>Pagos a Cuenta ITAN</td>
                <td>-{{ number_format(abs($determinacionRenta->pago_itan), 2) }}</td>
            </tr>
            <tr>
                <td>Saldos No Aplicados</td>
                <td>{{ number_format(abs($determinacionRenta->saldos_no_aplicados), 2) }}</td>
            </tr>

            @php
                $colorFila =
                    $totalPorPagar < 0 ? 'background:#d1fae5;color:#065f46;' : 'background:#fee2e2;color:#b91c1c;';
            @endphp

            @if ($totalPorPagar > 0)
                <tr style="font-weight:bold; {{ $colorFila }}">
                    <td>TOTAL A PAGAR RENTA ANUAL {{ $anio }}</td>
                    <td>{{ number_format($totalPorPagar, 2) }}</td>
                </tr>
            @else
                <tr style="font-weight:bold; {{ $colorFila }}">
                    <td>CRÉDITO RENTA ANUAL {{ $anio }}</td>
                    <td>{{ number_format($totalPorPagar, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer-ultima">
        ASECON PERÚ - Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
