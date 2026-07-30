<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pólizas SCTR — Recursos Humanos
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('polizas.index') }}"
                    class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white">
                    <i class="bi bi-arrow-left-circle"></i> Atrás
                </a>

                <a href="{{ route('polizas.vida.index') }}"
                    class="flex items-center gap-2 bg-indigo-500 px-4 py-2 rounded-md hover:bg-indigo-600 transition text-white text-center w-full md:w-auto">
                    <i class="bi bi-heart-pulse-fill"></i> Ver Pólizas Vida Ley
                </a>
            </div>

            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table id="tabla-sctr" class="min-w-full table-auto text-sm">
                    <thead class="bg-gray-100 text-gray-700 text-left">
                        <tr>
                            <th class="px-4 py-2 border-b">UD</th>
                            <th class="px-4 py-2 border-b">Empresa</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">Broker</th>
                            <th class="px-4 py-2 border-b">Aseguradora</th>
                            <th class="px-4 py-2 border-b">Inicio</th>
                            <th class="px-4 py-2 border-b">Fin</th>
                            <th class="px-4 py-2 border-b">Días Restantes</th>
                            <th class="px-4 py-2 border-b">Observaciones</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($polizas as $p)
                            @php
                                $dias = $p->dias_restantes;
                                $color =
                                    $dias === null
                                        ? 'text-gray-400'
                                        : ($dias < 0
                                            ? 'text-red-600'
                                            : ($dias <= 10
                                                ? 'text-amber-500'
                                                : 'text-green-700'));
                                $texto =
                                    $dias === null
                                        ? '-'
                                        : ($dias < 0
                                            ? abs($dias) . ' día' . (abs($dias) === 1 ? '' : 's') . ' vencido'
                                            : $dias .
                                                ' día' .
                                                ($dias === 1 ? '' : 's') .
                                                ' restante' .
                                                ($dias !== 1 ? 's' : ''));
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ substr($p->poliza->empresa->ruc, -1) }}</td>
                                <td class="px-4 py-2 border-b">{{ $p->poliza->empresa->razon_social }}</td>
                                <td class="px-4 py-2 border-b">{{ $p->poliza->empresa->ruc }}</td>
                                <td class="px-4 py-2 border-b">{{ $p->broker ?? '-' }}</td>
                                <td class="px-4 py-2 border-b">{{ $p->aseguradora ?? '-' }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ $p->fecha_inicio ? \Carbon\Carbon::parse($p->fecha_inicio)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $p->fecha_fin ? \Carbon\Carbon::parse($p->fecha_fin)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-2 border-b {{ $color }}">{{ $texto }}</td>
                                <td class="px-4 py-2 border-b ">
                                    {{ $p->observacion ? Str::limit($p->observacion, 20) : '—' }}</td>
                                @php
                                    $estado = strtoupper(trim($p->estado));
                                    // Si hay fechas (días no es null), mostrar VIGENTE/VENCIDO
                                    if ($dias !== null) {
                                        $estado = $dias < 0 ? 'VENCIDO' : 'VIGENTE';
                                    }

                                    $badgeClass = match ($estado) {
                                        'VIGENTE' => 'bg-green-100 text-green-800 border border-green-300',
                                        'VENCIDO' => 'bg-red-100 text-red-800 border border-red-300',
                                        'COTIZAR' => 'bg-gray-800 text-white',
                                        'COTIZ. ENVIADA'
                                            => 'bg-orange-100 text-orange-800 border border-orange-300 rounded-full px-3 py-2 whitespace-nowrap',
                                        'COTIZ. RECIBIDA'
                                            => 'bg-blue-100 text-blue-800 border border-blue-300 rounded-full px-3 py-2 whitespace-nowrap',
                                        'BAJO RIESGO'
                                            => 'bg-red-100 text-red-800 border border-red-300 rounded-full px-3 py-2 whitespace-nowrap',
                                        default => 'bg-gray-100 text-gray-600 border border-gray-300 ',
                                    };
                                @endphp
                                <td class="px-4 py-2 border-b text-center">
                                    <span
                                        class="{{ $estado === 'COTIZ. ENVIADA' || $estado === 'COTIZ. RECIBIDA' ? 'px-2 py-1 rounded text-xs' : 'px-2.5 py-1.5 rounded-full text-xs' }} font-semibold uppercase tracking-wide {{ $badgeClass }}">
                                        {{ $estado }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 border-b text-center">
                                    <a href="javascript:void(0)"
                                        onclick="verDetallePoliza({{ $p->id_poliza }}, 'sctr')"
                                        class="flex items-center py-1 rounded-md tooltip"
                                        style="color: #6B7280; transition: color 0.3s;"
                                        onmouseover="this.style.color='#4B5563';"
                                        onmouseout="this.style.color='#6B7280';">
                                        <span class="tooltip-text">Detalles</span>
                                        <x-details-icon />
                                    </a>

                                    <a href="{{ route('polizas.edit', ['id_poliza' => $p->id_poliza, 'tipo' => 'sctr']) }}"
                                        class="flex items-center py-1 rounded-md tooltip"
                                        style="color: #F59E0B; transition: color 0.3s;"
                                        onmouseover="this.style.color='#D97706';"
                                        onmouseout="this.style.color='#F59E0B';">
                                        <span class="tooltip-text">Editar</span>
                                        <x-edit-icon />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
{{-- MODAL DETALLES (AJAX) --}}
<x-modal-show id="modal-detalle-poliza" title="Detalles de la póliza">
    <div id="detalle-poliza-contenido" class="space-y-4 text-sm text-gray-700">
        <p class="text-center text-gray-400 italic">Cargando información...</p>
    </div>
</x-modal-show>

<script>
    function verDetallePoliza(idPoliza, tipo = 'vida') {
        const cont = document.getElementById('detalle-poliza-contenido');
        const titulo = document.querySelector('#modal-detalle-poliza h2');
        cont.innerHTML = `<p class="text-center text-gray-400 italic">Cargando información...</p>`;
        titulo.textContent = tipo === 'vida' ?
            'Detalles de la Póliza Vida Ley' :
            'Detalles de la Póliza SCTR';

        toggleModal('modal-detalle-poliza');

        fetch(`/polizas/${idPoliza}/detalle`)
            .then(res => res.json())
            .then(data => {
                const poliza = data[tipo];
                let html = `
                    <div class="border-b pb-2 mb-3">
                        <strong>Empresa:</strong> ${data.empresa.razon_social}<br>
                        <strong>RUC:</strong> ${data.empresa.ruc}<br>
                        <strong>Estado general:</strong> ${data.estado}
                    </div>
                `;

                if (!poliza) {
                    html +=
                        `<p class="text-center text-gray-400 italic">No se encontraron datos de esta póliza.</p>`;
                } else {
                    const diasRestantes = poliza.dias_restantes;

                    // --- Estado visible en el modal (VIGENTE / VENCIDO / COTIZAR...) ---
                    let estadoVisible = poliza.estado ? poliza.estado.toUpperCase().trim() : '-';

                    const fechaInicioValida = poliza.fecha_inicio && poliza.fecha_inicio !== "-" && poliza
                        .fecha_inicio.trim() !== "";
                    const fechaFinValida = poliza.fecha_fin && poliza.fecha_fin !== "-" && poliza.fecha_fin
                        .trim() !== "";

                    const tieneFechas = fechaInicioValida && fechaFinValida;


                    if (tieneFechas && diasRestantes !== null && diasRestantes !== undefined) {
                        estadoVisible = diasRestantes < 0 ? 'VENCIDO' : 'VIGENTE';
                    }

                    const color = diasRestantes < 0 ? 'text-red-600' : 'text-green-700';
                    const textoDias = diasRestantes < 0 ?
                        `${Math.abs(diasRestantes)} día(s) vencido` :
                        `${diasRestantes} día(s) restante(s)`;

                    html += `
                        <table class="w-full border text-sm">
                            <tr><td class="font-semibold px-2 py-1 border w-1/3">Aseguradora:</td><td class="px-2 py-1 border">${poliza.aseguradora}</td></tr>
                            <tr><td class="font-semibold px-2 py-1 border">Broker:</td><td class="px-2 py-1 border">${poliza.broker}</td></tr>
                            <tr><td class="font-semibold px-2 py-1 border">N° Póliza:</td><td class="px-2 py-1 border">${poliza.nro_poliza ?? '-'}</td></tr>
                            <tr><td class="font-semibold px-2 py-1 border">Inicio:</td><td class="px-2 py-1 border">${poliza.fecha_inicio}</td></tr>
                            <tr><td class="font-semibold px-2 py-1 border">Fin:</td><td class="px-2 py-1 border">${poliza.fecha_fin}</td></tr>
                            <tr>
                                <td class="font-semibold px-2 py-1 border">Estado:</td>
                                <td class="px-2 py-1 border">${estadoVisible}</td>
                            </tr>
                            <tr><td class="font-semibold px-2 py-1 border">Días restantes:</td><td class="px-2 py-1 border ${color}">${textoDias}</td></tr>
                            <tr><td class="font-semibold px-2 py-1 border">Observaciones:</td><td class="px-2 py-1 border">${poliza.observacion}</td></tr>
                            <tr><td class="font-semibold px-2 py-1 border">Última edición:</td><td class="px-2 py-1 border">${poliza.usuario}</td></tr>
                        </table>
                        <div class="flex justify-end gap-2 mt-4">
                            <x-secondary-button onclick="toggleModal('modal-detalle-poliza')">Cerrar</x-secondary-button>
                            <x-primary-button onclick="window.location.href='/polizas/${poliza.id_poliza}/edit?tipo=${tipo}'">Editar</x-primary-button>
                        </div>
                    `;
                }

                cont.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                cont.innerHTML = `<p class="text-center text-red-500">Error al cargar los detalles.</p>`;
            });
    }
</script>


<script>
    $(document).ready(() => {
        $('#tabla-sctr').DataTable({
            language: {
                url: "/js/datatables/lang/es-ES.json"
            },
            pageLength: 50
        });
    });
</script>
