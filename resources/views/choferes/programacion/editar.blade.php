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
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Control de Transporte') }}
            </h2>
            <a href="{{ route('choferes.index') }}"
                class="flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-850 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-350 text-sm px-3.5 py-2 rounded-lg transition font-medium">
                <i class="bi bi-arrow-left"></i> Volver a Choferes
            </a>
        </div>
    </x-slot>

    {{-- Estilos para el Control de Transporte --}}
    <style>
        .bg-crema {
            background-color: #fef8ec !important; /* soft cream */
        }
        .dark .bg-crema {
            background-color: #2b261d !important; /* dark warm cream */
        }
        .bg-feriado {
            background-color: #fef2f2 !important; /* soft red */
        }
        .dark .bg-feriado {
            background-color: #3b1e1e !important; /* dark soft red */
        }
    </style>

    <div class="py-10 bg-gray-50 dark:bg-[#1a1c21] min-h-screen">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ── Alertas ── --}}
            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900/30 text-green-800 dark:text-green-400 rounded-xl px-4 py-3 text-sm flex items-center gap-2 shadow-sm">
                    <i class="bi bi-check-circle-fill text-lg text-green-500"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Filtros de Mes y Año --}}
            <div class="bg-white dark:bg-[#22242a] p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                <form id="filter-form" method="GET" action="{{ route('programacion.editar') }}" class="flex flex-wrap items-end gap-4">
                    <div class="w-full sm:w-auto flex-1 sm:max-w-xs">
                        <label for="month" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase">Mes</label>
                        <select name="month" id="month" onchange="this.form.submit()" class="w-full bg-gray-50 dark:bg-[#1c1e22] border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-3 py-2 font-medium">
                            @php
                                $months = [
                                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                ];
                            @endphp
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-auto flex-1 sm:max-w-[150px]">
                        <label for="year" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase">Año</label>
                        <select name="year" id="year" onchange="this.form.submit()" class="w-full bg-gray-50 dark:bg-[#1c1e22] border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-3 py-2 font-medium">
                            @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </form>
            </div>

            {{-- Tabla de Control de Transporte (Estilo Excel) --}}
            @if($programaciones->isEmpty())
                <div class="p-8 text-center text-gray-500 dark:text-gray-400 bg-white dark:bg-[#22242a] rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                    <i class="bi bi-calendar-x text-4xl text-gray-400 dark:text-gray-500 block mb-2"></i>
                    <p class="font-semibold text-base">No hay programaciones registradas para este mes.</p>
                    <p class="text-xs text-gray-400 mt-1">Registra la programación de algún chofer desde su calendario para verla reflejada aquí.</p>
                </div>
            @else
                <div class="bg-white dark:bg-[#22242a] border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden">
                    {{-- Leyenda de colores del Excel --}}
                    <div class="flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400 p-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-[#1c1e22]">
                        <span class="flex items-center gap-1.5 font-medium">
                            <span class="inline-block w-4 h-4 rounded border" style="background:#ffffff; border-color:#e5e7eb;"></span>
                            SI (Programado)
                        </span>
                        <span class="flex items-center gap-1.5 font-medium">
                            <span class="inline-block w-4 h-4 rounded bg-yellow-300 dark:bg-yellow-400 border border-yellow-400"></span>
                            SI (Agregado / Editado)
                        </span>
                        <span class="flex items-center gap-1.5 font-medium">
                            <span class="inline-block w-4 h-4 rounded bg-red-500 border border-red-600"></span>
                            (Fecha Eliminada / Cambiada)
                        </span>
                        <span class="flex items-center gap-1.5 font-medium">
                            <span class="inline-block w-4 h-4 rounded bg-crema border border-amber-200/50"></span>
                            Día Domingo
                        </span>
                        <span class="flex items-center gap-1.5 font-medium">
                            <span class="inline-block w-4 h-4 rounded bg-red-50 border border-red-200 dark:bg-red-950/20 dark:border-red-900/30"></span>
                            Día Feriado
                        </span>
                    </div>

                    <div class="overflow-x-auto max-h-[70vh] custom-scrollbar">
                        <table class="min-w-full border-collapse border-spacing-0">
                            <thead class="bg-gray-100 dark:bg-[#1a1c21] sticky top-0 z-10">
                                <!-- Fila 1: Peso -->
                                <tr>
                                    <th rowspan="4" class="px-4 py-2 border text-xs font-bold text-gray-600 dark:text-gray-400 text-center uppercase tracking-wider border-gray-200 dark:border-gray-700 min-w-[110px] bg-gray-100 dark:bg-[#1a1c21]">DIA</th>
                                    <th rowspan="4" class="px-4 py-2 border text-xs font-bold text-gray-600 dark:text-gray-400 text-center uppercase tracking-wider border-gray-200 dark:border-gray-700 min-w-[65px] bg-gray-100 dark:bg-[#1a1c21]">FECHA</th>
                                    @foreach($programaciones as $prog)
                                        @php
                                            $styleInfo = getDriverHeaderClasses($prog->chofer->color);
                                        @endphp
                                        <th style="background-color: {{ $styleInfo['bg'] }}; color: {{ $styleInfo['text'] == 'text-white' ? '#ffffff' : '#111827' }}; border-color: rgba(0,0,0,0.08);" class="px-4 py-1.5 border text-[11px] font-black uppercase text-center min-w-[180px]">
                                            PESO: {{ $prog->chofer->peso ? $prog->chofer->peso . 'T' : '—' }}
                                        </th>
                                    @endforeach
                                </tr>
                                <!-- Fila 2: Empresa -->
                                <tr>
                                    @foreach($programaciones as $prog)
                                        @php
                                            $styleInfo = getDriverHeaderClasses($prog->chofer->color);
                                        @endphp
                                        <th style="background-color: {{ $styleInfo['bg'] }}; color: {{ $styleInfo['text'] == 'text-white' ? 'rgba(255,255,255,0.85)' : 'rgba(17,24,39,0.85)' }}; border-color: rgba(0,0,0,0.08);" class="px-4 py-1.5 border text-[10px] font-bold text-center truncate max-w-[200px]" title="{{ $prog->chofer->empresa->razon_social ?? '—' }}">
                                            {{ $prog->chofer->empresa->razon_social ?? '—' }}
                                        </th>
                                    @endforeach
                                </tr>
                                <!-- Fila 3: Chofer / Placa -->
                                <tr>
                                    @foreach($programaciones as $prog)
                                        @php
                                            $styleInfo = getDriverHeaderClasses($prog->chofer->color);
                                        @endphp
                                        <th style="background-color: {{ $styleInfo['bg'] }}; color: {{ $styleInfo['text'] == 'text-white' ? '#ffffff' : '#111827' }}; border-color: rgba(0,0,0,0.08);" class="px-4 py-1.5 border text-xs font-extrabold text-center">
                                            {{ $prog->chofer->nombre }}
                                            @if($prog->chofer->placa)
                                                <span class="block text-[10px] font-bold mt-0.5" style="color: {{ $styleInfo['text'] == 'text-white' ? 'rgba(255,255,255,0.8)' : 'rgba(17,24,39,0.8)' }};">[{{ $prog->chofer->placa }}]</span>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                                <!-- Fila 4: Acciones -->
                                <tr>
                                    @foreach($programaciones as $prog)
                                        <th class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-center bg-gray-50/50 dark:bg-[#202228]">
                                            <div class="flex items-center justify-center gap-3">
                                                <!-- Editar -->
                                                <a href="{{ route('programacion.index', ['id_chofer' => $prog->id_chofer, 'year' => $year]) }}" 
                                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-xs font-bold flex items-center gap-1 transition duration-150"
                                                    title="Editar programación">
                                                    <i class="bi bi-pencil-square"></i> Editar
                                                </a>
                                                
                                                <!-- Eliminar -->
                                                <form method="POST" 
                                                        action="{{ route('programacion.destroy', ['id_chofer' => $prog->id_chofer, 'id_programacion' => $prog->id_programacion]) }}"
                                                        onsubmit="return confirm('¿Seguro que deseas eliminar por completo la programación de este chofer para este mes?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-xs font-bold flex items-center gap-1 transition duration-150"
                                                            title="Eliminar programación">
                                                        <i class="bi bi-trash"></i> Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($days as $day)
                                    @php
                                        $d = $day['numero'];
                                        $isFeriado = $day['es_feriado'] ?? false;
                                        $isSunday = $day['es_domingo'] && !$isFeriado;
                                        $rowBg = $isFeriado ? 'bg-feriado' : ($isSunday ? 'bg-crema' : '');
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-[#282a32] {{ $rowBg }} transition duration-75">
                                        {{-- Nombre del Día --}}
                                        <td class="px-3 py-2 border text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase text-center border-gray-200 dark:border-gray-700 {{ $isFeriado ? 'text-red-600 dark:text-red-400 font-extrabold' : ($isSunday ? '' : 'bg-gray-50/30 dark:bg-[#1a1c21]/20') }}">
                                            {{ $day['nombre'] }}
                                        </td>
                                        {{-- Fecha --}}
                                        <td class="px-3 py-2 border text-[11px] font-bold text-gray-800 dark:text-gray-300 text-center border-gray-200 dark:border-gray-700 {{ $isFeriado ? 'text-red-700 dark:text-red-300 font-extrabold' : ($isSunday ? '' : 'bg-gray-50/50 dark:bg-[#1a1c21]/45') }}">
                                            {{ $d }}
                                        </td>
                                        {{-- Columnas de Choferes --}}
                                        @foreach($programaciones as $prog)
                                            @php
                                                $diasProg = $prog->dias_programacion ?? [];
                                                $diasOrig = $prog->dias_originales ?? $diasProg;

                                                $inCurrent = in_array($d, $diasProg);
                                                $inOriginal = in_array($d, $diasOrig);

                                                $cellClass = '';
                                                $cellText = '';

                                                if ($inOriginal && !$inCurrent) {
                                                    $cellClass = 'bg-red-500 text-white font-bold text-center border-red-600';
                                                    $cellText = ''; // Celda roja vacía
                                                } elseif (!$inOriginal && $inCurrent) {
                                                    $cellClass = 'bg-yellow-300 text-yellow-955 font-black text-center dark:bg-yellow-400 dark:text-yellow-950 border-yellow-400';
                                                    $cellText = 'SI';
                                                } elseif ($inOriginal && $inCurrent) {
                                                    $cellClass = 'text-gray-900 dark:text-gray-100 font-bold text-center ' . ($isFeriado ? '' : ($isSunday ? '' : 'bg-white dark:bg-transparent'));
                                                    $cellText = 'SI';
                                                } else {
                                                    $cellClass = $isFeriado ? '' : ($isSunday ? '' : 'bg-white dark:bg-transparent');
                                                }
                                            @endphp
                                            @php
                                                $fechaCellStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                                $nrosFacturas = $facturasAgrupadas[$fechaCellStr][$prog->id_chofer] ?? [];
                                                $cantFacturas = count($nrosFacturas);
                                                
                                                if ($cantFacturas > 0) {
                                                    $tooltipText = '<div class="flex flex-col gap-1 text-left">';
                                                    $tooltipText .= '<span class="font-bold text-blue-400"><i class="bi bi-file-earmark-text mr-1"></i>' . $cantFacturas . ' Factura' . ($cantFacturas > 1 ? 's' : '') . '</span>';
                                                    $tooltipText .= '</div>';
                                                } else {
                                                    $tooltipText = '<div class="text-gray-400"><i class="bi bi-slash-circle mr-1"></i>Sin facturas</div>';
                                                }
                                            @endphp
                                            <td class="border px-4 py-2 text-xs border-gray-200 dark:border-gray-700 {{ $cellClass }}">
                                                @if ($cellText === 'SI')
                                                    <button type="button" 
                                                        class="w-full text-center hover:underline focus:outline-none btn-ver-facturas-traslado font-bold bg-transparent border-0 p-0 text-inherit cursor-pointer" 
                                                        data-fecha="{{ $fechaCellStr }}" 
                                                        data-id-chofer="{{ $prog->id_chofer }}"
                                                        data-chofer-nombre="{{ $prog->chofer->nombre }}"
                                                        data-tooltip="{{ $tooltipText }}">
                                                        SI
                                                    </button>
                                                @else
                                                    {{ $cellText }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Modal de Facturas de Traslado --}}
    <x-modal-show id="modalFacturasTraslado" title="Facturas de Traslado" size="large">
        <div class="p-4 space-y-4">
            {{-- Info del Chofer y Traslado --}}
            <div class="w-full flex items-center gap-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/50 rounded-2xl px-5 py-4 mb-2">
                <div class="flex-shrink-0 w-11 h-11 bg-blue-100 dark:bg-blue-800/60 rounded-xl flex items-center justify-center">
                    <i class="bi bi-truck text-blue-500 dark:text-blue-350 text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-widest text-blue-500 dark:text-blue-400 leading-none mb-1">Chofer Programado</p>
                    <p id="lblChoferNombre" class="text-base font-bold text-blue-800 dark:text-blue-200 leading-tight truncate">—</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Fecha de Traslado: <span id="lblFechaTraslado" class="font-bold text-gray-800 dark:text-gray-300"></span></p>
                </div>
            </div>

            {{-- Contenedor de la Tabla --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm divide-y divide-gray-200 dark:divide-gray-700 hidden" id="tablaFacturasTraslado">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3 font-bold text-left text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">ID Factura</th>
                                <th class="px-4 py-3 font-bold text-left text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Emisor</th>
                                <th class="px-4 py-3 font-bold text-left text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Receptor</th>
                                <th class="px-4 py-3 font-bold text-left text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">GRR</th>
                                <th class="px-4 py-3 font-bold text-left text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">GRT</th>
                                <th class="px-4 py-3 font-bold text-right text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Monto Neto</th>
                                <th class="px-4 py-3 font-bold text-center text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700" id="tbodyFacturasTraslado">
                            <!-- Dinámico -->
                        </tbody>
                    </table>
                </div>
                
                {{-- Cargando... --}}
                <div id="facturas-loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-2 border-blue-500 border-t-transparent mb-2"></div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Buscando facturas asociadas...</p>
                </div>

                {{-- Sin Datos --}}
                <div id="noFacturasMessage" class="hidden text-center py-12 text-gray-400 text-sm">
                    <i class="bi bi-inbox text-4xl block mb-3 text-gray-300 dark:text-gray-600"></i>
                    <p class="font-medium text-gray-500 dark:text-gray-400">No se encontraron facturas asociadas a este traslado.</p>
                </div>
            </div>
        </div>
    </x-modal-show>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-ver-facturas-traslado').forEach(button => {
                button.addEventListener('click', function() {
                    const fecha = this.dataset.fecha;
                    const idChofer = this.dataset.idChofer;
                    const choferNombre = this.dataset.choferNombre;
                    
                    // Formatear fecha para el usuario: DD/MM/YYYY
                    const parts = fecha.split('-');
                    const fechaFormateada = `${parts[2]}/${parts[1]}/${parts[0]}`;
                    
                    document.getElementById('lblFechaTraslado').textContent = fechaFormateada;
                    document.getElementById('lblChoferNombre').textContent = choferNombre;
                    
                    const tbody = document.getElementById('tbodyFacturasTraslado');
                    const noFacturas = document.getElementById('noFacturasMessage');
                    const loader = document.getElementById('facturas-loading');
                    const table = document.getElementById('tablaFacturasTraslado');
                    
                    // Limpiar y mostrar cargador
                    loader.classList.remove('hidden');
                    table.classList.add('hidden');
                    noFacturas.classList.add('hidden');
                    tbody.innerHTML = '';
                    
                    toggleModal('modalFacturasTraslado');
                    
                    // Cargar facturas por AJAX
                    fetch(`{{ route('programacion.por_fecha_chofer') }}?fecha=${fecha}&id_chofer=${idChofer}`)
                        .then(response => response.json())
                        .then(data => {
                            loader.classList.add('hidden');
                            if (data.length === 0) {
                                noFacturas.classList.remove('hidden');
                                return;
                            }
                            
                            table.classList.remove('hidden');
                            data.forEach(factura => {
                                const emisor = factura.emisor || '—';
                                const receptor = factura.receptor || '—';
                                const grr = factura.grr || '—';
                                const grt = factura.grt || '—';
                                
                                const simbolo = 'S/';
                                const monto = parseFloat(factura.monto_neto).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                
                                const tr = document.createElement('tr');
                                tr.className = 'hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition duration-150';
                                tr.innerHTML = `
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-200 font-medium">${factura.id_factura}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-350 font-semibold">${emisor}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-350">${receptor}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono">${grr}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono">${grt}</td>
                                    <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-200 font-bold whitespace-nowrap">${simbolo} ${monto}</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ url('/facturas') }}/${factura.id_factura}/detalle" 
                                           class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition duration-150 shadow-sm"
                                           target="_blank">
                                            <i class="bi bi-eye-fill"></i> Detalle
                                        </a>
                                    </td>
                                `;
                                tbody.appendChild(tr);
                            });
                        })
                        .catch(err => {
                            console.error('Error fetching invoices:', err);
                            loader.classList.add('hidden');
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="7" class="text-center py-6 text-red-500 font-semibold">
                                        <i class="bi bi-exclamation-triangle-fill mr-1"></i> Error al cargar las facturas.
                                    </td>
                                </tr>
                            `;
                            table.classList.remove('hidden');
                        });
                });
            });

            // Tooltip flotante global
            const tooltip = document.getElementById('custom-transporte-tooltip');
            
            document.querySelectorAll('.btn-ver-facturas-traslado').forEach(button => {
                button.addEventListener('mouseenter', function(e) {
                    const text = this.getAttribute('data-tooltip');
                    if (!text) return;
                    
                    tooltip.innerHTML = text;
                    tooltip.classList.remove('hidden');
                    
                    const rect = this.getBoundingClientRect();
                    const left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2);
                    const top = rect.top - tooltip.offsetHeight - 8;
                    
                    tooltip.style.left = `${Math.max(8, left)}px`;
                    tooltip.style.top = `${top}px`;
                    tooltip.style.opacity = '1';
                });
                
                button.addEventListener('mouseleave', function() {
                    tooltip.classList.add('hidden');
                    tooltip.style.opacity = '0';
                });
            });
        });
    </script>

    {{-- Tooltip flotante global --}}
    <div id="custom-transporte-tooltip" class="fixed hidden z-[9999] bg-gray-900/95 dark:bg-gray-950/95 text-white text-[11px] font-medium rounded-lg px-3 py-2 shadow-xl border border-gray-800 pointer-events-none transition-opacity duration-150 max-w-xs leading-relaxed opacity-0"></div>
</x-app-layout>
