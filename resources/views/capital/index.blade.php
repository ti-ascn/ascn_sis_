<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Capital de Empresas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-6">

            <x-success-error-bagdes />

            <div class="flex justify-end mb-4">
                <a href="{{ route('capital.export') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition ease-in-out duration-150">
                    <i class="bi bi-file-earmark-excel mr-2 text-lg leading-none"></i> Exportar a Excel
                </a>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap" id="myTable-capital">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-4 border-b">ESTADO</th>
                            <th class="px-4 py-4 border-b">RAZÓN SOCIAL</th>
                            <th class="px-4 py-4 border-b">RUC</th>
                            <th class="px-4 py-4 border-b">PARTIDA</th>
                            <th class="px-4 py-4 border-b text-right">CAPITAL CONSTITUIDO</th>
                            <th class="px-4 py-4 border-b text-right">ADICIONES</th>
                            <th class="px-4 py-4 border-b text-right">TOTAL</th>
                            <th class="px-4 py-4 border-b">FECHA</th>
                            <th class="px-4 py-4 border-b">OBSERVACIONES</th>
                            <th class="px-4 py-4 border-b">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $empresa)
                            @php $capital = $empresa->capital; @endphp
                            <tr class="hover:bg-gray-50" id="fila-empresa-{{ $empresa->id_empresas_clientes }}">
                                <td class="px-4 py-4 border-b !text-center">
                                    {{ $empresa->estado }}
                                </td>
                                <td class="px-4 py-4 border-b font-medium">{{ $empresa->razon_social }}</td>
                                <td class="px-4 py-4 border-b">{{ $empresa->ruc ?? '—' }}</td>
                                <td class="px-4 py-4 border-b partida-cell-{{ $empresa->id_empresas_clientes }}">
                                    {{ $capital ? $capital->partida : '—' }}
                                </td>
                                <td class="px-4 py-4 border-b text-right constituido-cell-{{ $empresa->id_empresas_clientes }}"
                                    data-raw="{{ $capital ? (float) $capital->capital_Constituido : '0' }}">
                                    {{ $capital ? 'S/. ' . number_format($capital->capital_Constituido, 0, '.', ',') : '—' }}
                                </td>
                                <td class="px-4 py-4 border-b text-right aumentos-cell-{{ $empresa->id_empresas_clientes }}">
                                    {{ $capital ? 'S/. ' . number_format($capital->total_capital - $capital->capital_Constituido, 0, '.', ',') : '—' }}
                                </td>
                                <td class="px-4 py-4 border-b text-right font-semibold total-capital-{{ $empresa->id_empresas_clientes }}"
                                    data-raw="{{ $capital ? (float) $capital->total_capital : '' }}">
                                    {{ $capital ? 'S/. ' . number_format($capital->total_capital, 0, '.', ',') : '—' }}
                                </td>

                                <td class="px-4 py-4 border-b fecha-cell-{{ $empresa->id_empresas_clientes }}">
                                    {{ $capital && $capital->fecha ? \Carbon\Carbon::parse($capital->fecha)->format('d/m/Y') : '—' }}
                                    {{-- ya esta jefecito, no me grite soy sensible --}}
                                </td>

                                <td class="px-4 py-4 border-b obs-cell-{{ $empresa->id_empresas_clientes }}">
                                    {{ $capital ? $capital->observaciones ?? '—' : '—' }}
                                </td>
                                @php
                                    $cantidadAumentos = $capital ? $capital->aumentos->count() : 0;
                                @endphp
                                <td class="px-4 py-4 border-b">
                                    <div
                                        class="flex items-center gap-2 flex-wrap acciones-cell-{{ $empresa->id_empresas_clientes }}">
                                        @if ($capital)
                                            <a role="button"
                                                class="flex items-center rounded-md tooltip btn-editar-capital"
                                                style="color: #e6a817;" onmouseover="this.style.color='#f5bc42';"
                                                onmouseout="this.style.color='#e6a817';"
                                                data-id="{{ $capital->id_capital }}"
                                                data-empresa="{{ $empresa->id_empresas_clientes }}"
                                                data-razon="{{ $empresa->razon_social }}"
                                                data-ruc="{{ $empresa->ruc ?? '' }}"
                                                data-partida="{{ $capital->partida }}"
                                                data-constituido="{{ $capital->capital_Constituido }}"
                                                data-observaciones="{{ $capital->observaciones }}"
                                                data-fecha="{{ $capital->fecha }}">
                                                <x-edit-icon />
                                                <span class="tooltip-text">Editar</span>
                                            </a>
                                            <a role="button"
                                                class="flex items-center gap-1 rounded-md tooltip btn-ver-aumentos"
                                                style="color: #2986cc;" onmouseover="this.style.color='#2ba4ff';"
                                                onmouseout="this.style.color='#2986cc';"
                                                data-id="{{ $capital->id_capital ?? '' }}"
                                                data-razon="{{ $empresa->razon_social }}"
                                                data-constituido="{{ $capital->capital_Constituido ?? 0 }}"
                                                data-empresa="{{ $empresa->id_empresas_clientes }}"
                                                data-fecha="{{ $capital->fecha ?? '' }}">

                                                <x-list-icon />

                                                <span class="tooltip-text">
                                                    Aumentos ({{ $capital->aumentos_count ?? 0 }})
                                                </span>
                                            </a>
                                            <a role="button"
                                                class="flex items-center rounded-md tooltip btn-eliminar-capital"
                                                style="color: #cc2929;" onmouseover="this.style.color='#ff4444';"
                                                onmouseout="this.style.color='#cc2929';"
                                                data-id="{{ $capital->id_capital }}"
                                                data-empresa="{{ $empresa->id_empresas_clientes }}">
                                                <x-delete-icon />
                                                <span class="tooltip-text">Eliminar</span>
                                            </a>
                                        @else
                                            <a role="button"
                                                class="flex items-center rounded-md tooltip btn-registrar-capital"
                                                style="color: #28a745;" onmouseover="this.style.color='#34d058';"
                                                onmouseout="this.style.color='#28a745';"
                                                data-empresa="{{ $empresa->id_empresas_clientes }}"
                                                data-razon="{{ $empresa->razon_social }}"
                                                data-ruc="{{ $empresa->ruc ?? '' }}">
                                                <x-bank-icon />
                                                <span class="tooltip-text">Crear Capital</span>
                                            </a>
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

