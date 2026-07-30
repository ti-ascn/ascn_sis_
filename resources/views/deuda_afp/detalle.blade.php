@php
    use Carbon\Carbon;

    $periodo = Carbon::createFromDate((int) $anio, (int) $mes, 1)->locale('es');
    $mesNombre = $periodo->monthName;
    $anioPeriodo = $periodo->year;

    // Navegación de mes
    $cur = Carbon::createFromDate((int) $anio, (int) $mes, 1);
    $prev = $cur->copy()->subMonth();
    $next = $cur->copy()->addMonth();

    $baseDetalle = url('/deuda-afp/' . $empresa->id_empresas_clientes);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            Deudas AFP — {{ $empresa->razon_social }} ({{ $empresa->ruc }}),
            <span class="text-blue-600">PERÍODO {{ Str::upper($mesNombre) }} {{ $anioPeriodo }}</span>
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            {{-- Barra de navegación entre meses --}}
            <div class="flex flex-wrap items-center gap-3 mb-5">
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

                <div class="ml-auto flex items-center gap-3">
                    @if ($vencimientoMes)
                        <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-1.5 rounded">
                            <i class="bi bi-calendar-event"></i>
                            <span>Vencimiento (PDT): <strong>{{ $vencimientoMes->format('d/m/Y') }}</strong></span>
                        </div>
                    @endif

                    <button onclick="abrirModalNuevaDeuda()"
                        class="inline-flex items-center gap-2 bg-green-500 text-white px-3 py-1.5 rounded hover:bg-green-600">
                        <i class="bi bi-plus-circle"></i> Nueva deuda AFP
                    </button>
                </div>
            </div>

            {{-- Tabla principal --}}
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-[1200px] table-auto text-sm" id="myTable-detalle-afp">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-700">
                            <th class="px-3 py-2 border-b">AFP</th>
                            <th class="px-3 py-2 border-b !text-right">DEUDA</th>
                            <th class="px-3 py-2 border-b !text-right">INTERÉS</th>
                            <th class="px-3 py-2 border-b !text-right">TOTAL</th>
                            <th class="px-3 py-2 border-b">FECHA VENC.</th>
                            <th class="px-3 py-2 border-b">FECHA PAGO</th>
                            <th class="px-3 py-2 border-b">CREADO POR</th>
                            <th class="px-3 py-2 border-b">PAGADO POR</th>
                            <th class="px-3 py-2 border-b">OBSERVACIONES</th>
                            <th class="px-3 py-2 border-b">ESTADO</th>
                            <th class="px-3 py-2 border-b">ACCIONES</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @foreach ($rows as $r)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">{{ $r['afp'] }}</td>
                                <td class="px-3 py-2 !text-right">S/. {{ number_format($r['monto_deuda'], 2) }}</td>
                                <td class="px-3 py-2 !text-right text-red-600">
                                    {{ $r['interes'] > 0 ? 'S/. ' . number_format($r['interes'], 2) : '—' }}
                                </td>
                                <td class="px-3 py-2 !text-right font-semibold text-blue-600">
                                    S/. {{ number_format($r['total'], 2) }}
                                </td>
                                <td class="px-3 py-2">{{ $r['fecha_venc'] }}</td>
                                <td class="px-3 py-2">{{ $r['fecha_pago'] }}</td>
                                <td class="px-3 py-2">{{ $r['autor_deuda'] ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $r['pagador'] ?? '—' }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $r['observaciones'] ?? '—' }}</td>
                                <td class="px-3 py-2">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $r['estado'] === 'Pendiente' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $r['estado'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    {{-- Botón Pagar (solo si pendiente) --}}
                                    @if ($r['estado'] === 'Pendiente')
                                        <button type="button"
                                            onclick="abrirModalPago('{{ $r['id_deuda'] }}','{{ $r['monto_deuda'] }}','{{ $r['fecha_venc'] }}','{{ $r['afp'] }}')"
                                            class="flex items-center justify-center py-1 rounded-md tooltip"
                                            style="color:#10B981; transition:color 0.3s; margin-left:6px;"
                                            onmouseover="this.style.color='#059669';"
                                            onmouseout="this.style.color='#10B981';">
                                            <span class="tooltip-text">Registrar pago</span>
                                            <x-tax-icon />
                                        </button>
                                    @endif

                                    {{-- Botón Editar --}}
                                    @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                        <button type="button"
                                            onclick="abrirModalEditarAFP({
                                            id:'{{ $r['id_deuda'] }}',
                                            idAfp:'{{ $r['id_afp'] }}',
                                            afp:'{{ addslashes($r['afp']) }}',
                                            fechaVctoDMY:'{{ $r['fecha_venc'] }}',
                                            montoDeuda:'{{ $r['monto_deuda'] }}',
                                            interes:'{{ $r['interes'] }}',
                                            fechaPagoYMD:'{{ $r['fecha_pago_ymd'] }}',
                                            montoTotal:'{{ $r['total'] }}',
                                            observaciones:`{{ trim(preg_replace('/\r|\n/', ' ', $r['observaciones'] ?? '')) }}`
                                        })"
                                            class="flex items-center justify-center py-1 rounded-md tooltip"
                                            style="color:#f59e0b; transition:color 0.3s;"
                                            onmouseover="this.style.color='#d97706';"
                                            onmouseout="this.style.color='#f59e0b';">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </button>

                                        {{-- Botón Eliminar --}}
                                        <form action="{{ route('deuda-afp.destroy', $r['id_deuda']) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('¿Seguro que deseas eliminar esta deuda AFP?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="flex items-center justify-center py-1 rounded-md tooltip"
                                                style="color:#dc2626; transition:color 0.3s; margin-left:6px;"
                                                onmouseover="this.style.color='#b91c1c';"
                                                onmouseout="this.style.color='#dc2626';">
                                                <span class="tooltip-text">Eliminar</span>
                                                <x-delete-icon />
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    @if ($rows->count() > 0)
                        <tfoot>
                            <tr class="bg-gray-50 font-semibold">
                                <td class="px-3 py-2">TOTAL</td>
                                <td class="px-3 py-2 text-right">S/. {{ number_format($totales['deuda'], 2) }}</td>
                                <td class="px-3 py-2 text-right">S/. {{ number_format($totales['interes'], 2) }}</td>
                                <td class="px-3 py-2 text-right text-blue-600">S/.
                                    {{ number_format($totales['total'], 2) }}</td>
                                <td colspan="7"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- MODAL: Crear Deuda AFP --}}
