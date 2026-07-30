<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Facturación Mensual por Empresa
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />
            <div class="bg-white shadow-lg rounded-lg p-4 mb-4">
                <form method="GET" action="{{ route('facturas.mes_facturacion') }}" class="flex gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
                        <select name="mes" id="mes" onchange="this.form.submit()" 
                            class="border-gray-300 rounded-md shadow-sm w-32 focus:ring-blue-500 focus:border-blue-500">
                            <option value="01" {{ $mes == '01' ? 'selected' : '' }}>Enero</option>
                            <option value="02" {{ $mes == '02' ? 'selected' : '' }}>Febrero</option>
                            <option value="03" {{ $mes == '03' ? 'selected' : '' }}>Marzo</option>
                            <option value="04" {{ $mes == '04' ? 'selected' : '' }}>Abril</option>
                            <option value="05" {{ $mes == '05' ? 'selected' : '' }}>Mayo</option>
                            <option value="06" {{ $mes == '06' ? 'selected' : '' }}>Junio</option>
                            <option value="07" {{ $mes == '07' ? 'selected' : '' }}>Julio</option>
                            <option value="08" {{ $mes == '08' ? 'selected' : '' }}>Agosto</option>
                            <option value="09" {{ $mes == '09' ? 'selected' : '' }}>Septiembre</option>
                            <option value="10" {{ $mes == '10' ? 'selected' : '' }}>Octubre</option>
                            <option value="11" {{ $mes == '11' ? 'selected' : '' }}>Noviembre</option>
                            <option value="12" {{ $mes == '12' ? 'selected' : '' }}>Diciembre</option>
                        </select>
                    </div>
                    <select name="anio" id="anio" onchange="this.form.submit()" 
                        class="border-gray-300 rounded-md shadow-sm w-32 focus:ring-blue-500 focus:border-blue-500">
                        @for ($i = 0; $i <= 5; $i++)
                            @php $year = now()->year - $i; @endphp
                            <option value="{{ $year }}" {{ (int) $anio === $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                    {{-- <button type="submit"
                        class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 
                        text-white font-semibold px-4 py-2 rounded-md transition w-full md:w-auto">
                        <i class="bi bi-search"></i> Buscar
                    </button> --}}
                    @if (Auth::user()->tipo_usuario == 1 || (Auth::user()->tipo_usuario == 2 && Auth::user()->area->nombre == 'AUDITORIA'))
                        <a href="{{ route('relaciones_facturacion.index') }}"
                            class="flex items-center justify-center gap-2 bg-indigo-500 px-4 py-2 rounded-md 
                        hover:bg-indigo-600 transition duration-300 text-white w-full md:w-auto">
                            <i class="bi bi-diagram-3"></i> Relaciones
                        </a>
                    @endif
                </form>
            </div>
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto text-sm" id="myTable-facturacion">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-700">
                            <th class="px-3 py-2 border-b">Estado</th>
                            <th class="px-3 py-2 border-b">Razón Social</th>
                            <th class="px-3 py-2 border-b">RUC</th>
                            <th class="px-3 py-2 border-b">Inventario</th>
                            <th class="px-3 py-2 border-b">Total Emitido (VENTAS)</th>
                            <th class="px-3 py-2 border-b">Total Recibido (COMPRAS)</th>
                            <th>Total Crédito Fiscal</th>
                            <th>Total a Facturar</th>
                            <th class="px-3 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($empresas as $empresa)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    {{ $empresa->estado }}
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        @if (Auth::user()->area->nombre === 'AUDITORIA' || Auth::user()->area->nombre === 'ADMINISTRACION')
                                            <span
                                                class="btn-ver-relaciones transition font-medium hover:text-blue-700 hover:underline cursor-pointer"
                                                data-id="{{ $empresa->id_empresas_clientes }}"
                                                data-nombre="{{ $empresa->razon_social }}"
                                                data-mes="{{ $mes }}" data-anio="{{ $anio }}">
                                                {{ $empresa->razon_social }}
                                            </span>
                                        @else
                                            {{ $empresa->razon_social }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-2">{{ $empresa->ruc }}</td>
                                <td class="px-3 py-2">
                                    @if ($empresa->inventarios->count())
                                        <span class="">
                                            {{ $empresa->inventarios->first()->nombre_codigo }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">
                                    S/. {{ number_format($empresa->total_mes, 2, '.', ',') }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    S/. {{ number_format($empresa->total_recibido_mes ?? 0, 2, '.', ',') }}
                                </td>
                                @php
                                    $creditoFiscal = $empresa->credito_fiscal_mes;
                                    $tieneCredito = $empresa->tiene_credito_fiscal;

                                    $totalCreditoConBase = 0;

                                    if ($tieneCredito) {
                                        $totalCreditoConBase = round($creditoFiscal / 0.18 + $creditoFiscal, 0);
                                    }

                                    $totalFacturar =
                                        $empresa->total_mes - $empresa->total_recibido_mes - $totalCreditoConBase;

                                    $claseTotalFacturar =
                                        $totalFacturar > 0
                                            ? 'bg-red-100 text-red-700 font-bold'
                                            : 'bg-gray-100 text-gray-700 font-bold';
                                @endphp
                                <td class="px-3 py-2 text-right">
                                    @if (!$empresa->tiene_credito_fiscal)
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold
                                            rounded bg-gray-100 text-gray-500">
                                            Sin registrar
                                        </span>
                                    @else
                                        S/. {{ number_format($totalCreditoConBase, 2) }}
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right {{ $claseTotalFacturar }}">
                                    S/. {{ number_format($totalFacturar, 2) }}
                                </td>

                                <td class="px-3 py-2">
                                    @php
                                        $totalMes = $empresa->total_mes ?? 0;
                                        $totalRecibido = $empresa->total_recibido_mes ?? 0;

                                        $creditoFiscal = $empresa->credito_fiscal_mes ?? 0;
                                        $tieneCredito = $empresa->tiene_credito_fiscal ?? false;

                                        $totalCreditoConBase = 0;
                                        if ($tieneCredito) {
                                            $totalCreditoConBase = round($creditoFiscal / 0.18 + $creditoFiscal, 0);
                                        }
                                        $pendiente = $totalMes - $totalRecibido - $totalCreditoConBase;
                                    @endphp

                                    <div class="flex items-center gap-2">
                                        @if (Auth::user()->area->nombre == 'AUDITORIA' || Auth::user()->area->nombre == 'ADMINISTRACION' || Auth::user()->id == 9)
                                            @if ($empresa->inventarios->count())
                                                @php
                                                    $inventarioId = $empresa->inventarios->first()->id_inventario;
                                                @endphp

                                                {{-- + Emitir --}}
                                                <x-dropdown align="right" width="48">
                                                    <x-slot name="trigger">
                                                        <button class="text-white px-4 py-2 rounded-md tooltip"
                                                            style="color: #10b981;">
                                                            <span class="tooltip-text">+Emitir</span>
                                                            <x-factura-icon />
                                                        </button>
                                                    </x-slot>

                                                    <x-slot name="content">
                                                        <a href="{{ url("/facturas/create/empresa/$inventarioId") }}"
                                                            class="block px-4 py-2 hover:bg-gray-100 rounded">
                                                            Empresa
                                                        </a>
                                                        <a href="{{ url("/facturas/create/cliente/$inventarioId") }}"
                                                            class="block px-4 py-2 hover:bg-gray-100 rounded">
                                                            Cliente
                                                        </a>
                                                    </x-slot>
                                                </x-dropdown>
                                            @endif

                                            <button class="btn-factura-global text-white px-4 py-2 rounded-md tooltip"
                                                data-id-receptor="{{ $empresa->id_empresas_clientes }}"
                                                data-tipo="empresa" data-nombre="{{ $empresa->razon_social }}"
                                                data-pendiente="{{ number_format($pendiente, 2, '.', '') }}"
                                                style="color:#3b82f6;">
                                                <span class="tooltip-text">+Recibir</span>
                                                <x-factura-icon />
                                            </button>
                                        @endif

                                        @if (Auth::user()->area->nombre == 'CONTABILIDAD' || Auth::user()->id == 9)
                                            @php
                                                $tieneCredito = ($empresa->credito_fiscal_mes ?? 0) > 0;
                                                $tooltip = $tieneCredito ? 'Act. Crédito Fiscal' : 'Crédito Fiscal';
                                                $colorBase = $tieneCredito ? '#F59E0B' : '#10B981';
                                                $colorHover = $tieneCredito ? '#D97706' : '#059669';
                                            @endphp

                                            <button type="button"
                                                class="btn-credito-fiscal flex items-center py-1 rounded-md tooltip"
                                                data-id="{{ $empresa->id_empresas_clientes }}"
                                                data-nombre="{{ $empresa->razon_social }}"
                                                data-mes="{{ $mes }}" data-anio="{{ $anio }}"
                                                data-igv="{{ number_format($empresa->credito_fiscal_mes ?? 0, 2, '.', '') }}"
                                                data-total-facturar-prev="{{ number_format($empresa->total_facturar_prev ?? 0, 2, '.', '') }}"
                                                data-igv-prev-sugerido="{{ number_format($empresa->saldo_prev_igv_sugerido ?? 0, 2, '.', '') }}"
                                                style="color: {{ $colorBase }}; transition: color 0.3s;"
                                                onmouseover="this.style.color='{{ $colorHover }}';"
                                                onmouseout="this.style.color='{{ $colorBase }}';">
                                                <span class="tooltip-text">{{ $tooltip }}</span>

                                                <x-calculator-icon />
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<div id="modalRelaciones" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">

    <!-- Contenedor externo (click fuera cierra) -->
    <div class="absolute inset-0" onclick="closeModalRelaciones()"></div>

    <!-- Card del modal -->
    <div class="relative bg-white w-[95vw] max-w-7xl rounded-2xl shadow-2xl p-6 max-h-[90vh] flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between border-b pb-4 mb-4">
            <h2 class="text-lg font-bold text-gray-800">
                Relaciones y Facturación
            </h2>

            <button onclick="closeModalRelaciones()" class="text-gray-400 hover:text-gray-700 transition"> ✕ </button>
        </div>

        <!-- Loader -->
        <div id="loaderRelaciones" class="flex flex-col items-center justify-center py-10">
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-purple-500 border-t-transparent"></div>
            <p class="mt-2 text-gray-500 font-medium">Cargando relaciones...</p>
        </div>

        <!-- Contenido -->
        <div id="contentRelaciones" class="hidden overflow-y-auto pr-2 flex-1">

            <!-- Headers -->
            <div class="grid grid-cols-3 gap-6 mb-4 text-center font-bold text-sm">
                <div class="text-red-700 bg-red-100 py-2 rounded">R2</div>
                <div class="text-orange-700 bg-orange-100 py-2 rounded">R1</div>
                <div class="text-yellow-700 bg-yellow-100 py-2 rounded">A1</div>
            </div>

            <!-- Columns -->
            <div class="grid grid-cols-3 gap-6">
                <div id="col-R2" class="bg-red-50 p-3 rounded-lg space-y-4"></div>
                <div id="col-R1" class="bg-orange-50 p-3 rounded-lg space-y-4"></div>
                <div id="col-A1" class="bg-yellow-50 p-3 rounded-lg space-y-4"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('modalRelaciones');
        const loader = document.getElementById('loaderRelaciones');
        const content = document.getElementById('contentRelaciones');

        const colR2 = document.getElementById('col-R2');
        const colR1 = document.getElementById('col-R1');
        const colA1 = document.getElementById('col-A1');

        document.querySelectorAll('.btn-ver-relaciones').forEach(btn => {
            btn.addEventListener('click', () => {
                // 1. Mostrar Modal y Loader
                loader.classList.remove('hidden');
                content.classList.add('hidden');
                toggleModal('modalRelaciones');

                // 2. Obtener Datos
                const idEmpresa = btn.dataset.id;
                const mes = btn.dataset.mes;
                const anio = btn.dataset.anio;

                fetch(
                        `/facturas/ajax/relaciones?id_empresa=${idEmpresa}&mes=${mes}&anio=${anio}`
                    )
                    .then(res => res.json())
                    .then(data => {
                        // 3. Renderizar
                        renderColumn(colR2, data.R2, 'bg-white border-red-200');
                        renderColumn(colR1, data.R1, 'bg-white border-orange-200');
                        renderColumn(colA1, data.A1, 'bg-white border-yellow-200');

                        loader.classList.add('hidden');
                        content.classList.remove('hidden');
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error al cargar relaciones');
                        loader.classList.add('hidden');
                    });
            });
        });

        function renderColumn(container, items, cardClass) {
            container.innerHTML = '';
            if (!items || items.length === 0) {
                // container.innerHTML ='<p class="text-center text-gray-400 italic py-2">-</p>';
                return;
            }

            items.forEach(item => {
                const card = document.createElement('div');
                // Asignar ID único para poder buscarlo luego
                card.id = `card-empresa-${item.id || item.id_empresas_clientes}`;

                card.className = `
        ids-card mb-4 p-4 rounded-xl border shadow-sm 
        ${cardClass} hover:shadow-md transition bg-white
    `;

                // Generar HTML de acciones (restaurado)
                const accionesHtml = `
                    <div class="flex gap-2 pt-3 mt-2 border-t justify-end relative">
                        <!-- relacion R1 Dropdown / Botón Simple -->
                        ${item.estado === 'R1' && item.empresas_relacionadas && item.empresas_relacionadas.length ? `
                        <div class="relative inline-block text-left">
                            <button type="button" class="tooltip px-3 py-2 rounded-md text-orange-500 hover:text-orange-600 transition" onclick="toggleDropdown(this)">
                                <span class="tooltip-text">Ver A1 relacionadas</span>
                                <i class="bi bi-diagram-3"></i>
                            </button>
                            
                            <div class="absolute right-0 mt-2 w-56 bg-white border rounded-md shadow-lg hidden z-50"> 
                                <!-- Lista de A1 individuales -->
                                ${item.empresas_relacionadas.map(a1 => `
                                    <button type="button" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-sm text-gray-600" onclick='highlightSingleA1("${a1.id || a1.id_empresas_clientes}"); this.parentElement.classList.add("hidden");'>
                                        <i class="bi bi-person me-2"></i> ${a1.razon_social}
                                    </button>
                                `).join('')}
                            </div>
                        </div>` 
                        : ''}

                        <!-- + Emitir (dropdown JS) -->
                        ${item.inventario_id ? `
                        <div class="relative">
                            <button class="tooltip px-3 py-2 rounded-md text-emerald-500 hover:text-emerald-600 transition" onclick="this.nextElementSibling.classList.toggle('hidden')">
                                <span class="tooltip-text">+Emitir</span>
                                <i class="bi bi-receipt"></i>
                            </button>

                        <div class="absolute right-0 mt-2 w-40 bg-white border rounded-md shadow-lg hidden z-50">
                            <a href="/facturas/create/empresa/${item.inventario_id}" class="block px-4 py-2 hover:bg-gray-100">
                                <i class="bi bi-building"></i> Empresa
                            </a>

                            <a href="/facturas/create/cliente/${item.inventario_id}" class="block px-4 py-2 hover:bg-gray-100">
                                <i class="bi bi-person"></i> Cliente
                            </a>
                        </div>
                        </div>` : ''}

                        <!-- + Recibir -->
                        <button class="btn-factura-global tooltip text-blue-500 hover:text-blue-600 transition px-3 py-2 rounded-md" data-id-receptor="${item.id || item.id_empresas_clientes}" data-tipo="empresa" data-nombre="${item.razon_social}" data-pendiente="${item.total_factura}">
                            <span class="tooltip-text">+Recibir</span>
                            <i class="bi bi-receipt"></i>
                        </button>
                    </div>
                `;
                card.innerHTML = getCardContentHtml(item, accionesHtml);
                container.appendChild(card);
            });
        }
    });

    // Nuevo helper para solo generar HTML
    function getCardContentHtml(item, extraContent = '') {
        const cfRaw = item.credito_fiscal;
        let cfDisplay = '';
        if (cfRaw !== undefined && cfRaw !== null) {
            const cfStr = cfRaw.toString();
            cfDisplay = cfStr.startsWith('S/.') ? cfStr : 'S/. ' + cfStr;
        }

        const claseTotal = item.clase_total || 'bg-gray-100 text-gray-700';

        return `
        <!-- Header -->
        <div class="flex justify-between items-start mb-3">
            <div class="pr-2">
                <span class="block font-semibold text-gray-800 text-sm leading-snug">
                    ${item.razon_social}
                </span>
                ${item.ruc ? `<span class="text-xs text-gray-500">RUC: ${item.ruc}</span>` : ''}
            </div>
        </div>

        <!-- Body -->
        <div class="space-y-2 text-sm border-t pt-3 border-gray-100">
            ${item.total_emitido !== undefined ? `
            <div class="flex justify-between">
                <span class="text-gray-600">Emitido</span>
                <span class="font-medium text-gray-800">S/. ${item.total_emitido}</span>
            </div>` : ''}

            ${item.total_recibido !== undefined ? `
            <div class="flex justify-between">
                <span class="text-gray-600">Recibido</span>
                <span class="font-medium text-blue-600">S/. ${item.total_recibido}</span>
            </div>` : ''}

            ${item.credito_fiscal !== undefined ? `
            <div class="flex justify-between">
                <span class="text-gray-600">Crédito Fiscal</span>
                <span class="font-medium text-green-600">
                    ${cfDisplay}
                </span>
            </div>` : ''}

            ${item.total_factura !== undefined ? `
            <!-- Total -->
            <div class="flex justify-between items-center pt-3 mt-3 border-t border-gray-100">
                <span class="font-semibold text-gray-700">Total</span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${claseTotal}">
                    S/. ${item.total_factura}
                </span>
            </div>` : ''}

            ${extraContent}
        </div>
        `;
    }

    function openModalRelaciones() {
        document.getElementById('modalRelaciones').classList.remove('hidden');
        // Bloquear scroll del fondo
        document.body.classList.add('overflow-hidden');
    }

    function closeModalRelaciones() {
        document.getElementById('modalRelaciones').classList.add('hidden');
        // Restaurar scroll
        document.body.classList.remove('overflow-hidden');
    }
</script>

<script>
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-factura-global');
        if (!btn) return;

        openModalInventarios({
            id: btn.dataset.idReceptor,
            tipo: btn.dataset.tipo,
            nombre: btn.dataset.nombre,
            pendiente: btn.dataset.pendiente
        });
    });

    function toggleDropdown(button) {
        const dropdown = button.nextElementSibling;
        if (!dropdown) return;

        dropdown.classList.toggle('hidden');

        if (dropdown.classList.contains('hidden')) return;

        // Reset clases
        dropdown.classList.remove('bottom-full', 'mb-2', 'mt-2');

        const rect = dropdown.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.top;
        const spaceAbove = rect.top;

        // Si no hay espacio abajo, abrir arriba
        if (spaceBelow < 200 && spaceAbove > spaceBelow) {
            dropdown.classList.add('bottom-full', 'mb-2');
        } else {
            dropdown.classList.add('mt-2');
        }
    }
    // Función para resaltar una A1 específica desde el dropdown
    function highlightSingleA1(id) {
        const colA1 = document.getElementById('col-A1');
        const cards = colA1.querySelectorAll('.ids-card');

        // 1. Restaurar estado normal de todas
        cards.forEach(card => {
            card.classList.remove('ring-4', 'ring-blue-300', 'border-blue-500', 'bg-blue-50');
            card.classList.add('bg-white', 'border-yellow-200');
            card.style.opacity = '1';
        });

        // 2. Resaltar la seleccionada
        const targetCard = document.getElementById(`card-empresa-${id}`);
        if (targetCard) {
            targetCard.classList.remove('bg-white', 'border-yellow-200');
            // Resaltado llamativo
            targetCard.classList.add('ring-4', 'border-blue-500', 'bg-gray-50');
            // Scroll suave
            targetCard.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        } else {
            console.warn(`Card A1 con id ${id} no encontrada.`);
        }
    }
</script>

<x-modal-show id="modalCreditoFiscal" title="Registrar Crédito Fiscal Total del Mes Actual">
    <form method="POST" action="{{ route('credito_fiscal.guardar') }}">
        @csrf

        <input type="hidden" name="id_empresas_clientes" id="cf_empresa">
        <input type="hidden" name="mes" id="cf_mes">
        <input type="hidden" name="anio" id="cf_anio">

        <p class="mb-2 text-sm">
            Empresa: <strong id="cf_nombre"></strong>
        </p>

        {{-- Solo se muestra si el mes anterior fue negativo --}}
        <div id="cf_prev_wrap" class="hidden mb-3 p-2 rounded bg-yellow-50 text-yellow-800 text-sm">
            <div>Saldo a favor (Total a facturar) del mes anterior:</div>
            <div class="font-bold">S/. <span id="cf_prev_total">0.00</span></div>

            <button type="button" id="cf_btn_usar_prev" class="mt-2 text-xs underline font-semibold">
                Usar IGV sugerido del saldo anterior
            </button>
        </div>

        <label class="block text-sm font-medium">
            Crédito fiscal (IGV 18%)
        </label>
        <input type="number" step="0.01" name="monto" id="cf_igv_input"
            class="w-full border rounded px-3 py-2 mt-1">

        <div class="mt-3 text-sm text-gray-700">
            <p class="hidden">Base: <strong>S/. <span id="cf_base">0.00</span></strong></p>
            <p>Total (incluye IGV): <strong>S/. <span id="cf_total">0.00</span></strong></p>
        </div>

        <div class="mt-4 text-right">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">
                Guardar
            </button>
        </div>
    </form>
</x-modal-show>
<script>
    const igvInput = document.getElementById('cf_igv_input');
    const baseSpan = document.getElementById('cf_base');
    const totalSpan = document.getElementById('cf_total');

    const prevWrap = document.getElementById('cf_prev_wrap');
    const prevTotalSpan = document.getElementById('cf_prev_total');
    const btnUsarPrev = document.getElementById('cf_btn_usar_prev');

    function recalcularDesdeIGV() {
        const igv = parseFloat(igvInput.value) || 0;
        const base = igv / 0.18;
        const total = base + igv;

        baseSpan.textContent = base.toFixed(2);
        totalSpan.textContent = total.toFixed(0);
    }

    igvInput.addEventListener('input', recalcularDesdeIGV);

    document.querySelectorAll('.btn-credito-fiscal').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('cf_empresa').value = btn.dataset.id;
            document.getElementById('cf_mes').value = parseInt(btn.dataset.mes);
            document.getElementById('cf_anio').value = parseInt(btn.dataset.anio);
            document.getElementById('cf_nombre').textContent = btn.dataset.nombre;

            const igvActual = parseFloat(btn.dataset.igv) || 0;
            const totalFactPrev = parseFloat(btn.dataset.totalFacturarPrev) || 0;
            const igvPrevSug = parseFloat(btn.dataset.igvPrevSugerido) || 0;

            // 👉 PRIORIDAD DE PREFILL
            if (totalFactPrev < 0 && igvPrevSug > 0) {
                // ✅ Caso correcto: hay saldo a favor → usar automáticamente
                prevWrap.classList.remove('hidden');
                prevTotalSpan.textContent = totalFactPrev.toFixed(2);

                igvInput.value = igvPrevSug.toFixed(2);
                recalcularDesdeIGV();

                // Botón sigue existiendo por si el usuario quiere re-aplicar
                btnUsarPrev.onclick = () => {
                    igvInput.value = igvPrevSug.toFixed(2);
                    recalcularDesdeIGV();
                };
            } else {
                // ✅ Caso normal: usar lo que ya estaba registrado en el mes
                prevWrap.classList.add('hidden');
                prevTotalSpan.textContent = '0.00';

                igvInput.value = igvActual.toFixed(2);
                recalcularDesdeIGV();

                btnUsarPrev.onclick = null;
            }

            recalcularDesdeIGV();
            toggleModal('modalCreditoFiscal');
        });
    });
