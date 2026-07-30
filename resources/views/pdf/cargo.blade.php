<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>C{{ $cargo->nro_cargo }}-{{ $cargo->empresaCliente->razon_social }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            position: relative;
        }

        .watermark {
            position: fixed;
            top: 60%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.2;
            z-index: -1;
        }

        .watermark img {
            width: 1300px;
            height: auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            color: #111827;
        }

        .header-logo {
            margin-bottom: 20px;
        }

        .header-logo img {
            height: 140px;
        }

        .header h2 {
            margin: 15px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .header h1 {
            margin: 15px 0;
            font-size: 20px;
            font-weight: bold;
        }

        .header h3 {
            margin: 15px 0;
            font-size: 18px;
            font-weight: normal;
        }

        .content {
            margin: 30px 0;
            text-align: justify;
            line-height: 1.8;
            font-size: 14px;
        }

        .table-divider {
            border-top: 3px solid #c39743;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            page-break-inside: auto;
        }

        table.main-table {
            page-break-after: auto;
            margin-bottom: 250px;
        }

        tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background-color: #c39743;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) {
            background-color: transparent;
        }

        .footer-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 0 20px;
        }

        .footer-divider {
            border-top: 3px solid #c39743;
            margin: 10px 0;
        }

        .footer-row {
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            gap: 80px;
        }

        .firma {
            text-align: center;
            flex: 0 0 auto;
            margin: 200px auto 0;
        }

        .firma-linea {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 50px;
        }

        .firma-texto {
            margin-top: 5px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .firma-texto p {
            margin: 2px 0;
        }

        .firma-numero {
            position: absolute;
            right: 0;
            bottom: 20px;
            font-weight: bold;
            font-size: 11px;
        }

        .contact-info {
            flex: 1;
            font-size: 11px;
            line-height: 1.2;
            position: relative;
            margin-top: 40px;
        }

        .contact-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 100%;
        }

        .contact-row p:first-child {
            flex: 0 0 auto;
        }

        .contact-row p:last-child {
            position: absolute;
            right: 0;
            font-size: 11px;
            font-weight: normal;
        }

        .contact-left {
            text-align: left;
        }

        .contact-right {
            text-align: right;
        }

        .contact-info strong {
            color: #c39743;
        }
    </style>
</head>