{{-- =====================================================================
     MODAL: REGISTRAR / EDITAR CAPITAL
====================================================================== --}}
<x-modal-show id="modalCapital" title="Crear Capital">
    <form id="formCapital" class="space-y-4">
        @csrf
        <input type="hidden" id="capital_id" name="capital_id">
        <input type="hidden" id="capital_empresa_id" name="empresas_cliente">
        <div>
            <x-input-label for="capital_razon" value="Empresa" />
            <input type="text" id="capital_razon"
                class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
        </div>
        <div>
            <x-input-label for="capital_ruc" value="RUC" />
            <input type="text" id="capital_ruc" class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-100"
                readonly>
        </div>
        <div>
            <x-input-label for="capital_partida" value="Partida" />
            <input type="text" id="capital_partida" name="partida" maxlength="255"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
        </div>
        <div>
            <x-input-label for="capital_constituido" value="Capital Constituido" />
            <input type="number" step="0.01" min="0" id="capital_constituido" name="capital_Constituido"
                required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
        </div>
        <div>
            <x-input-label for="capital_fecha" value="Fecha" />{{-- Te amo jefecito --}}
            <input type="date" id="capital_fecha" name="fecha"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
        </div>
        <div>
            <x-input-label for="capital_obs" value="Observaciones" />
            <textarea id="capital_obs" name="observaciones" rows="2" maxlength="255"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" onclick="toggleModal('modalCapital')"
                class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition text-white">
                Cancelar
            </button>
            <x-primary-button type="button" id="btnGuardarCapital" class="px-6 py-2 flex items-center gap-2">
                <i class="bi bi-check-lg" id="btnGuardarCapitalIcon"></i>
                <span id="btnGuardarCapitalText">Guardar</span>
            </x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- =====================================================================
     MODAL: LISTA DE AUMENTOS DE CAPITAL
