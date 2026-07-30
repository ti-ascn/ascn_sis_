@php
    use Carbon\Carbon;

    // CORRECCIÓN: Mostrar el mes actual (no el mes pasado)
    $periodo = Carbon::createFromDate((int) $anio, (int) $mes, 1)->locale('es');

    $mesNombre = $periodo->monthName;
    $anioPeriodo = $periodo->year;

    // Navegación de mes
    $cur = Carbon::createFromDate((int) $anio, (int) $mes, 1);
    $prev = $cur->copy()->subMonth();
    $next = $cur->copy()->addMonth();

    $baseDetalle = url("/deudas/{$tipo}/" . $empresa->id_empresas_clientes);
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            Deudas
            <x-dropdown align="left" width="48">
                <x-slot name="trigger">
                    <button class="px-2 py-1 rounded border flex items-center gap-1">
                        ({{ ucfirst($tipo) }})
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </x-slot>
                <x-slot name="content">
                    <div class="absolute z-50 w-40 bg-white shadow-lg rounded-md border p-2">
                        <x-dropdown-link :href="route('deudas.detalle', [
                            'tipo' => 'contabilidad',
                            'empresa' => $empresa->id_empresas_clientes,
                            'anio' => $anio,
                            'mes' => $mes,
                        ])">
                            <i class="bi bi-journal-text mr-2 text-green-500"></i> Contabilidad
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('deudas.detalle', [
                            'tipo' => 'recursos',
                            'empresa' => $empresa->id_empresas_clientes,
                            'anio' => $anio,
                            'mes' => $mes,
                        ])">
                            <i class="bi bi-person-lines-fill mr-2 text-blue-500"></i> Recursos
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('deudas.detalle', [
                            'tipo' => 'ambas',
                            'empresa' => $empresa->id_empresas_clientes,
                            'anio' => $anio,
                            'mes' => $mes,
                        ])">
                            <i class="bi bi-layers-fill mr-2 text-purple-500"></i> Ambas
                        </x-dropdown-link>
                    </div>
                </x-slot>
            </x-dropdown>
            — {{ $empresa->razon_social }} {{ $empresa->ruc }}
            Período <span class="text-blue-500">{{ Str::upper($mesNombre) }}-{{ $anioPeriodo }}</span>
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            {{-- Barra: cambio de mes/año --}}
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <a href="{{ $baseDetalle . '/' . $prev->year . '/' . $prev->month }}"
                    class="inline-flex items-center px-3 py-1.5 rounded border text-sm hover:bg-gray-50">
                    ‹ Mes anterior
                </a>

                <div class="flex items-center gap-2">
                    <label for="month-picker" class="text-sm text-gray-700">Ir a mes:</label>
                    <input id="month-picker" type="month" value="{{ sprintf('%04d-%02d', $anio, $mes) }}"
                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="button" class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm hover:bg-blue-700"
                        onclick="goToSelectedMonth()">
                        Ir
                    </button>
                </div>

                <a href="{{ $baseDetalle . '/' . $next->year . '/' . $next->month }}"
                    class="inline-flex items-center px-3 py-1.5 rounded border text-sm hover:bg-gray-50">
                    Mes siguiente ›
                </a>

                <a href="{{ route('determinacion_mensual.show', $determinacion->id_determinacion_mensual ?? 0) }}"
                    class="flex items-center gap-2 bg-green-800 px-4 py-2 rounded-md hover:bg-green-900 transition text-white">
                    <i class="bi bi-newspaper"></i> Determinación Mensual
                </a>

                {{-- DROPDOWN ARCHIVOS --}}
                <div class="relative">
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition text-white">
                                <i class="bi bi-folder2-open"></i>
                                Archivos
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="p-2">
                                <!-- Ver Constancia con submenú -->
                                <div class="relative" x-data="{ abiertoConstancia: false }">
                                    <button @mouseenter="abiertoConstancia = true"
                                        @mouseleave="abiertoConstancia = false"
                                        class="w-full text-left px-3 py-2 hover:bg-gray-100 rounded-md flex items-center justify-between gap-2">
                                        <span class="flex items-center gap-2">
                                            <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                            Ver Constancia
                                        </span>
                                        <i class="bi bi-chevron-right text-gray-400 text-xs"></i>
                                    </button>

                                    <!-- Submenú Constancia -->
                                    <div x-show="abiertoConstancia" @mouseenter="abiertoConstancia = true"
                                        @mouseleave="abiertoConstancia = false" x-transition
                                        class="absolute left-full top-0 bg-white border border-gray-200 shadow-lg rounded-md min-w-max z-50 p-1">

                                        @if ($determinacion)
                                            @if ($determinacion->archivo_pdt_constancia)
                                                <a href="{{ asset($determinacion->archivo_pdt_constancia) }}"
                                                    target="_blank"
                                                    class="w-full px-3 py-2 hover:bg-gray-100 rounded-md flex items-center gap-2">
                                                    <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                                    Constancia Original
                                                </a>
                                            @endif

                                            @if ($determinacion->archivo_pdt_constancia_rectificado)
                                                <a href="{{ asset($determinacion->archivo_pdt_constancia_rectificado) }}"
                                                    target="_blank"
                                                    class="w-full px-3 py-2 hover:bg-gray-100 rounded-md flex items-center gap-2">
                                                    <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                                    Constancia Rectificado 1
                                                </a>
                                            @endif

                                            @for ($i = 2; $i <= 4; $i++)
                                                @php $fieldName = "pdt_constancia_rectificatorio_{$i}"; @endphp
                                                @if ($determinacion->$fieldName)
                                                    <a href="{{ asset($determinacion->$fieldName) }}" target="_blank"
                                                        class="w-full px-3 py-2 hover:bg-gray-100 rounded-md flex items-center gap-2">
                                                        <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                                        Constancia Rectificado {{ $i }}
                                                    </a>
                                                @endif
                                            @endfor
                                        @else
                                            <p class="px-3 py-2 text-sm text-gray-400 italic">Sin determinación registrada.</p>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                {{-- Vencimiento PDT del mes mostrado --}}
                <div class="ml-auto flex items-center gap-3">
                    @if ($vencimientoMes)
                        <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-1.5 rounded">
                            <i class="bi bi-calendar-event"></i>
                            <span>Vencimiento (PDT): <strong>{{ $vencimientoMes->format('d/m/Y') }}</strong></span>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-2 bg-amber-50 text-amber-700 px-3 py-1.5 rounded">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span>Vencimiento no configurado para este mes.</span>
                        </div>
                    @endif

                    <button type="button"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded bg-green-500 text-white text-sm hover:bg-green-600"
                        onclick="abrirModalNuevaDeuda()">
                        <i class="bi bi-plus-circle"></i> Nueva deuda
                    </button>
                    <a href="{{ route('deudas.pdf.detalle', [$tipo, $empresa->id_empresas_clientes, $anio, $mes]) }}"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded bg-red-600 text-white text-sm hover:bg-red-700">
                        <i class="bi bi-filetype-pdf"></i> Exportar PDF
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto bg-gray-50 shadow-lg rounded-lg">
                <table class="min-w-[1200px] table-auto text-sm" id="myTable-detalle">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-700">
                            <th class="px-3 py-2 border-b">TRIBUTO</th>
                            <th class="px-3 py-2 border-b !text-right">DEUDA</th>
                            <th class="px-3 py-2 border-b !text-right">INTERÉS</th>
                            <th class="px-3 py-2 border-b !text-right">DEUDA TOTAL</th>
                            <th class="px-3 py-2 border-b">FECHA VENC.</th>
                            <th class="px-3 py-2 border-b">FECHA PAGO</th>
                            <th class="px-3 py-2 border-b !text-right">MONTO TOTAL PAGADO</th>
                            <th class="px-3 py-2 border-b">OBSERVACIONES</th>
                            <th class="px-3 py-2 border-b">CREADA POR</th>
                            <th class="px-3 py-2 border-b">ESTADO</th>
                            <th class="px-3 py-2 border-b">ACCIONES</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @foreach ($rows as $r)
                            @php
                                $principalPend = max(0, $r['monto_deuda'] - ($r['pagado_principal'] ?? 0));
                                $interesPagado = $r['interes_pagado'] ?? 0;
                                $interesPendiente = $r['interes_restante'] ?? 0;

                                $completamentePagado = $principalPend === 0 && $interesPendiente === 0;
                                $mostrarPagar = $principalPend > 0 || $interesPendiente > 0;

                                $interesAMostrar = $interesPendiente > 0 ? $interesPendiente : $interesPagado;
                                $mostrarInteres = $interesAMostrar > 0;
                            @endphp


                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    {{ $r['tributo'] }}
                                </td>
                                <td class="px-3 py-2 !text-right">
                                    S/. {{ number_format($r['monto_deuda'], 0, '.', ',') }}
                                </td>
                                <td
                                    class="px-3 py-2 !text-right {{ $interesPendiente > 0 ? 'text-red-600 font-semibold' : ($mostrarInteres ? 'text-gray-600' : 'text-gray-400') }}">
                                    @if ($mostrarInteres)
                                        S/. {{ number_format($interesAMostrar, 0, '.', ',') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">
                                    S/. {{ number_format($r['monto_deuda'] + $interesAMostrar, 0, '.', ',') }}
                                </td>
                                <td class="px-3 py-2">{{ $r['fecha_vcto'] }}</td>
                                <td class="px-3 py-2">{{ $r['fecha_pago'] ?? '—' }}</td>
                                <td class="px-3 py-2 text-right">
                                    S/. {{ number_format($r['pagado_total'], 0, '.', ',') }}
                                </td>
                                <td>
                                    {{ $r['observaciones'] ?? '—' }}
                                </td>
                                <td class="px-3 py-2">{{ $r['autor_deuda'] ?? '—' }}</td>
                                <td class="px-3 py-2">
                                    @if ($r['estado'] === 'Pendiente')
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700">
                                            Pendiente
                                        </span>
                                    @elseif ($r['estado'] === 'Pagado')
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-700">
                                            Pagado
                                        </span>
                                    @elseif ($r['estado'] === 'Sin movimiento')
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-gray-200 text-gray-600">
                                            Sin movimiento
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @php
                                        $tienePagos = \App\Models\PagoDeuda::where(
                                            'id_deuda',
                                            $r['id_deuda'],
                                        )->exists();
                                    @endphp

                                    @if ($tienePagos)
                                        <button type="button" onclick="abrirModalDetalles('{{ $r['id_deuda'] }}')"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #2563eb; transition: color 0.3s; margin-left:6px;"
                                            onmouseover="this.style.color='#1d4ed8';"
                                            onmouseout="this.style.color='#2563eb';">
                                            <span class="tooltip-text">Ver detalles</span>
                                            <x-details-icon />
                                        </button>
                                    @endif
                                    <button type="button"
                                        onclick="abrirModalCambiarTributo('{{ $r['id_deuda'] }}', '{{ addslashes($r['observaciones'] ?? '') }}')"
                                        class="flex items-center py-1 rounded-md tooltip"
                                        style="color:#f59e0b; transition:color 0.3s; margin-left:6px;"
                                        onmouseover="this.style.color='#d97706';"
                                        onmouseout="this.style.color='#f59e0b';">
                                        <span class="tooltip-text">Cambiar tributo</span>
                                        <x-change-icon />
                                    </button>
                                    @if ($mostrarPagar)
                                        @php
                                            $principalPend = max(
                                                0,
                                                $r['monto_deuda'] - ($r['pagado_total'] - /*intereses*/ 0),
                                            );
                                            $interesRest = $r['interes_restante'] ?? 0;
                                            $mostrarPagar = $principalPend > 0 || $interesRest > 0;
                                        @endphp
                                        <button type="button"
                                            onclick="abrirModalPago({
                                                idDeuda: '{{ $r['id_deuda'] }}',
                                                tributo: '{{ addslashes($r['tributo']) }}',
                                                montoDeuda: '{{ $r['monto_deuda'] }}',
                                                fechaVcto: '{{ \Carbon\Carbon::createFromFormat('d/m/Y', $r['fecha_vcto'])->format('Y-m-d') }}',
                                                pagadoPrincipal: '{{ $r['pagado_principal'] ?? 0 }}',
                                                interesPendiente: '{{ $r['interes_restante'] ?? 0 }}',
                                                interesPagado: '{{ $r['interes_pagado'] ?? 0 }}',
                                                observaciones: '{{ trim(preg_replace("/\r|\n/", ' ', addslashes($r['observaciones'] ?? ''))) }}'
                                            })"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #10B981; transition: color 0.3s;"
                                            onmouseover="this.style.color='#059669';"
                                            onmouseout="this.style.color='#10B981';">
                                            <span class="tooltip-text">Pagar</span>
                                            <x-tax-icon />
                                        </button>

                                        {{-- Solo usuarios tipo 1 o 2 y que NO sean de CONTABILIDAD pueden eliminar --}}
                                        @if (
                                            ((Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2) &&
                                                Auth::user()->area->nombre != 'CONTABILIDAD') ||
                                                now()->lessThan('2025-10-30'))
                                            <form action="{{ route('deudas.destroy', $r['id_deuda']) }}" method="POST"
                                                style="display:inline;"
                                                onsubmit="return confirm('¿Seguro que deseas eliminar esta deuda y sus pagos?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626; transition: color 0.3s; margin-left:6px;"
                                                    onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';">
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        {{-- Si ya está pagado --}}
                                        @if (
                                            ((Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2) &&
                                                Auth::user()->area->nombre != 'CONTABILIDAD') ||
                                                now()->lessThan('2025-10-30'))
                                            <form action="{{ route('deudas.destroy', $r['id_deuda']) }}"
                                                method="POST" style="display:inline;"
                                                onsubmit="return confirm('¿Seguro que deseas eliminar esta deuda y sus pagos?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';">
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="bg-gray-50 font-semibold">
                            <th class="px-3 py-2 text-right border-t">TOTAL</th>
                            <th class="px-3 py-2 !text-right border-t">
                                S/. {{ number_format($totales['deuda'], 0, '.', ',') }}</th>
                            <th class="px-3 py-2 !text-right border-t">
                                S/. {{ number_format($totales['interes'], 0, '.', ',') }}</th>
                            <th class="px-3 py-2 !text-right border-t">
                                S/. {{ number_format($totales['deuda'] + $totales['interes'], 0, '.', ',') }}
                            </th>
                            <th class="px-3 py-2 border-t">&nbsp;</th>
                            <th class="px-3 py-2 border-t">&nbsp;</th>
                            <th class="px-3 py-2 !text-right border-t">
                                S/. {{ number_format($totales['pagado'], 0, '.', ',') }}
                            </th>
                            <th class="px-3 py-2 border-t">&nbsp;</th>
                            <th class="px-3 py-2 border-t">&nbsp;</th>
                            <th class="px-3 py-2 !text-left border-t">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $totales['saldo'] > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    Saldo: S/. {{ number_format($totales['saldo'], 0, '.', ',') }}
                                </span>
                                <br>
                                <small class="text-gray-600">
                                    (Int. pagado: S/. {{ number_format($totales['interes_pagado'], 0, '.', ',') }})
                                </small>
                            </th>
                            <th class="px-3 py-2 border-t">&nbsp;</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<x-modal-show id="modal-cambiar-tributo" title="Cambiar tributo de deuda">
    <form method="POST" id="form-cambiar-tributo">
        @csrf
        @method('PUT')

        <input type="hidden" name="id_deuda" id="ct-id-deuda">

        {{-- Selector de tributo --}}
        <div class="mb-4">
            <x-input-label :value="__('Selecciona nuevo tributo')" />
            <select name="id_tributo" id="ct-id-tributo"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                @foreach ($tributos as $t)
                    <option value="{{ $t->id_tributo }}">{{ $t->nombre }} ({{ $t->codigo }})</option>
                @endforeach
            </select>
        </div>

        {{-- Campo de observaciones en input --}}
        <div class="mb-4">
            <x-input-label :value="__('Observaciones')" />
            <x-text-input name="observaciones" id="ct-observaciones" type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                placeholder="Motivo o comentario (opcional)" />
        </div>

        <div class="flex justify-end gap-3">
            <x-primary-button>Cambiar tributo</x-primary-button>
        </div>
    </form>
