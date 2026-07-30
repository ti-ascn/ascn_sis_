@php
if (!function_exists('getDriverHeaderClasses')) {
    function getDriverHeaderClasses($color) {
        $bg = $color ?: '#cbd5e1';
        $hex = str_replace('#', '', $bg);
        if (strlen($hex) == 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2)) ?: 0;
        $g = hexdec(substr($hex, 2, 2)) ?: 0;
        $b = hexdec(substr($hex, 4, 2)) ?: 0;
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        $textClass = ($yiq >= 128) ? 'text-gray-900' : 'text-white';
        return [
            'bg' => $bg,
            'text' => $textClass
        ];
    }
}
@endphp
<style>
    .row-crema {
        background-color: #fef8ec !important;
    }
    .dark .row-crema {
        background-color: #2b261d !important;
    }
    
    .bg-sticky-col {
        background-color: #f9fafb !important;
        color: #374151 !important;
    }
    .dark .bg-sticky-col {
        background-color: #1e2025 !important;
        color: #e5e7eb !important;
    }

    .bg-sticky-sunday {
        background-color: #fef8ec !important;
        color: #b45309 !important;
    }
    .dark .bg-sticky-sunday {
        background-color: #2b261d !important;
        color: #fde047 !important;
    }

    .row-feriado {
        background-color: #fef2f2 !important;
    }
    .dark .row-feriado {
        background-color: #3b1e1e !important;
    }
    .bg-sticky-feriado {
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
    }
    .dark .bg-sticky-feriado {
        background-color: #3b1e1e !important;
        color: #f87171 !important;
    }

    .border-gray-250 {
        border-color: #e5e7eb !important;
    }
    .dark .border-gray-250 {
        border-color: #374151 !important;
    }
    
    .dark tr.row-crema td {
        color: #f1f5f9 !important;
    }
    .dark td.bg-sticky-sunday {
        color: #fde047 !important;
    }
    
    .hover-green:hover {
        background-color: #dcfce7 !important;
    }
    .dark .hover-green:hover {
        background-color: rgba(22, 101, 52, 0.45) !important;
    }

    #tabla-programacion-grid .highlight-hover-row, 
    #tabla-programacion-grid .highlight-hover-col {
        background-color: #bae6fd !important; /* sky-200 (Celeste claro brillante) */
        color: #0f172a !important; /* Texto oscuro para legibilidad */
    }
    .dark #tabla-programacion-grid .highlight-hover-row, 
    .dark #tabla-programacion-grid .highlight-hover-col {
        background-color: rgba(14, 165, 233, 0.3) !important; /* Celeste translúcido en modo oscuro */
        color: #f8fafc !important;
    }
    
    #tabla-programacion-grid .highlight-hover-cell {
        background-color: #38bdf8 !important; /* sky-400 (Intersección más brillante) */
        color: #0f172a !important;
        font-weight: 900 !important;
    }
    .dark #tabla-programacion-grid .highlight-hover-cell {
        background-color: #0284c7 !important; /* sky-600 en modo oscuro */
        color: #ffffff !important;
        font-weight: 900 !important;
    }
