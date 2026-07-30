<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Registro de Contratos (Por Destino)') }}
            </h2>
            <a href="{{ route('contratos.index') }}"
                class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white text-sm">
                <i class="bi bi-arrow-left"></i> Volver a Contratos Normales
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                {{-- Botón Nuevo Contrato (Dropdown) --}}
                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                <div class="relative w-full md:w-auto z-50">
                    <x-dropdown align="left" width="60">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                                <i class="bi bi-link-45deg"></i> Nuevo Contrato por Destino
                                <i class="bi bi-chevron-down text-sm"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="w-60 bg-white shadow-lg rounded-md border p-2">
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
            </div>
            <br>
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg p-4">
                <table class="min-w-[1200px] table-auto display nowrap responsive" id="tabla-relaciones">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Empresa o Cliente Destino</th>
                            <th class="px-4 py-2 border-b">Tipo</th>
                            <th class="px-4 py-2 border-b">Empresas Origen y Estado Firma</th>
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @if (count($agrupado) === 0)
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">
                                    No hay registro de contratos por destino
                                </td>
                            </tr>
                        @else
                            @foreach ($agrupado as $key => $grupo)
                                <tr class="hover:bg-gray-50">
                                    {{-- DESTINO --}}
                                    <td class="px-4 py-2 border-b w-1/3">
                                        <span class="font-bold">{{ $grupo['destino']->razon_social }}</span>
                                        <br>
                                        <span class="text-gray-500 text-xs">{{ $grupo['destino']->ruc }}</span>
                                    </td>

                                    {{-- TIPO --}}
                                    <td class="px-4 py-2 border-b">
                                        @if($grupo['tipo'] === 'empresa')
                                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-blue-400">
                                                Empresa Destino
                                            </span>
                                        @else
                                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-green-400">
                                                Cliente Auditoría
                                            </span>
                                        @endif
                                    </td>
                                    
                                    {{-- ORIGENES --}}
                                    <td class="px-4 py-2 border-b">
                                        @if (count($grupo['origenes']))
                                            <ul class="list-disc ml-6 text-gray-700 space-y-1">
                                                @foreach ($grupo['origenes'] as $origen)
                                                    <li>
                                                        <button type="button"
                                                            class="text-left hover:underline text-blue-600 modal-fecha-btn"
                                                            data-nombre="{{ $origen->razon_social }}"
                                                            data-inicio="{{ $origen->fecha_inicio ?? '' }}"
                                                            data-fin="{{ $origen->fecha_fin ?? '' }}"
                                                            data-contrato-id="{{ $origen->id_contrato ?? '' }}"
                                                            data-firmado="{{ $origen->firmado ? 1 : 0 }}">
                                                            <span class="text-[13px]">{{ $origen->razon_social }}</span>
                                                            @if($origen->firmado)
                                                                <i class="bi bi-check-circle-fill text-green-500 ms-1" title="Firmado"></i>
                                                            @else
                                                                <i class="bi bi-x-circle text-red-400 ms-1" title="Pendiente de firma"></i>
                                                            @endif
                                                            <span class="text-gray-500 text-xs"> –
                                                                {{ $origen->ruc }}</span>
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-gray-400 italic">Sin Empresas Origen</span>
                                        @endif
                                    </td>
                                    
                                    {{-- ACCIONES --}}
                                    <td class="px-4 py-2 border-b">
                                        <div class="flex items-center space-x-3 justify-center">
                                            {{-- Editar --}}
                                            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                                <a href="{{ route('contratos.edit_destinos', ['tipo' => $grupo['tipo'], 'id' => $grupo['tipo'] === 'empresa' ? $grupo['destino']->id_empresas_clientes : $grupo['destino']->id_cliente]) }}"
                                                    class="flex items-center rounded-md tooltip" style="color: #F59E0B;"
                                                    onmouseover="this.style.color='#D97706';"
                                                    onmouseout="this.style.color='#F59E0B';">
                                                    <x-edit-icon />
                                                    <span class="tooltip-text">Editar</span>
                                                </a>
                                            @endif
                                            
                                            {{-- Eliminar --}}
                                            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                                <form action="{{ route('contratos.desactivar_destinos', ['tipo' => $grupo['tipo'], 'id' => $grupo['tipo'] === 'empresa' ? $grupo['destino']->id_empresas_clientes : $grupo['destino']->id_cliente]) }}"
                                                    method="POST" class="inline-block"
                                                    onsubmit="return confirm('¿Eliminar TODAS las relaciones de este destino?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="flex items-center rounded-md tooltip"
                                                        style="color:#666666;" onmouseover="this.style.color='#999999';"
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
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ comentario })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            toggleModal('modalFechas');
            location.reload();
        } else {
            alert(res.message || 'Error.');
        }
    })
    .catch(() => alert('Error de conexión.'))
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
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
        } else {
            alert(res.message || 'Error.');
        }
    })
    .catch(() => alert('Error de conexión.'))
    .finally(() => {
        btn.disabled = false;
        btn.textContent = originalText;
    });
});
</script>
