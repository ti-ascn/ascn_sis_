@php
    $mesesArray = [
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
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Todos los Estados de Cuenta
            </h2>
            <a href="{{ route('estados_cuenta.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm">
                ← Volver a vista por año
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            {{-- FILTROS --}}
            <div class="bg-white shadow-lg rounded-lg p-4 mb-4">
                <form method="GET" action="{{ route('estados_cuenta.todos') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                            placeholder="RUC, razón social o cuenta..."
                            class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                        <select name="anio" class="border-gray-300 rounded-md shadow-sm w-32">
                            <option value="">Todos</option>
                            @foreach ($anios as $a)
                                <option value="{{ $a }}" {{ request('anio') == $a ? 'selected' : '' }}>
                                    {{ $a }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
                        <select name="mes" class="border-gray-300 rounded-md shadow-sm w-40">
                            <option value="">Todos</option>
                            @foreach ($mesesArray as $num => $nombre)
                                <option value="{{ $num }}" {{ request('mes') == $num ? 'selected' : '' }}>
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition h-[42px]">
                        <i class="bi bi-search"></i> Filtrar
                    </button>

                    @if (request()->anyFilled(['buscar', 'anio', 'mes']))
                        <a href="{{ route('estados_cuenta.todos') }}"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md transition h-[42px]">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    @endif
                </form>
            </div>

            {{-- TABLA DE TODOS LOS REGISTROS --}}
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto text-sm" id="tabla-todos-estados">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-3 border-b">ID</th>
                            <th class="px-4 py-3 border-b">RUC</th>
                            <th class="px-4 py-3 border-b">Razón Social</th>
                            <th class="px-4 py-3 border-b">Cuenta</th>
                            <th class="px-4 py-3 border-b text-center">Año</th>
                            <th class="px-4 py-3 border-b text-center">Mes</th>
                            <th class="px-4 py-3 border-b text-center">Recepción</th>
                            <th class="px-4 py-3 border-b text-center">Estado</th>
                            <th class="px-4 py-3 border-b">Observación</th>
                            <th class="px-4 py-3 border-b text-center">PDF</th>
                            <th class="px-4 py-3 border-b text-center">Últ. actualización</th>
                            <th class="px-4 py-3 border-b text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($estados as $estado)
                            @php
                                $recepcionColor = match ($estado->recepcion) {
                                    'SI' => 'bg-green-100 text-green-800',
                                    'NO' => 'bg-red-100 text-red-800',
                                    'N/A' => 'bg-gray-800 text-white',
                                    default => 'bg-gray-100',
                                };

                                $estatusColor = match ($estado->estatus) {
                                    'TR' => 'bg-blue-100 text-blue-800',
                                    'EP' => 'bg-yellow-100 text-yellow-800',
                                    'NT' => 'bg-red-100 text-red-800',
                                    'N/A' => 'bg-gray-600 text-white',
                                    default => 'bg-gray-100',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 border-b">
                                <td class="px-4 py-3 text-center">{{ $estado->id_estado_cuenta }}</td>
                                <td class="px-4 py-3">{{ $estado->cuenta->empresa->ruc }}</td>
                                <td class="px-4 py-3 max-w-[200px] truncate"
                                    title="{{ $estado->cuenta->empresa->razon_social }}">
                                    {{ $estado->cuenta->empresa->razon_social }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $estado->cuenta->nombre }}
                                    <span class="text-xs">({{ $estado->cuenta->tipo == 0 ? 'S/' : '$' }})</span>
                                </td>
                                <td class="px-4 py-3 text-center font-semibold">{{ $estado->anio }}</td>
                                <td class="px-4 py-3 text-center">{{ $mesesArray[$estado->mes] ?? $estado->mes }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $recepcionColor }}">
                                        {{ $estado->recepcion }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $estatusColor }}">
                                        {{ $estado->estatus }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 max-w-[200px] truncate" title="{{ $estado->observacion }}">
                                    {{ $estado->observacion ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($estado->archivo_pdf)
                                        <a href="{{ asset($estado->archivo_pdf) }}" target="_blank"
                                            class="text-blue-600 hover:text-blue-800 hover:scale-110 transition-transform inline-block">
                                            <i class="bi bi-file-earmark-pdf-fill text-xl"></i>
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    {{ $estado->updated_at->format('d/m/Y H:i') }}<br>
                                    <span class="text-gray-500">{{ $estado->usuario?->name ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button onclick="editarRegistro({{ $estado->id_estado_cuenta }})"
                                            class="text-blue-600 hover:text-blue-800 p-1 rounded">
                                            <i class="bi bi-pencil-square text-lg"></i>
                                        </button>
                                        <button onclick="eliminarRegistro({{ $estado->id_estado_cuenta }})"
                                            class="text-red-600 hover:text-red-800 p-1 rounded">
                                            <i class="bi bi-trash3 text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINACIÓN --}}
            <div class="mt-4">
                {{ $estados->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

{{-- MODAL EDITAR --}}
<x-modal-show id="modalEditarEstado" title="Editar Estado de Cuenta">
    <form id="formEditarEstado" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <input type="hidden" name="id_estado_cuenta" id="edit-id">

        <div class="space-y-4">
            {{-- Información de la cuenta --}}
            <div class="bg-gray-50 p-3 rounded-lg">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><strong>RUC:</strong> <span id="edit-ruc"></span></div>
                    <div><strong>Razón Social:</strong> <span id="edit-razon-social"></span></div>
                    <div><strong>Cuenta:</strong> <span id="edit-cuenta"></span></div>
                    <div><strong>Período:</strong> <span id="edit-periodo"></span></div>
                </div>
            </div>

            {{-- Recepción --}}
            <div>
                <x-input-label value="Recepción" />
                <select name="recepcion" id="edit-recepcion"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="SI">Sí</option>
                    <option value="NO">No</option>
                    <option value="N/A">N/A</option>
                </select>
            </div>

            {{-- Estado del trabajo --}}
            <div>
                <x-input-label value="Estado del trabajo" />
                <select name="estatus" id="edit-estatus"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="TR">Trabajado</option>
                    <option value="EP">En proceso</option>
                    <option value="NT">No trabajado</option>
                    <option value="N/A">N/A</option>
                    <option value="- -">Sin Recepción</option>
                </select>
            </div>

            {{-- PDF --}}
            <div class="border rounded-md p-4 bg-gray-50">
                <div class="flex items-center justify-between mb-2">
                    <x-input-label value="ARCHIVO PDF" />
                    <a id="edit-pdf-link" href="#" target="_blank"
                        class="text-sm text-blue-600 hover:underline">
                        Ver PDF actual
                    </a>
                </div>
                <input type="file" name="archivo_pdf" accept="application/pdf"
                    class="block w-full border-gray-300 rounded-md shadow-sm">
                <p class="text-xs text-gray-500 mt-1">Dejar vacío para mantener el PDF actual.</p>
            </div>

            {{-- Observación --}}
            <div>
                <x-input-label value="Observación" />
                <textarea name="observacion" id="edit-observacion" rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Detalle adicional, incidencias, faltantes, etc."></textarea>
            </div>

            <div id="edit-info-usuario" class="text-xs text-gray-500 border-t pt-3"></div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="toggleModal('modalEditarEstado')"
                    class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded-md">
                    Cancelar
                </button>
                <x-primary-button class="px-6 py-2">
                    Actualizar estado
                </x-primary-button>
            </div>
        </div>
    </form>
</x-modal-show>

<script>
    // Función para editar registro
    function editarRegistro(id) {
        fetch(`/estados-cuenta/${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit-id').value = data.id_estado_cuenta;
                document.getElementById('edit-ruc').textContent = data.cuenta?.empresa?.ruc || '—';
                document.getElementById('edit-razon-social').textContent = data.cuenta?.empresa?.razon_social ||
                    '—';
                document.getElementById('edit-cuenta').textContent =
                    `${data.cuenta?.nombre || '—'} (${data.cuenta?.tipo == 0 ? 'S/' : '$'})`;
                document.getElementById('edit-periodo').textContent = `${data.mes}/${data.anio}`;
                document.getElementById('edit-recepcion').value = data.recepcion;
                document.getElementById('edit-estatus').value = data.estatus;
                document.getElementById('edit-observacion').value = data.observacion || '';

                const pdfLink = document.getElementById('edit-pdf-link');
                if (data.archivo_pdf) {
                    pdfLink.href = data.archivo_pdf.startsWith('http') ? data.archivo_pdf : '/' + data.archivo_pdf;
                    pdfLink.classList.remove('hidden');
                } else {
                    pdfLink.classList.add('hidden');
                }

                const infoUsuario = document.getElementById('edit-info-usuario');
                if (data.usuario) {
                    infoUsuario.innerHTML =
                        `Última actualización por <strong>${data.usuario.name}</strong> · ${new Date(data.updated_at).toLocaleString()}`;
                } else {
                    infoUsuario.innerHTML = '';
                }

                // Configurar el action del formulario
                document.getElementById('formEditarEstado').action = `/estados-cuenta/${id}`;

                toggleModal('modalEditarEstado');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los datos del registro');
            });
    }

    // Función para eliminar registro
    function eliminarRegistro(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer.')) {
            fetch(`/estados-cuenta/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el registro');
                });
        }
    }

    // DataTable opcional
    $(document).ready(function() {
        if ($('#tabla-todos-estados tbody tr').length > 0) {
            $('#tabla-todos-estados').DataTable({
                paging: false,
                searching: false,
                ordering: true,
                responsive: false,
                language: {
                    url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
                }
            });
        }
    });
</script>