</x-modal-show>

<script>
    function abrirModalCambiarTributo(idDeuda, observaciones = '') {
        const form = document.getElementById('form-cambiar-tributo');
        form.action = `/deudas/${idDeuda}/cambiar-tributo`;
        document.getElementById('ct-id-deuda').value = idDeuda;
        document.getElementById('ct-observaciones').value = observaciones || '';
        toggleModal('modal-cambiar-tributo');
    }
</script>


{{-- !COMIENZO AJAX --}}
<x-modal-show id="modal-detalles-deuda" title="Detalles de la deuda">
    <div id="detalles-contenido" class="space-y-4 text-sm text-gray-700">
        <p class="text-center text-gray-400 italic">Cargando detalles...</p>
    </div>

    <div class="flex justify-end mt-4">
        <x-secondary-button onclick="toggleModal('modal-detalles-deuda')">
            Cerrar
        </x-secondary-button>
    </div>
</x-modal-show>
<script>
    function abrirModalDetalles(idDeuda) {
        const cont = document.getElementById('detalles-contenido');
        cont.innerHTML = `<p class="text-center text-gray-400 italic">Cargando detalles...</p>`;
        toggleModal('modal-detalles-deuda');

        fetch(`/deudas/${idDeuda}/pagos`)
            .then(res => res.json())
            .then(data => {
                let html = `
                <div class="border-b pb-2 mb-2">
                    <strong>Tributo:</strong> ${data.tributo}<br>
                    <strong>Monto original:</strong> S/. ${data.monto_deuda}<br>
                    <strong>Fecha de vencimiento:</strong> ${data.fecha_venc}
                </div>
            `;

                if (data.pagos.length === 0) {
                    html += `<p class="text-center text-gray-400 italic">No se registran pagos.</p>`;
                } else {
                    html += `
                    <table class="w-full text-sm border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-2 py-1 border">Fecha de pago</th>
                                <th class="px-2 py-1 border text-right">Monto</th>
                                <th class="px-2 py-1 border text-right">Interés</th>
                                <th class="px-2 py-1 border text-right">Total</th>
                                <th class="px-2 py-1 border">Autor</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.pagos.map(p => `
                                <tr>
                                    <td class="px-2 py-1 border text-center">${p.fecha}</td>
                                    <td class="px-2 py-1 border text-right">S/. ${p.monto}</td>
                                    <td class="px-2 py-1 border text-right">S/. ${p.interes}</td>
                                    <td class="px-2 py-1 border text-right font-semibold">S/. ${p.total}</td>
                                    <td class="px-2 py-1 border text-center">${p.usuario}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
                }

                cont.innerHTML = html;
            })
            .catch(err => {
                cont.innerHTML = `<p class="text-center text-red-500">Error al cargar los detalles.</p>`;
                console.error(err);
            });
    }
