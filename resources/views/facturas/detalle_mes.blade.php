<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Facturas detalladas de {{ $meses_es[$mes] ?? '—' }} {{ $anio }}
        </h2>
    </x-slot>

    <div class="w-full px-4">
        <div class="mb-4 flex flex-wrap gap-4 items-center md:flex-row flex-col">
            <a href="{{ route('guias.index', ['mes' => $mes, 'anio' => $anio]) }}"
                class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white">
                <i class="bi bi-arrow-left-circle"></i> Volver
            </a>
        </div>
        <div class="overflow-x-auto bg-white shadow rounded-lg p-4">
            <table id="myTable-fmensuales" class="min-w-[1200px] table-auto display nowrap">
                <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
                    <tr>
                        <th class="px-4 py-2">EMPRESA QUE FACTURA</th>
                        <th class="px-4 py-2">FECHA</th>
                        <th class="px-4 py-2">RUC</th>
                        <th class="px-4 py-2">RAZON SOCIAL</th>
                        <th class="px-4 py-2">DESCRIPCION</th>
                        <th class="px-4 py-2">UNID MED.</th>
                        <th class="px-4 py-2">CANT</th>
                        <th class="px-4 py-2">PESO UNT</th>
                        <th class="px-4 py-2">PESO TOTAL</th>
                        <th class="px-4 py-2">TOTAL DOC.</th>
                        <th class="px-4 py-2">DOC. ELECTRONICO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($facturas as $factura)
                        @foreach ($factura->detalles as $detalle)
                            <tr>
                                <td class="px-4 py-2">
                                    {{ $detalle->producto->inventario->empresa->razon_social ?? '—' }}
                                </td>

                                <td class="px-4 py-2">
                                    {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}</td>

                                <td class="px-4 py-2">{{ $factura->ruc_receptor }}</td>

                                <td class="px-4 py-2">
                                    {{ $factura->empresa?->razon_social ?? $factura->cliente?->razon_social }}</td>

                                <td class="px-4 py-2">{{ mb_strtoupper($detalle->producto?->nombre) ?? '—' }}</td>

                                <td class="px-4 py-2">
                                    {{ $detalle->unidad_medida ?? ($detalle->producto?->unidad_medida ?? '—') }}</td>

                                <td class="px-4 py-2">{{ $detalle->cantidad }}</td>

                                <td class="px-4 py-2">{{ number_format($detalle->producto?->peso ?? 0, 2) }}</td>

                                <td class="px-4 py-2">{{ number_format($detalle->peso_total, 2) }}</td>

                                <td class="px-4 py-2">S/ {{ number_format($detalle->precio_total, 2) }}</td>

                                <td class="px-4 py-2">{{ $factura->nro_factura ?? '—' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
<script>
    $(document).ready(function() {
        let razonSocial = @json($nombreReceptor);
        let hoy = new Date();
        let fechaHoy = hoy.toLocaleDateString('es-PE').replace(/\//g, '-');
        razonSocial = razonSocial.replace(/[^\w\s]/gi, '').replace(/\s+/g, '_');

        $('#myTable-fmensuales').DataTable({
            responsive: false,
            paging: false,
            ordering: false,
            searching: false,
            info: false,
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excel',
                    text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                    title: `Facturas_Mensual_${razonSocial}_${fechaHoy}`
                },
                {
                    extend: 'pdf',
                    text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                    title: `Facturas_Mensual_${razonSocial}_${fechaHoy}`,
                    orientation: 'landscape',
                    pageSize: 'A4'
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer"></i> Imprimir',
                    title: `Facturas_Mensual_${razonSocial}_${fechaHoy}`
                }
            ],
            language: {
                url: '/js/datatables/lang/es-ES.json'
            }
        });
    });
</script>