<x-modal-show id="modal-crear-deuda" title="Registrar deuda AFP">
    <form method="POST" action="{{ route('deuda-afp.store') }}" class="space-y-6" autocomplete="off"
        id="form-crear-deuda">
        @csrf
        <input type="hidden" name="id_empresa_cliente" value="{{ $empresa->id_empresas_clientes }}">
        <input type="hidden" name="periodo" value="{{ sprintf('%04d-%02d', $anio, $mes) }}">

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Empresa')" />
                <x-text-input type="text" class="mt-1 block w-full bg-gray-200"
                    value="{{ $empresa->razon_social }}" disabled />
            </div>
            <div>
                <x-input-label :value="__('Fecha de vencimiento')" />
                <x-text-input name="fecha_vencimiento" type="date" class="mt-1 block w-full bg-gray-200"
                    value="{{ $vencimientoMes ? $vencimientoMes->format('Y-m-d') : '' }}" readonly required />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('AFP')" />
                <select name="id_afp" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    @foreach ($afps as $a)
                        <option value="{{ $a->id_afp }}">{{ $a->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label :value="__('Monto')" />
                <x-text-input name="monto_deuda" type="number" step="0.01" min="0"
                    class="mt-1 block w-full" required />
            </div>
        </div>

        <div class="flex justify-end">
            <x-primary-button class="px-6 py-2">Crear deuda</x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- MODAL: Editar Deuda AFP (mismo layout que pagar, con AFP editable) --}}