====================================================================== --}}
<x-modal-show id="modalAumentos" title="Aumentos de Capital" size="large">

    <div class="flex flex-col gap-4 mb-6 -mt-2">
        <!-- Título de la Empresa -->
        <div class="flex items-center gap-2.5 pb-3 border-b border-gray-150">
            <div class="w-2.5 h-6 bg-blue-600 rounded-full"></div>
            <h3 class="text-base font-bold text-gray-800">
                Aumentos — <span id="modal-aumentos-empresa" class="text-blue-700"></span>
            </h3>
        </div>

        <!-- Indicadores Clave -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Capital Constituido -->
            <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-3.5 flex items-center gap-3">
                <div class="p-2.5 bg-blue-500 text-white rounded-lg shadow-sm shadow-blue-100">
                    <i class="bi bi-bank text-lg leading-none"></i>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-blue-600 uppercase tracking-wider">Capital Constituido</p>
                    <p id="modal-aumentos-constituido" class="text-base font-extrabold text-blue-900 mt-0.5">—</p>
                </div>
            </div>

            <!-- Fecha de Constitución -->
            <div class="bg-gray-50/60 border border-gray-200 rounded-xl p-3.5 flex items-center gap-3">
                <div class="p-2.5 bg-gray-500 text-white rounded-lg shadow-sm shadow-gray-100">
                    <i class="bi bi-calendar3 text-lg leading-none"></i>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Fecha Constitución</p>
                    <p id="modal-aumentos-fecha" class="text-sm font-bold text-gray-700 mt-1">—</p>
                </div>
            </div>

            <!-- Total Acumulado -->
            <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-3.5 flex items-center gap-3">
                <div class="p-2.5 bg-emerald-500 text-white rounded-lg shadow-sm shadow-emerald-100">
                    <i class="bi bi-graph-up-arrow text-lg leading-none"></i>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider">Total Acumulado</p>
                    <p id="modal-aumentos-total" class="text-base font-extrabold text-emerald-900 mt-0.5">—</p>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-y-auto max-h-[40vh] rounded-lg border border-gray-200">
        <div id="aumentos-loading" class="text-center py-10 text-gray-500">
            <i class="bi bi-arrow-repeat text-2xl"></i> Cargando...
        </div>
        <table id="tabla-aumentos" class="min-w-full text-sm hidden">
            <thead class="bg-gray-100 text-gray-700 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left border-b font-semibold">Fecha</th>
                    <th class="px-3 py-2 text-right border-b font-semibold">Monto Aumento</th>
                    <th class="px-3 py-2 text-left border-b font-semibold">Banco</th>
                    <th class="px-3 py-2 text-left border-b font-semibold">Observaciones</th>
                    <th class="px-3 py-2 text-left border-b font-semibold">Testimonio</th>
                    <th class="px-3 py-2 text-center border-b font-semibold w-24">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbody-aumentos" class="divide-y divide-gray-100"></tbody>
            <tfoot class="bg-gray-50 text-sm">
                <tr>
                    <td class="px-3 py-2 text-right border-t text-gray-400 text-xs italic">suma de aumentos:</td>
                    <td class="px-3 py-2 text-right border-t font-semibold text-gray-600" id="tfoot-total-aumentos">
                    </td>
                    <td colspan="3" class="border-t"></td>
                </tr>
            </tfoot>
        </table>
        <div id="aumentos-sin-datos" class="hidden text-center py-10 text-gray-400 text-sm">
            <i class="bi bi-inbox text-3xl block mb-2"></i> Sin aumentos registrados.
        </div>
    </div>

    <hr class="my-4">

    <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2" id="tituloFormAumento">
        <i class="bi bi-plus-circle text-green-500"></i> Registrar Aumento
    </h3>

    <div id="aumento-alert" class="hidden mb-3 px-3 py-2 rounded-md text-sm font-medium flex items-center gap-2">
    </div>

    <form id="formAumento" class="space-y-3" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="aumento_id" name="aumento_id">
        <input type="hidden" id="aumento_id_capital" name="id_capital">
        <input type="hidden" id="aumento_empresa_id">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <x-input-label for="aumento_monto" value="Monto (S/.)" />
                <input type="number" step="0.01" min="0" id="aumento_monto" name="monto_Capital"
                    required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
            </div>
            <div>
                <x-input-label for="aumento_banco" value="Banco" />
                <select id="aumento_banco" name="banco" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
                    <option value="SIN BANCO">SIN BANCO</option>
                    <option value="BCP">BCP</option>
                    <option value="BBVA">BBVA</option>
                    <option value="INTERBANK">INTERBANK</option>
                    <option value="CAJA TRUJILLO">CAJA TRUJILLO</option>
                    <option value="SCOTIABANK">SCOTIABANK</option>
                    <option value="BANCO DE LA NACIÓN">BANCO DE LA NACIÓN</option>
                </select>
            </div>
            <div>
                <x-input-label for="aumento_fecha" value="Fecha" />
                <input type="date" id="aumento_fecha" name="fecha" required
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
            </div>
        </div>
        <div>
            <x-input-label for="aumento_obs" value="Observaciones" />
            <input type="text" id="aumento_obs" name="observaciones" maxlength="255"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
        </div>
        <div>
            <x-input-label for="aumento_archivo" value="Archivo Testimonio (PDF)" />
            <div id="aumento_archivo_actual"
                class="hidden mb-2 flex items-center gap-2 text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-md px-3 py-2">
                <i class="bi bi-file-earmark-pdf text-red-500"></i>
                <span id="aumento_archivo_nombre" class="truncate max-w-xs text-xs"></span>
                <a id="aumento_archivo_ver" href="#" target="_blank"
                    class="ml-auto text-red-500 hover:text-red-700 text-xs font-medium flex items-center gap-1 flex-shrink-0">
                    <i class="bi bi-eye"></i> Ver
                </a>
            </div>
            <input type="file" id="aumento_archivo" name="archivo_testimonio" accept="application/pdf"
                class="block w-full border border-gray-300 rounded-md shadow-sm text-sm
                       file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0
                       file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-400 mt-1">Máximo 10 MB. Solo archivos PDF.</p>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" id="btnCancelarAumento"
                class="hidden flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition text-white text-sm">
                <i class="bi bi-x-circle"></i> Cancelar edición
            </button>
            <x-primary-button type="button" id="btnGuardarAumento" class="px-6 py-2 flex items-center gap-2">
                <i class="bi bi-plus-circle" id="btnGuardarAumentoIcon"></i>
                <span id="btnGuardarAumentoText">Guardar Aumento</span>
            </x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- =====================================================================
     SCRIPTS
