<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Programación de Transporte') }}
            </h2>
            <a href="{{ route('choferes.index') }}"
               class="flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-850 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-350 text-sm px-3.5 py-2 rounded-lg transition font-medium">
                <i class="bi bi-arrow-left"></i> Volver a Choferes
            </a>
        </div>
    </x-slot>

    {{-- Estilos del Calendario --}}
    <style>
        /* ── Variables ── */
        :root {
            --cal-accent:     #2563eb;
            --cal-accent-lt:  #eff6ff;
            --cal-accent-md:  #bfdbfe;
            --cal-accent-txt: #1e40af;
            --cal-prog:       #10b981;
            --cal-prog-lt:    #ecfdf5;
            --cal-prog-md:    #a7f3d0;
            --cal-prog-txt:   #065f46;
            --cal-border:     #e5e7eb;
            --cal-radius:     12px;
            --cal-day-sel:    #2563eb;
            --cal-day-sel-lt: #eff6ff;
        }

        .dark {
            --cal-border:     #374151;
            --cal-accent-lt:  rgba(37, 99, 235, 0.15);
            --cal-accent-md:  rgba(37, 99, 235, 0.3);
            --cal-accent-txt: #93c5fd;
            --cal-prog-lt:    rgba(16, 185, 129, 0.15);
            --cal-prog-md:    rgba(16, 185, 129, 0.3);
            --cal-prog-txt:   #34d399;
            --cal-day-sel-lt: rgba(37, 99, 235, 0.15);
        }

        /* ── Layout de los 12 meses ── */
        .cal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 1.5rem;
        }

        /* ── Tarjeta de cada mes ── */
        .cal-card {
            background: #fff;
            border: 1px solid var(--cal-border);
            border-radius: var(--cal-radius);
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dark .cal-card {
            background: #22242a;
        }
        .cal-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .dark .cal-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.25);
        }

        .cal-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1rem;
            background: #f9fafb;
            border-bottom: 1px solid var(--cal-border);
        }
        .dark .cal-card-header {
            background: #1c1e22;
        }
        .cal-card-header.has-prog {
            background: var(--cal-prog-lt);
            border-bottom-color: var(--cal-prog-md);
        }
        .cal-month-name {
            font-weight: 700;
            font-size: 0.9rem;
            color: #111827;
            text-transform: capitalize;
        }
        .dark .cal-month-name {
            color: #e5e7eb;
        }
        .cal-card-header.has-prog .cal-month-name {
            color: var(--cal-prog-txt);
        }

        .badge-prog {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 99px;
            background: var(--cal-prog-md);
            color: var(--cal-prog-txt);
        }

        /* ── Mini calendario ── */
        .mini-cal {
            padding: 0.75rem;
        }
        .mini-cal-head {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-size: 0.7rem;
            font-weight: 750;
            color: #9ca3af;
            margin-bottom: 6px;
        }
        .mini-cal-body {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 3px;
        }
        .mini-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.75rem;
            color: #374151;
            cursor: pointer;
            transition: all 0.15s;
            user-select: none;
        }
        .dark .mini-day {
            color: #d1d5db;
        }
        .mini-day:hover {
            background: var(--cal-accent-md);
            color: var(--cal-accent-txt);
        }
        .mini-day.empty {
            cursor: default;
            pointer-events: none;
            opacity: 0.15;
        }
        .mini-day.programado {
            background: var(--cal-day-sel);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
        }
        .mini-day.today-marker {
            outline: 2px solid var(--cal-accent);
            outline-offset: -2px;
        }

        /* ── Acciones del mes ── */
        .cal-actions {
            display: flex;
            gap: 0.5rem;
            padding: 0 0.75rem 0.75rem;
        }
        .btn-cal {
            flex: 1;
            padding: 7px 0;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .btn-cal-primary {
            background: var(--cal-accent);
            color: #fff;
            border-color: var(--cal-accent);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.15);
        }
        .btn-cal-primary:hover {
            background: #1d4ed8;
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.25);
        }
        .btn-cal-danger {
            background: transparent;
            color: #ef4444;
            border-color: #fee2e2;
        }
        .dark .btn-cal-danger {
            border-color: rgba(239, 68, 68, 0.2);
        }
        .btn-cal-danger:hover {
            background: #fef2f2;
        }
        .dark .btn-cal-danger:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        /* ── Modal ── */
        #prog-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 9998;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        #prog-modal-overlay.open {
            display: flex;
        }

        #prog-modal {
            background: #fff;
            border-radius: 16px;
            width: 440px;
            max-width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 1.75rem;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            animation: modalScale 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .dark #prog-modal {
            background: #22242a;
            border: 1px solid #374151;
            color: #fff;
        }

        @keyframes modalScale {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        #prog-modal h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #111827;
        }
        .dark #prog-modal h3 {
            color: #f3f4f6;
        }

        .day-picker-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
            margin-bottom: 1rem;
        }
        .dp-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid transparent;
            background: #f3f4f6;
            color: #374151;
            transition: all 0.12s;
            user-select: none;
        }
        .dark .dp-day {
            background: #2e323b;
            color: #d1d5db;
        }
        .dp-day:hover {
            border-color: var(--cal-accent-md);
            background: var(--cal-accent-lt);
        }
        .dp-day.selected {
            background: var(--cal-accent);
            color: #fff;
            border-color: var(--cal-accent);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
        }
        .mini-day.feriado {
            color: #dc2626 !important;
            font-weight: 750;
            background: #fee2e2;
        }
        .dark .mini-day.feriado {
            color: #f87171 !important;
            background: #451a1a;
        }
        .mini-day.feriado.programado {
            background: #dc2626 !important;
            color: #fff !important;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.25);
        }
        .dark .mini-day.feriado.programado {
            background: #ef4444 !important;
            color: #fff !important;
        }
        .dp-day.feriado {
            color: #dc2626 !important;
            font-weight: 750;
            background: #fee2e2;
        }
        .dark .dp-day.feriado {
            color: #f87171 !important;
            background: #451a1a;
        }
        .dp-day.feriado:hover {
            border-color: #f87171 !important;
            background: #fecaca !important;
        }
        .dark .dp-day.feriado:hover {
            background: #5c2424 !important;
        }
        .dp-day.feriado.selected {
            background: #dc2626 !important;
            color: #fff !important;
            border-color: #dc2626 !important;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.4);
        }
        .dark .dp-day.feriado.selected {
            background: #ef4444 !important;
            color: #fff !important;
            border-color: #ef4444 !important;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.4);
        }
        .dp-day.disabled {
            opacity: 0;
            pointer-events: none;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1.25rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #9ca3af;
            line-height: 1;
            transition: color 0.15s;
        }
        .modal-close:hover {
            color: #374151;
        }
        .dark .modal-close:hover {
            color: #d1d5db;
        }

        /* ── Resumen de días seleccionados ── */
        #sel-summary {
            font-size: 0.82rem;
            color: var(--cal-accent-txt);
            background: var(--cal-accent-lt);
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 1rem;
            min-height: 2.25rem;
            border: 1px solid rgba(37, 99, 235, 0.15);
            font-weight: 600;
        }

        /* ── Selector de año ── */
        .year-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f3f4f6;
            padding: 4px;
            border-radius: 8px;
            border: 1px solid var(--cal-border);
        }
        .dark .year-nav {
            background: #1c1e22;
        }
        .year-nav button {
            background: #fff;
            border: 1px solid var(--cal-border);
            border-radius: 6px;
            padding: 4px 10px;
            cursor: pointer;
            font-size: 0.85rem;
            color: #374151;
            transition: all 0.15s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .dark .year-nav button {
            background: #2e323b;
            color: #d1d5db;
        }
        .year-nav button:hover {
            background: #f9fafb;
        }
        .dark .year-nav button:hover {
            background: #373c47;
        }
        .year-nav .year-label {
            font-weight: 700;
            font-size: 0.95rem;
            min-width: 55px;
            text-align: center;
            color: #111827;
        }
        .dark .year-nav .year-label {
            color: #f3f4f6;
        }
    </style>

    <div class="py-10 bg-gray-50 dark:bg-[#1a1c21] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ── Alertas ── --}}
            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900/30 text-green-800 dark:text-green-400 rounded-xl px-4 py-3 text-sm flex items-center gap-2 shadow-sm">
                    <i class="bi bi-check-circle-fill text-lg text-green-500"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/30 text-red-800 dark:text-red-400 rounded-xl px-4 py-3 text-sm flex flex-col gap-1 shadow-sm">
                    <div class="flex items-center gap-2 font-semibold">
                        <i class="bi bi-exclamation-triangle-fill text-lg text-red-500"></i>
                        <span>Por favor corrige los siguientes errores:</span>
                    </div>
                    <ul class="list-disc list-inside pl-6 mt-1 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Ficha de Información del Chofer --}}
            <div class="bg-white dark:bg-[#22242a] shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800 transition duration-300">
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <!-- Info Principal -->
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800 shrink-0">
                                <i class="bi bi-person-badge-fill text-2xl"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-950 dark:text-white">{{ $chofer->nombre }}</h1>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    DNI: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $chofer->dni }}</span>
                                    @if($chofer->licencia)
                                        · Licencia: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $chofer->licencia }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Detalles Técnicos -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-3 text-xs border-t lg:border-t-0 border-gray-100 dark:border-gray-800 pt-4 lg:pt-0">
                            <div>
                                <span class="text-gray-400 dark:text-gray-500 block mb-0.5">Vehículo / Placa</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $chofer->marca ?? '—' }} @if($chofer->placa) [{{ $chofer->placa }}] @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-400 dark:text-gray-500 block mb-0.5">Config. Vehicular</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $chofer->config_vehicular ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 dark:text-gray-550 block mb-0.5">Peso (Capacidad)</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $chofer->peso ? $chofer->peso . ' T' : '—' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-400 dark:text-gray-550 block mb-0.5">Certificado / GNV</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $chofer->certificado ?? '—' }} @if($chofer->cng) ({{ $chofer->cng }}) @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cabecera de controles y año --}}
            <div class="flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-[#22242a] p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Año de Programación:</span>
                    <div class="year-nav">
                        <a href="{{ route('programacion.index', ['id_chofer' => $chofer->id_chofer, 'year' => $year - 1]) }}">
                            <button type="button" class="focus:outline-none font-bold">&#8592;</button>
                        </a>
                        <span class="year-label">{{ $year }}</span>
                        <a href="{{ route('programacion.index', ['id_chofer' => $chofer->id_chofer, 'year' => $year + 1]) }}">
                            <button type="button" class="focus:outline-none font-bold">&#8594;</button>
                        </a>
                    </div>
                </div>

                {{-- Leyenda --}}
                <div class="flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full" style="background:var(--cal-day-sel)"></span>
                        Día programado
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full border-2" style="border-color:var(--cal-accent)"></span>
                        Día actual
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded" style="background:var(--cal-prog-md)"></span>
                        Mes programado
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full" style="background:#dc2626"></span>
                        Día Feriado
                    </span>
                </div>
            </div>

            {{-- ── Grid de 12 meses ── --}}
            <div class="cal-grid">
                @php
                    $today     = \Carbon\Carbon::today();
                    $months    = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                                  'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                    $weekdays  = ['Lu','Ma','Mi','Ju','Vi','Sá','Do'];
                @endphp

                @for ($m = 1; $m <= 12; $m++)
                    @php
                        $prog       = $programaciones->get($m);
                        $diasProg   = $prog ? $prog->dias_programacion : [];
                        $hasProg    = !empty($diasProg);
                        $firstDay   = \Carbon\Carbon::createFromDate($year, $m, 1);
                        $daysInMonth = $firstDay->daysInMonth;
                        // weekday del primer día (0=Lun … 6=Dom) para nuestro header Lu-Do
                        $startOffset = ($firstDay->dayOfWeek + 6) % 7; // PHP: 0=Sun
                    @endphp

                    <div class="cal-card">
                        {{-- Header del mes --}}
                        <div class="cal-card-header {{ $hasProg ? 'has-prog' : '' }}">
                            <span class="cal-month-name">{{ $months[$m-1] }}</span>
                            @if ($hasProg)
                                <span class="badge-prog">
                                    <i class="bi bi-check-lg"></i>
                                    {{ count($diasProg) }} días
                                </span>
                            @endif
                        </div>

                        {{-- Mini calendario --}}
                        <div class="mini-cal">
                            <div class="mini-cal-head">
                                @foreach ($weekdays as $wd)
                                    <div>{{ $wd }}</div>
                                @endforeach
                            </div>
                            <div class="mini-cal-body">
                                {{-- Celdas vacías al inicio --}}
                                @for ($e = 0; $e < $startOffset; $e++)
                                    <div class="mini-day empty"></div>
                                @endfor
                                {{-- Días del mes --}}
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $isProg  = in_array($d, $diasProg);
                                        $isToday = ($today->year == $year && $today->month == $m && $today->day == $d);
                                        $isFeriado = in_array($d, $feriados[$m] ?? []);
                                        $classes = 'mini-day';
                                        if ($isProg)  $classes .= ' programado';
                                        if ($isToday) $classes .= ' today-marker';
                                        if ($isFeriado) $classes .= ' feriado';
                                    @endphp
                                    <div class="{{ $classes }}" title="{{ $isFeriado ? 'Feriado' : '' }}">{{ $d }}</div>
                                @endfor
                            </div>
                        </div>

                        {{-- Botones de acción del mes --}}
                        <div class="cal-actions">
                            <button type="button"
                                class="btn-cal btn-cal-primary"
                                onclick="openModal({{ $m }}, {{ $daysInMonth }}, {{ $year }}, {{ json_encode($diasProg) }}, '{{ $months[$m-1] }}', {{ json_encode($feriados[$m] ?? []) }})">
                                <i class="bi bi-{{ $hasProg ? 'pencil-square' : 'plus-circle' }}"></i>
                                {{ $hasProg ? 'Editar' : 'Programar' }}
                            </button>

                            @if ($hasProg)
                                <form method="POST"
                                      action="{{ route('programacion.destroy', ['id_chofer' => $chofer->id_chofer, 'id_programacion' => $prog->id_programacion]) }}"
                                      onsubmit="return confirm('¿Eliminar la programación de {{ $months[$m-1] }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-cal btn-cal-danger" title="Eliminar programación">
                                        <i class="bi bi-trash text-[15px]"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>

        </div>
    </div>

    {{-- MODAL DE PROGRAMACIÓN --}}
    <div id="prog-modal-overlay" onclick="closeModal(event)">
        <div id="prog-modal">
            <button class="modal-close" onclick="closeModalDirect()">&times;</button>

            <h3 id="modal-title" class="text-lg font-bold">Programar mes</h3>
            <p class="subtitle text-xs text-gray-500 dark:text-gray-400 mt-1 mb-4">Selecciona los días en que el chofer usará el transporte</p>

            {{-- Resumen seleccionados --}}
            <div id="sel-summary" class="text-xs">Ningún día seleccionado</div>

            {{-- Cabecera del picker (días de la semana) --}}
            <div class="grid grid-cols-7 text-center text-[10px] font-black text-gray-400 dark:text-gray-500 mb-2 uppercase tracking-wider">
                <div>Lu</div>
                <div>Ma</div>
                <div>Mi</div>
                <div>Ju</div>
                <div>Vi</div>
                <div>Sá</div>
                <div>Do</div>
            </div>
            
            {{-- Grid de días --}}
            <div class="day-picker-grid" id="day-picker"></div>

            {{-- Botones de selección rápida --}}
            <div class="flex justify-between items-center border-t border-gray-100 dark:border-gray-800 pt-3 mb-4">
                <button type="button" onclick="selectAllDays()"
                    class="text-xs font-bold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-1">
                    <i class="bi bi-check-all text-base"></i> Seleccionar Todos
                </button>
                <button type="button" onclick="clearAllDays()"
                    class="text-xs font-bold text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 flex items-center gap-1">
                    <i class="bi bi-trash text-sm"></i> Limpiar Todo
                </button>
            </div>

            {{-- Formulario oculto --}}
            <form id="prog-form" method="POST">
                @csrf
                <input type="hidden" name="year"  id="f-year">
                <input type="hidden" name="month" id="f-month">
                <div id="dias-inputs"></div>

                <div class="flex justify-end gap-2 mt-4 border-t border-gray-100 dark:border-gray-800 pt-3">
                    <button type="button" onclick="closeModalDirect()"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white shadow-sm flex items-center gap-1.5 font-bold">
                        <i class="bi bi-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- JAVASCRIPT --}}
    <script>
        let selectedDays = new Set();
        let currentDaysInMonth = 31;

        function openModal(month, daysInMonth, year, existing, monthName, monthHolidays = []) {
            selectedDays = new Set(existing.map(Number));
            currentDaysInMonth = daysInMonth;
            const holidaySet = new Set(monthHolidays.map(Number));

            // Título
            document.getElementById('modal-title').textContent =
                (existing.length > 0 ? 'Editar programación — ' : 'Programar — ') + monthName + ' ' + year;

            // Form action
            document.getElementById('prog-form').action =
                `/choferes/{{ $chofer->id_chofer }}/programacion`;
            document.getElementById('f-year').value  = year;
            document.getElementById('f-month').value = month;

            // Calcular startOffset para el primer día de ese mes/año
            const firstDay = new Date(year, month - 1, 1);
            // dayOfWeek: 0=Sun, 1=Mon, ..., 6=Sat
            // Queremos: 0=Lun, 1=Mar, ..., 6=Dom
            const startOffset = (firstDay.getDay() + 6) % 7;

            // Renderizar picker
            const picker = document.getElementById('day-picker');
            picker.innerHTML = '';
            
            // Insertar celdas deshabilitadas (desfase de días)
            for (let e = 0; e < startOffset; e++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'dp-day disabled';
                picker.appendChild(emptyCell);
            }

            // Insertar días del mes
            for (let d = 1; d <= daysInMonth; d++) {
                const btn = document.createElement('div');
                let classes = 'dp-day';
                if (selectedDays.has(d)) classes += ' selected';
                if (holidaySet.has(d)) classes += ' feriado';
                btn.className = classes;
                btn.textContent = d;
                btn.dataset.day = d;
                btn.addEventListener('click', () => toggleDay(d, btn));
                picker.appendChild(btn);
            }

            updateSummary();
            document.getElementById('prog-modal-overlay').classList.add('open');
        }

        function toggleDay(d, btn) {
            if (selectedDays.has(d)) {
                selectedDays.delete(d);
                btn.classList.remove('selected');
            } else {
                selectedDays.add(d);
                btn.classList.add('selected');
            }
            updateSummary();
        }

        function selectAllDays() {
            selectedDays.clear();
            for (let d = 1; d <= currentDaysInMonth; d++) {
                selectedDays.add(d);
            }
            document.querySelectorAll('#day-picker .dp-day:not(.disabled)').forEach(btn => {
                btn.classList.add('selected');
            });
            updateSummary();
        }

        function clearAllDays() {
            selectedDays.clear();
            document.querySelectorAll('#day-picker .dp-day:not(.disabled)').forEach(btn => {
                btn.classList.remove('selected');
            });
            updateSummary();
        }

        function updateSummary() {
            const sorted = [...selectedDays].sort((a, b) => a - b);
            const el = document.getElementById('sel-summary');
            if (sorted.length === 0) {
                el.textContent = 'Ningún día seleccionado';
            } else {
                el.textContent = sorted.length + ' día(s) seleccionado(s): ' + sorted.join(', ');
            }
            
            // Actualizar inputs ocultos del formulario
            const cont = document.getElementById('dias-inputs');
            cont.innerHTML = '';
            sorted.forEach(d => {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = 'dias_programacion[]';
                inp.value = d;
                cont.appendChild(inp);
            });
        }

        function closeModal(event) {
            if (event.target.id === 'prog-modal-overlay') closeModalDirect();
        }
        function closeModalDirect() {
            document.getElementById('prog-modal-overlay').classList.remove('open');
        }

        // Prevenir que clics dentro del modal cierren el diálogo
        document.getElementById('prog-modal').addEventListener('click', e => e.stopPropagation());
    </script>
</x-app-layout>