<x-modal-show id="modal-editar-afp" title="Editar deuda AFP">
    <form method="POST" action="{{ route('deuda-afp.update', ['deuda_afp' => '__ID__']) }}" id="form-editar-afp"
        class="space-y-6" autocomplete="off">
        @csrf @method('PUT')
        <input type="hidden" name="id_deuda" id="edit-id-deuda">

        {{-- Fila 1: AFP (editable) + Vencimiento (solo lectura) --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('AFP')" />
                <select name="id_afp" id="edit-id-afp"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    @foreach ($afps as $a)
                        <option value="{{ $a->id_afp }}">{{ $a->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label :value="__('Fecha de vencimiento')" />
                <x-text-input id="edit-fecha-vcto" type="date" class="mt-1 block w-full bg-gray-200" disabled />
            </div>
        </div>

        {{-- Fila 2: Deuda + Fecha de pago --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Monto deuda (S/.)')" />
                <x-text-input name="monto_deuda" id="edit-deuda" type="number" step="0.01" min="0"
                    class="mt-1 block w-full" required />
            </div>
            <div>
                <x-input-label :value="__('Fecha de pago')" />
                <x-text-input name="fecha_pago" id="edit-fecha-pago" type="date" class="mt-1 block w-full" />
                <small class="text-gray-500">Déjalo vacío si aún no se pagó.</small>
            </div>
        </div>

        {{-- Fila 3: Interés (editable) + Total pagado (auto / también editable si prefieres) --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Monto total pagado (S/.)')" />
                <x-text-input name="monto_total" id="edit-monto-total" type="number" step="0.01" min="0"
                    class="mt-1 block w-full" required />
                <small id="edit-total-helper" class="text-xs text-gray-500 block mt-1">
                    El total no puede ser menor que la deuda.
                </small>
            </div>
            <div>
                <x-input-label :value="__('Interés (S/.)')" />
                <x-text-input name="interes" id="edit-interes" type="number" step="0.01" min="0"
                    class="mt-1 block w-full" />
                <small class="text-gray-500">Puedes ajustar el interés. Si lo cambias, el total se recalcula.</small>
            </div>
        </div>

        {{-- Fila 4: Total final (solo lectura, es el mismo total) --}}
        <div>
            <x-input-label :value="__('Total final (S/.)')" />
            <x-text-input id="edit-total" type="number" step="0.01"
                class="mt-1 block w-full bg-gray-200 font-semibold text-blue-600 text-lg" readonly />
        </div>

        {{-- Fila 5: Observaciones --}}
        <div>
            <x-input-label :value="__('Observaciones')" />
            <x-text-input name="observaciones" id="edit-observaciones" type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                placeholder="Añade una observación (opcional)" />
        </div>

        <div class="flex justify-end gap-3 pt-3">
            <x-primary-button class="px-6 py-2">Guardar cambios</x-primary-button>
        </div>
    </form>
</x-modal-show>
<script>
    function abrirModalEditarAFP({
        id,
        idAfp,
        afp,
        fechaVctoDMY,
        montoDeuda,
        interes,
        fechaPagoYMD,
        montoTotal,
        observaciones
    }) {
        const form = document.getElementById('form-editar-afp');
        form.action = `/deuda-afp/${id}`;

        const $ = (id) => document.getElementById(id);
        $('edit-id-deuda').value = id;
        $('edit-id-afp').value = idAfp;
        $('edit-fecha-vcto').value = fechaVctoDMY ? fechaVctoDMY.split('/').reverse().join('-') : '';

        $('edit-deuda').value = (+montoDeuda || 0).toFixed(2);
        $('edit-interes').value = (+interes || 0).toFixed(2);
        $('edit-monto-total').value = (+montoTotal || 0).toFixed(2);
        $('edit-fecha-pago').value = fechaPagoYMD || '';
        $('edit-observaciones').value = observaciones || '';

        const inDeuda = $('edit-deuda');
        const inInter = $('edit-interes');
        const inTotal = $('edit-monto-total');
        const inFinal = $('edit-total');
        const inFecha = $('edit-fecha-pago');
        const hint = $('edit-total-helper');

        const toNum = v => isFinite(parseFloat(v)) ? parseFloat(v) : 0;
        const r2 = n => (Math.round(n * 100) / 100).toFixed(2);

        function toggleSinPagoUI(sinPago) {
            if (sinPago) {
                inInter.value = '0.00';
                inTotal.value = '0.00';
                inInter.setAttribute('readonly', 'readonly');
                inTotal.setAttribute('readonly', 'readonly');
                hint.textContent = 'Sin fecha de pago: el total pagado es 0.';
                hint.classList.remove('text-red-600');
            } else {
                inInter.removeAttribute('readonly');
                inTotal.removeAttribute('readonly');
                hint.textContent = 'El total no puede ser menor que la deuda.';
            }
            inFinal.value = r2(toNum(inTotal.value)); // refleja lo pagado
        }

        // Estado inicial según fecha
        toggleSinPagoUI(!inFecha.value);

        // Si agregan/borra fecha -> habilita/deshabilita cálculo
        inFecha.oninput = () => toggleSinPagoUI(!inFecha.value);

        // Recalculos SUAVES (no clamping mientras se escribe)
        function softFromTotal() {
            if (!inFecha.value) return; // sin pago, no recalcular
            const deuda = toNum(inDeuda.value);
            const total = toNum(inTotal.value);
            const inter = Math.max(0, total - deuda);
            inInter.value = r2(inter);
            inFinal.value = r2(total);
            if (total < deuda) hint.classList.add('text-red-600');
            else hint.classList.remove('text-red-600');
        }

        function softFromInter() {
            if (!inFecha.value) return; // sin pago, no recalcular
            const deuda = toNum(inDeuda.value);
            const inter = Math.max(0, toNum(inInter.value));
            const total = deuda + inter;
            inTotal.value = r2(total);
            inFinal.value = r2(total);
            hint.classList.remove('text-red-600');
        }

        function softFromDeuda() {
            // si NO hay pago, no tocar total/interés (se quedan 0)
            if (!inFecha.value) {
                inFinal.value = r2(toNum(inTotal.value));
                return;
            }
            const deuda = toNum(inDeuda.value);
            const inter = Math.max(0, toNum(inInter.value));
            const total = deuda + inter;
            inTotal.value = r2(total);
            inFinal.value = r2(total);
            hint.classList.remove('text-red-600');
        }

        // Clamping SOLO al salir del input (si hay pago)
        function clampTotalOnBlur() {
            if (!inFecha.value) return;
            const deuda = toNum(inDeuda.value);
            let total = toNum(inTotal.value);
            if (total < deuda) {
                total = deuda;
                inTotal.value = r2(total);
                inInter.value = r2(Math.max(0, total - deuda));
                inFinal.value = r2(total);
            }
            hint.classList.remove('text-red-600');
        }

        inTotal.oninput = softFromTotal;
        inTotal.onblur = clampTotalOnBlur;
        inInter.oninput = softFromInter;
        inDeuda.oninput = softFromDeuda;

        // primer render
        if (inFecha.value) softFromTotal();
        else inFinal.value = '0.00';

        toggleModal('modal-editar-afp');
    }
</script>


{{-- MODAL: Registrar pago AFP --}}
<x-modal-show id="modal-pago-afp" title="Registrar pago AFP">
    <form method="POST" action="{{ route('pago_afp.store') }}" class="space-y-6" autocomplete="off"
        id="form-pago-afp">
        @csrf
        <input type="hidden" name="id_deuda" id="pay-id-deuda">

        {{-- === FILA 1: AFP + Fecha de vencimiento === --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('AFP')" />
                <x-text-input id="pay-afp" type="text" class="mt-1 block w-full bg-gray-200" disabled />
            </div>
            <div>
                <x-input-label :value="__('Fecha de vencimiento')" />
                <x-text-input id="pay-fecha-vcto" type="date" class="mt-1 block w-full bg-gray-200" disabled />
            </div>
        </div>

        {{-- === FILA 2: Monto deuda + Fecha de pago === --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Monto deuda (S/.)')" />
                <x-text-input id="pay-deuda" type="number" class="mt-1 block w-full bg-gray-200" disabled />
            </div>
            <div>
                <x-input-label :value="__('Fecha de pago')" />
                <x-text-input name="fecha_pago" id="pay-fecha-pago" type="date" class="mt-1 block w-full"
                    value="{{ now()->format('Y-m-d') }}" required />
            </div>
        </div>

        {{-- === FILA 3: Monto total + Interés === --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Monto total pagado (S/.)')" />
                <x-text-input name="monto_total" id="pay-monto-total" type="number" step="0.01" min="0"
                    class="mt-1 block w-full" required />
                <small id="pay-total-helper" class="text-xs text-gray-500 block mt-1">
                    Si ingresas un valor menor a la deuda, se ajustará automáticamente.
                </small>
            </div>
            <div>
                <x-input-label :value="__('Interés calculado (S/.)')" />
                <x-text-input name="interes" id="pay-interes" type="number" step="0.01"
                    class="mt-1 block w-full bg-gray-100" readonly />
            </div>
        </div>

        {{-- === FILA 4: Total final (ancho completo) === --}}
        <div>
            <x-input-label :value="__('Total final (S/.)')" />
            <x-text-input id="pay-total" type="number" step="0.01"
                class="mt-1 block w-full bg-gray-200 font-semibold text-blue-600 text-lg" readonly />
        </div>

        {{-- === FILA 5: Observaciones (ancho completo) === --}}
        <div>
            <x-input-label :value="__('Observaciones')" />
            <x-text-input name="observaciones" id="pay-observaciones" type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                placeholder="Añade una observación (opcional)" />
        </div>

        {{-- === BOTÓN === --}}
        <div class="flex justify-end gap-3 pt-3">
            <x-primary-button class="px-6 py-2">Registrar pago</x-primary-button>
        </div>
    </form>
</x-modal-show>


<script>
    const BASE_DETALLE_URL = @json($baseDetalle);
    //const DAILY_INTEREST = 0.0003;

    function goToSelectedMonth() {
        const el = document.getElementById('month-picker');
        if (!el || !el.value) return;
        const [yy, mm] = el.value.split('-');
        window.location.href = `${BASE_DETALLE_URL}/${parseInt(yy,10)}/${parseInt(mm,10)}`;
    }

    function abrirModalPago(idDeuda, montoDeuda, fechaVencStr, afpNombre, creador = '—', pagador = '—') {
        // Asigna los valores al modal
        document.getElementById('pay-id-deuda').value = idDeuda;
        document.getElementById('pay-afp').value = afpNombre;
        document.getElementById('pay-deuda').value = parseFloat(montoDeuda).toFixed(2);
        document.getElementById('pay-fecha-vcto').value = fechaVencStr.split('/').reverse().join('-');
        document.getElementById('pay-monto-total').value = parseFloat(montoDeuda).toFixed(2);
        document.getElementById('pay-interes').value = '0.00';
        document.getElementById('pay-total').value = parseFloat(montoDeuda).toFixed(2);

        const inputTotal = document.getElementById('pay-monto-total');
        const inputInteres = document.getElementById('pay-interes');
        const inputTotalFinal = document.getElementById('pay-total');
        const deudaBase = parseFloat(montoDeuda);
        const helperText = document.getElementById('pay-total-helper');

        // Recalcula el interés en tiempo real
        function recalcularInteres() {
            const total = parseFloat(inputTotal.value) || 0;
            const interes = total - deudaBase;
            inputInteres.value = interes > 0 ? interes.toFixed(2) : '0.00';
            inputTotalFinal.value = (deudaBase + parseFloat(inputInteres.value)).toFixed(2);
        }

        // Función para ajustar automáticamente si el monto es menor a la deuda
        function ajustarMontoTotal() {
            const totalIngresado = parseFloat(inputTotal.value) || 0;

            if (totalIngresado < deudaBase) {
                // Ajustar al monto deuda
                inputTotal.value = deudaBase.toFixed(2);
                helperText.textContent = 'Se ajustó al monto de la deuda.';
                helperText.className = 'text-xs block mt-1 text-green-600';
            } else if (totalIngresado === deudaBase) {
                helperText.textContent = 'Monto igual a la deuda. No hay interés.';
                helperText.className = 'text-xs block mt-1 text-blue-600';
            } else {
                helperText.textContent = 'Si ingresas un valor menor a la deuda, se ajustará automáticamente.';
                helperText.className = 'text-xs block mt-1 text-gray-500';
            }

            recalcularInteres();
        }

        // Para mostrar advertencia mientras se escribe
        function validarEnTiempoReal() {
            const totalIngresado = parseFloat(inputTotal.value) || 0;

            if (totalIngresado < deudaBase && totalIngresado > 0) {
                helperText.textContent = 'El monto no puede ser menor a la deuda. Se ajustará automáticamente.';
                helperText.className = 'text-xs block mt-1 text-red-600';
            } else {
                helperText.textContent = 'Si ingresas un valor menor a la deuda, se ajustará automáticamente.';
                helperText.className = 'text-xs block mt-1 text-gray-500';
            }

            recalcularInteres();
        }

        // Event listeners
        inputTotal.addEventListener('input', validarEnTiempoReal);
        inputTotal.addEventListener('blur', ajustarMontoTotal);
        inputTotal.addEventListener('change', ajustarMontoTotal);

        // Event listeners
        inputTotal.addEventListener('input', recalcularInteres);
        inputTotal.addEventListener('blur', ajustarMontoTotal); // Se ajusta al salir del campo
        inputTotal.addEventListener('change', ajustarMontoTotal); // Se ajusta al cambiar

        // También ajustar inmediatamente si se pega un valor
        inputTotal.addEventListener('paste', function() {
            setTimeout(ajustarMontoTotal, 10);
        });

        toggleModal('modal-pago-afp');
    }

    function abrirModalNuevaDeuda() {
        toggleModal('modal-crear-deuda');
    }
</script>
<script>
    // ===== DataTables =====
    $(document).ready(function() {
        // Formatear la razón social para el título (sin caracteres especiales, en mayúsculas)
        const razonSocialFormateada = "{{ Str::slug($empresa->razon_social, '_') }}".toUpperCase();
        const periodoFormateado = "{{ Str::upper($mesNombre) }}".toUpperCase();
        const anioPeriodo = "{{ $anio }}";

        // Timestamp: YYYYMMDD_HHMMSS
        const now = new Date();
        const timestamp = now.toISOString().slice(0, 10).replace(/-/g, '') +
            '_' +
            now.toTimeString().slice(0, 8).replace(/:/g, '');

        const exportTitle =
            `DEUDAS_AFP_${razonSocialFormateada}_PERIODO_${periodoFormateado}_${anioPeriodo}_${timestamp}`;

        $('#myTable-detalle-afp').DataTable({
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
