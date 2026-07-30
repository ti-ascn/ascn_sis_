<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>COTIZACIÓN - {{ strtoupper($inventario->empresa->razon_social) }}</title>
    <style>
        @page {
            margin: 110px 35px 90px 35px;
        }

        /* Dinámicos */
        body {
            font-family: '{{ $style['font_family'] }}', DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        /* Dinámicos */
        th {
            background: {{ $style['header'] }};
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        /* Dinámicos */
        .pill {
            background: {{ $style['pill_bg'] }};
        }

        /* Encabezado fijo */
        header {
            position: fixed;
            top: -90px;
            left: 0;
            right: 0;
            height: 90px;
        }

        .h-row {
            display: table;
            width: 100%;
        }

        .h-left {
            display: table-cell;
            vertical-align: middle;
        }

        .h-right {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
        }

        .logo {
            height: 52px;
        }

        /* Pie fijo */
        footer {
            position: fixed;
            bottom: -70px;
            left: 0;
            right: 0;
            height: 60px;
            font-size: 10px;
            color: #6b7280;
        }

        .foot-top {
            border-top: 1px solid #E5E7EB;
            padding-top: 8px;
        }

        .page-num:after {
            content: counter(page);
        }

        h1 {
            font-size: 16px;
            margin: 0 0 6px 0;
            letter-spacing: .5px;
        }

        .muted {
            color: #6B7280;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-3 {
            margin-bottom: 12px;
        }

        .mt-5 {
            margin-top: 20px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            display: table-header-group;
        }

        /* repetir en cada página */
        tfoot {
            display: table-footer-group;
        }

        th,
        td {
            border: 1px solid #E5E7EB;
            padding: 7px 8px;
        }

        .right {
            text-align: right;
        }

        tr {
            page-break-inside: avoid;
        }

        tbody tr:nth-child(odd) {
            background: #FCFCFD;
        }

        .pill {
            display: inline-block;
            padding: 2px 8px;
            background: #F3F4F6;
            border-radius: 999px;
            font-size: 10px;
        }

        .signature {
            margin-top: 40px;
            text-align: center;
            page-break-inside: avoid;
        }

        .sig-box {
            display: inline-block;
            text-align: center;
            min-width: 260px;
            /* ancho mínimo del bloque */
        }

        .sig-image {
            height: 70px;
            margin-bottom: 6px;
        }

        .sig-line {
            /*ESTE NO PORQUE ES LA LINEA DE LA FIRMA */
            border-top: 1px solid #111827;
            /* ajusta (230–320px) al gusto */
            width: 260px;
            margin: 6px auto 8px;
        }

        .sig-name {
            font-weight: 700;
            text-transform: uppercase;
        }

        .sig-title,
        .sig-company {
            font-size: 11px;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .link {
            color: #2563EB;
            text-decoration: none;
            word-break: break-all;
        }
    </style>
    @php
        $isStack = in_array($headerLayout, ['stack_left', 'stack_right']);
        $hasLogo = $headerShowLogo && !empty(optional($inventario->empresa->branding)->logo_empresa); // <— usa el flag
        // Altura real del header en apilado (ajústalo si aún te queda justo):
        $STACK_H = $hasLogo ? 150 : 128; // px
        $TOP_MT = $STACK_H + 18; // margen superior de página (un pelín más alto que el header)
    @endphp

    <style>
        /* Usa SOLO este @page, elimina el anterior */
        @page {
            margin: {{ $isStack ? $TOP_MT . 'px 35px 90px 35px' : '110px 35px 90px 35px' }};
        }

        header {
            position: fixed;
            left: 0;
            right: 0;
            top: -{{ $isStack ? $STACK_H . 'px' : '90px' }};
            height: {{ $isStack ? $STACK_H . 'px' : '90px' }};
        }

        /* Ajustes de header apilado */
        header .stack * {
            margin: 0;
            line-height: 1.15;
        }

        header .stack .small {
            font-size: 11px;
            color: #6B7280;
        }

        header .stack .logo {
            height: {{ $hasLogo ? 48 : 0 }}px;
        }

        /* un poco más pequeño en apilado */
    </style>
    {{-- !copiar a STYLES por si se quiere poner marca de agua en el pdf generado
    /* --- capas para que el watermark quede detrás --- */
    header, footer { position: fixed; z-index: 10; } /* encima */
    main { position: relative; z-index: 1; } /* contenido */
    html, body { position: relative; } /* contexto */

    /* --- marca de agua centrada en todas las páginas --- */
    body::before {
    content: "";
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0; /* ocupa toda la página */
    background: url("{{ public_path('storage/' . $inventario->empresa->branding->logo_empresa) }}") no-repeat center
    center;
    background-size: contain; /* ajusta sin recortar */
    opacity: 0.08; /* atenúa la marca de agua */
    z-index: 0; /* por detrás del contenido */
    pointer-events: none; /* por si acaso */
    } 
    --}}
</head>

<body>

    {{-- ENCABEZADO --}}
    <header>
        @if ($headerLayout === 'logo_left' || $headerLayout === 'logo_right')
            <div class="h-row">
                @if ($headerLayout === 'logo_left')
                    <div class="h-left">
                        @if ($headerShowLogo && optional($inventario->empresa->branding)->logo_empresa)
                            <img class="logo" src="{{ public_path($inventario->empresa->branding->logo_empresa) }}"
                                alt="logo">
                        @endif
                    </div>
                    <div class="h-right" style="text-align:right;">
                        <h1>{{ $inventario->empresa->razon_social }}</h1>
                        <div class="muted">RUC: {{ $inventario->empresa->ruc }}</div>
                        <div class="muted">{{ $correo }} &nbsp;•&nbsp; {{ $celular }}</div>
                    </div>
                @else
                    <div class="h-left">
                        <h1>{{ $inventario->empresa->razon_social }}</h1>
                        <div class="muted">RUC: {{ $inventario->empresa->ruc }}</div>
                        <div class="muted">{{ $correo }} &nbsp;•&nbsp; {{ $celular }}</div>
                    </div>
                    <div class="h-right">
                        @if ($headerShowLogo && optional($inventario->empresa->branding)->logo_empresa)
                            <img class="logo" src="{{ public_path($inventario->empresa->branding->logo_empresa) }}"
                                alt="logo">
                        @endif
                    </div>
                @endif
            </div>
        @elseif ($headerLayout === 'stack_left' || $headerLayout === 'stack_right')
            <div class="stack" style="text-align: {{ $headerLayout === 'stack_right' ? 'right' : 'left' }};">
                @if ($headerShowLogo && optional($inventario->empresa->branding)->logo_empresa)
                    <img class="logo" src="{{ public_path($inventario->empresa->branding->logo_empresa) }}"
                        alt="logo">
                @endif
                <h1>{{ $inventario->empresa->razon_social }}</h1>
                <div class="small">RUC: {{ $inventario->empresa->ruc }}</div>
                <div class="small">{{ $correo }} &nbsp;•&nbsp; {{ $celular }}</div>
                <div class="small">{{ $lugar }}, {{ $fecha_texto }}</div>
            </div>
        @endif
    </header>


    {{-- PIE --}}
    <footer>
        <div class="foot-top">
            @if ($footerLayout === 'info_left_page_right')
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        {{-- info --}}
                        @if ($direccion)
                            {{ $direccion }}
                        @else
                            {{ $correo }} • {{ $celular }}
                        @endif
                        @php $webRaw = trim((string) ($web ?? '')); @endphp
                        @if ($webRaw !== '')
                            @php
                                $webUrl = preg_match('~^https?://~i', $webRaw) ? $webRaw : 'https://' . $webRaw;
                                $webText = parse_url($webUrl, PHP_URL_HOST) ?: $webUrl;
                            @endphp
                            &nbsp;•&nbsp;<a href="{{ $webUrl }}" target="_blank" rel="noopener noreferrer"
                                class="link">{{ $webText }}</a>
                        @endif
                    </div>
                    <div> Página <span class="page-num"></span> </div>
                </div>
            @elseif ($footerLayout === 'info_center')
                <div style="text-align:center;">
                    @if ($footerShowLogo && optional($inventario->empresa->branding)->logo_empresa)
                        <div><img src="{{ public_path($inventario->empresa->branding->logo_empresa) }}"
                                alt="logo" style="height:16px; margin-bottom:4px"></div>
                    @endif
                    <div>
                        @if ($direccion)
                            {{ $direccion }}
                        @else
                            {{ $correo }} • {{ $celular }}
                        @endif
                        @php $webRaw = trim((string) ($web ?? '')); @endphp
                        @if ($webRaw !== '')
                            @php
                                $webUrl = preg_match('~^https?://~i', $webRaw) ? $webRaw : 'https://' . $webRaw;
                                $webText = parse_url($webUrl, PHP_URL_HOST) ?: $webUrl;
                            @endphp
                            &nbsp;•&nbsp;<a href="{{ $webUrl }}" target="_blank" rel="noopener noreferrer"
                                class="link">{{ $webText }}</a>
                        @endif
                    </div>
                    <div> Página <span class="page-num"></span> </div>
                </div>
            @elseif ($footerLayout === 'logo_left_info_right' || $footerLayout === 'logo_right_info_left')
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    @if ($footerLayout === 'logo_left_info_right')
                        <div>
                            @if ($footerShowLogo && optional($inventario->empresa->branding)->logo_empresa)
                                <img src="{{ public_path($inventario->empresa->branding->logo_empresa) }}"
                                    alt="logo" style="height:16px; vertical-align:middle; margin-right:8px">
                            @endif
                        </div>
                        <div style="text-align:right; flex:1; margin-left:12px;">
                            @if ($direccion)
                                {{ $direccion }}
                            @else
                                {{ $correo }} • {{ $celular }}
                            @endif
                            @php $webRaw = trim((string) ($web ?? '')); @endphp
                            @if ($webRaw !== '')
                                @php
                                    $webUrl = preg_match('~^https?://~i', $webRaw) ? $webRaw : 'https://' . $webRaw;
                                    $webText = parse_url($webUrl, PHP_URL_HOST) ?: $webUrl;
                                @endphp
                                &nbsp;•&nbsp;<a href="{{ $webUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="link">{{ $webText }}</a>
                            @endif
                            &nbsp;&nbsp;|&nbsp;&nbsp; Página <span class="page-num"></span>
                        </div>
                    @else
                        {{-- logo_right_info_left --}}
                        <div style="flex:1; margin-right:12px;">
                            @if ($direccion)
                                {{ $direccion }}
                            @else
                                {{ $correo }} • {{ $celular }}
                            @endif
                            @php $webRaw = trim((string) ($web ?? '')); @endphp
                            @if ($webRaw !== '')
                                @php
                                    $webUrl = preg_match('~^https?://~i', $webRaw) ? $webRaw : 'https://' . $webRaw;
                                    $webText = parse_url($webUrl, PHP_URL_HOST) ?: $webUrl;
                                @endphp
                                &nbsp;•&nbsp;<a href="{{ $webUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="link">{{ $webText }}</a>
                            @endif
                            &nbsp;&nbsp;|&nbsp;&nbsp; Página <span class="page-num"></span>
                        </div>
                        <div>
                            @if ($footerShowLogo && optional($inventario->empresa->branding)->logo_empresa)
                                <img src="{{ public_path($inventario->empresa->branding->logo_empresa) }}"
                                    alt="logo" style="height:24px">
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </footer>

    {{-- CUERPO --}}
    <main>
        @if (!$isStack)
            <div class="mt-5 mb-3 text-right">{{ $lugar }}, {{ $fecha_texto }}</div>
        @endif


        <div class="mb-3">
            <strong>RUC:</strong> {{ $destinatario->ruc }}<br>
            <strong>RAZÓN SOCIAL:</strong> {{ $destinatario->razon_social }}
        </div>

        @if ($presentacion)
            {{-- COLOR DINÁMICO --}}
            <div class="mb-4"
                style="background:{{ $style['note_bg'] }}; padding:10px; border:1px solid {{ $style['note_border'] }};">
                {{ $presentacion }}
            </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th style="width:75%">Producto</th>
                    <th class="right">Precio {{ $simbolo }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inventario->productos as $p)
                    @php $precio = $tc==1 ? $p->precio_unitario : ($p->precio_unitario / $tc); @endphp
                    <tr>
                        <td>{{ $p->nombre }}</td>
                        <td class="right">{{ $simbolo }} {{ number_format($precio, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($cuentas->count())
            <div class="mb-2" style="margin-top:18px;"><strong>Cuentas para pagos</strong></div>
            <table>
                <thead>
                    <tr>
                        <th>Banco / Nombre</th>
                        <th>Moneda</th>
                        <th>N° Cuenta</th>
                        <th>CCI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cuentas as $c)
                        <tr>
                            <td>{{ $c->nombre }}</td>
                            <td>{{ $c->tipo == 1 ? '$' : 'S/' }}</td>
                            <td>{{ $c->nro_cuenta ?: '—' }}</td>
                            <td>{{ $c->nro_interbancario ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="signature">
            @if ($despedida)
                <div class="mb-2" style="margin-top:14px;">{{ $despedida }}</div>
            @endif

            <div class="sig-box">
                @if (optional($inventario->empresa->branding)->firma_imagen)
                    <img class="sig-image"
                        src="{{ public_path($inventario->empresa->branding->firma_imagen) }}"
                        alt="firma">
                @endif

                <div class="sig-line"></div>

                <div class="sig-name">
                    {{ $inventario->empresa->branding->firma_nombre ?? '-' }}
                </div>

                <div class="sig-title">
                    GERENTE GENERAL
                </div>

                <div class="sig-company">{{ $inventario->empresa->razon_social }}</div>
            </div>
        </div>

    </main>

</body>

</html>
