@php
    $ano = $fecha->year;
    $porcentajeVentas10 = $ano >= 2026 ? '10.5%' : '10%';
    $porcentaje_compras = (float) $determinacion->porcentaje_compras > 0.1 ? '10.5' : '10';
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Determinación Mensual</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 3px solid #c39743;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .logo-block {
            text-align: right;
        }

        .logo-block img {
            height: 135px;
            margin-bottom: -50px;
        }

        .info-row {
            margin-top: 110px !important;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .empresa-info {
            margin-top: -100px;
            font-size: 12.5px;
            line-height: 1.5;
            text-align: right;
        }

        .empresa-info strong {
            font-size: 15px;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        td,
        th {
            border: 1px solid #d1d5db;
            padding: 6px;
        }

        th {
            background: #c39743;
            color: white;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .section {
            background: #fef3c7;
            font-weight: bold;
        }

        .total {
            background: #fde68a;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            right: 10px;
            font-size: 9px;
            color: #666;
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
    </style>
</head>

<body>
    {{-- HEADER --}}
    <div class="header">
        <div class="logo-block">
            <img src="{{ public_path('img/asecon-logo.png') }}" alt="Logo">
        </div>

        <div class="info-row">
            <div class="empresa-info">
                <strong>{{ strtoupper($empresa->razon_social) }}</strong><br>
                RUC: {{ $empresa->ruc }}<br>
                <strong>DETERMINACION MENSUAL DE IMPUESTOS</strong><br>
                {{ strtoupper($fecha->locale('es')->translatedFormat('F Y')) }}
            </div>
        </div>
    </div>

    <div class="section-title">
        VENTAS
    </div>

    <table>
        <tr>
            <td>Base Ventas 18%</td>
            <td class="right">{{ number_format($determinacion->base_ventas ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Base Ventas {{ $porcentajeVentas10 }}</td>
            <td class="right">{{ number_format($determinacion->base_ventas_10 ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>IGV Ventas 18%</td>
            <td class="right">{{ number_format($determinacion->igv_ventas ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>IGV Ventas {{ $porcentajeVentas10 }}</td>
            <td class="right">{{ number_format($determinacion->igv_ventas_10 ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #b91c1c;">Descuentos Concedidos y/o Devoluciones de Ventas</td>
            <td class="right" style="color: #b91c1c;">{{ number_format($determinacion->descuentos_conce_devolu ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #b91c1c;">Descuentos Concedidos Tributo</td>
            <td class="right" style="color: #b91c1c;">{{ number_format($determinacion->descuento_concedido_tributo ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Ventas no Gravadas (Sin Considerar exportaciones)</td>
            <td class="right">{{ number_format($determinacion->ventas_nogravadas_sinexpor ?? 0, 2) }}</td>
        </tr>
        <tr class="section">
            <td><strong>TOTAL VENTAS</strong></td>
            <td class="right"><strong>{{ number_format($determinacion->total_ventas ?? 0, 2) }}</strong></td>
        </tr>
    </table>

    <div class="section-title">
        COMPRAS
    </div>
    <table>

        <tr>
            <td>Base Compras 18%</td>
            <td class="right">{{ number_format($determinacion->base_compras_18 ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Base Compras {{ $porcentaje_compras }}%</td>
            <td class="right">{{ number_format($determinacion->base_compras_10 ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Base Compras NG</td>
            <td class="right">{{ number_format($determinacion->base_compras_ng ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>IGV Compras 18%</td>
            <td class="right">{{ number_format($determinacion->igv_compras_18 ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>IGV Compras {{ $porcentaje_compras }}%</td>
            <td class="right">{{ number_format($determinacion->igv_compras_10 ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Compras Netas Destinadas a Ventas Gravadas y no Gravadas</td>
            <td class="right">{{ number_format($determinacion->comprasnetas_gravadas_nogravadas ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Compras Netas Destinada a Ventas no Gravadas Exclusivamente</td>
            <td class="right">{{ number_format($determinacion->comprasnetas_nogravadas_exclusivas ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Compras Internas no Gravadas</td>
            <td class="right">{{ number_format($determinacion->comprasinternas_nogravadas ?? 0, 2) }}</td>
        </tr>
        <tr class="section">
            <td><strong>TOTAL COMPRAS</strong></td>
            <td class="right"><strong>{{ number_format($determinacion->total_compras ?? 0, 2) }}</strong></td>
        </tr>
    </table>

    <div class="section-title">
        IGV A PAGAR
    </div>

    <Table>
        <tr>
            <td>IGV (Crédito / Pago)</td>
            <td class="right">{{ number_format($determinacion->igv_a_pagar ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #b91c1c;">Saldo a Favor del Periodo Anterior</td>
            <td class="right" style="color: #b91c1c;">
                {{ number_format($determinacion->saldo_a_favor_periodo_anterior ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Total IGV a Pagar o Saldo a Favor</td>
            <td class="right">{{ number_format($determinacion->total_igv_a_pagar ?? 0, 2) }}</td>
        </tr>
    </Table>

    <div class="section-title">
        PORCENTAJE DE MYPE
    </div>
    <table>
        <tr>
            <td>Porcentaje de MYPE</td>
            <td class="right">
                {{ rtrim(rtrim(number_format($determinacion->porcentaje_mype ?? 0, 4, '.', ''), '0'), '.') }}
            </td>
        </tr>
    </table>

    <div class="section-title">
        RENTA
    </div>
    <Table>
        <tr>
            <td>Ingresos Netos</td>
            <td class="right">{{ number_format($determinacion->ingresos_netos ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Calculo Renta a Pagar</td>
            <td class="right">{{ number_format($determinacion->renta_pagar ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #b91c1c;"> Saldo a Favor Renta del Periodo Anterior</td>
            <td class="right"style="color: #b91c1c;">
                {{ number_format($determinacion->saldo_a_favor_periodo_anterior ?? 0, 2) }}</td>
        </tr>
        <tr class="section">
            <td><strong>TOTAL RENTA A PAGAR</strong></td>
            <td class="right"><strong>{{ number_format($determinacion->total_renta_a_pagar ?? 0, 2) }}</strong>
        </tr>
    </Table>

    <div class="section-title">
        TRIBUTOS
    </div>

    <Table>
        <tr class="section">
            <td><strong>TOTAL A PAGAR TRIBUTOS</strong></td>
            <td class="right"><strong>{{ number_format($determinacion->total_a_pagar_tributos ?? 0, 2) }}</strong>
            </td>
        </tr>
    </Table>

    <div class="section-title">
        RETENCIONES Y PERCEPCIONES
    </div>

    <Table>
        <tr>
            <td>Retenciones del Periodo</td>
            <td class="right">{{ number_format($determinacion->retenciones_del_periodo ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Retenciones del Periodo Anterior</td>
            <td class="right">{{ number_format($determinacion->retenciones_periodo_anterior ?? 0, 2) }}</td>
        </tr>
        <tr class="section">
            <td><strong>TOTAL RETENCIONES</Strong></td>
            <td class="right"><strong>{{ number_format($determinacion->total_retenciones ?? 0, 2) }}</strong>
            </td>
        </tr>
        <tr>
            <td>Percepciones del Periodo</td>
            <td class="right">{{ number_format($determinacion->percepciones_del_periodo ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Percepción del Periodo Anterior</td>
            <td class="right">{{ number_format($determinacion->percepcion_periodo_anterior ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Percepciones No Aplicadas</td>
            <td class="right">{{ number_format($determinacion->percepciones_no_aplicadas ?? 0, 2) }}</td>
        </tr>
        <tr class="section">
            <td><strong>TOTAL PERCEPCIONES</strong></td>
            <td class="right"><strong>{{ number_format($determinacion->total_percepciones ?? 0, 2) }}</strong></td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="footer-ultima">
        ASECON PERÚ - Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
