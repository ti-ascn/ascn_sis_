<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Cuentas de {{ $empresa->razon_social }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            {{-- Botones --}}
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col mb-6">
                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <a href="{{ route('cuentas_empresas.create', $empresa->id_empresas_clientes) }}"
                        class="flex items-center gap-2 bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition text-white">
                        <i class="bi bi-bank"></i> Nueva Cuenta
                    </a>
                @endif
                @if (Auth::user()->area->nombre != 'LEGAL')
                    <a href="{{ route('empresas_clientes_auditoria.all') }}"
                        class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                        <i class="bi bi-building"></i> Empresas
                    </a>
                    <a href="{{ route('clientes.index') }}"
                        class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-person-lines-fill"></i> Clientes
                    </a>
                    <a href="{{ route('choferes.index') }}"
                        class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-truck-front-fill"></i> Choferes
                    </a>
                    <a href="{{ route('direcciones.index') }}"
                        class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-geo-alt-fill"></i> Direcciones
                    </a>
                @endif
            </div>

            {{-- Tabla de cuentas --}}
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-[1200px] table-auto display nowrap responsive nowrap" id="myTable-cuentas">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Banco</th>
                            <th class="px-4 py-2 border-b">Nro. Cuenta</th>
                            <th class="px-4 py-2 border-b">CCI</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Fecha Creación</th>
                            <th class="px-4 py-2 border-b">Fecha Baja</th>
                            <th class="px-4 py-2 border-b">Observaciones</th>
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cuentas as $cuenta)
                            <tr class="hover:bg-gray-50">
                                {{-- Nombre con símbolo --}}
                                <td class="px-4 py-2 border-b">
                                    {{ $cuenta->nombre }} ({{ $cuenta->tipo == 0 ? 'S/.' : '$' }})
                                </td>
                                <td class="px-4 py-2 border-b">{{ $cuenta->nro_cuenta ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">{{ $cuenta->nro_interbancario ?? '—' }}</td>
                                <td class="px-4 py-2 border-b text-center">
                                    @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                        <button
                                            class="estado-toggle px-3 py-1 text-xs font-semibold text-white rounded-full transition cursor-pointer"
                                            data-id="{{ $cuenta->id_cuentas_empresa }}"
                                            data-estado="{{ $cuenta->estado }}"
                                            style="background-color: {{ $cuenta->estado ? '#22c55e' : '#ef4444' }};">
                                            {{ $cuenta->estado ? 'Activo' : 'Inactivo' }}
                                        </button>
                                    @else
                                        <span
                                            class="px-3 py-1 text-xs font-semibold text-white rounded-full cursor-not-allowed opacity-70"
                                            style="background-color: {{ $cuenta->estado ? '#22c55e' : '#ef4444' }};">
                                            {{ $cuenta->estado ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $cuenta->fecha_creacion ? \Carbon\Carbon::parse($cuenta->fecha_creacion)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $cuenta->fecha_baja ? \Carbon\Carbon::parse($cuenta->fecha_baja)->format('d/m/Y H:i') : '—' }}
                                </td>
                                <td class="px-4 py-2 border-b text-sm text-gray-700">
                                    {{ $cuenta->observaciones ? Str::limit($cuenta->observaciones, 30) : '—' }}
                                </td>
                                <td class="px-4 py-2 border-b text-center">
                                    <div class="flex justify-center items-center gap-3">
                                        {{-- Detalles --}}
                                        <a href="javascript:void(0)"
                                            class="tooltip btn-detalles-cuenta text-gray-500 hover:text-gray-700 transition"
                                            data-id="{{ $cuenta->id_cuentas_empresa }}"
                                            data-empresa="{{ $cuenta->empresa->razon_social ?? '—' }}"
                                            data-banco="{{ $cuenta->nombre }}"
                                            data-cuenta="{{ $cuenta->nro_cuenta ?? '—' }}"
                                            data-cci="{{ $cuenta->nro_interbancario ?? '—' }}"
                                            data-tipo="{{ $cuenta->tipo == 0 ? 'Soles (S/.)' : 'Dólares ($)' }}"
                                            data-estado="{{ $cuenta->estado == 1 ? 'Activo' : 'Inactivo' }}"
                                            data-fecha-creacion="{{ $cuenta->fecha_creacion ? \Carbon\Carbon::parse($cuenta->fecha_creacion)->format('d/m/Y') : '—' }}"
                                            data-fecha-baja="{{ $cuenta->fecha_baja ? \Carbon\Carbon::parse($cuenta->fecha_baja)->format('d/m/Y H:i') : '—' }}"
                                            data-observaciones="{{ $cuenta->observaciones ?? '—' }}">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon class="w-5 h-5" />
                                        </a>

                                        {{-- Editar --}}
                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                            <a href="{{ route('cuentas_empresas.edit', $cuenta->id_cuentas_empresa) }}"
                                                class="tooltip text-yellow-500 hover:text-yellow-600 transition">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon class="w-5 h-5" />
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
<x-modal-show id="cuentaModal" title="Detalles de la Cuenta Bancaria">
    <div class="space-y-4 text-sm text-gray-800">
        <div class="grid grid-cols-2 gap-2">
            <p><strong>ID:</strong> <span id="cuentaId"></span></p>
            <p><strong>Empresa:</strong> <span id="cuentaEmpresa"></span></p>
            <p><strong>Banco:</strong> <span id="cuentaBanco"></span></p>
            <p><strong>Número de Cuenta:</strong> <span id="cuentaCuenta"></span></p>
            <p><strong>CCI:</strong> <span id="cuentaCci"></span></p>
            <p><strong>Tipo:</strong> <span id="cuentaTipo"></span></p>
            <p><strong>Estado:</strong> <span id="cuentaEstado"></span></p>
            <p><strong>Fecha Creación:</strong> <span id="cuentaCreacion"></span></p>
            <p><strong>Fecha Baja:</strong> <span id="cuentaBaja"></span></p>
        </div>
        <div>
            <p><strong>Observaciones:</strong></p>
            <p id="cuentaObs" class="bg-gray-50 border rounded-md p-2 text-gray-700 text-sm whitespace-pre-line"></p>
        </div>
    </div>
</x-modal-show>
<script>
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-detalles-cuenta');
        if (btn) {
            const data = btn.dataset;

            document.getElementById('cuentaId').textContent = data.id;
            document.getElementById('cuentaEmpresa').textContent = data.empresa;
            document.getElementById('cuentaBanco').textContent = data.banco;
            document.getElementById('cuentaCuenta').textContent = data.cuenta;
            document.getElementById('cuentaCci').textContent = data.cci;
            document.getElementById('cuentaTipo').textContent = data.tipo;
            document.getElementById('cuentaEstado').textContent = data.estado;
            document.getElementById('cuentaCreacion').textContent = data.fechaCreacion;
            document.getElementById('cuentaBaja').textContent = data.fechaBaja;
            document.getElementById('cuentaObs').textContent = data.observaciones;

            toggleModal('cuentaModal');
        }
    });
