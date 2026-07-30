@php
    use Carbon\Carbon;
    $nombreMes = strtoupper(Carbon::now()->locale('es')->monthName);
    $mesLabel = fn($i) => strtoupper(Carbon::create()->month($i)->locale('es')->monthName);
    $periodoActual = now()->startOfMonth()->format('Y-m-d');
    $vencimiento = now()->addMonthNoOverflow()->setDay(10)->format('Y-m-d');
@endphp


<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Deudas AFP — Año <span class="text-blue-500">{{ $anio }}</span>
        </h2>
    </x-slot>

    <style>
        .empresa-link {
            color: #111827;
            text-decoration: none;
            border-bottom: 1px solid transparent;
        }

        .empresa-link:hover {
            border-bottom: 1px solid #3b82f6;
        }

        .cell-total {
            font-weight: 600;
        }
    </style>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            {{-- Filtro de año --}}
            <div class="bg-gray-100 p-4 rounded-lg shadow mb-6 flex items-center gap-4">
                <form action="{{ route('deuda-afp.index') }}" method="GET" class="flex items-center gap-4">
                    <select name="anio" onchange="this.form.submit()"
                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @for ($a = now()->year; $a >= 2020; $a--)
                            <option value="{{ $a }}" {{ $a == $anio ? 'selected' : '' }}>{{ $a }}
                            </option>
                        @endfor
                    </select>
                    {{-- <button type="submit"
                        class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md text-white hover:bg-blue-600 transition duration-300">
                        <i class="bi bi-arrow-repeat"></i> Cambiar año
                    </button> --}}
                </form>
                <br>
                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <a href="{{ route('tipo-afp.index') }}"
                        class="ml-auto bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white shadow-md">
                        <i class="bi bi-bank"></i> Tipos de AFP
                    </a>
                @endif
            </div>

            {{-- Tabla --}}
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-[1200px] table-auto text-sm" id="tabla-afp">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-700">
                            <th class="px-3 py-2 border-b">UD</th>
                            <th class="px-3 py-2 border-b">ESTADO RECURSOS</th>
                            <th class="px-3 py-2 border-b">RAZÓN SOCIAL</th>
                            <th class="px-3 py-2 border-b">RUC</th>
                            @for ($m = 1; $m <= 12; $m++)
                                <th class="px-3 py-2 border-b">{{ $mesLabel($m) }}</th>
                            @endfor
                            <th class="px-3 py-2 border-b">TOTAL</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @foreach ($filas as $f)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">{{ $f['ud'] }}</td>
                                <td class="px-3 py-2">{{ $f['estado_recursos'] }}</td>

                                {{-- RAZÓN SOCIAL - Abre Modal --}}
                                <td class="px-3 py-2">
                                    <a href="javascript:void(0)"
                                        class="empresa-link transition-colors hover:text-blue-600"
                                        onclick="abrirModalAfp(this, {{ $f['id'] }}, '{{ addslashes($f['razon']) }}')">
                                        {{ $f['razon'] }}
                                    </a>
                                </td>

                                <td class="px-3 py-2">
                                    <a href="javascript:void(0)"
                                        class="empresa-link transition-colors hover:text-blue-600"
                                        onclick="abrirModalPDF('{{ $f['id'] }}','{{ addslashes($f['razon']) }}')">
                                        {{ $f['ruc'] }}
                                    </a>
                                </td>

                                @for ($m = 1; $m <= 12; $m++)
                                    @php
                                        $c = $f['meses'][$m];
                                        $hasDeuda = $c['estado'] !== '—';
                                        $cellClasses = $hasDeuda ? 'text-gray-800' : 'text-gray-400';
                                    @endphp

                                    <td class="px-3 py-2 {{ $cellClasses }}">
                                        @php
                                            $monto = $hasDeuda
                                                ? 'S/. ' . number_format($c['total'], 2, '.', ',')
                                                : $mesLabel($m);
                                            $estado = $hasDeuda ? $c['estado'] : '';
                                            $estadoColor = $estado === 'Pendiente' ? 'text-red-600' : 'text-gray-500';
                                        @endphp

                                        <div class="leading-tight">
                                            <a href="{{ route('deuda-afp.detalle', ['empresa' => $f['id'], 'anio' => $anio, 'mes' => $m]) }}"
                                                target="_blank"
                                                class="{{ $hasDeuda ? 'text-blue-600 hover:underline' : 'text-gray-400 hover:text-blue-500 hover:underline' }}">
                                                {!! $monto !!}
                                            </a>
                                            @if ($hasDeuda)
                                                <span
                                                    class="block text-[11px] mt-0.5 {{ $estadoColor }}">{{ $estado }}
                                                    @if ($estado == 'Pendiente')
                                                        sin intereses
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                @endfor

                                {{-- TOTAL FILA --}}
                                @php
                                    $totalFila = collect($f['meses'])->sum('total');
                                @endphp
                                <td class="!px-5 !py-5 cell-total text-blue-600">
                                    S/. {{ number_format($totalFila, 2, '.', ',') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-app-layout>

{{-- MODAL: Generar Reporte AFP --}}
<x-modal-show id="modal-pdf" title="Generar Reporte de Deudas AFP">
    <form method="GET" action="{{ route('deuda-afp.pdf') }}" target="_blank" class="space-y-6">
        <input type="hidden" name="id_empresa_cliente" id="pdf-id-empresa">

        <div>
            <x-input-label :value="__('Empresa')" />
            <x-text-input id="pdf-razon" type="text" class="mt-1 block w-full" disabled />
        </div>

        <div>
            <x-input-label :value="__('Período hasta')" />
            <select name="mes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                        {{ strtoupper(\Carbon\Carbon::create()->month($m)->locale('es')->monthName) }}
                        {{ now()->year }}
                    </option>
                @endfor
            </select>
            <small class="text-gray-500">* Se incluirán las deudas que vence hasta el mes siguiente al período
                seleccionado.</small>
        </div>

        <div class="mt-3">
            <x-input-label :value="__('Estado de deuda')" />
            <select name="estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="ambos">Ambos</option>
                <option value="pendientes">Pendientes</option>
                <option value="pagados">Pagados</option>
            </select>
        </div>

        <div class="flex justify-end mt-6">
            <x-primary-button class="px-6 py-2">Generar PDF</x-primary-button>
        </div>
    </form>
</x-modal-show>
<script>
    function abrirModalPDF(id, razon) {
        document.getElementById('pdf-id-empresa').value = id;
        document.getElementById('pdf-razon').value = razon;
        toggleModal('modal-pdf');
    }
</script>


{{-- MODAL: CREAR DEUDA AFP --}}
@php
    // Fecha base: primer día del mes actual
    $periodoActual = now()->startOfMonth()->format('Y-m-d');

    // Vencimiento calculado dinámicamente (5to día hábil del mes siguiente)
    $vencimiento = \App\Models\DeudaAfp::calcularVencimiento(\Carbon\Carbon::now())->format('Y-m-d');
@endphp

{{-- MODAL: CREAR DEUDA AFP --}}
<x-modal-show id="modal-crear-afp" title="Registrar deuda AFP para el período actual, {{ $nombreMes }}">
    <form method="POST" action="{{ route('deuda-afp.store') }}" class="space-y-6" autocomplete="off">
        @csrf
        <input type="hidden" name="id_empresa_cliente" id="md-id-empresa">

        {{-- Empresa --}}
        <div>
            <x-input-label :value="__('Empresa')" />
            <x-text-input id="md-razon" type="text" class="mt-1 block w-full bg-gray-200" disabled />
        </div>

        {{-- AFP --}}
        <div>
            <x-input-label :value="__('AFP')" />
            <select name="id_afp" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                @foreach ($afps as $a)
                    <option value="{{ $a->id_afp }}">{{ $a->nombre }}</option>
                @endforeach
            </select>
        </div>

        {{-- Periodo oculto (primer día del mes actual) --}}
        <input class="hidden" name="periodo" value="{{ $periodoActual }}">

        {{-- Monto deuda --}}
        <div>
            <x-input-label :value="__('Monto deuda (S/.)')" />
            <x-text-input name="monto_deuda" type="number" step="0.01" min="0" class="mt-1 block w-full"
                required />
        </div>

        {{-- Fecha de vencimiento --}}
        <div>
            <x-input-label :value="__('Fecha de vencimiento')" />
            <x-text-input name="fecha_vencimiento" type="date" class="mt-1 block w-full bg-gray-200"
                value="{{ $vencimiento }}" readonly required />
            <small class="text-gray-500">* El interés corre desde el día siguiente si no se paga a tiempo.</small>
        </div>

        <div class="flex justify-end mt-6">
            <x-primary-button class="px-6 py-2">
                {{ __('Crear deuda AFP') }}
            </x-primary-button>
        </div>
    </form>
</x-modal-show>


<script>
    function abrirModalAfp(anchorEl, id, razon) {
        anchorEl.classList.add('text-blue-600');
        document.getElementById('md-id-empresa').value = id;
        document.getElementById('md-razon').value = razon;
        toggleModal('modal-crear-afp');
    }


    $(document).ready(function() {
        const anio = '{{ $anio }}';
        const ahora = new Date();
        const pad = num => String(num).padStart(2, '0');
        const fechaNum =
            `${ahora.getFullYear()}${pad(ahora.getMonth()+1)}${pad(ahora.getDate())}_${pad(ahora.getHours())}${pad(ahora.getMinutes())}${pad(ahora.getSeconds())}`;
        const tituloArchivo = `DEUDAS_AFP_${anio}_${fechaNum}`;

        $('#tabla-afp').DataTable({
            responsive: false,
            paging: true,
            ordering: true,
            pageLength: 200,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: tituloArchivo,
                exportOptions: {
                    format: {
                        body: function(data, row, column, node) {
                            if (typeof data === 'string') {
                                // Buscar el elemento <a> si existe para extraer el contenido principal (monto o link)
                                const aEl = node.querySelector('a');
                                let text = aEl ? aEl.textContent : data;

                                // Limpiar etiquetas HTML residuales
                                text = text.replace(/<[^>]*>?/gm, '').trim();

                                // Decodificar entidades HTML
                                let txt = document.createElement('textarea');
                                txt.innerHTML = text;
                                text = txt.value;

                                // Si es un monto monetario, remover S/. y comas para dejarlo numérico
                                if (text.includes('S/.')) {
                                    let clean = text.replace(/S\/\.\s?/g, '').replace(/,/g, '').trim();
                                    return isNaN(clean) || clean === '' ? text : parseFloat(clean);
                                }
                                return text;
                            }
                            return data;
                        }
                    }
                }
            }],
            language: {
                url: "/js/datatables/lang/es-ES.json"
            }
        });
    });
</script>
