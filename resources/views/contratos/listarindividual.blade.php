<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col space-y-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight"> Contratos de {{ $empresa->razon_social }}
                <span class="text-sm text-blue-500">{{ $empresa->ruc }}</span>
            </h2>
            <a href="{{ route('contratos.listar') }}"
                class="inline-flex items-center gap-2 bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded transition w-fit">
                <i class="bi bi-arrow-left"></i>
                Atrás
            </a>

        </div>
    </x-slot>


    <div x-data="{ tab: 'activos' }" class="space-y-6">
        {{-- Pestañas --}}
        <div class="flex border-b border-gray-200 mb-4">
            <button class="px-4 py-2 font-medium text-gray-700 focus:outline-none"
                :class="tab === 'activos' ? 'border-b-2 border-green-500 text-green-600' : ''"
                @click="tab = 'activos'">
                Contratos Activos
            </button>
            <button class="px-4 py-2 font-medium text-gray-700 focus:outline-none"
                :class="tab === 'inactivos' ? 'border-b-2 border-red-500 text-red-600' : ''" @click="tab = 'inactivos'">
                Contratos Inactivos
            </button>
        </div>

        {{-- ================= TAB ACTIVO ================= --}}
        <div x-show="tab === 'activos'" x-transition>
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg p-4">
                <table class="min-w-full table-auto display nowrap responsive" id="tabla-activos-inactivos">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Tipo</th>
                            <th class="px-4 py-2 border-b">Destino</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">Fecha Inicio</th>
                            <th class="px-4 py-2 border-b">Fecha Fin</th>
                            <th class="px-4 py-2 border-b text-center">Firmado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contratosActivos as $c)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">
                                    {{ $c->empresaDestino ? 'Empresa Destino' : 'Cliente Auditoría' }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $c->empresaDestino->razon_social ?? ($c->clienteAuditoriaDestino->razon_social ?? '-') }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $c->empresaDestino->ruc ?? ($c->clienteAuditoriaDestino->ruc ?? '-') }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($c->fecha_inicio)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($c->fecha_fin)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 border-b text-center">
                                    @if ($c->firmado)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">SI</span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-full">NO</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-gray-500 italic">
                                    No hay contratos activos
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================= TAB INACTIVO ================= --}}
        <div x-show="tab === 'inactivos'" x-transition>
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg p-4">
                <table class="min-w-full table-auto display nowrap responsive" id="tabla-activos-inactivos">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Tipo</th>
                            <th class="px-4 py-2 border-b">Destino</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">Fecha Inicio</th>
                            <th class="px-4 py-2 border-b">Fecha Fin</th>
                            <th class="px-4 py-2 border-b text-center">Firmado</th>
                            <th class="px-4 py-2 border-b">Fecha Cancelacion</th>
                            <th class="px-4 py-2 border-b">Comentario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contratosInactivos as $c)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">
                                    {{ $c->empresaDestino ? 'Empresa Destino' : 'Cliente Auditoría' }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $c->empresaDestino->razon_social ?? ($c->clienteAuditoriaDestino->razon_social ?? '-') }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $c->empresaDestino->ruc ?? ($c->clienteAuditoriaDestino->ruc ?? '-') }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($c->fecha_inicio)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($c->fecha_fin)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 border-b text-center">
                                    @if ($c->firmado)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">SI</span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-full">NO</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($c->fecha_cancelacion)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $c->comentario ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-center text-gray-500 italic">
                                    No hay contratos inactivos
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>


<script>
    $('#tabla-activos-inactivos').DataTable({
        responsive: false,
        paging: true,
        ordering: true,
        pageLength: 200,
        lengthMenu: [10, 25, 50, 100, 200],
        dom: 'Bfrtip',
        buttons: [{
            extend: 'excel',
            text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
            title: tituloArchivo,
            exportOptions: {
                //columns: ':not(:last)',
                format: {
                    body: function(data, row, column, node) {
                        // Si hay texto oculto .export-data, úsalo
                        const exportEl = node.querySelector('.export-data');
                        let text = exportEl ? exportEl.textContent : data;
                        // Eliminar etiquetas HTML residuales
                        return text.replace(/<[^>]*>?/gm, '').trim();
                    }
                }
            }
        }],
        language: {
            url: "/js/datatables/lang/es-ES.json"
        }
    });
</script>