</script>

{{-- !PETICION AJAX --}}
<script>
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.estado-toggle');
        if (!btn) return;

        const id = btn.dataset.id;
        const estadoActual = parseInt(btn.dataset.estado);
        const nuevoEstado = estadoActual === 1 ? 0 : 1;
        const accion = nuevoEstado === 1 ? 'activar' : 'desactivar';

        // ⚠️ Confirmación antes de enviar
        const confirmado = confirm(`¿Estás seguro de que deseas ${accion.toUpperCase()} esta cuenta?\n` +
            (nuevoEstado === 0 ? 'Esto registrará la fecha de baja automáticamente.' : ''));
        if (!confirmado) return;

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const url = `/cuentas_empresas/${id}/toggle`;

            const res = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': token
                },
            });

            if (res.ok) {
                const data = await res.json();
                btn.dataset.estado = data.nuevo_estado;

                // Buscar la celda de "Fecha Baja" en la misma fila
                const fila = btn.closest('tr');
                const celdaFechaBaja = fila.querySelector('td:nth-child(6)');

                // Transición suave
                btn.style.transition = 'all 0.3s ease';
                celdaFechaBaja.style.transition = 'background-color 0.3s ease';

                if (data.nuevo_estado === 1) {
                    btn.textContent = 'Activo';
                    btn.style.backgroundColor = '#22c55e';
                    celdaFechaBaja.textContent = '—';
                    celdaFechaBaja.style.backgroundColor = '#dcfce7'; // verde claro flash
                } else {
                    btn.textContent = 'Inactivo';
                    btn.style.backgroundColor = '#ef4444';
                    celdaFechaBaja.textContent = data.fecha_baja ?? '—';
                    celdaFechaBaja.style.backgroundColor = '#fee2e2'; // rojo claro flash
                }

                // Quitar el color de fondo del flash
                setTimeout(() => celdaFechaBaja.style.backgroundColor = '', 800);

            } else {
                alert('Error al actualizar el estado.');
            }
        } catch (error) {
            console.error(error);
            alert('Ocurrió un problema con la conexión.');
        }
    });
</script>
{{-- !FIN PETICION AJAX --}}

<script>
    $(document).ready(function() {
        $('#myTable-cuentas').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            responsive: false,
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            }
        });
    });
</script>