</style>
<div class="space-y-4">
    {{-- Filtros dentro del Modal - Año y Mes --}}
    <div class="flex items-center gap-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
        <div class="flex-1 max-w-[200px]">
            <label for="modal_year" class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Año de Programación</label>
            <select id="modal_year" onchange="cargarGrillaProgramacion()" class="w-full bg-white border border-gray-300 rounded-lg text-sm text-gray-800 px-3 py-1.5 font-medium focus:ring-amber-500 focus:border-amber-500">
                @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="flex-1 max-w-[200px]">
            <label for="modal_month" class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Mes de Programación</label>
            <select id="modal_month" onchange="cargarGrillaProgramacion()" class="w-full bg-white border border-gray-300 rounded-lg text-sm text-gray-800 px-3 py-1.5 font-medium focus:ring-amber-500 focus:border-amber-500">
                <option value="all" {{ $month == 'all' ? 'selected' : '' }}>Todos los meses</option>
                @php
                    $monthNames = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];
                @endphp
                @foreach($monthNames as $num => $name)
                    <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Leyenda --}}
    <div class="flex flex-wrap gap-4 text-[10px] text-gray-505 border-b border-gray-100 pb-2">
        <span class="flex items-center gap-1.5 font-medium">
            <span class="inline-block w-3.5 h-3.5 rounded border border-gray-300 bg-white dark:bg-transparent"></span>
            SI (Original Programado - Seleccionable)
        </span>
        <span class="flex items-center gap-1.5 font-medium">
            <span class="inline-block w-3.5 h-3.5 rounded bg-yellow-300 border border-yellow-400"></span>
            SI (Agregado/Editado - Seleccionable)
        </span>
        <span class="flex items-center gap-1.5 font-medium">
            <span class="inline-block w-3.5 h-3.5 rounded bg-blue-600 border border-blue-700"></span>
            Selección Actual
        </span>
        <span class="flex items-center gap-1.5 font-medium">
            <span class="inline-block w-3.5 h-3.5 rounded bg-red-100 border border-red-200 dark:bg-red-950/40 dark:border-red-900/40"></span>
            Día Feriado (Rojo)
        </span>
    </div>

    @if($choferes->isEmpty())
        <div class="p-6 text-center text-gray-505 bg-white rounded-lg border border-gray-250">
            <i class="bi bi-calendar-x text-3xl text-gray-400 block mb-1"></i>
            <p class="font-semibold text-sm">No hay choferes activos registrados.</p>
        </div>
    @else
        <div class="overflow-x-auto max-h-[60vh] border border-gray-200 rounded-lg shadow-inner">
            <table class="min-w-full border-collapse border-spacing-0" id="tabla-programacion-grid">
                <thead class="bg-gray-100 sticky top-0 z-20">
                    <!-- Fila 1: Peso -->
                    <tr>
                        <th rowspan="3" style="position: sticky; left: 0px; z-index: 30; min-width: 90px;" class="px-3 py-2 border text-xs font-bold text-gray-600 text-center uppercase tracking-wider border-gray-200 bg-gray-100">MES</th>
                        <th rowspan="3" style="position: sticky; left: 90px; z-index: 30; min-width: 70px;" class="px-3 py-2 border text-xs font-bold text-gray-600 text-center uppercase tracking-wider border-gray-200 bg-gray-100">DIA</th>
                        <th rowspan="3" style="position: sticky; left: 160px; z-index: 30; min-width: 50px;" class="px-3 py-2 border text-xs font-bold text-gray-600 text-center uppercase tracking-wider border-gray-200 bg-gray-100">FECHA</th>
                        @foreach($choferes as $idx => $chofer)
                            @php
                                $styleInfo = getDriverHeaderClasses($chofer->color);
                            @endphp
                            <th data-col-index="{{ $idx }}" style="background-color: {{ $styleInfo['bg'] }}; color: {{ $styleInfo['text'] == 'text-white' ? '#ffffff' : '#111827' }}; border-color: rgba(0,0,0,0.08);" class="px-3 py-1 border text-[10px] font-black uppercase text-center min-w-[150px]">
                                PESO: {{ $chofer->peso ? $chofer->peso . 'T' : '—' }}
                            </th>
                        @endforeach
                    </tr>
                    <!-- Fila 2: Empresa -->
                    <tr>
                        @foreach($choferes as $idx => $chofer)
                            @php
                                $styleInfo = getDriverHeaderClasses($chofer->color);
                            @endphp
                            <th data-col-index="{{ $idx }}" style="background-color: {{ $styleInfo['bg'] }}; color: {{ $styleInfo['text'] == 'text-white' ? 'rgba(255,255,255,0.85)' : 'rgba(17,24,39,0.85)' }}; border-color: rgba(0,0,0,0.08);" class="px-3 py-1 border text-[9px] font-bold text-center truncate max-w-[160px]" title="{{ $chofer->empresa->razon_social ?? '—' }}">
                                {{ $chofer->empresa->razon_social ?? '—' }}
                            </th>
                        @endforeach
                    </tr>
                    <!-- Fila 3: Chofer / Placa -->
                    <tr>
                        @foreach($choferes as $idx => $chofer)
                            @php
                                $styleInfo = getDriverHeaderClasses($chofer->color);
                            @endphp
                            <th data-col-index="{{ $idx }}" style="background-color: {{ $styleInfo['bg'] }}; color: {{ $styleInfo['text'] == 'text-white' ? '#ffffff' : '#111827' }}; border-color: rgba(0,0,0,0.08);" class="px-3 py-1 border text-xs font-extrabold text-center">
                                {{ $chofer->nombre }}
                                @if($chofer->placa)
                                    <span class="block text-[9px] font-bold mt-0.5" style="color: {{ $styleInfo['text'] == 'text-white' ? 'rgba(255,255,255,0.8)' : 'rgba(17,24,39,0.8)' }};">[{{ $chofer->placa }}]</span>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($calendar as $mNum => $mInfo)
                        @php
                            $monthName = $mInfo['name'];
                            $days = $mInfo['days'];
                            $daysCount = $mInfo['count'];
                        @endphp
                        @foreach($days as $day)
                            @php
                                $d = $day['numero'];
                                $isFeriado = $day['es_feriado'] ?? false;
                                $isSunday = $day['es_domingo'] && !$isFeriado;
                                $rowBg = $isFeriado ? 'row-feriado' : ($isSunday ? 'row-crema' : '');
                                $cellDateStr = sprintf('%04d-%02d-%02d', $year, $mNum, $d);
                                $isToday = $cellDateStr === now()->format('Y-m-d');
                            @endphp
                            <tr class="hover:bg-gray-50 {{ $rowBg }} transition duration-75">
                                {{-- Nombre del Mes (Rowspan para agrupar) --}}
                                @if($d === 1)
                                    <td rowspan="{{ $daysCount }}" style="position: sticky; left: 0px; z-index: 10;" class="border px-3 py-2 text-xs font-black uppercase text-center bg-sticky-col border-gray-250 align-middle">
                                        {{ $monthName }}
                                    </td>
                                @endif
                                
                                {{-- Nombre del Día --}}
                                <td style="position: sticky; left: 90px; z-index: 10;" class="px-2.5 py-1.5 border text-[10px] font-bold uppercase text-center border-gray-200 {{ $isSunday ? 'bg-sticky-sunday' : 'bg-sticky-col' }}">
                                    {!! str_replace('(FERIADO)', '<span class="text-red-600 font-black">(FERIADO)</span>', $day['nombre']) !!}
                                </td>
                                
                                {{-- Fecha (Número de Día) --}}
                                <td style="position: sticky; left: 160px; z-index: 10;" class="px-2.5 py-1.5 border text-[10px] font-bold text-center border-gray-200 {{ $isFeriado ? 'bg-sticky-feriado' : ($isSunday ? 'bg-sticky-sunday' : 'bg-white dark:bg-[#202228] dark:text-gray-300') }}">
                                    {{ $d }}
                                    @if($isToday)
                                        <span class="bg-red-500 text-white rounded px-1 ml-1 text-[9px] animate-pulse">HOY</span>
                                    @endif
                                </td>
                                
                                {{-- Columnas de Choferes --}}
                                @foreach($choferes as $idx => $chofer)
                                    @php
                                        // Buscar programación para este chofer y este mes
                                        $prog = $progLookup["{$chofer->id_chofer}-{$mNum}"] ?? null;
                                        
                                        $inCurrent = false;
                                        $inOriginal = false;
                                        
                                        if ($prog) {
                                            $diasProg = $prog->dias_programacion ?? [];
                                            $diasOrig = $prog->dias_originales ?? $diasProg;
                                            $inCurrent = in_array($d, $diasProg);
                                            $inOriginal = in_array($d, $diasOrig);
                                        }

                                        $cellClass = '';
                                        $cellText = '';
                                        $isClickable = false;

                                        // Determinar si esta celda específica coincide con la selección actual
                                        $isCurrentSelection = false;
                                        if ($selectedChofer && $selectedChofer == $chofer->id_chofer && $selectedDate) {
                                            try {
                                                $parsedSelDate = \Carbon\Carbon::parse($selectedDate);
                                                if ($parsedSelDate->year == $year && $parsedSelDate->month == $mNum && $parsedSelDate->day == $d) {
                                                    $isCurrentSelection = true;
                                                }
                                            } catch (\Exception $e) {}
                                        }

                                        if ($isCurrentSelection) {
                                            $cellClass = 'bg-blue-600 text-white font-bold text-center border-blue-700 ring-2 ring-blue-300 ring-inset';
                                            $cellText = 'SI 🟢';
                                            $isClickable = true;
                                        } elseif ($inOriginal && !$inCurrent) {
                                            $cellClass = 'bg-red-100 text-red-500 font-bold text-center border-red-200 dark:bg-red-950/20 dark:text-red-400 dark:border-red-900/30';
                                            $cellText = ''; 
                                        } elseif (!$inOriginal && $inCurrent) {
                                            $cellClass = 'bg-yellow-300 text-yellow-900 font-black text-center border-yellow-400 cursor-pointer hover:bg-yellow-400 dark:bg-yellow-500/20 dark:text-yellow-400 dark:border-yellow-900/30 dark:hover:bg-yellow-500/30';
                                            $cellText = 'SI';
                                            $isClickable = true;
                                        } elseif ($inOriginal && $inCurrent) {
                                            $baseBg = $isFeriado 
                                                ? 'bg-sticky-feriado' 
                                                : ($isSunday ? 'bg-sticky-sunday' : 'bg-white dark:bg-transparent');
                                            $cellClass = $baseBg . ' hover-green text-gray-900 font-bold text-center border-gray-200 cursor-pointer dark:text-gray-100 dark:border-gray-700';
                                            $cellText = 'SI';
                                            $isClickable = true;
                                        } else {
                                            $cellClass = $isFeriado ? '' : ($isSunday ? 'bg-sticky-sunday' : 'bg-white dark:bg-transparent');
                                        }
                                    @endphp
                                    <td data-col-index="{{ $idx }}" class="border px-3 py-1.5 text-xs border-gray-250 {{ $cellClass }}"
                                        @if($isClickable)
                                            onclick="seleccionarFechaProgramacion('{{ $cellDateStr }}', '{{ $chofer->id_chofer }}', '{{ $chofer->nombre }}', '{{ $chofer->placa }}')"
                                            title="Seleccionar esta fecha para {{ $chofer->nombre }}"
                                        @endif>
                                        {{ $cellText }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" 
        onload="(function() {
            const table = document.getElementById('tabla-programacion-grid');
            if (!table) return;

            if (table.dataset.hoverBound) return;
            table.dataset.hoverBound = 'true';

            table.addEventListener('mouseover', function(e) {
                const cell = e.target.closest('tbody td');
                if (!cell) {
                  // Si no está sobre el cuerpo, limpiar cualquier resaltado activo
                    table.querySelectorAll('.highlight-hover-row').forEach(c => c.classList.remove('highlight-hover-row'));
                    table.querySelectorAll('.highlight-hover-col').forEach(c => c.classList.remove('highlight-hover-col'));
                    table.querySelectorAll('.highlight-hover-cell').forEach(c => c.classList.remove('highlight-hover-cell'));
                    return;
                }

                const colIndex = cell.dataset.colIndex;
                const tr = cell.closest('tr');

             // Limpiar resaltados anteriores
                table.querySelectorAll('.highlight-hover-row').forEach(c => c.classList.remove('highlight-hover-row'));
                table.querySelectorAll('.highlight-hover-col').forEach(c => c.classList.remove('highlight-hover-col'));
                table.querySelectorAll('.highlight-hover-cell').forEach(c => c.classList.remove('highlight-hover-cell'));

             // Resaltar fila (solo los elementos de esta fila en tbody)
                if (tr) {
                    tr.querySelectorAll('td, th').forEach(c => c.classList.add('highlight-hover-row'));
                }

             // Resaltar columna (solo celdas del tbody para no pintar la cabecera)
                if (colIndex !== undefined && colIndex !== '') {
                    table.querySelectorAll(`tbody [data-col-index='${colIndex}']`).forEach(c => c.classList.add('highlight-hover-col'));
                }

             // Resaltar celda activa
                cell.classList.add('highlight-hover-cell');
            });

            table.addEventListener('mouseleave', function() {
                table.querySelectorAll('.highlight-hover-row').forEach(c => c.classList.remove('highlight-hover-row'));
                table.querySelectorAll('.highlight-hover-col').forEach(c => c.classList.remove('highlight-hover-col'));
                table.querySelectorAll('.highlight-hover-cell').forEach(c => c.classList.remove('highlight-hover-cell'));
            });
        })()" 
        style="display:none;" />