<body>
    <div class="watermark">
        <img src="{{ public_path('img/asecon-logo.png') }}" alt="Logo">
    </div>
    <div class="header">
        <div class="header-logo">
            <img src="{{ public_path('img/aseconcargo.png') }}" alt="Logo Asecon">
        </div>
        <h2>ASESORIA EMPRESARIAL</h2>
        <h1>ACTA Nº {{ $cargo->nro_cargo }}</h1>
        <h3>CARGO DE {{ $cargo->estado_cargo == 0 ? 'RECEPCION' : 'ENTREGA' }} DE DOCUMENTOS</h3>
    </div>

    <div class="table-divider" style="margin-top: 30px;"></div>
    <div class="content">
        @if ($cargo->estado_cargo == 0)
            <p style="margin-top: -10px;">
                Con fecha {{ \Carbon\Carbon::parse($cargo->fecha)->format('d') }} de
                {{ \Carbon\Carbon::parse($cargo->fecha)->locale('es')->translatedFormat('F') }} del año
                {{ \Carbon\Carbon::parse($cargo->fecha)->format('Y') }},
                la empresa <strong>{{ $cargo->empresaCliente->razon_social }}</strong> con N.º de RUC
                <strong>{{ $cargo->empresaCliente->ruc }}</strong>, hace entrega de los documentos que se detallan
                en el siguiente cuadro, lo expuesto:
            </p>
        @else
            <p style="margin-top: -10px;">
                En la ciudad de Trujillo, siendo las {{ \Carbon\Carbon::parse($cargo->fecha)->format('H:i') }}
                horas del
                {{ \Carbon\Carbon::parse($cargo->fecha)->format('d') }} del mes de
                {{ \Carbon\Carbon::parse($cargo->fecha)->locale('es')->translatedFormat('F') }} del año
                {{ \Carbon\Carbon::parse($cargo->fecha)->format('Y') }} en calidad de representante de la empresa
                <strong>{{ $cargo->empresaCliente->razon_social }}</strong> con N.º de RUC:
                <strong>{{ $cargo->empresaCliente->ruc }}</strong>; por otro lado, la empresa ASECON procede a la
                entrega
                de la documentación posteriormente detallada que dicho sea corresponde con los lineamientos de
                transición
                establecidos por ambas empresas, a continuación, se detallan y enlistan en el Anexo a la presente Acta
                así:
            </p>
        @endif
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 10%;">ITEM</th>
                <th style="width: 100%;">NOMBRE DEL DOCUMENTO</th>
                <th style="width: 25%;">TIPO DE DOCUMENTO</th>
                @if ($cargo->estado_cargo == 0)
                    <th style="width: 20%;">CANTIDADES</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($cargo->detalles as $index => $detalle)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $detalle->nombre_documento }}</td>
                    <td style="text-align: center;">{{ $detalle->tipo_documento }}</td>
                    @if ($cargo->estado_cargo == 0)
                        <td style="text-align: center;">{{ $detalle->cantidades }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer-container">
        @if ($cargo->estado_cargo == 0)
            <table style="width: 100%; border: none; margin-top: 80px;">
                <tr>
                    <td style="width: 50%; text-align: center; border: none; font-weight: bold; font-size: 12px;">
                        RECIBE:</td>
                </tr>
                <tr>
                    <td style="border: none; text-align: center; padding: 5px 0;">
                        <img src="{{ public_path('img/firmacargo.png') }}" alt="Firma" style="height: 80px;">
                    </td>
                </tr>
                <tr>
                    <td style="border: none; text-align: center; padding: 2px 0;">
                        <div style="border-top: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                    </td>
                </tr>
                <tr>
                    <td
                        style="border: none; text-align: center; font-size: 10px; font-weight: bold; text-transform: uppercase; line-height: 1.1;">
                        <p style="margin: 0;">MARÍA DEL CARMEN GONZÁLEZ GONZALES</p>
                        <p style="margin: 0;">SECRETARIA DE GERENCIA</p>
                        <p style="margin: 0;">74355756</p>
                    </td>
                </tr>
            </table>
            <div class="table-divider" style="margin-top: 20px;"></div>
            <div class="contact-info" style="margin-top: 10px;">
                <div class="contact-row">
                    <p style="margin: 0;">CAL. LOS FRESNOS 0455 URB. CALIFORNIA</p>
                    <p style="margin: 0;"> 993-280-096</p>
                </div>
                <p style="margin: 0; margin-left: 40px;">LA LIBERTAD - TRUJILLO</p>
                <p style="margin: 0; margin-left: 28px;"> RECEPCION@ASECONCC.COM</p>
                <p style="margin: 0; margin-left: 28px;"> CONTABILIDAD@ASECONCC.COM</p>
            </div>
        @else
            <table style="width: 100%; border: none; margin-top: 80px;">
                <tr>
                    <td style="width: 50%; text-align: center; border: none; font-weight: bold; font-size: 12px;">
                        ENTREGA:</td>
                    <td style="width: 50%; text-align: center; border: none; font-weight: bold; font-size: 12px;">
                        RECIBE:</td>
                </tr>
                <tr>
                    <td style="border: none; text-align: center; padding: 5px 0;">
                        <img src="{{ public_path('img/firmacargo.png') }}" alt="Firma" style="height: 80px;">
                    </td>
                    <td style="border: none;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="border: none; text-align: center; padding: 2px 0;">
                        <div style="border-top: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                    </td>
                    <td style="border: none; text-align: center; padding: 2px 0;">
                        <div style="border-top: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                    </td>
                </tr>
                <tr>
                    <td
                        style="border: none; text-align: center; font-size: 10px; font-weight: bold; text-transform: uppercase; line-height: 1.1;">
                        <p style="margin: 0;">MARÍA DEL CARMEN GONZÁLEZ GONZALES</p>
                        <p style="margin: 0;">SECRETARIA DE GERENCIA</p>
                        <p style="margin: 0;">74355756</p>
                    </td>
                    <td
                        style="border: none; text-align: center; font-size: 10px; font-weight: bold; text-transform: uppercase;">
                        <p style="margin: 0; text-align: left; margin-left: 20px;">NOMBRE:</p>
                        <p style="margin: 0; text-align: left; margin-left: 20px;">DNI:</p>
                        <p style="margin: 0;">RESPONSABLE</p>
                    </td>
                </tr>
            </table>
            <div class="table-divider" style="margin-top: 20px;"></div>
            <div class="contact-info" style="margin-top: 10px;">
                <div class="contact-row">
                    <p style="margin: 0;">CAL. LOS FRESNOS 0455 URB. CALIFORNIA</p>
                    <p style="margin: 0;"> 993-280-096</p>
                </div>
                <p style="margin: 0; margin-left: 40px;">LA LIBERTAD - TRUJILLO</p>
                <p style="margin: 0; margin-left: 28px;"> GERENCIA@ASECONCC.COM</p>
            </div>
        @endif
    </div>
</body>

</html>
