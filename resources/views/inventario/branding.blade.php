<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Branding de Empresas') }}
        </h2>
    </x-slot>

    {{-- ── Estilos personalizados ── --}}
    <style>
        /* ── Variables ── */
        :root {
            --brand-bg: #F8FAFC;
            --card-bg: #FFFFFF;
            --border: #E2E8F0;
            --accent: #6366F1;
            --accent-hover: #4F46E5;
            --text-main: #1E293B;
            --text-sub: #64748B;
            --success: #10B981;
            --radius: 14px;
        }

        body {
            background: var(--brand-bg);
        }

        /* ── Grid de tarjetas ── */
        .branding-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        /* ── Tarjeta ── */
        .brand-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            transition: box-shadow .25s, transform .2s;
            display: flex;
            flex-direction: column;
        }

        .brand-card:hover {
            box-shadow: 0 12px 32px rgba(99, 102, 241, .12);
            transform: translateY(-3px);
        }

        /* ── Encabezado de la tarjeta ── */
        .card-header {
            padding: 1rem 1.25rem .75rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .company-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366F1, #818CF8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            font-size: .85rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .company-avatar img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #fff;
        }

        .company-name {
            font-size: .95rem;
            font-weight: 600;
            color: var(--text-main);
            line-height: 1.3;
        }

        .company-meta {
            font-size: .75rem;
            color: var(--text-sub);
            margin-top: 1px;
        }

        /* ── Badge de color ── */
        .color-badge {
            margin-left: auto;
            flex-shrink: 0;
            padding: 2px 10px;
            border-radius: 99px;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            border: 1px solid;
        }

        /* ── Cuerpo ── */
        .card-body {
            padding: 1rem 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: .85rem;
        }

        /* ── Bloque de imagen ── */
        .img-block {
            display: flex;
            flex-direction: column;
            gap: .4rem;
        }

        .img-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--text-sub);
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .img-label svg {
            width: 13px;
            height: 13px;
        }

        .img-frame {
            background: #F1F5F9;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 90px;
            position: relative;
        }

        .img-frame img {
            max-width: 100%;
            max-height: 85px;
            object-fit: contain;
            padding: .5rem;
        }

        .img-empty {
            color: #CBD5E1;
            font-size: .8rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .3rem;
        }

        .img-empty svg {
            width: 26px;
            height: 26px;
        }

        /* ── Botón copiar encima de la imagen ── */
        .copy-btn {
            position: absolute;
            top: 6px;
            right: 6px;
            background: rgba(255, 255, 255, .9);
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: 4px 9px;
            font-size: .7rem;
            font-weight: 600;
            color: var(--accent);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: .3rem;
            transition: background .2s, color .2s;
            backdrop-filter: blur(4px);
        }

        .copy-btn:hover {
            background: var(--accent);
            color: #fff;
        }

        .copy-btn svg {
            width: 12px;
            height: 12px;
        }

        .copy-btn.copied {
            background: var(--success);
            color: #fff;
            border-color: var(--success);
        }

        /* ── Fila de chips de config ── */
        .config-row {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            margin-top: .25rem;
        }

        .cfg-chip {
            font-size: .68rem;
            padding: 2px 8px;
            border-radius: 6px;
            background: #F1F5F9;
            color: var(--text-sub);
            border: 1px solid var(--border);
            font-weight: 500;
        }

        /* ── Footer de tarjeta (firma) ── */
        .card-footer {
            padding: .75rem 1.25rem;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }

        .updated-info {
            font-size: .7rem;
            color: var(--text-sub);
        }

        /* ── Botón editar ── */
        .edit-btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: 5px 13px;
            border-radius: 8px;
            background: #EEF2FF;
            color: var(--accent);
            font-size: .75rem;
            font-weight: 600;
            border: 1px solid #C7D2FE;
            cursor: pointer;
            transition: background .2s, color .2s;
            text-decoration: none;
        }

        .edit-btn:hover {
            background: var(--accent);
            color: #fff;
        }

        .edit-btn svg {
            width: 13px;
            height: 13px;
        }

        /* ── Toast ── */
        #toast {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            background: #1E293B;
            color: #fff;
            padding: .7rem 1.2rem;
            border-radius: 10px;
            font-size: .82rem;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .2);
            transform: translateY(80px);
            opacity: 0;
            transition: all .3s cubic-bezier(.34, 1.56, .64, 1);
            z-index: 9999;
            pointer-events: none;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        #toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        /* ── Empty state ── */
        .empty-state {
            grid-column: 1/-1;
            text-align: center;
            padding: 3rem;
            color: var(--text-sub);
        }

        .empty-state svg {
            width: 48px;
            height: 48px;
            margin: 0 auto .75rem;
            opacity: .3;
        }

        /* ── Buscador ── */
        .search-bar {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: .55rem 1rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            width: 100%;
            max-width: 340px;
        }

        .search-bar input {
            border: none;
            outline: none;
            font-size: .88rem;
            width: 100%;
            color: var(--text-main);
            background: transparent;
        }

        .search-bar svg {
            color: var(--text-sub);
            flex-shrink: 0;
            width: 16px;
            height: 16px;
        }

        /* ── Dark Mode Overrides ── */
        .dark {
            --brand-bg: #22242a;
            --card-bg: #1a1c21;
            --border: #374151;
            --text-main: #f3f4f6;
            --text-sub: #9ca3af;
            --accent: #818cf8;
        }

        .dark .brand-card {
            background: var(--card-bg);
            border-color: var(--border);
        }

        .dark .card-header,
        .dark .card-footer {
            border-color: var(--border);
        }

        .dark .img-frame,
        .dark .cfg-chip,
        .dark .search-bar {
            background-color: #2a2d35;
            border-color: var(--border);
        }

        .dark .copy-btn {
            background: rgba(30, 41, 59, 0.8);
            border-color: var(--border);
            color: var(--accent);
        }

        .dark .edit-btn {
            background-color: #312e81;
            color: #e0e7ff;
            border-color: #4338ca;
        }

        .dark .edit-btn:hover {
            background-color: #4338ca;
        }

        .dark .card-header .color-badge {
            /* Maintain readability for color badges in dark mode */
            filter: brightness(1.1);
        }

        .dark .company-avatar img {
            background: #1a1c21;
        }
    </style>

    <div class="py-10">
        <div class="w-full px-4 max-w-7xl mx-auto">

            <x-success-error-bagdes />

            {{-- ── Barra superior ── --}}
            <div class="flex flex-wrap items-center gap-3 mb-6">
                {{-- !VOLVER ATRÁS CON CACHE --}}
                <button onclick="window.history.back()"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg shadow text-sm font-medium">
                    ← Atrás
                </button>

                <div class="search-bar">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <input type="text" id="searchInput" placeholder="Buscar empresa…"
                        oninput="filterCards(this.value)">
                </div>

                <span id="countLabel" class="text-sm text-gray-500 ml-auto">
                    {{ count($brandings) }} registros
                </span>
            </div>

            {{-- ── Grid ── --}}
            <div class="branding-grid" id="brandingGrid">

                @forelse ($brandings as $b)
                    @php
                        $empresa = $b->empresa;
                        $nombre = $empresa->razon_social ?? '—';
                        $ruc = $empresa->ruc ?? '';
                        $initials = strtoupper(substr($nombre, 0, 2));
                        $color = $b->color ?? 'gray';

                        // Colores de badge según la variable guardada
                        $colorMap = [
                            'blue' => ['bg' => '#EFF6FF', 'border' => '#BFDBFE', 'text' => '#1D4ED8'],
                            'sky' => ['bg' => '#E0F2FE', 'border' => '#BAE6FD', 'text' => '#0369A1'],
                            'emerald' => ['bg' => '#D1FAE5', 'border' => '#6EE7B7', 'text' => '#065F46'],
                            'teal' => ['bg' => '#CCFBF1', 'border' => '#5EEAD4', 'text' => '#0F766E'],
                            'amber' => ['bg' => '#FEF3C7', 'border' => '#FDE68A', 'text' => '#92400E'],
                            'orange' => ['bg' => '#FFEDD5', 'border' => '#FED7AA', 'text' => '#9A3412'],
                            'red' => ['bg' => '#FEE2E2', 'border' => '#FCA5A5', 'text' => '#991B1B'],
                            'violet' => ['bg' => '#EDE9FE', 'border' => '#C4B5FD', 'text' => '#5B21B6'],
                            'fuchsia' => ['bg' => '#F5D0FE', 'border' => '#E879F9', 'text' => '#86198F'],
                            'gray' => ['bg' => '#F3F4F6', 'border' => '#D1D5DB', 'text' => '#374151'],
                        ];
                        $c = $colorMap[$color] ?? $colorMap['gray'];

                        $logoUrl = $b->logo_empresa ? asset($b->logo_empresa) : null;
                        $firmaUrl = $b->firma_imagen ? asset($b->firma_imagen) : null;

                        $updatedBy = $b->usuario?->name ?? null;
                        $updatedAt = $b->updated_at ? $b->updated_at->format('d/m/Y H:i') : null;

                        $colorLabels = [
                            'blue' => 'Azul Marino',
                            'sky' => 'Azul Cielo',
                            'emerald' => 'Verde Esmeralda',
                            'teal' => 'Verde Teal',
                            'amber' => 'Ámbar',
                            'orange' => 'Naranja',
                            'red' => 'Rojo',
                            'violet' => 'Morado',
                            'fuchsia' => 'Fucsia',
                            'gray' => 'Gris',
                        ];
                        $fuente = $b->fuente ?? 'DejaVu Sans';
                        $headerLayout = $b->header_layout ?? '—';
                        $footerLayout = $b->footer_layout ?? '—';
                    @endphp

                    <div class="brand-card" data-name="{{ strtolower($nombre) }}">

                        {{-- ── Encabezado ── --}}
                        <div class="card-header">
                            <div class="company-avatar">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $nombre }}">
                                @else
                                    {{ $initials }}
                                @endif
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div class="company-name" title="{{ $nombre }}">
                                    {{ Str::limit($nombre, 32) }}
                                </div>
                                @if ($ruc)
                                    <div class="company-meta">RUC {{ $ruc }}</div>
                                @endif
                            </div>
                            <span class="color-badge"
                                style="background:{{ $c['bg'] }};border-color:{{ $c['border'] }};color:{{ $c['text'] }}">
                                {{ $colorLabels[$color] ?? $color }}
                            </span>
                        </div>

                        {{-- ── Cuerpo ── --}}
                        <div class="card-body">

                            {{-- Logo --}}
                            <div class="img-block">
                                <div class="img-label">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <path d="m21 15-5-5L5 21" />
                                    </svg>
                                    Logo
                                </div>
                                <div class="img-frame">
                                    @if ($logoUrl)
                                        <img src="{{ $logoUrl }}" alt="Logo {{ $nombre }}">
                                        <button class="copy-btn" title="Ver logo"
                                            onclick="window.open('{{ $logoUrl }}', '_blank')">
                                            <svg fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Ver
                                        </button>
                                    @else
                                        <div class="img-empty">
                                            <svg fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M3 9a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 10.07 4h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 18.07 7H19a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                                <circle cx="12" cy="13" r="3" />
                                            </svg>
                                            Sin logo
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Firma --}}
                            <div class="img-block">
                                <div class="img-label">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12 20h9" />
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                                    </svg>
                                    Firma
                                    @if ($b->firma_nombre)
                                        <span
                                            style="font-weight:500;text-transform:none;letter-spacing:0;font-size:.72rem;color:#94A3B8;">
                                            — {{ $b->firma_nombre }}
                                        </span>
                                    @endif
                                </div>
                                <div class="img-frame">
                                    @if ($firmaUrl)
                                        <img src="{{ $firmaUrl }}" alt="Firma {{ $nombre }}">
                                        <button class="copy-btn" title="Ver firma"
                                            onclick="window.open('{{ $firmaUrl }}', '_blank')">
                                            <svg fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Ver
                                        </button>
                                    @else
                                        <div class="img-empty">
                                            <svg fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                                            </svg>
                                            Sin firma
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ── Footer ── --}}
                        <div class="card-footer">
                            <div class="updated-info">
                                @if ($updatedBy || $updatedAt)
                                    <span>Actualizado por <strong>{{ $updatedBy ?? '—' }}</strong></span><br>
                                    <span>{{ $updatedAt }}</span>
                                @else
                                    <span>Sin actualizaciones</span>
                                @endif
                            </div>

                        </div>

                    </div>
                @empty
                    <div class="empty-state">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path
                                d="M3 9a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 10.07 4h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 18.07 7H19a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        </svg>
                        <p class="font-semibold text-gray-500">No hay registros de branding todavía.</p>
                    </div>
                @endforelse

            </div>{{-- /grid --}}
        </div>
    </div>

    {{-- ── Toast ── --}}
    <div id="toast">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="20 6 9 17 4 12" />
        </svg>
        <span id="toastMsg">URL copiada</span>
    </div>

    <script>
        /* ── Copiar URL ── */
        function copyUrl(url, btn) {
            navigator.clipboard.writeText(url).then(() => {
                btn.classList.add('copied');
                btn.innerHTML = `
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:12px;height:12px;">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg> Copiado`;
                showToast('URL copiada al portapapeles');
                setTimeout(() => {
                    btn.classList.remove('copied');
                    btn.innerHTML = `
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:12px;height:12px;">
                            <rect x="9" y="9" width="13" height="13" rx="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg> Copiar URL`;
                }, 2500);
            }).catch(() => {
                // Fallback
                const ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast('URL copiada');
            });
        }

        /* ── Toast ── */
        let toastTimer;

        function showToast(msg) {
            const t = document.getElementById('toast');
            document.getElementById('toastMsg').textContent = msg;
            t.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => t.classList.remove('show'), 2500);
        }

        /* ── Filtro búsqueda ── */
        function filterCards(q) {
            const term = q.toLowerCase().trim();
            const cards = document.querySelectorAll('.brand-card');
            let visible = 0;
            cards.forEach(c => {
                const match = c.dataset.name.includes(term);
                c.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            document.getElementById('countLabel').textContent = `${visible} registro${visible !== 1 ? 's' : ''}`;
        }
    </script>

</x-app-layout>
