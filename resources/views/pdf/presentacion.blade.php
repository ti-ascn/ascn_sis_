<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>PRESENTACIÓN - {{ strtoupper($inventario->empresa->razon_social) }}</title>

    @php
        $branding = optional($inventario->empresa->branding);

        $tieneImagenFondo = !empty($imagenFondoBase64);

        // Determinar dónde va info_empresa y lugar_info
        $infoEmpresaEnCabecera =
            !$tieneImagenFondo && $mostrarDatos && in_array($infoEmpresa, ['cabecera_1', 'cabecera_2']);
        $lugarInfoEnCabecera =
            !$tieneImagenFondo && $mostrarDatos && in_array($lugarInfo, ['cabecera_1', 'cabecera_2']);
        $infoEmpresaEnPie =
            !$tieneImagenFondo && $mostrarDatos && in_array($infoEmpresa, ['pie_pagina_1', 'pie_pagina_2']);
        $lugarInfoEnPie = !$tieneImagenFondo && $mostrarDatos && in_array($lugarInfo, ['pie_pagina_1', 'pie_pagina_2']);

        $hayHeader = $infoEmpresaEnCabecera || $lugarInfoEnCabecera;
        $hayFooter = $infoEmpresaEnPie || $lugarInfoEnPie;

        $headerH = $hayHeader ? 70 : 0;

        // Márgenes en mm → usados directamente en @page
        $mTop = $margenTop ?? 25;
        $mBottom = $margenBottom ?? 25;
        $mLeft = $margenLeft ?? 25;
        $mRight = $margenRight ?? 25;

        // Cuando hay imagen de fondo el margen real lo maneja el content-wrapper
        // para que la imagen cubra toda la hoja.
        $pageMarginTop = $tieneImagenFondo ? 0 : $mTop;
        $pageMarginBottom = $tieneImagenFondo ? 0 : $mBottom;
        $pageMarginLeft = $tieneImagenFondo ? 0 : $mLeft;
        $pageMarginRight = $tieneImagenFondo ? 0 : $mRight;

        // Colores de acento
        $colorAcento = match ($branding->color ?? 'gray') {
            'blue' => '#1E3A5F',
            'sky' => '#0369A1',
            'emerald' => '#065F46',
            'teal' => '#0F5C55',
            'amber' => '#92400E',
            'orange' => '#9A3412',
            'red' => '#991B1B',
            'violet' => '#4C1D95',
            'fuchsia' => '#86198F',
            default => '#1F2937',
        };

        // Variante más clara del color de acento para líneas sutiles
        $colorLight = match ($branding->color ?? 'gray') {
            'blue' => '#BFDBFE',
            'sky' => '#BAE6FD',
            'emerald' => '#A7F3D0',
            'teal' => '#99F6E4',
            'amber' => '#FDE68A',
            'orange' => '#FED7AA',
            'red' => '#FCA5A5',
            'violet' => '#DDD6FE',
            'fuchsia' => '#F0ABFC',
            default => '#D1D5DB',
        };

        $fechaAlign = $fecha_alineacion ?? 'right';
        $tipoDestDisplay = $tipoDestinatario ?? 'cargo_subtitulo';

        // ── Precomputar qué va en cada columna del header ─────────────────────
        // info_empresa: cabecera_1 = RUC izq / Logo der  |  cabecera_2 = Logo izq / RUC der
        // lugar_info:   cabecera_1 = Dir izq / Correo der  |  cabecera_2 = Correo izq / Dir der

        $logoUrl = $branding->logo_empresa ? public_path($branding->logo_empresa) : null;

        // Columna izquierda del header
        // info_empresa
        $hdrLeft_empresa = null;
        $hdrRight_empresa = null;
        if ($infoEmpresaEnCabecera) {
            if ($infoEmpresa === 'cabecera_1') {
                // RUC izq / Logo der
                $hdrLeft_empresa = 'ruc';
                $hdrRight_empresa = 'logo';
            } else {
                // Logo izq / RUC der
                $hdrLeft_empresa = 'logo';
                $hdrRight_empresa = 'ruc';
            }
        }
        // lugar_info
        $hdrLeft_lugar = null;
        $hdrRight_lugar = null;
        if ($lugarInfoEnCabecera) {
            if ($lugarInfo === 'cabecera_1') {
                // Dir izq / Correo der
                $hdrLeft_lugar = 'direccion';
                $hdrRight_lugar = 'correo';
            } else {
                // Correo izq / Dir der
                $hdrLeft_lugar = 'correo';
                $hdrRight_lugar = 'direccion';
            }
        }

        // ── Precomputar footer ────────────────────────────────────────────────
        // info_empresa: pie_pagina_1 = RUC izq / Logo der  |  pie_pagina_2 = Logo izq / RUC der
        // lugar_info:   pie_pagina_1 = Dir izq / Correo der  |  pie_pagina_2 = Correo izq / Dir der
        $logoUrl2 = $branding->logo_empresa ? public_path($branding->logo_empresa) : null;

        $ftrLeft_empresa = null;
        $ftrRight_empresa = null;
        if ($infoEmpresaEnPie) {
            if ($infoEmpresa === 'pie_pagina_1') {
                $ftrLeft_empresa = 'ruc';
                $ftrRight_empresa = 'logo';
            } else {
                $ftrLeft_empresa = 'logo';
                $ftrRight_empresa = 'ruc';
            }
        }
        $ftrLeft_lugar = null;
        $ftrRight_lugar = null;
        if ($lugarInfoEnPie) {
            if ($lugarInfo === 'pie_pagina_1') {
                $ftrLeft_lugar = 'direccion';
                $ftrRight_lugar = 'correo';
            } else {
                $ftrLeft_lugar = 'correo';
                $ftrRight_lugar = 'direccion';
            }
        }
    @endphp

    <style>
        @page {
            size: A4 portrait;
            margin-top: {{ $pageMarginTop }}mm;
            margin-bottom: {{ $pageMarginBottom }}mm;
            margin-left: {{ $pageMarginLeft }}mm;
            margin-right: {{ $pageMarginRight }}mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: '{{ $style['font_family'] }}', 'DejaVu Sans', sans-serif;
            font-size: 10.5pt;
            color: #1F2937;
            line-height: 1.55;
            margin: 0;
            padding: 0;
        }

        @if ($tieneImagenFondo)
            body {
                background-image: url("{{ $imagenFondoBase64 }}");
                background-size: 210mm 297mm;
                background-position: top left;
                background-repeat: no-repeat;
            }
        @endif

        /* ─── Marca de agua ─────────────────────────────────────────── */
        @if ($marcaAgua && $logoBase64)
            .watermark {
                position: fixed;
                top: 50%;
                left: 50%;
                width: 140mm;
                transform: translate(-70%, -70%);
                opacity: 0.07;
                z-index: -1;
            }
        @endif

        /* ─── Header fijo ───────────────────────────────────────────── */
        header {
            position: fixed;
            top: -{{ $headerH + 5 }}px;
            left: 0;
            right: 0;
            height: {{ $headerH }}px;
            padding: 0 {{ $mLeft }}mm {{ $hayHeader ? '8px' : '0' }} {{ $mLeft }}mm;
        }

        /* ─── Footer fijo ───────────────────────────────────────────── */
        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            padding: 6px {{ $mRight }}mm 0 {{ $mLeft }}mm;
            font-size: 8.5pt;
            color: #6B7280;
            border-top: {{ $hayFooter ? '1px solid #E5E7EB' : 'none' }};
        }

        /* ─── Utilidades de layout ──────────────────────────────────── */
        .row-table {
            display: table;
            width: 100%;
        }

        .col-left {
            display: table-cell;
            vertical-align: middle;
        }

        .col-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .logo {
            height: 40px;
            max-width: 180px;
            object-fit: contain;
        }

        .logo-footer {
            height: 22px;
            max-width: 120px;
            object-fit: contain;
            vertical-align: middle;
        }

        .muted {
            color: #6B7280;
            font-size: 8.5pt;
            line-height: 1.4;
        }

        /* ─── Contenido principal ───────────────────────────────────── */
        .content-wrapper {
            @if ($tieneImagenFondo)
                margin: {{ $mTop }}mm {{ $mRight }}mm {{ $mBottom }}mm {{ $mLeft }}mm;
            @endif
        }

        /* ─── Fecha y lugar ─────────────────────────────────────────── */
        .fecha-bloque {
            text-align: {{ $fechaAlign }};
            margin-bottom: 32px;
        }

        .fecha-ciudad {
            font-size: 10pt;
            color: #374151;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .fecha-valor {
            font-size: 10pt;
            color: {{ $colorAcento }};
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: 1px;
        }

        .fecha-linea {
            width: 60px;
            border: none;
            border-top: 2px solid {{ $colorAcento }};
            margin: 6px 0 0 auto;
        }

        /* ─── Destinatario ──────────────────────────────────────────── */
        .destinatario-bloque {
            margin-bottom: 28px;
        }

        .destinatario-nombre {
            font-size: 13pt;
            font-weight: 700;
            color: #111827;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .destinatario-cargo {
            font-size: 8.5pt;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        .destinatario-nombre-normal {
            font-size: 13pt;
            font-weight: 400;
            color: #111827;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .destinatario-cargo-bold {
            font-size: 8.5pt;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        .destinatario-estimado {
            font-size: 10pt;
            color: #374151;
            margin-bottom: 6px;
        }

        .destinatario-linea {
            border: none;
            border-top: 1px solid {{ $colorLight }};
            margin: 10px 0 0 0;
        }

        /* ─── Cuerpo del texto ──────────────────────────────────────── */
        .saludo {
            font-size: 10.5pt;
            color: #374151;
            margin-bottom: 14px;
        }

        .cuerpo {
            text-align: justify;
            font-size: 10.5pt;
            line-height: 1.65;
            color: #1F2937;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .despedida {
            margin-top: 28px;
            font-size: 10.5pt;
            color: #374151;
            white-space: pre-wrap;
            word-break: break-word;
        }

        /* ─── Firma ─────────────────────────────────────────────────── */
        .signature {
            margin-top: 48px;
            text-align: center;
        }

        .sig-image {
            height: 65px;
            margin: 0 auto 4px auto;
            object-fit: contain;
            display: block;
        }

        .sig-line {
            border: none;
            border-top: 1.5px solid {{ $colorAcento }};
            width: 260px;
            margin: 8px auto 6px auto;
        }

        .sig-name {
            font-weight: 700;
            font-size: 10.5pt;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sig-title {
            font-size: 8.5pt;
            color: {{ $colorAcento }};
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 1px;
        }

        .sig-company {
            font-size: 8.5pt;
            color: #6B7280;
            margin-top: 1px;
        }

        .link {
            color: #2563EB;
            text-decoration: none;
            font-size: 8.5pt;
            line-height: 1.4;
        }
    </style>
</head>

<body>

    {{-- ── Marca de agua ────────────────────────────────────────────────── --}}
    @if ($marcaAgua && $logoBase64)
        <img class="watermark" src="{{ $logoBase64 }}" alt="marca de agua">
    @endif

    {{-- ── Header fijo ─────────────────────────────────────────────────── --}}
    <header>
        @if ($hayHeader)
            <div class="row-table">
                {{-- COLUMNA IZQUIERDA --}}
                <div class="col-left">
                    {{-- Info empresa: lo que le toca a la izquierda --}}
                    @if ($hdrLeft_empresa === 'ruc')
                        <div style="font-weight:700;font-size:10pt;">{{ $inventario->empresa->razon_social }}</div>
                        <div class="muted">RUC: {{ $inventario->empresa->ruc }}</div>
                        @if (!empty($celular))
                            <div class="muted">{{ $celular }}</div>
                        @endif
                        @if (!empty($web))
                            <div class="link">{{ $web }}</div>
                        @endif
                    @elseif ($hdrLeft_empresa === 'logo')
                        @if ($logoUrl)
                            <img class="logo" src="{{ $logoUrl }}" alt="logo">
                        @endif
                    @endif
                    {{-- Lugar info: lo que le toca a la izquierda --}}
                    @if ($hdrLeft_lugar === 'direccion')
                        <div class="muted">{{ $direccion }}</div>
                    @elseif ($hdrLeft_lugar === 'correo')
                        <div class="muted">{{ $correo }}</div>
                    @endif
                </div>

                {{-- COLUMNA DERECHA --}}
                <div class="col-right">
                    {{-- Info empresa: lo que le toca a la derecha --}}
                    @if ($hdrRight_empresa === 'logo')
                        @if ($logoUrl)
                            <img class="logo" src="{{ $logoUrl }}" alt="logo">
                        @endif
                    @elseif ($hdrRight_empresa === 'ruc')
                        <div style="font-weight:700;font-size:10pt;">{{ $inventario->empresa->razon_social }}</div>
                        <div class="muted">RUC: {{ $inventario->empresa->ruc }}</div>
                        @if (!empty($celular))
                            <div class="muted">{{ $celular }}</div>
                        @endif
                        @if (!empty($web))
                            <div class="link">{{ $web }}</div>
                        @endif
                    @endif
                    {{-- Lugar info: lo que le toca a la derecha --}}
                    @if ($hdrRight_lugar === 'correo')
                        <div class="muted">{{ $correo }}</div>
                    @elseif ($hdrRight_lugar === 'direccion')
                        <div class="muted">{{ $direccion }}</div>
                    @endif
                </div>
            </div>
        @endif
    </header>

    {{-- ── Footer fijo ─────────────────────────────────────────────────── --}}
    <footer>
        @if ($hayFooter)
            <div class="row-table">
                {{-- COLUMNA IZQUIERDA --}}
                <div class="col-left">
                    {{-- Info empresa izquierda --}}
                    @if ($ftrLeft_empresa === 'ruc')
                        <span style="font-weight:600;">{{ $inventario->empresa->razon_social }}</span>
                        <span class="muted"> | RUC: {{ $inventario->empresa->ruc }}</span>
                        @if (!empty($celular))
                            <span class="muted"> | {{ $celular }}</span>
                        @endif
                        @if (!empty($web))
                            <span class="link"> | {{ $web }}</span>
                        @endif
                    @elseif ($ftrLeft_empresa === 'logo')
                        @if ($logoUrl2)
                            <img class="logo-footer" src="{{ $logoUrl2 }}" alt="logo">
                        @endif
                    @endif
                    {{-- Lugar info izquierda --}}
                    @if ($ftrLeft_lugar === 'direccion')
                        <span>{{ $direccion }}</span>
                    @elseif ($ftrLeft_lugar === 'correo')
                        <span>{{ $correo }}</span>
                    @endif
                </div>

                {{-- COLUMNA DERECHA --}}
                <div class="col-right">
                    {{-- Info empresa derecha --}}
                    @if ($ftrRight_empresa === 'logo')
                        @if ($logoUrl2)
                            <img class="logo-footer" src="{{ $logoUrl2 }}" alt="logo">
                        @endif
                    @elseif ($ftrRight_empresa === 'ruc')
                        <span style="font-weight:600;">{{ $inventario->empresa->razon_social }}</span>
                        <span class="muted"> | RUC: {{ $inventario->empresa->ruc }}</span>
                        @if (!empty($celular))
                            <span class="muted"> | {{ $celular }}</span>
                        @endif
                        @if (!empty($web))
                            <span class="link"> | {{ $web }}</span>
                        @endif
                    @endif
                    {{-- Lugar info derecha --}}
                    @if ($ftrRight_lugar === 'correo')
                        <span>{{ $correo }}</span>
                    @elseif ($ftrRight_lugar === 'direccion')
                        <span>{{ $direccion }}</span>
                    @endif
                </div>
            </div>
        @endif
    </footer>

    {{-- ── Contenido principal ──────────────────────────────────────────── --}}
    <div class="content-wrapper">

        {{-- Fecha con alineación y formato dinámicos --}}
        <div class="fecha-bloque">
            <div class="fecha-valor">{!! $fecha_html !!}</div>
        </div>

        {{-- Destinatario con tipo dinámico --}}
        <div class="destinatario-bloque">
            @switch($tipoDestDisplay)
                @case('cargo_subtitulo')
                    <div class="destinatario-nombre">{{ $destinatario->razon_social }}</div>
                    <div class="destinatario-cargo">Gerente General &middot; Presente</div>
                @break

                @case('cargo_arriba_nombre_abajo')
                    <div class="destinatario-cargo">GERENTE GENERAL &middot; PRESENTE</div>
                    <div class="destinatario-nombre" style="margin-top:4px;">{{ $destinatario->razon_social }}</div>
                @break

                @case('solo_cargo')
                    <div class="destinatario-cargo" style="font-size:10pt;letter-spacing:1.5px;">GERENTE GENERAL</div>
                @break

                @case('estimado_cargo_nombre')
                    <div class="destinatario-estimado">Estimado:</div>
                    <div class="destinatario-cargo">GERENTE GENERAL</div>
                    <div class="destinatario-nombre" style="margin-top:4px;">{{ $destinatario->razon_social }}</div>
                @break

                @case('nombre_bold_cargo_normal')
                    <div class="destinatario-nombre">{{ $destinatario->razon_social }}</div>
                    <div class="destinatario-cargo" style="font-weight:400;">Gerente General &middot; Presente</div>
                @break

                @case('nombre_normal_cargo_bold')
                    <div class="destinatario-nombre-normal">{{ $destinatario->razon_social }}</div>
                    <div class="destinatario-cargo-bold">GERENTE GENERAL &middot; PRESENTE</div>
                @break

                @default
                    <div class="destinatario-nombre">{{ $destinatario->razon_social }}</div>
                    <div class="destinatario-cargo">Gerente General &middot; Presente</div>
            @endswitch
            {{-- <hr class="destinatario-linea"> --}}
        </div>

        {{-- Saludo (solo si el texto NO empieza ya con saludo) --}}
        @php
            $textoInicia = mb_strtolower(ltrim($texto_presentacion ?? ''));
            $tieneSaludo =
                str_starts_with($textoInicia, 'estimad') ||
                str_starts_with($textoInicia, 'de nuestra') ||
                str_starts_with($textoInicia, 'a quien');
        @endphp
        @if (!$tieneSaludo && $texto_presentacion)
            <div class="saludo">De nuestra consideración:</div>
        @endif

        {{-- Cuerpo --}}
        @if ($texto_presentacion)
            <div class="cuerpo">{!! $texto_presentacion !!}</div>
        @endif

        {{-- Despedida --}}
        @if ($texto_despedida)
            <div class="despedida">{!! $texto_despedida !!}</div>
        @endif

        {{-- Firma --}}
        <div class="signature">
            @if ($mostrarInfoFirma && $branding->firma_imagen)
                <img class="sig-image" src="{{ public_path($branding->firma_imagen) }}" alt="firma">
            @endif

            @if ($mostrarInfoFirma)
                <hr class="sig-line">
                <div class="sig-name">{{ $branding->firma_nombre ?? '____________________' }}</div>
                <div class="sig-title">Gerente General</div>
                <div class="sig-company">{{ $inventario->empresa->razon_social }}</div>
            @endif
        </div>

    </div>

</body>

</html>
