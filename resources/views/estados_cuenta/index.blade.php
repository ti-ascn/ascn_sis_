@php
    $meses = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Estados de Cuenta – Año {{ $anio }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            {{-- BUSCADOR --}}
            <div class="bg-white shadow-lg rounded-lg p-4 mb-4 flex items-end gap-4">

                {{-- FORM SOLO PARA BUSCAR --}}
                <form method="GET" action="{{ route('estados_cuenta.index') }}" class="flex items-end gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                        <select name="anio" onchange="this.form.submit()"
                            class="border-gray-300 rounded-md shadow-sm w-32
                        focus:ring-blue-500 focus:border-blue-500">
                            @for ($i = 0; $i <= 5; $i++)
                                @php $year = now()->year - $i; @endphp
                                <option value="{{ $year }}" {{ (int) $anio === $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- <button type="submit"
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                    text-white px-4 py-2 rounded-md transition h-[42px]">
                        <i class="bi bi-search"></i>
                        Buscar
                    </button> --}}
                </form>

                <a href="{{ route('estados_cuenta.exportar.excel', ['anio' => $anio]) }}"
                    class="flex items-center gap-2 bg-green-600 hover:bg-green-700
                        text-white px-4 py-2 rounded-md transition h-[42px]">
                    <i class="bi bi-file-earmark-excel"></i>
                    Exportar Excel
                </a>

                <a href="{{ route('estados_cuenta.todos') }}"
                    class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700
            text-white px-4 py-2 rounded-md transition h-[42px]">
                    <i class="bi bi-table"></i>
                    Todos los registros
                </a>

                <button type="button" onclick="toggleModal('modalLeyendaEstadosCuenta')"
                    class="flex items-center gap-2 bg-gray-200 hover:bg-gray-300
                text-gray-700 font-semibold px-4 py-2 rounded-md transition h-[42px]">
                    <i class="bi bi-info-circle"></i>

                </button>

            </div>


            {{-- TABLA --}}
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-[1600px] table-auto text-sm" id="tabla-estados-cuenta">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-3 py-2 border-b">RUC</th>
                            <th class="px-3 py-2 border-b">Estado</th>
                            <th class="px-3 py-2 border-b">Sede</th>
                            <th class="px-3 py-2 border-b">Razón Social / Cuenta</th>
                            @foreach (['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $mes)
                                <th class="px-3 py-2 border-b !text-center">{{ $mes }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($cuentas as $cuenta)
                            @php $empresa = $cuenta->empresa; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 border-b">
                                    {{ $empresa->ruc }}
                                </td>
                                <td class="px-3 py-2 border-b">
                                    {{ $empresa->estado }}
                                </td>

                                <td class="px-3 py-2 border-b">
                                    {{ $cuenta->sede ?? 'Trujillo' }}
                                </td>

                                {{-- RAZÓN SOCIAL --}}
                                <td class="px-3 py-2 border-b">
                                    <span
                                        class="btn-open-estado cursor-pointer font-medium text-gray-800
                                                    hover:underline hover:text-blue-700 transition"
                                        data-id-cuenta="{{ $cuenta->id_cuentas_empresa }}"
                                        data-cuenta-nombre="{{ $cuenta->nombre }} ({{ $empresa->razon_social }})"
                                        data-mes="1" data-anio="{{ $anio }}">
                                        {{ $empresa->razon_social }}
                                    </span>

                                    <div class="text-xs text-gray-500 mt-0.5">
                                        {{ $cuenta->nombre }}
                                        <span class="font-semibold">
                                            ({{ $cuenta->tipo == 0 ? 'S/' : '$' }})
                                        </span>
                                    </div>
                                </td>

                                {{-- MESES  --}}
                                @for ($m = 1; $m <= 12; $m++)
                                    @php
                                        $estado = $cuenta->estadosCuenta->firstWhere('mes', $m);

                                        // Colores SOLO para recepción
                                        $recepcionColor = match ($estado?->recepcion) {
                                            'SI' => 'bg-green-500',
                                            'NO' => 'bg-red-500',
                                            'N/A' => 'bg-black',
                                            default => 'bg-gray-200',
                                        };
                                    @endphp

                                    <td class="px-3 py-2 border-b text-center">
                                        <span
                                            class="btn-open-estado inline-flex flex-col items-center justify-center
                                                    w-10 h-10 rounded-full cursor-pointer
                                                    hover:ring-2 hover:ring-blue-300 transition
                                                    {{ $recepcionColor }}"
                                            data-id-cuenta="{{ $cuenta->id_cuentas_empresa }}"
                                            data-id-estado="{{ $estado?->id_estado_cuenta }}"
                                            data-usuario="{{ $estado?->usuario?->name }}"
                                            data-fecha="{{ $estado?->updated_at?->format('d/m/Y H:i') }}"
                                            data-cuenta-nombre="{{ $cuenta->nombre }} ({{ $empresa->razon_social }})"
                                            data-mes="{{ $m }}" data-anio="{{ $anio }}"
                                            data-recepcion="{{ $estado?->recepcion }}"
                                            data-estatus="{{ $estado?->estatus }}"
                                            data-observacion="{{ $estado?->observacion }}"
                                            data-pdf="{{ $estado?->archivo_pdf ? asset($estado->archivo_pdf) : '' }}">
                                            {{-- ESTADO (SOLO TEXTO) --}}
                                            <span
                                                class="text-xs font-bold {{ $estado && $estado->recepcion === 'N/A' ? 'text-white' : 'text-gray-800' }}">
                                                {{ $estado?->estatus ?? '—' }}
                                            </span>
                                        </span>
                                        {{-- ICONOS PDF - CENTRADO --}}
                                        @if ($estado && $estado->archivo_pdf)
                                            <div class="flex justify-center gap-3 mt-1">
                                                @if ($estado->archivo_pdf)
                                                    <a href="{{ asset($estado->archivo_pdf) }}" target="_blank"
                                                        class="tooltip inline-flex flex-col items-center text-blue-600 hover:text-blue-800 hover:scale-110 transition-transform">
                                                        <i class="bi bi-file-earmark-pdf-fill text-lg"></i>
                                                        <span class="tooltip-text">PDF</span>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- Modal: Leyenda Estados de Cuenta --}}
<x-modal-show id="modalLeyendaEstadosCuenta" title="Leyenda de Estados de Cuenta">

    <div class="space-y-6 text-sm text-gray-700">

        {{-- RECEPCIÓN --}}
        <div>
            <h3 class="font-semibold text-gray-800 mb-2">Recepción</h3>

            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-green-500"></span>
                    <span><strong>Verde:</strong> Sí</span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-red-500"></span>
                    <span><strong>Rojo:</strong> No</span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-black"></span>
                    <span class="text-black">
                        <strong class="text-black">Negro:</strong> N/A
                    </span>
                </div>
            </div>
        </div>

        {{-- ESTADO DEL TRABAJO --}}
        <div>
            <h3 class="font-semibold text-gray-800 mb-2">Estado del trabajo</h3>

            <ul class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <li><strong>TR</strong> → Trabajado</li>
                <li><strong>EP</strong> → En proceso</li>
                <li><strong>NT</strong> → No trabajado</li>
                <li><strong>N/A</strong> → No aplica</li>
                <li><strong>- -</strong> → Sin Recepción</li>
            </ul>
        </div>

        <div class="text-xs text-gray-500 border-t pt-3">
            * El color representa la recepción de documentos.<br>
            * Las siglas indican el estado del trabajo del mes.
        </div>

    </div>
</x-modal-show>

{{-- Modal: Registrar Estado de Cuenta --}}
<x-modal-show id="modalEstadoCuenta" title="Registrar estado de cuenta">

    <form id="form-estado-cuenta" method="POST" action="{{ route('estados_cuenta.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <input type="hidden" name="id_cuenta_empresa" id="ec-id-cuenta">

        {{-- Cuenta --}}
        <div>
            <x-input-label value="Cuenta bancaria" />
            <x-text-input id="ec-cuenta-nombre" type="text" class="mt-1 block w-full" disabled />
        </div>

        <input type="hidden" name="id_estado_cuenta" id="ec-id-estado">

        {{-- Mes / Año --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label value="Mes" />
                <select name="mes"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    required>
                    @foreach ($meses as $num => $nombre)
                        <option value="{{ $num }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label value="Año" />
                <select name="anio"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                        focus:ring-blue-500 focus:border-blue-500"
                    required>
                    @for ($i = 0; $i <= 5; $i++)
                        @php $year = now()->year - $i; @endphp
                        <option value="{{ $year }}" {{ (int) $anio === $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
            </div>

        </div>

        {{-- Recepción / Estado --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label value="Recepción" />
                <select name="recepcion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="SI">Sí</option>
                    <option value="NO">No</option>
                    <option value="N/A">N/A</option>
                </select>
            </div>

            <div>
                <x-input-label value="Estado del trabajo" />
                <select name="estatus" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="TR">Trabajado</option>
                    <option value="EP">En proceso</option>
                    <option value="NT">No trabajado</option>
                    <option value="N/A">N/A</option>
                    <option value="- -">Sin Recepción</option>
                </select>
            </div>
        </div>
        {{-- ===================== --}}
        {{-- PDF ESTADO DE CUENTA --}}
        {{-- ===================== --}}
        <div class="border rounded-md p-4 bg-gray-50 space-y-3">
            <div class="flex items-center justify-between">
                <x-input-label value="ARCHIVO PDF" />
                <a id="ec-pdf-link" href="#" target="_blank"
                    class="text-sm text-blue-600 hover:underline hidden">
                    Ver PDF
                </a>
            </div>

            <input type="file" name="archivo_pdf" accept="application/pdf"
                class="block w-full border-gray-300 rounded-md shadow-sm">

            <p class="text-xs text-gray-500">
                PDF principal del estado de cuenta mensual.
            </p>
        </div>

        {{-- Observación --}}
        <div>
            <x-input-label value="Observación" />
            <textarea name="observacion" rows="3"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Detalle adicional, incidencias, faltantes, etc."></textarea>
        </div>

        <div id="ec-info-usuario" class="text-xs text-gray-500 border-t pt-3 hidden">
        </div>

        {{-- Acciones --}}
        <div class="flex justify-end pt-4">
            <x-primary-button id="btn-submit-estado" class="px-6 py-2">
                <span id="btn-text">Guardar estado</span>
                <span id="btn-loader" class="hidden">
                    <i class="bi bi-arrow-repeat animate-spin mr-2"></i> Procesando...
                </span>
            </x-primary-button>
        </div>

    </form>
</x-modal-show>

<script>
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-open-estado');
        if (!btn) return;

        // IDs
        document.getElementById('ec-id-cuenta').value = btn.dataset.idCuenta ?? '';
        document.getElementById('ec-id-estado').value = btn.dataset.idEstado ?? '';

        // Texto
        document.getElementById('ec-cuenta-nombre').value = btn.dataset.cuentaNombre ?? '';

        // Inputs
        const mesSelect = document.querySelector('#modalEstadoCuenta select[name="mes"]');
        const anioSelect = document.querySelector('#modalEstadoCuenta select[name="anio"]');
        const recepcionSelect = document.querySelector('#modalEstadoCuenta select[name="recepcion"]');
        const estatusSelect = document.querySelector('#modalEstadoCuenta select[name="estatus"]');
        const obsTextarea = document.querySelector('#modalEstadoCuenta textarea[name="observacion"]');
        const infoUsuario = document.getElementById('ec-info-usuario');

        if (mesSelect) mesSelect.value = btn.dataset.mes;
        if (anioSelect) anioSelect.value = btn.dataset.anio;

        // 👇 PREFILL SOLO SI YA EXISTE
        if (btn.dataset.idEstado) {
            recepcionSelect.value = btn.dataset.recepcion;
            estatusSelect.value = btn.dataset.estatus;
            obsTextarea.value = btn.dataset.observacion ?? '';
        } else {
            recepcionSelect.value = 'N/A';
            estatusSelect.value = 'N/A';
            obsTextarea.value = '';
        }


        if (btn.dataset.idEstado && btn.dataset.usuario) {
            infoUsuario.innerHTML =
                `Última actualización por <strong>${btn.dataset.usuario}</strong> · ${btn.dataset.fecha}`;
            infoUsuario.classList.remove('hidden');
        } else {
            infoUsuario.classList.add('hidden');
        }

        const pdfLink = document.getElementById('ec-pdf-link');

        if (pdfLink) {
            if (btn.dataset.pdf) {
                pdfLink.href = btn.dataset.pdf;
                pdfLink.classList.remove('hidden');
            } else {
                pdfLink.classList.add('hidden');
            }
        }

        toggleModal('modalEstadoCuenta');
    });

    // Manejo de carga en el formulario
    const formEstado = document.getElementById('form-estado-cuenta');
    const btnSubmit = document.getElementById('btn-submit-estado');
    const btnText = document.getElementById('btn-text');
    const btnLoader = document.getElementById('btn-loader');

    if (formEstado) {
        formEstado.addEventListener('submit', function() {
            btnSubmit.disabled = true;
            btnText.classList.add('hidden');
            btnLoader.classList.remove('hidden');
        });
    }
</script>



<script>
    $(document).ready(function() {
        $('#tabla-estados-cuenta').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            pageLength: 200,
            responsive: false,
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            }
        });
    });
</script>
