<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Control de Pólizas — Recursos Humanos
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('polizas.vida.index') }}"
                    class="flex items-center gap-2 bg-indigo-500 px-4 py-2 rounded-md hover:bg-indigo-600 transition text-white text-center w-full md:w-auto">
                    <i class="bi bi-heart-pulse-fill"></i> Ver Pólizas Vida Ley
                </a>

                <a href="{{ route('polizas.sctr.index') }}"
                    class="flex items-center gap-2 bg-orange-500 px-4 py-2 rounded-md hover:bg-orange-600 transition text-white text-center w-full md:w-auto">
                    <i class="bi bi-shield-fill-check"></i> Ver Pólizas SCTR
                </a>

                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <a href="{{ route('empresas_clientes.gestion_planilla') }}"
                        class="flex items-center gap-2 bg-indigo-500 px-4 py-2 rounded-md hover:bg-indigo-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-clipboard-data"></i> Gestión de Planilla
                    </a>
                @endif
            </div>

            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table id="tabla-polizas" class="min-w-full table-auto text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-left text-gray-700">
                            <th class="px-4 py-2 border-b">UD</th>
                            <th class="px-4 py-2 border-b">Razón Social</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b text-center">Póliza Vida</th>
                            <th class="px-4 py-2 border-b text-center">Póliza SCTR</th>
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $i => $empresa)
                            @php
                                $tienePoliza = $empresa->polizas && $empresa->polizas->first();
                                $poliza = $empresa->polizas->first();
                                $tieneVida = $poliza && $poliza->vida;
                                $tieneSctr = $poliza && $poliza->sctr;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ substr($empresa->ruc, -1) }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if (!$tienePoliza)
                                        <a href="javascript:void(0)" class="empresa-link text-blue-600 hover:underline"
                                            onclick="abrirModalPoliza({{ $empresa->id_empresas_clientes }}, '{{ addslashes($empresa->razon_social) }}')">
                                            {{ $empresa->razon_social }}
                                        </a>
                                    @else
                                        <span class="text-gray-700">{{ $empresa->razon_social }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">{{ $empresa->ruc }}</td>

                                {{-- Columna VIDA --}}
                                <td class="px-4 py-2 border-b text-center">
                                    @if ($tieneVida)
                                        <button type="button"
                                            onclick="verDetallePoliza({{ $poliza->id_poliza }}, 'vida')"
                                            class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-xs rounded-md hover:bg-green-200 transition">
                                            Ver
                                        </button>
                                    @else
                                        @if ($tienePoliza)
                                            <a href="{{ route('polizas.create', ['id_poliza' => $poliza->id_poliza, 'tipos' => 'vida']) }}"
                                                class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-600 text-xs rounded-md hover:bg-blue-200 transition">
                                                + Agregar
                                            </a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Columna SCTR --}}
                                <td class="px-4 py-2 border-b text-center">
                                    @if ($tieneSctr)
                                        <button type="button"
                                            onclick="verDetallePoliza({{ $poliza->id_poliza }}, 'sctr')"
                                            class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-xs rounded-md hover:bg-green-200 transition">
                                            Ver
                                        </button>
                                    @else
                                        @if ($tienePoliza)
                                            <a href="{{ route('polizas.create', ['id_poliza' => $poliza->id_poliza, 'tipos' => 'sctr']) }}"
                                                class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-600 text-xs rounded-md hover:bg-blue-200 transition">
                                                + Agregar
                                            </a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                <td class="px-4 py-2 border-b text-center">
                                    @if ($tienePoliza)
                                        <form method="POST"
                                            action="{{ route('polizas.toggle', $poliza->id_poliza) }}">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="px-3 py-1 text-xs rounded-md transition font-semibold
                    {{ $poliza->estado ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-600 hover:bg-red-200' }}">
                                                {{ $poliza->estado ? 'Activo' : 'Inactivo' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- !AJAX --}}
<x-modal-show id="modal-detalle-poliza" title="Detalles de la póliza">
    <div id="detalle-poliza-contenido" class="space-y-4 text-sm text-gray-700">
        <p class="text-center text-gray-400 italic">Cargando información...</p>
    </div>
</x-modal-show>

<script>
    function verDetallePoliza(idPoliza, tipo) {
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
                let html = `
                    <div class="border-b pb-2 mb-3">
                        <strong>Empresa:</strong> ${data.empresa.razon_social}<br>
                        <strong>RUC:</strong> ${data.empresa.ruc}<br>
                        <strong>Estado general:</strong> ${data.estado}
                    </div>
                `;

                const poliza = data[tipo];
                if (!poliza) {
                    html +=
                        `<p class="text-center text-gray-400 italic">No se encontraron datos de esta póliza.</p>`;
                } else {
                    const diasRestantes = poliza.dias_restantes;

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
                        `${diasRestantes} día(s) restantes`;

                    html += `
                        <table class="w-full border text-sm">
                            <tr>
                                <td class="font-semibold px-2 py-1 border w-1/3">Aseguradora:</td>
                                <td class="px-2 py-1 border">${poliza.aseguradora ?? '-'}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold px-2 py-1 border">Broker:</td>
                                <td class="px-2 py-1 border">${poliza.broker}</td>
                            </tr>
                    `;

                    if (tipo === 'vida' && poliza.nro_poliza) {
                        html += `
                            <tr>
                                <td class="font-semibold px-2 py-1 border">N° Póliza:</td>
                                <td class="px-2 py-1 border">${poliza.nro_poliza}</td>
                            </tr>
                        `;
                    }

                    html += `
                            <tr>
                                <td class="font-semibold px-2 py-1 border">Inicio:</td>
                                <td class="px-2 py-1 border">${poliza.fecha_inicio ?? '-'}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold px-2 py-1 border">Fin:</td>
                                <td class="px-2 py-1 border">${poliza.fecha_fin ?? '-'}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold px-2 py-1 border">Estado:</td>
                                <td class="px-2 py-1 border">${estadoVisible}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold px-2 py-1 border">Días restantes:</td>
                                <td class="px-2 py-1 border ${color}">${textoDias}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold px-2 py-1 border">Observaciones:</td>
                                <td class="px-2 py-1 border">${poliza.observacion ?? '-'}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold px-2 py-1 border">Última edición:</td>
                                <td class="px-2 py-1 border">${poliza.usuario ?? '-'}</td>
                            </tr>
                        </table>
                        <div class="flex justify-end gap-2 mt-4">
                            <button
                                onclick="toggleModal('modal-detalle-poliza')"
                                class="px-4 py-2 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                                Cerrar
                            </button>
                            <button
                                onclick="window.location.href='/polizas/${poliza.id_poliza}/edit?tipo=${tipo}'"
                                class="px-4 py-2 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition">
                                Editar
                            </button>
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
{{-- !FIN AJAX --}}

{{-- MODAL: CREAR PÓLIZA --}}
<x-modal-show id="modal-poliza" title="Registrar nueva póliza">
    <form method="POST" action="{{ route('polizas.store') }}" id="form-poliza-create">
        @csrf
        <input type="hidden" name="id_empresa_cliente" id="empresa-id">

        <div class="space-y-4">
            <div>
                <x-input-label :value="__('Empresa')" />
                <x-text-input id="empresa-nombre" type="text" class="mt-1 block w-full bg-gray-200" disabled />
            </div>

            <div class="hidden">
                <x-input-label :value="__('Broker')" />
                <x-text-input name="broker" type="text" class="mt-1 block w-full" placeholder="Nombre del broker" />
            </div>

            <div>
                <x-input-label :value="__('Tipos de póliza a crear')" />
                <div class="flex flex-col gap-2 mt-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="tipos[]" value="vida" class="rounded text-blue-500"> Vida Ley
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="tipos[]" value="sctr" class="rounded text-blue-500"> SCTR
                    </label>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <x-primary-button>Continuar</x-primary-button>
            </div>
        </div>
    </form>
</x-modal-show>

<script>
    function abrirModalPoliza(id, nombre) {
        document.getElementById('empresa-id').value = id;
        document.getElementById('empresa-nombre').value = nombre;

        // reset checkboxes
        document.querySelectorAll('#modal-poliza input[type=checkbox]').forEach(chk => {
            chk.checked = false;
            chk.disabled = false;
            chk.parentElement.style.opacity = 1;
        });

        toggleModal('modal-poliza');
    }
</script>

<script>
    $(document).ready(function() {
        $('#tabla-polizas').DataTable({
            language: {
                url: "/js/datatables/lang/es-ES.json"
            },
            pageLength: 100
        });
    });
</script>