</script>
{{-- !FIN AJAX --}}

{{-- MODAL: Registrar pago de deuda --}}
<x-modal-show id="modal-pago-deuda" title="Registrar pago">
    <form method="POST" action="{{ route('pago_deudas.store') }}" class="space-y-6" autocomplete="off"
        id="form-pago-deuda">
        @csrf
        <input type="hidden" name="id_deuda" id="pay-id-deuda">
        <input type="hidden" name="incluir_interes" value="0" id="pay-incluir-interes-hidden">

        {{-- === CABECERA: tributo + vcto === --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Tributo')" />
                <x-text-input id="pay-tributo" type="text" class="mt-1 block w-full bg-gray-200" disabled />
            </div>
            <div>
                <x-input-label :value="__('Fecha de vencimiento')" />
                <x-text-input name="fecha_vencimiento" id="pay-fecha-vcto" type="date" class="mt-1 block w-full bg-gray-200" disabled />
            </div>
        </div>

        {{-- === BLOQUE PRINCIPAL: pendiente + fecha pago + monto === --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Principal pendiente (S/.)')" />
                <x-text-input id="pay-principal-pend" type="number" class="mt-1 block w-full bg-gray-200"
                    disabled />
            </div>
            <div>
                <x-input-label :value="__('Fecha de pago')" />
                <x-text-input name="fecha_pago" id="pay-fecha-pago" type="date" class="mt-1 block w-full"
                    required />
            </div>
        </div>
        <div class="grid md:grid-cols-1 gap-4">
            <div>
                <x-input-label :value="__('Monto a pagar (principal)')" />
                <x-text-input name="monto" id="pay-monto" type="number" step="1" min="0"
                    class="mt-1 block w-full" required />
                {{-- aquí va el texto de máximo, justo debajo del input --}}
                <small class="text-gray-500 mt-1 block" id="pay-monto-helper">
                    Máximo: <span id="pay-monto-max">0</span>
                </small>

                <input type="hidden" id="pay-monto-hidden" name="monto" value="0">
                <label class="inline-flex items-center gap-2 text-sm select-none mt-2">
                    <input id="pay-incluir-interes-check" type="checkbox" class="rounded border-gray-300" checked>
                    <span>Incluir interés en este pago</span>
                </label>
                <p id="pay-msg-sin-interes" class="text-xs text-amber-600 mt-1 hidden">
                    * El interés devengado quedará pendiente.
                </p>
            </div>
            <div>
                <x-input-label :value="__('Observaciones')" />
                <textarea name="observaciones" id="pay-observaciones" rows="2"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
            </div>

        </div>

        {{-- === INTERÉS Y TOTAL === --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <div class="flex items-center justify-between">
                    <x-input-label :value="__('Interés calculado (S/.)')" />
                    <span id="pay-interes-raw-label" class="ml-2 text-[11px] text-gray-500">
                        sin redondear: S/. 0.00
                    </span>
                </div>
                <x-text-input id="pay-interes" name="interes" type="number" class="mt-1 block w-full" />
                <small class="text-gray-500">Interés diario desde el día siguiente al vencimiento.</small>
            </div>

            <div>
                <x-input-label :value="__('Total a pagar (S/.)')" />
                <x-text-input id="pay-total" type="number" class="mt-1 block w-full bg-gray-200" readonly />
            </div>
        </div>

        {{-- === BOTONES === --}}
        <div class="flex justify-end gap-3">
            <x-primary-button class="px-6 py-2">Guardar pago</x-primary-button>
        </div>
    </form>
</x-modal-show>


<script>
    // ===== Cambio de mes =====
    const BASE_DETALLE_URL = @json($baseDetalle);

    function goToSelectedMonth() {
        const el = document.getElementById('month-picker');
        if (!el || !el.value) return;
        const [yy, mm] = el.value.split('-');
        window.location.href = `${BASE_DETALLE_URL}/${parseInt(yy,10)}/${parseInt(mm,10)}`;
    }

    document.getElementById('month-picker')?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            goToSelectedMonth();
        }
    });

    // ===== Interés (0.003% diario sobre principal pendiente) =====
    const DAILY_RATE = 0.0003;
    const roundHalfUp = (n) => Math.round(n);

    function daysBetweenForInterest(fechaVctoStr, fechaPagoStr) {
        const v = new Date(fechaVctoStr);
        const p = new Date(fechaPagoStr);

        // corre desde el día siguiente al vencimiento
        const start = new Date(v.getFullYear(), v.getMonth(), v.getDate() + 1);
        const end = new Date(p.getFullYear(), p.getMonth(), p.getDate());

        // si paga antes del “día siguiente”, no hay interés
        if (end < start) return 0;

        // cuenta el primer día (día 1)
        const diffDays = Math.floor((end - start) / (1000 * 60 * 60 * 24));
        return diffDays + 1;
    }

    // Estado global del modal
    let _pay = {
        idDeuda: null,
        fechaVcto: null,
        montoDeuda: 0,
        principalPend: 0,
        interesPendiente: 0,
        interesPagado: 0
    };

    function abrirModalPago(payload) {
        const principalPend = Math.max(0, parseInt(payload.montoDeuda, 10) - parseInt(payload.pagadoPrincipal, 10));

        _pay = {
            idDeuda: payload.idDeuda,
            fechaVcto: payload.fechaVcto,
            montoDeuda: parseFloat(payload.montoDeuda),
            principalPend: principalPend,
            interesPendiente: parseInt(payload.interesPendiente || '0', 10),
            interesPagado: parseInt(payload.interesPagado || '0', 10)
        };

        document.getElementById('pay-id-deuda').value = _pay.idDeuda;
        document.getElementById('pay-tributo').value = payload.tributo;
        
        const payVctoInput = document.getElementById('pay-fecha-vcto');
        payVctoInput.value = _pay.fechaVcto;
        if (payload.tributo.toUpperCase().includes('RENTA ANUAL')) {
            payVctoInput.removeAttribute('disabled');
            payVctoInput.classList.remove('bg-gray-200');
        } else {
            payVctoInput.setAttribute('disabled', 'disabled');
            payVctoInput.classList.add('bg-gray-200');
        }

        document.getElementById('pay-observaciones').value = payload.observaciones || '';
        document.getElementById('pay-principal-pend').value = principalPend;
        document.getElementById('pay-monto-max').textContent = principalPend.toLocaleString('en-US');

        const hoy = new Date();
        const hoyStr =
            `${hoy.getFullYear()}-${String(hoy.getMonth()+1).padStart(2,'0')}-${String(hoy.getDate()).padStart(2,'0')}`;
        document.getElementById('pay-fecha-pago').value = hoyStr;

        const montoInput = document.getElementById('pay-monto');
        const montoHidden = document.getElementById('pay-monto-hidden');
        const incluirChk = document.getElementById('pay-incluir-interes-check');

        // Configurar monto inicial
        if (principalPend > 0) {
            montoInput.removeAttribute('disabled');
            montoInput.removeAttribute('readonly');
            montoInput.value = principalPend; // Por defecto el monto total
        } else if (_pay.interesPendiente > 0) {
            // Solo interés pendiente
            montoInput.value = 0;
            montoInput.setAttribute('readonly', 'readonly');
        }

        montoHidden.value = montoInput.value;

        // Aplicar la lógica de interés obligatorio para pagos parciales
        recalcularInteresYTotal();
        toggleModal('modal-pago-deuda');
    }

    function recalcularInteresYTotal() {
        const montoInput = document.getElementById('pay-monto');
        const montoHidden = document.getElementById('pay-monto-hidden');
        const fechaPago = document.getElementById('pay-fecha-pago').value;
        const incluirChk = document.getElementById('pay-incluir-interes-check');
        const interesInput = document.getElementById('pay-interes');
        const monto = Math.min(Math.max(0, parseInt(montoInput.value || '0', 10)), _pay.principalPend);

        let interes = 0;
        let interesRaw = 0;

        // === Bloquear o habilitar input según el estado del checkbox ===
        if (!incluirChk.checked) {
            interesInput.setAttribute('readonly', 'readonly');
            interesInput.classList.add('bg-gray-100');
        } else {
            interesInput.removeAttribute('readonly');
            interesInput.classList.remove('bg-gray-100');
        }

        // === Calcular interés si está activado el check ===
        if (incluirChk.checked) {
            const dias = daysBetweenForInterest(_pay.fechaVcto, fechaPago);
            interesRaw = _pay.montoDeuda * DAILY_RATE * dias;
            interes = roundHalfUp(interesRaw);
        } else {
            interesRaw = 0;
            interes = 0;
            interesInput.value = 0;
        }

        // === Actualizar campos ===
        document.getElementById('pay-incluir-interes-hidden').value = incluirChk.checked ? '1' : '0';
        document.getElementById('pay-interes-raw-label').textContent =
            `sin redondear: S/. ${Number(interesRaw).toFixed(2)}`;
        interesInput.value = interes;
        document.getElementById('pay-total').value = monto + interes;
        montoHidden.value = String(monto);
    }

    // === Event Listeners actualizados ===
    document.getElementById('pay-fecha-vcto')?.addEventListener('change', function() {
        _pay.fechaVcto = this.value;
        recalcularInteresYTotal();
    });
    document.getElementById('pay-fecha-pago')?.addEventListener('change', recalcularInteresYTotal);
    document.getElementById('pay-monto')?.addEventListener('input', recalcularInteresYTotal);
    document.getElementById('pay-incluir-interes-check')?.addEventListener('change', recalcularInteresYTotal);

    // === Si el usuario edita el interés manualmente, recalcula el total ===
    document.getElementById('pay-interes')?.addEventListener('input', function() {
        const monto = parseFloat(document.getElementById('pay-monto').value || '0');
        const interes = parseFloat(this.value || '0');
        document.getElementById('pay-total').value = monto + interes;
    });

    // === Validación visual y autocorrección del monto principal ===
    const montoInput = document.getElementById('pay-monto');
    const montoHelper = document.getElementById('pay-monto-helper');

    function validarMontoVisual() {
        const max = _pay.principalPend;
        let valor = parseFloat(montoInput.value || '0');

        if (isNaN(valor) || valor < 0) {
            valor = 0;
        }

        // Si el valor excede el máximo permitido → reajustar automáticamente
        if (valor > max) {
            montoInput.value = max;
            valor = max;

            // Breve efecto visual para avisar
            montoInput.classList.add('border-amber-500');
            montoHelper.innerHTML =
                `<span class="text-amber-600 text-xs font-medium">Se ajustó automáticamente al máximo permitido: S/. ${max.toLocaleString('en-US')}</span>`;

            setTimeout(() => {
                montoInput.classList.remove('border-amber-500');
                montoHelper.innerHTML =
                    `Máximo: <span id="pay-monto-max">${max.toLocaleString('en-US')}</span>`;
            }, 2000);
        }

        // recalcula total normalmente después de ajustar
        recalcularInteresYTotal();
    }

    montoInput?.addEventListener('input', validarMontoVisual);
    // === FIN de la Validación visual y autocorrección del monto principal ===

    // Event listeners
    document.getElementById('pay-fecha-pago')?.addEventListener('change', recalcularInteresYTotal);
    document.getElementById('pay-monto')?.addEventListener('input', recalcularInteresYTotal);
    document.getElementById('pay-incluir-interes-check')?.addEventListener('change', recalcularInteresYTotal);

    // Permitir que el usuario edite manualmente el interés
    document.getElementById('pay-interes')?.addEventListener('input', function() {
        const monto = parseFloat(document.getElementById('pay-monto').value || '0');
        const interes = parseFloat(this.value || '0');
        document.getElementById('pay-total').value = monto + interes;
    });

    // ===== DataTables =====
    $(document).ready(function() {
        const exportTitle =
            `DEUDAS {{ strtoupper($tipo) }} {{ strtoupper(Str::slug($empresa->razon_social, ' ')) }} {{ $empresa->ruc }} PERÍODO {{ strtoupper($mesNombre) }} {{ $anio }}`;

        $('#myTable-detalle').DataTable({
            responsive: false,
            paging: false,
            info: false,
            ordering: true,
            retrieve: true,
            searching: false,
            deferRender: true,
            language: {
                url: "/js/datatables/lang/es-ES.json"
            },
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: exportTitle,
                exportOptions: {
                    columns: ':not(:last)'
                }
            }]
        });
    });
</script>


{{-- MODAL: Crear Deuda --}}
<x-modal-show id="modal-crear-deuda" title="Registrar deuda ({{ ucfirst($tipo) }})">
    @if ($tributos->isEmpty())
        {{-- Si ya todos los tributos están registrados, mostrar solo el mensaje --}}
        <div class="text-center py-10 px-6 text-gray-600">
            <i class="bi bi-check-circle text-green-500 text-4xl mb-3"></i>
            <p class="text-lg font-medium">
                Todos los tributos de {{ Str::upper($mesNombre) }} ya tienen una deuda registrada.
            </p>
            <p class="text-sm text-gray-500 mt-2">
                No hay tributos pendientes para registrar este mes.
            </p>
        </div>
    @else
        {{-- Si hay tributos pendientes, mostrar el formulario normal --}}
        <form method="POST" action="{{ route('deudas.store') }}" class="space-y-6" autocomplete="off"
            id="form-crear-deuda">
            @csrf
            <input type="hidden" name="id_empresa_cliente" value="{{ $empresa->id_empresas_clientes }}">

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <x-input-label :value="__('Empresa')" />
                    <x-text-input type="text" class="mt-1 block w-full" value="{{ $empresa->razon_social }}"
                        disabled />
                </div>
                <div>
                    <x-input-label :value="__('Fecha de vencimiento')" />
                    <x-text-input name="fecha_vencimiento" id="cd-fecha-vcto" type="date"
                        class="mt-1 block w-full"
                        value="{{ $vencimientoMes ? $vencimientoMes->format('Y-m-d') : '' }}"
                        data-original-value="{{ $vencimientoMes ? $vencimientoMes->format('Y-m-d') : '' }}"
                        readonly required />
                    <small class="text-gray-500">El interés corre desde el día siguiente al vencimiento.</small>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <x-input-label :value="__('Tributo a pagar')" />
                    <select name="id_tributo" id="cd-tributo"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        @foreach ($tributos ?? [] as $t)
                            <option value="{{ $t->id_tributo }}">{{ $t->nombre }} ({{ $t->codigo }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label :value="__('Monto (entero)')" />
                    <x-text-input name="monto_deuda" id="cd-monto" type="number" step="1" min="0"
                        class="mt-1 block w-full" required />
                </div>
            </div>

            <div class="flex justify-end">
                <x-primary-button class="px-6 py-2">Crear deuda</x-primary-button>
            </div>
        </form>
    @endif
</x-modal-show>

<script>
    function abrirModalNuevaDeuda() {
        const vctoInput = document.getElementById('cd-fecha-vcto');
        if (vctoInput && !vctoInput.value) {
            const y = {{ (int) $anio }};
            const m = {{ (int) $mes }};
            const d = 15;
            vctoInput.value = `${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            vctoInput.dataset.originalValue = vctoInput.value;
        }

        const monto = document.getElementById('cd-monto');
        if (monto) monto.value = '';

        const tributoSelect = document.getElementById('cd-tributo');
        if (tributoSelect) {
            tributoSelect.dispatchEvent(new Event('change'));
        }

        toggleModal('modal-crear-deuda');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const tributoSelect = document.getElementById('cd-tributo');
        const vctoInput = document.getElementById('cd-fecha-vcto');

        if (tributoSelect && vctoInput) {
            tributoSelect.addEventListener('change', function() {
                const selectedText = this.options[this.selectedIndex]?.text.toUpperCase() || '';
                if (selectedText.includes('RENTA ANUAL')) {
                    vctoInput.removeAttribute('readonly');
                } else {
                    vctoInput.setAttribute('readonly', 'readonly');
                    if (vctoInput.dataset.originalValue) {
                        vctoInput.value = vctoInput.dataset.originalValue;
                    }
                }
            });
        }
    });
</script>