====================================================================== --}}
<script>
    $(document).ready(function() {

        // ─── DATATABLES ──────────────────────────────────────────────────────────
        $('#myTable-capital').DataTable({
            responsive: false,
            paging: true,
            ordering: true,
            pageLength: 100,
            language: {
                url: '/js/datatables/lang/es-ES.json'
            },
            order: [
                [1, 'asc']
            ],
            columnDefs: [{
                orderable: false,
                targets: [5, 6]
            }]
        });

        // ─── HELPERS ─────────────────────────────────────────────────────────────
        const MESES_ES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        function formatFechaLegible(fechaStr) {
            const p = fechaStr.split('-');
            if (p.length !== 3) return fechaStr;
            return `${parseInt(p[2], 10)} ${MESES_ES[parseInt(p[1], 10) - 1] ?? '?'} ${p[0]}`;
        }

        function fmtMonto(n) {
            return parseFloat(n).toLocaleString('es-PE', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function getRawTotal(empresaId) {
            const val = parseFloat($(`.total-capital-${empresaId}`).attr('data-raw') || '0');
            return isNaN(val) ? 0 : val;
        }

        function setCeldaTotal(empresaId, valor) {
            const celda = $(`.total-capital-${empresaId}`);
            celda.attr('data-raw', valor);
            celda.text('S/. ' + fmtMonto(valor));
        }

        function sincronizarTotal(empresaId, totalCapitalServidor) {
            setCeldaTotal(empresaId, totalCapitalServidor);
            $('#modal-aumentos-total').text('S/. ' + fmtMonto(totalCapitalServidor));

            const constituido = parseFloat($(`.constituido-cell-${empresaId}`).attr('data-raw') || '0');
            const total = parseFloat(totalCapitalServidor) || 0;
            const nuevoAumento = Math.max(0, total - constituido);
            $(`.aumentos-cell-${empresaId}`).text('S/. ' + fmtMonto(nuevoAumento));
        }

        // ─── TOAST (esquina inferior derecha) ────────────────────────────────────
        function mostrarToast(mensaje, tipo = 'success') {
            if (typeof showAjaxToast === 'function') {
                showAjaxToast(mensaje, tipo);
            }
        }

        // ─── ALERT BADGE dentro del modal de aumentos ────────────────────────────
        function showAlertAumento(msg, tipo = 'success') {
            const el = document.getElementById('aumento-alert');
            const ok = tipo === 'success';
            el.className = `mb-3 px-3 py-2 rounded-md text-sm font-medium flex items-center gap-2 ${
            ok ? 'bg-green-50 text-green-700 border border-green-200'
               : 'bg-red-50 text-red-700 border border-red-200'}`;
            el.innerHTML = `<i class="bi ${ok ? 'bi-check-circle-fill' : 'bi-x-circle-fill'}"></i> ${msg}`;
            el.classList.remove('hidden');
            setTimeout(() => el.classList.add('hidden'), 4000);
        }

        // ─── RESET FORM AUMENTO ──────────────────────────────────────────────────
        function resetFormAumento() {
            $('#aumento_id').val('');
            $('#aumento_monto').val('');
            $('#aumento_banco').val('SIN BANCO');
            $('#aumento_fecha').val('');
            $('#aumento_obs').val('');
            $('#aumento_archivo').val('');
            $('#aumento_archivo_actual').addClass('hidden');
            $('#tituloFormAumento').html('<i class="bi bi-plus-circle text-green-500"></i> Registrar Aumento');
            $('#btnGuardarAumentoIcon').attr('class', 'bi bi-plus-circle');
            $('#btnGuardarAumentoText').text('Guardar Aumento');
            $('#btnCancelarAumento').addClass('hidden');
            document.getElementById('aumento-alert').classList.add('hidden');
        }

        // ─── HTML de los 3 botones de acciones (editar/aumentos/eliminar) ─────────
        // Se usa para insertar en el DOM cuando se crea un capital nuevo.
        function htmlBotonesCapital(idCapital, empresaId, razon, ruc, partida, constituido, obs) {
            // Obtenemos los SVG de los iconos del primer botón ya renderizado en la página.
            // Si no existe ninguno (página sin capitales), usamos texto de fallback.
            const svgEditar = document.querySelector('.btn-editar-capital x-edit-icon, .btn-editar-capital svg')
                ?.outerHTML ??
                '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>';
            const svgAumentos = document.querySelector('.btn-ver-aumentos x-list-icon, .btn-ver-aumentos svg')
                ?.outerHTML ??
                '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h7.5" /></svg>';
            const svgEliminar = document.querySelector(
                    '.btn-eliminar-capital x-delete-icon, .btn-eliminar-capital svg')?.outerHTML ??
                '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>';

            const obsEsc = (obs || '').replace(/"/g, '&quot;');

            return `
            <a role="button"
                class="flex items-center rounded-md tooltip btn-editar-capital"
                style="color: #e6a817;" onmouseover="this.style.color='#f5bc42';" onmouseout="this.style.color='#e6a817';"
                data-id="${idCapital}" data-empresa="${empresaId}" data-razon="${razon}"
                data-ruc="${ruc}" data-partida="${partida}" data-constituido="${constituido}"
                data-observaciones="${obsEsc}">
                ${svgEditar}
                <span class="tooltip-text">Editar</span>
            </a>
            <a role="button"
                class="flex items-center rounded-md tooltip btn-ver-aumentos"
                style="color: #2986cc;" onmouseover="this.style.color='#2ba4ff';" onmouseout="this.style.color='#2986cc';"
                data-id="${idCapital}" data-razon="${razon}" data-constituido="${constituido}" data-empresa="${empresaId}">
                ${svgAumentos}
                <span class="tooltip-text">Aumentos</span>
            </a>
            <a role="button"
                class="flex items-center rounded-md tooltip btn-eliminar-capital"
                style="color: #cc2929;" onmouseover="this.style.color='#ff4444';" onmouseout="this.style.color='#cc2929';"
                data-id="${idCapital}" data-empresa="${empresaId}">
                ${svgEliminar}
                <span class="tooltip-text">Eliminar</span>
            </a>`;
        }

        // ─── CARGAR AUMENTOS ─────────────────────────────────────────────────────
        function cargarAumentos(id_capital) {
            const loading = document.getElementById('aumentos-loading');
            const tabla = document.getElementById('tabla-aumentos');
            const sinDatos = document.getElementById('aumentos-sin-datos');
            const tbody = document.getElementById('tbody-aumentos');
            const tfoot = document.getElementById('tfoot-total-aumentos');

            loading.classList.remove('hidden');
            tabla.classList.add('hidden');
            sinDatos.classList.add('hidden');

            $.get(`/capital/${id_capital}/aumentos`, function(res) {
                loading.classList.add('hidden');

                if (!res.aumentos.length) {
                    sinDatos.classList.remove('hidden');
                    tfoot.textContent = 'S/. 0';
                    tfoot.dataset.suma = '0';
                    return;
                }

                let sumaAumentos = 0;

                tbody.innerHTML = res.aumentos.map(a => {
                    const monto = parseFloat(a.monto_Capital);
                    sumaAumentos += monto;

                    let testiHtml = '<span class="text-gray-400 text-xs">—</span>';
                    if (a.archivo_testimonio) {
                        const nombreArchivo = a.archivo_testimonio.split('/').pop();
                        testiHtml = `
                        <a href="/${a.archivo_testimonio}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-2 py-1 text-xs font-medium rounded-md
                                  bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 transition max-w-[180px]"
                           title="${nombreArchivo}">
                            <i class="bi bi-file-earmark-pdf flex-shrink-0"></i>
                            <span class="truncate">${nombreArchivo}</span>
                        </a>`;
                    }

                    const obs = a.observaciones ?? '';
                    const obsHtml = obs.length > 50 ?
                        `<span class="block max-w-[200px] truncate cursor-help text-xs" title="${obs}">${obs}</span>` :
                        (obs ? `<span class="text-xs">${obs}</span>` :
                            '<span class="text-gray-400 text-xs">—</span>');

                    return `
                <tr class="hover:bg-gray-50 transition" id="fila-aumento-${a.id_Aumento_Capital}">
                    <td class="px-3 py-2.5 text-sm text-gray-700 whitespace-nowrap">${formatFechaLegible(a.fecha)}</td>
                    <td class="px-3 py-2.5 text-right font-semibold text-gray-800 whitespace-nowrap">S/. ${fmtMonto(monto)}</td>
                    <td class="px-3 py-2.5 text-sm text-gray-700 whitespace-nowrap">${a.banco || 'SIN BANCO'}</td>
                    <td class="px-3 py-2.5">${obsHtml}</td>
                    <td class="px-3 py-2.5">${testiHtml}</td>
                    <td class="px-3 py-2.5 text-center">
                        <div class="flex justify-center gap-1">
                            <button type="button"
                                class="btn-editar-aumento-modal p-1.5 rounded-md hover:bg-yellow-50 transition"
                                style="color:#e6a817;" onmouseover="this.style.color='#f5bc42';" onmouseout="this.style.color='#e6a817';"
                                data-id="${a.id_Aumento_Capital}" data-monto="${a.monto_Capital}" data-banco="${a.banco || 'SIN BANCO'}"
                                data-fecha="${a.fecha}" data-obs="${(a.observaciones ?? '').replace(/"/g, '&quot;')}"
                                data-archivo="${a.archivo_testimonio ?? ''}" title="Editar">
                                <i class="bi bi-pencil text-base"></i>
                            </button>
                            <button type="button"
                                class="btn-eliminar-aumento-modal p-1.5 rounded-md hover:bg-red-50 transition"
                                style="color:#cc2929;" onmouseover="this.style.color='#ff4444';" onmouseout="this.style.color='#cc2929';"
                                data-id="${a.id_Aumento_Capital}" data-monto="${a.monto_Capital}" title="Eliminar">
                                <i class="bi bi-trash text-base"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
                }).join('');

                tfoot.textContent = `S/. ${fmtMonto(sumaAumentos)}`;
                tfoot.dataset.suma = sumaAumentos;
                tabla.classList.remove('hidden');
                bindBotonesAumento(id_capital);
            });
        }

        // ─── BIND BOTONES TABLA AUMENTOS ────────────────────────────────────────
        function bindBotonesAumento(id_capital) {
            document.querySelectorAll('.btn-editar-aumento-modal').forEach(btn => {
                btn.addEventListener('click', () => {
                    $('#aumento_id').val(btn.dataset.id);
                    $('#aumento_monto').val(btn.dataset.monto);
                    $('#aumento_banco').val(btn.dataset.banco);
                    $('#aumento_fecha').val(btn.dataset.fecha);
                    $('#aumento_obs').val(btn.dataset.obs);

                    if (btn.dataset.archivo) {
                        const nombreArchivo = btn.dataset.archivo.split('/').pop();
                        $('#aumento_archivo_nombre').text(nombreArchivo);
                        $('#aumento_archivo_ver').attr('href', '/' + btn.dataset.archivo);
                        $('#aumento_archivo_actual').removeClass('hidden');
                    } else {
                        $('#aumento_archivo_actual').addClass('hidden');
                    }

                    $('#tituloFormAumento').html(
                        '<i class="bi bi-pencil text-yellow-500"></i> Editar Aumento');
                    $('#btnGuardarAumentoIcon').attr('class', 'bi bi-save');
                    $('#btnGuardarAumentoText').text('Actualizar Aumento');
                    $('#btnCancelarAumento').removeClass('hidden');
                    document.getElementById('formAumento').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });

            document.querySelectorAll('.btn-eliminar-aumento-modal').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (!confirm('¿Eliminar este aumento de capital?')) return;

                    const id = btn.dataset.id;
                    const empresaId = $('#aumento_empresa_id').val();
                    const monto = parseFloat(btn.dataset.monto);

                    $.ajax({
                        url: `/capital/aumentos/${id}`,
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            _method: 'DELETE'
                        },
                        success: function(res) {
                            sincronizarTotal(empresaId, res.total_capital);

                            const tfoot = document.getElementById(
                                'tfoot-total-aumentos');
                            const nuevaSuma = Math.max(0, parseFloat(tfoot.dataset
                                .suma || '0') - monto);
                            tfoot.textContent = `S/. ${fmtMonto(nuevaSuma)}`;
                            tfoot.dataset.suma = nuevaSuma;

                            $(`#fila-aumento-${id}`).fadeOut(200, function() {
                                $(this).remove();
                                if (!document.querySelectorAll(
                                        '#tbody-aumentos tr').length) {
                                    document.getElementById(
                                        'tabla-aumentos').classList.add(
                                        'hidden');
                                    document.getElementById(
                                            'aumentos-sin-datos').classList
                                        .remove('hidden');
                                }
                            });

                            showAlertAumento('Aumento eliminado correctamente.',
                                'success');
                        },
                        error: function() {
                            showAlertAumento('Error al eliminar el aumento.',
                                'error');
                        }
                    });
                });
            });
        }

        // ─── ABRIR MODAL AUMENTOS ────────────────────────────────────────────────
        $(document).on('click', '.btn-ver-aumentos', function() {
            const id_capital = $(this).data('id');
            const razon = $(this).data('razon');
            const empresaId = $(this).data('empresa');
            const constituido = $(this).data('constituido');
            const fecha = $(this).data('fecha');

            $('#aumento_id_capital').val(id_capital);
            $('#aumento_empresa_id').val(empresaId);
            $('#modal-aumentos-empresa').text(razon);
            $('#modal-aumentos-constituido').text(constituido ? 'S/. ' + fmtMonto(constituido) : '—');
            $('#modal-aumentos-fecha').text(fecha ? formatFechaLegible(fecha) : '—');

            const totalActual = getRawTotal(empresaId);
            $('#modal-aumentos-total').text(totalActual ? 'S/. ' + fmtMonto(totalActual) : '—');

            resetFormAumento();
            cargarAumentos(id_capital);
            toggleModal('modalAumentos');
        });

        $('#btnCancelarAumento').on('click', function() {
            resetFormAumento();
        });

        // ─── GUARDAR AUMENTO ─────────────────────────────────────────────────────
        $('#btnGuardarAumento').on('click', function() {
            const id = $('#aumento_id').val();
            const id_capital = $('#aumento_id_capital').val();
            const url = id ? `/capital/aumentos/${id}` : `/capital/aumentos/store`;

            const btn = document.getElementById('btnGuardarAumento');
            btn.disabled = true;
            $('#btnGuardarAumentoIcon').attr('class', 'bi bi-arrow-repeat');
            $('#btnGuardarAumentoText').text('Guardando...');

            const formData = new FormData($('#formAumento')[0]);
            formData.set('_token', $('meta[name="csrf-token"]').attr('content'));
            if (id) formData.append('_method', 'PUT');

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    const empresaId = $('#aumento_empresa_id').val();
                    sincronizarTotal(empresaId, res.total_capital);
                    showAlertAumento(res.message || (id ? 'Aumento actualizado.' :
                        'Aumento registrado.'), 'success');
                    resetFormAumento();
                    cargarAumentos(id_capital);
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.errors ?
                        Object.values(xhr.responseJSON.errors).flat().join(' | ') :
                        (xhr.responseJSON?.message || 'Error al guardar.');
                    showAlertAumento(msg, 'error');
                },
                complete: function() {
                    btn.disabled = false;
                    const enEdicion = !!$('#aumento_id').val();
                    $('#btnGuardarAumentoIcon').attr('class', enEdicion ? 'bi bi-save' :
                        'bi bi-plus-circle');
                    $('#btnGuardarAumentoText').text(enEdicion ? 'Actualizar Aumento' :
                        'Guardar Aumento');
                }
            });
        });

        // ─── CAPITAL: REGISTRAR ──────────────────────────────────────────────────
        $(document).on('click', '.btn-registrar-capital', function() {
            $('#capital_id').val('');
            $('#capital_empresa_id').val($(this).data('empresa'));
            $('#capital_razon').val($(this).data('razon'));
            $('#capital_ruc').val($(this).data('ruc') || '—');
            $('#capital_partida').val('');
            $('#capital_constituido').val('');
            $('#capital_obs').val('');
            $('#capital_fecha').val('');
            toggleModal('modalCapital');
        });

        // ─── CAPITAL: EDITAR ─────────────────────────────────────────────────────
        $(document).on('click', '.btn-editar-capital', function() {
            $('#capital_id').val($(this).data('id'));
            $('#capital_empresa_id').val($(this).data('empresa'));
            $('#capital_razon').val($(this).data('razon'));
            $('#capital_ruc').val($(this).data('ruc') || '—');
            $('#capital_partida').val($(this).data('partida'));
            $('#capital_constituido').val($(this).data('constituido'));
            $('#capital_obs').val($(this).data('observaciones'));
            $('#capital_fecha').val($(this).data('fecha'));
            toggleModal('modalCapital');
        });

        // ─── CAPITAL: GUARDAR ────────────────────────────────────────────────────
        $('#btnGuardarCapital').on('click', function() {
            const id = $('#capital_id').val();
            const url = id ? `/capital/${id}` : '/capital/store';

            const btn = document.getElementById('btnGuardarCapital');
            btn.disabled = true;
            $('#btnGuardarCapitalIcon').attr('class', 'bi bi-arrow-repeat');
            $('#btnGuardarCapitalText').text('Guardando...');

            console.log("Fecha enviada:", $('#capital_fecha').val());
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: id ? 'PUT' : 'POST',
                    empresas_cliente: $('#capital_empresa_id').val(),
                    partida: $('#capital_partida').val(),
                    capital_Constituido: $('#capital_constituido').val(),
                    fecha: $('#capital_fecha').val(),
                    observaciones: $('#capital_obs').val(),
                },
                success: function(res) {
                    toggleModal('modalCapital');

                    const empresaId = $('#capital_empresa_id').val();
                    const partida = $('#capital_partida').val() || '—';
                    const constituido = parseFloat($('#capital_constituido').val()) || 0;
                    const obs = $('#capital_obs').val() || '—';
                    const fila = $(`#fila-empresa-${empresaId}`);

                    fila.find(`.partida-cell-${empresaId}`).text(partida);
                    fila.find(`.fecha-cell-${empresaId}`).text(
                        formatearFecha($('#capital_fecha').val())
                    );
                    fila.find(`.obs-cell-${empresaId}`).text(obs);

                    if (!id) {
                        // ── NUEVO capital: reemplazar botón banco con los 3 botones ──
                        const idCapital = res.capital.id_capital;
                        const razon = $('#capital_razon').val();
                        const ruc = $('#capital_ruc').val();

                        fila.find(`.constituido-cell-${empresaId}`).attr('data-raw', constituido).text('S/. ' + fmtMonto(constituido));
                        fila.find(`.aumentos-cell-${empresaId}`).text('S/. 0');
                        setCeldaTotal(empresaId, constituido);

                        // Reemplazamos el contenido del div de acciones directamente con HTML
                        fila.find(`.acciones-cell-${empresaId}`)
                            .html(htmlBotonesCapital(idCapital, empresaId, razon, ruc,
                                partida, constituido, obs));

                        mostrarToast(res.message || 'Capital registrado correctamente.',
                            'success');

                    } else {
                        // ── EDITAR: actualizar totales y data-* en botones ──
                        fila.find(`.constituido-cell-${empresaId}`).attr('data-raw', constituido).text('S/. ' + fmtMonto(constituido));
                        if (res.total_capital !== undefined) {
                            setCeldaTotal(empresaId, res.total_capital);
                            const totalCap = parseFloat(res.total_capital) || 0;
                            const nuevoAumento = Math.max(0, totalCap - constituido);
                            fila.find(`.aumentos-cell-${empresaId}`).text('S/. ' + fmtMonto(nuevoAumento));
                        }

                        let btn = fila.find('.btn-editar-capital');

                        btn.data('partida', partida);
                        btn.data('constituido', constituido);
                        btn.data('observaciones', obs);
                        btn.data('fecha', $('#capital_fecha').val());

                        btn.attr('data-partida', partida);
                        btn.attr('data-constituido', constituido);
                        btn.attr('data-observaciones', obs);
                        btn.attr('data-fecha', $('#capital_fecha').val());

                        let btnAumentos = fila.find('.btn-ver-aumentos');

                        btnAumentos.data('constituido', constituido);
                        btnAumentos.data('fecha', $('#capital_fecha').val());

                        btnAumentos.attr('data-constituido', constituido);
                        btnAumentos.attr('data-fecha', $('#capital_fecha').val());

                        fila.find('.btn-ver-aumentos').attr('data-constituido',
                            constituido);

                        mostrarToast(res.message || 'Capital actualizado correctamente.',
                            'success');
                    }
                },
                error: function(xhr) {
                    const msgs = xhr.responseJSON?.errors ?
                        Object.values(xhr.responseJSON.errors).flat().join('\n') :
                        (xhr.responseJSON?.error || xhr.responseJSON?.message ||
                            'Error al guardar.');
                    alert(msgs);
                },
                complete: function() {
                    btn.disabled = false;
                    $('#btnGuardarCapitalIcon').attr('class', 'bi bi-check-lg');
                    $('#btnGuardarCapitalText').text('Guardar');
                }
            });
        });

        // ─── CAPITAL: ELIMINAR (AJAX puro, sin recargar página) ─────────────────
        $(document).on('click', '.btn-eliminar-capital', function() {
            if (!confirm('¿Estás seguro de eliminar este capital y todos sus aumentos?')) return;

            const id = $(this).data('id');
            const empresaId = $(this).data('empresa');

            $.ajax({
                url: `/capital/${id}`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'DELETE'
                },
                success: function(res) {
                    const fila = $(`#fila-empresa-${empresaId}`);

                    // Limpiar celdas de la fila (volver al estado "sin capital")
                    fila.find(`.partida-cell-${empresaId}`).text('—');
                    fila.find(`.fecha-cell-${empresaId}`).text('—'); // tmr oe ya esta
                    fila.find(`.obs-cell-${empresaId}`).text('—');
                    fila.find(`.constituido-cell-${empresaId}`).attr('data-raw', '0').text('—');
                    fila.find(`.aumentos-cell-${empresaId}`).text('—');

                    const celdaTotal = fila.find(`.total-capital-${empresaId}`);
                    celdaTotal.attr('data-raw', '').text('—');

                    // Reemplazar botones con solo el botón de crear capital
                    // Reutilizamos los data del primer btn-registrar-capital que exista,
                    // o construimos el HTML directamente con los datos de la fila.
                    const razon = fila.find('td:nth-child(2)').text().trim();
                    const ruc = fila.find('td:nth-child(3)').text().trim().replace('—', '');

                    fila.find(`.acciones-cell-${empresaId}`).html(`
                    <a role="button"
                        class="flex items-center rounded-md tooltip btn-registrar-capital"
                        style="color: #28a745;" onmouseover="this.style.color='#34d058';"
                        onmouseout="this.style.color='#28a745';"
                        data-empresa="${empresaId}" data-razon="${razon}" data-ruc="${ruc}">
                        <x-bank-icon/>
                        <span class="tooltip-text">Crear Capital</span>
                    </a>`);

                    mostrarToast(res.message || 'Capital eliminado correctamente.',
                        'error');
                },
                error: function() {
                    mostrarToast('Error al eliminar.', 'error');
                }
            });
        });

        // ─── Tamaño modal aumentos ────────────────────────────────────────────────
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-ver-aumentos')) {
                setTimeout(function() {
                    const modal = document.querySelector('#modalAumentos .bg-white');
                    if (modal) {
                        modal.style.maxWidth = '1100px';
                        modal.style.width = '95%';
                    }
                }, 100);
            }
        });

        function formatearFecha(fecha) {
            if (!fecha) return '—';

            const partes = fecha.split('-');
            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        }

    });
</script>