</script>

<x-modal-show id="modalInventariosGlobal" title="Selecciona un inventario para facturar">
    <div class="text-sm text-gray-800 max-h-[70vh] overflow-y-auto px-1">

        <p><strong>Destinatario:</strong> <span id="nombreDestinatario"></span></p>

        <div id="loaderInventarios" class="w-full text-center py-6 hidden">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-green-500 border-t-transparent">
            </div>
            <p class="mt-2 text-gray-700">Cargando inventarios...</p>
        </div>

        <table class="min-w-full mt-3 bg-white rounded-md text-sm hidden" id="tablaInventarios">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">Inventario</th>
                    <th class="px-4 py-2 text-left">Código</th>
                    <th class="px-4 py-2 text-left">Acción</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</x-modal-show>

<script>
    let tipoFactura = null;
    let idReceptor = null;
    let pendientePorFacturar = null;

    // Esta función se llama desde el listener delegado (definido más arriba/abajo)
    function openModalInventarios({
        id,
        tipo,
        nombre,
        pendiente
    }) {
        tipoFactura = tipo;
        idReceptor = id;
        pendientePorFacturar = pendiente ?? 0;

        document.getElementById('nombreDestinatario').textContent = nombre;

        const loader = document.getElementById('loaderInventarios');
        const tabla = document.getElementById('tablaInventarios');

        loader.classList.remove('hidden');
        tabla.classList.add('hidden');
        toggleModal('modalInventariosGlobal');
        requestAnimationFrame(() => {
            fetch(`/ajax/facturacion/inventarios-relacionados?id_receptor=${idReceptor}&tipo=${tipoFactura}`)
                .then(res => res.json())
                .then(data => {
                    const tbody = tabla.querySelector('tbody');
                    tbody.innerHTML = '';

                    data.forEach(inv => {
                        tbody.innerHTML += `
                            <tr>
                                <td class="px-4 py-2 border-b">${inv.empresa?.razon_social ?? '—'}</td>
                                <td class="px-4 py-2 border-b">${inv.nombre_codigo ?? '—'}</td>
                                <td class="px-4 py-2 border-b">
                                    <button
                                        type="button"class="btn-redirigir-factura flex items-center py-1 rounded-md tooltip" data-id="${inv.id_inventario}" style="color:#10b981;"> 
                                        <span class="tooltip-text">+Factura</span>
                                        <i class="bi bi-receipt"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    attachFacturaButtons();
                    loader.classList.add('hidden');
                    tabla.classList.remove('hidden');
                })
                .catch(err => {
                    console.error(err);
                    alert("Error al cargar inventarios");
                    loader.classList.add('hidden');
                });
        });
    }

    function attachFacturaButtons() {
        document.querySelectorAll('.btn-redirigir-factura').forEach(btn => {
            btn.addEventListener('click', () => {

                const idInventario = btn.dataset.id;

                const urlBase = tipoFactura === 'empresa' ?
                    `/facturas/create/empresa/${idInventario}` :
                    `/facturas/create/cliente/${idInventario}`;
                window.location.href =
                    `${urlBase}?receptor=${idReceptor}&pendiente=${pendientePorFacturar}`;
            });
        });
    }
</script>

<script>
    $(document).ready(function() {
        $('#myTable-facturacion').DataTable({
            responsive: false, // Hacer la tabla responsive
            dom: 'Bfrtip', // Activa el diseño para los botones
            pageLength: 200,
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: function() {
                    const hoy = new Date();
                    const dia = hoy.getDate().toString().padStart(2, '0');
                    const mes = (hoy.getMonth() + 1).toString().padStart(2, '0');
                    const anio = hoy.getFullYear();
                    return `FACTURACION_MENSUAL_EMPRESAS_DESCARGADO_${dia}_${mes}_${anio}`;
                },
                exportOptions: {
                    columns: ':not(:last)' // Excluye la primera y la última columna
                }
            }],
            language: {
                url: "/js/datatables/lang/es-ES.json" // Textos en español
            },
        });
    });
</script>
