<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center gap-2">
            {{ $fraccionamiento->refinanciamiento ? 'Refinanciamiento del Fraccionamiento' : 'Fraccionamiento' }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            {{-- BOTONES SUPERIORES --}}
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col mb-4">
                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <a href="{{ route('fraccionamiento.create') }}"
                        class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-plus-circle"></i> Nuevo Fraccionamiento
                    </a>
                @endif

                <a href="{{ route('fraccionamiento.pdf', $fraccionamiento->id_fraccionamiento) }}" target="_blank"
                    class="flex items-center gap-2 bg-red-600 px-4 py-2 rounded-md hover:bg-red-700 text-white">
                    <i class="bi bi-filetype-pdf"></i> Exportar PDF
                </a>

                <a href="{{ route('fraccionamiento.index') }}"
                    class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white text-center w-full md:w-auto">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            {{-- CARD PRINCIPAL --}}
            <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">

                {{-- ENCABEZADO --}}
                <div class="flex flex-col md:flex-row md:justify-between md:items-center border-b pb-4 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">
                            Resolución N° <span class="text-blue-600">{{ $fraccionamiento->nro_resolucion }}</span>
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            <strong>Empresa:</strong> {{ $fraccionamiento->empresa->razon_social ?? 'Sin empresa' }}
                        </p>
                        <p class="text-sm text-gray-600">
                            <strong>Tributo:</strong> {{ $fraccionamiento->tributo_asociado }}
                            ({{ $fraccionamiento->codigo }})
                        </p>
                        <p class="text-sm text-gray-600">
                            <strong>Periodo:</strong> {{ $fraccionamiento->periodo }}
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 text-right">
                        <p class="text-sm text-gray-500 font-medium">Total Fraccionado</p>
                        <p class="text-2xl font-semibold text-green-600">
                            S/ {{ number_format($fraccionamiento->total, 2) }}
                        </p>
                    </div>
                </div>

                {{-- TABLA DE DETALLES --}}
                <div class="overflow-x-auto">
                    <table id="tabla-fraccionamiento" class="min-w-full border border-gray-200 text-sm rounded-lg">
                        <thead
                            class="bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-4 py-3 border text-center w-1/12">N° / Cuota</th>
                                <th class="px-4 py-3 border text-center w-2/12">Fecha Vencimiento</th>
                                <th class="px-4 py-3 border text-right w-2/12">Amortización (S/)</th>
                                <th class="px-4 py-3 border text-right w-2/12">Interés (S/)</th>
                                <th class="px-4 py-3 border text-right w-2/12">Total (S/)</th>
                                <th class="px-4 py-3 border text-center w-2/12">Fecha de Pago</th>
                                <th class="px-4 py-3 border text-center w-1/12">Estado</th>
                                <th class="px-4 py-3 border text-center w-1/12">Pagar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fraccionamiento->detalles as $detalle)
                                <tr style="{{ $detalle->estado === 'pagado' ? 'background-color:#ECFDF5;' : '' }}">
                                    <td class="border px-3 py-2 text-center font-medium text-gray-700">
                                        {{ $detalle->nombre_cuota }}</td>
                                    <td class="border px-3 py-2 text-center">
                                        {{ \Carbon\Carbon::parse($detalle->fecha_vencimiento)->format('d/m/Y') }}</td>
                                    <td class="border px-3 py-2 text-right">
                                        {{ number_format($detalle->amortizacion, 2) }}</td>
                                    <td class="border px-3 py-2 text-right">{{ number_format($detalle->interes, 2) }}
                                    </td>
                                    <td class="border px-3 py-2 text-right">{{ number_format($detalle->total, 2) }}
                                    </td>
                                    <td class="border px-3 py-2 text-center">
                                        {{ $detalle->fecha_pago ? \Carbon\Carbon::parse($detalle->fecha_pago)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="border px-3 py-2 text-center">
                                        <span
                                            class="px-2 py-1 rounded text-xs font-semibold 
                            {{ $detalle->estado === 'pagado' ? 'bg-green-200 text-green-800' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ ucfirst($detalle->estado) }}
                                        </span>
                                    </td>
                                    <td class="border px-3 py-2 text-center">
                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                            @if ($detalle->estado === 'pagado')
                                                <form
                                                    action="{{ route('detalle_fraccionamiento.toggle', $detalle->id_detalle_fraccionamiento) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                                        <x-deactivate-icon />
                                                    </button>
                                                </form>
                                            @else
                                                <form
                                                    action="{{ route('detalle_fraccionamiento.toggle', $detalle->id_detalle_fraccionamiento) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <!-- BOTÓN PAGAR -->
                                                    <button type="button" class="text-green-600 hover:text-green-800"
                                                        onclick="abrirModalPago({{ $detalle->id_detalle_fraccionamiento }})">
                                                        <x-activate-icon />
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            Sin acciones
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-modal-show id="modalPago" title="Registrar Fecha de Pago">
    <form id="formModalPago" method="POST">
        @csrf
        @method('PATCH')

        <label for="fecha_pago" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Pago:</label>
        <input type="date" name="fecha_pago" id="fecha_pago"
            class="w-full border-gray-300 rounded-md mb-4 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
            required>

        <div class="flex justify-end gap-3 mt-4">
            <button type="submit"
                class="px-4 py-2 rounded-md bg-green-500 hover:bg-green-600 text-white font-semibold transition">
                Guardar
            </button>
        </div>
    </form>
</x-modal-show>


<script>
    function abrirModalPago(id) {
        const form = document.getElementById('formModalPago');
        form.action = `/detalle_fraccionamiento/${id}/toggle`;
        toggleModal('modalPago');
    }
</script>



{{-- DATATABLES EXPORTS --}}
<script>
    $(document).ready(function() {
        const table = $('#tabla-fraccionamiento').DataTable({
            dom: 'Bfrtip',
            paging: false,
            ordering: false,
            searching: false,
            info: false,
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                title: function() {
                    const empresa =
                        "{{ $fraccionamiento->empresa->razon_social ?? 'Empresa' }}"
                        .replace(/[^a-zA-Z0-9]/g, "_");
                    const resolucion = "{{ $fraccionamiento->nro_resolucion }}";
                    return `Fraccionamiento_${empresa}_Res_${resolucion}`;
                },
                className: 'hidden',
                exportOptions: {
                    columns: ':not(:last)'
                }
            }],
            language: {
                url: "/js/datatables/lang/es-ES.json"
            }
        });
    });
</script>
