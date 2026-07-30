<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registro de Contratos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                {{-- *Botón Nuevo Contrato (Dropdown) --}}
                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <div class="relative w-full md:w-auto z-50">
                        <x-dropdown align="left" width="60">
                            <x-slot name="trigger">
                                <button
                                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                                    <i class="bi bi-link-45deg"></i> Nuevo Contrato
                                    <i class="bi bi-chevron-down text-sm"></i>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="w-60 bg-white shadow-lg rounded-md border p-2">
                                    <a href="{{ route('contratos.create') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
                                        <i class="bi bi-arrow-right-short me-2"></i> Desde Empresa Origen
                                    </a>
                                    <hr class="my-1 border-gray-200">
                                    <span
                                        class="block px-4 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Hacia
                                        Destinos (Inverso)</span>
                                    <a href="{{ route('contratos.create_destinos', 'empresa') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
                                        <i class="bi bi-building me-2"></i> Para Empresa Destino
                                    </a>
                                    <a href="{{ route('contratos.create_destinos', 'cliente') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
                                        <i class="bi bi-person me-2"></i> Para Cliente Auditoría
                                    </a>
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endif
                <a href="{{ route('contratos.listar') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white w-full md:w-auto">
                    <i class="bi bi-card-list"></i> Listar Contratos
                </a>
                <button type="button" onclick="event.preventDefault(); toggleModal('modalrelacioncontratos')"
                    class="flex items-center justify-center gap-2 bg-purple-500 px-4 py-2 rounded-md hover:bg-purple-600 transition duration-300 text-white w-full md:w-auto">
                    <i class="bi bi-arrow-left-right"></i> Relacion Contratos
                </button>
                <a href="{{ route('contratos.index_destinos') }}"
                    class="flex items-center justify-center gap-2 bg-indigo-500 px-4 py-2 rounded-md hover:bg-indigo-600 transition duration-300 text-white w-full md:w-auto">
                    <i class="bi bi-table"></i> Contratos por Destinos
                </a>
                <button type="button" onclick="event.preventDefault(); openVencimientoModal()"
                    class="flex items-center justify-center gap-2 bg-rose-500 px-4 py-2 rounded-md hover:bg-rose-600 transition duration-300 text-white w-full md:w-auto shadow-sm">
                    <i class="bi bi-calendar-x"></i> Contratos por Vencer
                </button>
                <a href="{{ route('contratos.export', 'activos') }}"
                    class=" bg-green-800 px-4 py-2 rounded-md hover:bg-green-900 transition duration-300 text-white shadow-md">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Descargar Excel
                </a>
            </div>
            <br>
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg p-4">
                <table class="min-w-[1200px] table-auto display nowrap responsive" id="tabla-relaciones">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Empresa Origen</th>
                            <th class="px-4 py-2 border-b">Empresas Destino y Estado Firma</th>
                            <th class="px-4 py-2 border-b">Cliente Auditoria Destino y Estado Firma</th>
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @if (count($agrupado) === 0)
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">
                                    No hay registro de contratos
                                </td>
                            </tr>
                        @else
                            @foreach ($agrupado as $origenId => $grupo)
                                <tr class="hover:bg-gray-50">
                                    {{-- ORIGEN --}}
                                    <td class="px-4 py-2 border-b w-1/4">
                                        <span>{{ $grupo['origen']->razon_social }}</span>
                                        <br>
                                        <span class="text-gray-500 text-xs">{{ $grupo['origen']->ruc }}</span>
                                    </td>
                                    {{-- DESTINOS EMPRESAS --}}
                                    <td class="px-4 py-2 border-b">
                                        @if (count($grupo['destinos']))
                                            <ul class="list-disc ml-6 text-gray-700 space-y-1">
                                                @foreach ($grupo['destinos'] as $dest)
                                                    <li>
                                                        <button type="button"
                                                            class="text-left hover:underline text-blue-600 modal-fecha-btn"
                                                            data-nombre="{{ $dest->razon_social }}"
                                                            data-inicio="{{ $dest->pivot->fecha_inicio ?? ($dest->fecha_inicio ?? '') }}"
                                                            data-fin="{{ $dest->pivot->fecha_fin ?? ($dest->fecha_fin ?? '') }}"
                                                            data-contrato-id="{{ $dest->id_contrato ?? '' }}"
                                                            data-firmado="{{ $dest->firmado ? 1 : 0 }}">
                                                            <span class="text-[13px]">{{ $dest->razon_social }}</span>
                                                            @if ($dest->firmado)
                                                                <i class="bi bi-check-circle-fill text-green-500 ms-1"
                                                                    title="Firmado"></i>
                                                            @else
                                                                <i class="bi bi-x-circle text-red-400 ms-1"
                                                                    title="Pendiente de firma"></i>
                                                            @endif
                                                            <span class="text-gray-500 text-xs"> –
                                                                {{ $dest->ruc }}</span>
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-gray-400 italic">Sin Empresas Destino</span>
                                        @endif
                                    </td>
                                    {{-- DESTINOS CLIENTE AUDITORÍA --}}
                                    <td class="px-4 py-2 border-b">
                                        @if (count($grupo['clientes']))
                                            <ul class="list-disc ml-6 text-gray-700 space-y-1">
                                                @foreach ($grupo['clientes'] as $cliente)
                                                    <li>
                                                        <button type="button"
                                                            class="text-left hover:underline text-green-600 modal-fecha-btn"
                                                            data-nombre="{{ $cliente->razon_social }}"
                                                            data-inicio="{{ $cliente->pivot->fecha_inicio ?? ($cliente->fecha_inicio ?? '') }}"
                                                            data-fin="{{ $cliente->pivot->fecha_fin ?? ($cliente->fecha_fin ?? '') }}"
                                                            data-contrato-id="{{ $cliente->id_contrato ?? '' }}"
                                                            data-firmado="{{ $cliente->firmado ? 1 : 0 }}">
                                                            <span
                                                                class="text-[13px]">{{ $cliente->razon_social }}</span>
                                                            @if ($cliente->firmado)
                                                                <i class="bi bi-check-circle-fill text-green-500 ms-1"
                                                                    title="Firmado"></i>
                                                            @else
                                                                <i class="bi bi-x-circle text-red-400 ms-1"
                                                                    title="Pendiente de firma"></i>
                                                            @endif
                                                            <span class="text-gray-500 text-xs">–
                                                                {{ $cliente->ruc }}</span>
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-gray-400 italic">Sin clientes auditoría</span>
                                        @endif
                                    </td>
                                    {{-- ACCIONES --}}
                                    <td class="px-4 py-2 border-b">
                                        <div class="flex items-center space-x-3">
                                            {{-- Editar --}}
                                            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                                <a href="{{ route('contratos.edit', $origenId) }}"
                                                    class="flex items-center rounded-md tooltip" style="color: #F59E0B;"
                                                    onmouseover="this.style.color='#D97706';"
                                                    onmouseout="this.style.color='#F59E0B';">
                                                    <x-edit-icon />
                                                    <span class="tooltip-text">Editar</span>
                                                </a>
                                            @endif
                                            {{-- Lista --}}
                                            <a href="{{ route('contratos.listarindividual', $origenId) }}"
                                                class="flex items-center rounded-md tooltip" style="color: #2986cc;"
                                                onmouseover="this.style.color='#2ba4ff';"
                                                onmouseout="this.style.color='#2986cc';">
                                                <x-list-icon />
                                                <span class="tooltip-text">lista</span>
                                            </a>
                                            {{-- Eliminar --}}
                                            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                                <form action="{{ route('contratos.desactivar', $origenId) }}"
                                                    method="POST" class="inline-block"
                                                    onsubmit="return confirm('¿Eliminar TODAS las relaciones de esta empresa origen?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="flex items-center rounded-md tooltip"
                                                        style="color:#666666;"
                                                        onmouseover="this.style.color='#999999';"
                                                        onmouseout="this.style.color='#666666';">
                                                        <x-delete-icon />
                                                        <span class="tooltip-text">Desactivar</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<x-modal-show id="modalrelacioncontratos" title="Relación de Contratos" size="small">
    <div class="p-4 space-y-4">
        <label class="block text-sm font-medium text-gray-700">Seleccione un destinatario para ver sus
            contratos:</label>
        <div class="grid grid-cols-1 gap-4">
            <div>
                <select id="select_destinatario" class="w-full px-4 py-2 border rounded-md text-sm">
                    <option value="">-- Seleccione un destinatario --</option>

                    <!-- Grupo de Empresas Destino -->
                    <optgroup label="Empresas Destino">
                        @foreach ($todosDestinos as $dest)
                            <option value="empresa_{{ $dest->id_empresas_clientes }}">{{ $dest->razon_social }}
                            </option>
                        @endforeach
                    </optgroup>

                    <!-- Grupo de Clientes Auditoría -->
                    <optgroup label="Clientes Auditoría">
                        @foreach ($todosClientes as $cli)
                            <option value="cliente_{{ $cli->id_cliente }}">{{ $cli->razon_social }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="flex items-end">
            <button id="btn_buscar_relacion" type="button"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="bi bi-search me-2"></i> Buscar
            </button>
        </div>
    </div>
</x-modal-show>

<x-modal-show id="modalFechas" title="Vigencia del Contrato">
    <div class="space-y-3 text-sm text-gray-700">
        <h3 class="text-lg font-semibold mb-2" id="modalTitulo"></h3>
        <p><strong>Fecha inicio:</strong> <span id="modalInicio"></span></p>
        <p><strong>Fecha fin:</strong> <span id="modalFin"></span></p>
        <p><strong>Tiempo restante:</strong> <span id="modalRestante"></span></p>
        <p><strong>Estado de firma:</strong> <span id="modalFirmadoStatus"></span></p>

        <div class="pt-2">
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                Comentario al desactivar
            </label>
            <textarea id="modalComentario" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"
                placeholder="Opcional..."></textarea>
        </div>

        <input type="hidden" id="modalContratoId">

        <div class="flex justify-end pt-2 gap-2">
            <button id="btnToggleFirmado"
                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                Cargando...
            </button>
            <button id="btnDesactivarContrato"
                class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">
                Desactivar Contrato
            </button>
        </div>
    </div>
</x-modal-show>

<x-modal-show id="modalVencimientoContratos" title="Contratos por Vencer" size="large">
    <div class="p-2">
        <div
            class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
            <div>
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="bi bi-clock-history text-blue-600"></i>
                    Control de Vencimientos
                </h3>
                <p class="text-sm text-gray-500">Visualiza los contratos que están próximos a finalizar.</p>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <label for="select_periodo_vencimiento"
                    class="text-sm font-semibold text-gray-600 whitespace-nowrap">Filtrar:</label>
                <select id="select_periodo_vencimiento" onchange="loadExpiringContracts(this.value)"
                    class="w-full md:w-64 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 shadow-sm">
                    <option value="mes" selected>Contratos por vencer este Mes</option>
                    <option value="semana">Contratos por vencer esta Semana</option>
                </select>
            </div>
        </div>

        <div
            class="flex flex-wrap items-center gap-x-5 gap-y-2 mb-4 px-4 py-2 bg-white rounded-xl border border-gray-100 shadow-sm w-fit">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mr-2">Referencia:</span>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                <span class="text-[11px] font-bold text-gray-600 uppercase">≤ 7 días</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span class="text-[11px] font-bold text-gray-600 uppercase">≤ 15 días</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                <span class="text-[11px] font-bold text-gray-600 uppercase">> 15 días</span>
            </div>
        </div>

        <div id="vencimiento_content" class="space-y-4 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
            <div class="flex flex-col items-center justify-center py-20">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-100 border-t-blue-600 mb-4"></div>
                <p class="text-gray-500 font-medium animate-pulse">Cargando contratos...</p>
            </div>
        </div>
    </div>
</x-modal-show>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<script>
    $('#tabla-relaciones').DataTable({
        responsive: false,
        paging: true,
        ordering: true,
        pageLength: 200,
        lengthMenu: [10, 25, 50, 100, 200],
        columnDefs: [{
            defaultContent: '',
            targets: '_all'
        }],
        buttons: [{
            extend: 'excel',
            text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
            exportOptions: {
                format: {
                    body: function(data, row, column, node) {
                        const exportEl = node.querySelector('.export-data');
                        let text = exportEl ? exportEl.textContent : data;
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

<script>
    let lastClickedBtn = null;

    document.querySelectorAll('.modal-fecha-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            lastClickedBtn = this;
            const nombre = this.dataset.nombre;
            const inicio = this.dataset.inicio;
            const fin = this.dataset.fin;
            const contratoId = this.dataset.contratoId;

            if (!inicio || !fin) {
                alert('Fechas no disponibles.');
                return;
            }

            const fechaInicio = new Date(inicio + 'T00:00:00');
            const fechaFin = new Date(fin + 'T23:59:59');
            const hoy = new Date();

            fechaInicio.setHours(0, 0, 0, 0);
            fechaFin.setHours(0, 0, 0, 0);
            hoy.setHours(0, 0, 0, 0);

            const diffMs = fechaFin - hoy;

            let restante = diffMs <= 0 ?
                'Contrato vencido' :
                `${Math.round(diffMs / (1000 * 60 * 60 * 24))} día(s)`;

            document.getElementById('modalTitulo').textContent = nombre;
            document.getElementById('modalInicio').textContent = fechaInicio.toLocaleDateString();
            document.getElementById('modalFin').textContent = fechaFin.toLocaleDateString();
            document.getElementById('modalRestante').textContent = restante;
            document.getElementById('modalComentario').value = '';
            document.getElementById('modalContratoId').value = contratoId ?? '';

            // Actualizar estado de firma en el modal
            actualizarUIModalFirmado(this.dataset.firmado == '1');

            toggleModal('modalFechas');
        });
    });

    function actualizarUIModalFirmado(isFirmado) {
        const statusSpan = document.getElementById('modalFirmadoStatus');
        const toggleBtn = document.getElementById('btnToggleFirmado');

        if (isFirmado) {
            statusSpan.innerHTML = '<span class="text-green-600 font-bold">FIRMADO</span>';
            toggleBtn.textContent = 'Quitar Firma';
            toggleBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            toggleBtn.classList.add('bg-amber-600', 'hover:bg-amber-700');
        } else {
            statusSpan.innerHTML = '<span class="text-red-600 font-bold">PENDIENTE</span>';
            toggleBtn.textContent = 'Marcar como Firmado';
            toggleBtn.classList.remove('bg-amber-600', 'hover:bg-amber-700');
            toggleBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }
    }
</script>

<script>
    document.getElementById('btn_buscar_relacion').addEventListener('click', function() {
        const destinatario = document.getElementById('select_destinatario').value;
        if (!destinatario) {
            alert('Por favor, seleccione un destinatario.');
            return;
        }
        const [type, id] = destinatario.split('_');
        if (type && id) {
            if (type === 'empresa') {
                window.location.href = `{{ url('contratos/relacion/empresa') }}/${id}`;
            } else if (type === 'cliente') {
                window.location.href = `{{ url('contratos/relacion/cliente') }}/${id}`;
            } else {
                alert('Por favor, seleccione un destinatario válido.');
            }
        } else {
            alert('Por favor, seleccione un destinatario válido.');
        }
    });
</script>

<script>
    document.getElementById('btnDesactivarContrato').addEventListener('click', function() {

        const id = document.getElementById('modalContratoId').value;
        const comentario = document.getElementById('modalComentario').value.trim();

        if (!id) {
            alert('No se encontró el contrato.');
            return;
        }

        if (!confirm('¿Desactivar este contrato?')) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = 'Desactivando...';

        fetch(`/contratos/${id}/desactivar-comentario`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content')
                },
                body: JSON.stringify({
                    comentario
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    toggleModal('modalFechas');
                    location.reload();
                } else {
                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast(res.message || 'Error al desactivar el contrato.', 'error');
                    } else {
                        alert(res.message || 'Error.');
                    }
                }
            })
            .catch(() => {
                if (typeof showAjaxToast === 'function') {
                    showAjaxToast('Error de conexión.', 'error');
                } else {
                    alert('Error de conexión.');
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Desactivar Contrato';
            });
    });

    document.getElementById('btnToggleFirmado').addEventListener('click', function() {
        const id = document.getElementById('modalContratoId').value;
        if (!id) return;

        const btn = this;
        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = 'Procesando...';

        fetch(`/contratos/${id}/toggle-firmado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content')
                }
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    // Actualizar el botón que abrió el modal
                    if (lastClickedBtn) {
                        const nuevoEstado = res.firmado;
                        lastClickedBtn.dataset.firmado = nuevoEstado ? '1' : '0';

                        // Actualizar el icono en la tabla
                        const iconContainer = lastClickedBtn.parentElement.querySelector('i.bi');
                        if (iconContainer) {
                            if (nuevoEstado) {
                                iconContainer.className = 'bi bi-check-circle-fill text-green-500 ms-1';
                                iconContainer.title = 'Firmado';
                            } else {
                                iconContainer.className = 'bi bi-x-circle text-red-400 ms-1';
                                iconContainer.title = 'Pendiente de firma';
                            }
                        }

                        // Actualizar el modal sin cerrarlo
                        actualizarUIModalFirmado(nuevoEstado);
                    }
                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast(res.message || 'Estado de firma actualizado correctamente.', 'edit');
                    }
                } else {
                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast(res.message || 'Error al cambiar estado de firma.', 'error');
                    } else {
                        alert(res.message || 'Error.');
                    }
                }
            })
            .catch(() => {
                if (typeof showAjaxToast === 'function') {
                    showAjaxToast('Error de conexión.', 'error');
                } else {
                    alert('Error de conexión.');
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = originalText;
            });
    });

    function loadExpiringContracts(period = 'mes') {
        const container = document.getElementById('vencimiento_content');

        fetch(`{{ route('contratos.vencimiento.ajax') }}?period=${period}`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    container.innerHTML = `
                    <div class="text-center py-16 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                        <div class="bg-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                            <i class="bi bi-calendar-check text-4xl text-green-400"></i>
                        </div>
                        <p class="text-gray-600 font-bold text-lg">¡Todo al día!</p>
                        <p class="text-gray-400">No hay contratos por vencer en este periodo.</p>
                    </div>
                `;
                    return;
                }

                let html = '<div class="grid grid-cols-1 gap-4">';
                data.forEach(item => {
                    let colorClass = 'bg-white border-blue-100 hover:border-blue-300';
                    let accentColor = 'bg-blue-500';
                    let textColor = 'text-blue-700';
                    let iconClass = 'bi-info-circle';
                    let daysText = `${item.dias_restantes} días`;
                    let badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300 border border-transparent dark:border-blue-900/50';

                    if (item.dias_restantes <= 7) {
                        colorClass = 'bg-red-50 border-red-200 hover:border-red-300';
                        accentColor = 'bg-red-600';
                        textColor = 'text-red-700';
                        iconClass = 'bi-exclamation-triangle-fill';
                        badgeClass = 'bg-red-600 text-white dark:bg-red-950/40 dark:text-red-300 border border-transparent dark:border-red-900/50';
                    } else if (item.dias_restantes <= 15) {
                        colorClass = 'bg-amber-50 border-amber-200 hover:border-amber-300';
                        accentColor = 'bg-amber-500';
                        textColor = 'text-amber-700';
                        iconClass = 'bi-exclamation-circle-fill';
                        badgeClass = 'bg-amber-500 text-white dark:bg-amber-950/40 dark:text-amber-300 border border-transparent dark:border-amber-900/50';
                    }

                    if (item.dias_restantes < 0) {
                        daysText = `Venció hace ${Math.abs(item.dias_restantes)} días`;
                    } else if (item.dias_restantes === 0) {
                        daysText = 'Vence hoy';
                    }

                    html += `
                    <div class="group relative flex flex-col md:flex-row md:items-center justify-between p-5 rounded-2xl border-2 ${colorClass} transition-all duration-300 hover:shadow-lg overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 ${accentColor}"></div>
                        
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-white shadow-sm border border-gray-100">
                                <i class="bi ${iconClass} text-2xl ${textColor}"></i>
                            </div>
                            <div>
                                <h4 class="font-black text-gray-800 text-lg leading-tight group-hover:text-blue-600 transition-colors">${item.destino}</h4>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-tighter">Origen:</span>
                                    <span class="text-sm font-semibold text-gray-700">${item.origen}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 md:mt-0 flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center gap-2">
                            <div class="px-4 py-1.5 rounded-full text-sm font-black uppercase tracking-widest ${badgeClass} shadow-sm">
                                ${daysText}
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-gray-500 bg-gray-100 px-3 py-1 rounded-lg">
                                    <i class="bi bi-calendar3"></i>
                                    ${item.fecha_fin}
                                </div>
                                <button type="button" 
                                    onclick="openDeactivateFromVencimiento('${item.id}', '${item.destino.replace(/'/g, "\\'")}', '${item.raw_fecha_inicio}', '${item.raw_fecha_fin}', '${daysText}')"
                                    class="bg-red-100 hover:bg-red-200 text-red-600 p-1.5 rounded-lg transition-colors tooltip-custom"
                                    title="Desactivar Contrato">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                });
                html += '</div>';
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                container.innerHTML = `
                <div class="text-center py-16 bg-red-50 rounded-2xl border-2 border-dashed border-red-200">
                    <div class="bg-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <i class="bi bi-bug text-4xl text-red-500"></i>
                    </div>
                    <p class="text-red-600 font-bold text-lg">¡Ups! Algo salió mal</p>
                    <p class="text-red-400">No pudimos cargar la información en este momento.</p>
                </div>
            `;
            });
    }

    function openDeactivateFromVencimiento(id, nombre, inicio, fin, restante) {
        // Cerrar modal actual
        toggleModal('modalVencimientoContratos');

        // Preparar modal de desactivación
        document.getElementById('modalTitulo').textContent = nombre;
        document.getElementById('modalInicio').textContent = new Date(inicio + 'T00:00:00').toLocaleDateString();
        document.getElementById('modalFin').textContent = new Date(fin + 'T23:59:59').toLocaleDateString();
        document.getElementById('modalRestante').textContent = restante;
        document.getElementById('modalComentario').value = '';
        document.getElementById('modalContratoId').value = id;

        // Abrir modal de desactivación (pequeño retraso para que la animación fluya)
        setTimeout(() => {
            toggleModal('modalFechas');
        }, 300);
    }

    function openVencimientoModal() {
        toggleModal('modalVencimientoContratos');
        loadExpiringContracts('mes');
    }
</script>
