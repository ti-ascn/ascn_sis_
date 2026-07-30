<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registros') }} - <span class="text-blue-500">{{ $empresa->razon_social }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <div class="mb-4 flex flex-wrap gap-4 items-center">
                <a href="{{ route('recepcion.documentos') }}"
                    class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>

                <div class="relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md text-white hover:bg-gray-600 transition duration-300">
                                <i class="bi bi-plus-circle-fill"></i> Nuevo Registro
                                <i class="bi bi-chevron-down ml-1"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="absolute right-0 z-50 w-48 bg-white shadow-lg rounded-md border p-2">
                                <!-- Botón de Solicitud -->
                                @if ($botonSolicitudActivo)
                                    <x-dropdown-link href="#"
                                        onclick="abrirModal('solicitud', {{ $empresa->id_empresas_clientes }})">
                                        <i class="bi bi-chat-dots-fill mr-2"></i> Solicitud
                                    </x-dropdown-link>
                                @else
                                    <x-dropdown-link href="#" style="cursor: not-allowed; opacity: 0.5;">
                                        <i class="bi bi-chat-dots-fill mr-2"></i> Solicitud
                                    </x-dropdown-link>
                                @endif

                                <!-- Botón de Recepción -->
                                @if ($botonRecepcionActivo)
                                    <x-dropdown-link href="#"
                                        onclick="abrirModal('recepcion', {{ $empresa->id_empresas_clientes }})">
                                        <i class="bi bi-file-earmark-arrow-down-fill mr-2"></i> Recepción
                                    </x-dropdown-link>
                                @else
                                    <x-dropdown-link href="#" style="cursor: not-allowed; opacity: 0.5;">
                                        <i class="bi bi-file-earmark-arrow-down-fill mr-2"></i> Recepción
                                    </x-dropdown-link>
                                @endif

                                <!-- Botón de Entrega -->
                                @if ($botonEntregaActivo)
                                    <x-dropdown-link href="#"
                                        onclick="abrirModal('entrega', {{ $empresa->id_empresas_clientes }})">
                                        <i class="bi bi-box-seam-fill mr-2"></i> Entrega
                                    </x-dropdown-link>
                                @else
                                    <x-dropdown-link href="#" style="cursor: not-allowed; opacity: 0.5;">
                                        <i class="bi bi-box-seam-fill mr-2"></i> Entrega
                                    </x-dropdown-link>
                                @endif
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                <button onclick="openModal()"
                    class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white">
                    <i class="bi bi-calendar-range"></i> Buscar por Periodo
                </button>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto" id="myTable-recepcion">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">FECHA</th>
                            <th class="px-4 py-2 border-b">PERIODO</th>
                            <th class="px-4 py-2 border-b">OPCIÓN</th>
                            <th class="px-4 py-2 border-b">ESTADO</th>
                            <th class="px-4 py-2 border-b">TIPO DE CONTACTO</th>
                            <th class="px-4 py-2 border-b">TIPO DE DOCUMENTO</th>
                            <th class="px-4 py-2 border-b">OBSERVACIÓN</th>
                            <th class="px-4 py-2 border-b">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($todosRegistros as $registro)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">
                                    {{ $registro->created_at->format('d/m/Y H:i:s') }}

                                    @if ($registrosSolicitud->contains($registro))
                                        <span class="text-red-500">ult.</span>
                                    @endif

                                    @if (
                                        $registro->estado === 'recepcion' &&
                                            isset($ultimaRecepcion) &&
                                            $registro->id_recepcion === $ultimaRecepcion->id_recepcion)
                                        <span class="text-red-500">ult.</span>
                                    @endif

                                    @if (
                                        $registro->estado === 'entrega' &&
                                            isset($ultimaEntrega) &&
                                            $registro->id_recepcion === $ultimaEntrega->id_recepcion)
                                        <span class="text-red-500">ult.</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ ucfirst(\Carbon\Carbon::parse($registro->fecha)->isoFormat('MMMM YYYY')) }}
                                    <!-- Mes en español -->
                                </td>
                                <td class="px-4 py-2 border-b">{{ $registro->opcion }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($registro->estado === 'solicitud')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-blue-500 rounded-full w-24">
                                            Solicitud
                                        </span>
                                    @elseif($registro->estado === 'recepcion')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">
                                            Recepción
                                        </span>
                                    @elseif($registro->estado === 'entrega')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                            Entrega
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">{{ strtoupper($registro->tipo_contacto ?? '-') }}</td>
                                <td class="px-4 py-2 border-b">{{ strtoupper($registro->tipo_documento ?? '-') }}</td>
                                <td class="px-4 py-2 border-b">{{ $registro->observacion ?? 'Sin observación' }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if (
                                        $registrosSolicitud->contains($registro) ||
                                            ($registro->estado === 'recepcion' &&
                                                isset($ultimaRecepcion) &&
                                                $registro->id_recepcion === $ultimaRecepcion->id_recepcion) ||
                                            ($registro->estado === 'entrega' &&
                                                isset($ultimaEntrega) &&
                                                $registro->id_recepcion === $ultimaEntrega->id_recepcion))
                                        <a href="{{ route('recepcion.documentos.edit', $registro->id_recepcion) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #F59E0B; transition: color 0.3s;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </a>
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

<!-- Modal para solicitud, recepción y entrega -->
<x-modal-show id="modal-accion" title="Registrar Acción">
    <form id="form-accion" method="POST" action="{{ route('recepcion.documentos.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="id_empresas_clientes" id="id_empresas_clientes">
        <input type="hidden" name="estado" id="estado">

        <!-- Campos dinámicos para solicitud -->
        <div id="campos-solicitud" class="hidden">
            <div>
                <label for="tipo_contacto" class="block text-sm font-medium text-gray-700">Tipo de Contacto</label>
                <select name="tipo_contacto" id="tipo_contacto"
                    class="block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="WHATSAPP">Whatsapp</option>
                    <option value="LLAMADA">Llamada</option>
                </select>
            </div>
        </div>

        <!-- Campos dinámicos para recepción -->
        <div id="campos-recepcion" class="hidden">
            <div>
                <label for="tipo_documento" class="block text-sm font-medium text-gray-700">Tipo de Documento</label>
                <select name="tipo_documento" id="tipo_documento"
                    class="block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="DIGITAL">Digital</option>
                    <option value="FISICO">Físico</option>
                </select>
            </div>
        </div>

        <!-- Campo para el periodo -->
        <div class="mb-4">
            <label for="fecha" class="block text-sm font-medium text-gray-700">Periodo <span
                    class="text-red-500">(mes anterior por defecto)</span></label>
            <select name="fecha" id="fecha" class="block w-full border-gray-300 rounded-md shadow-sm">
                @php
                    // Generar los últimos 12 meses
                    $meses = [];
                    $base = \Carbon\Carbon::now()->startOfMonth();
                    for ($i = 0; $i < 12; $i++) {
                        $meses[] = $base->copy()->subMonths($i)->format('Y-m');
                    }
                    $mesAnterior = $base->copy()->subMonths(1)->format('Y-m');
                @endphp
                @foreach ($meses as $mes)
                    <option value="{{ $mes }}" {{ $mes == $mesAnterior ? 'selected' : '' }}>
                        {{ ucfirst(\Carbon\Carbon::parse($mes)->isoFormat('MMMM YYYY')) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Campo para la opción (Si, No, N/A) -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Opción</label>
            <div class="flex space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion" value="Si" class="form-radio text-blue-500">
                    <span class="ml-2">Si</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion" value="No" checked class="form-radio text-blue-500">
                    <span class="ml-2">No</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="opcion" value="N/A" class="form-radio text-blue-500">
                    <span class="ml-2">N/A</span>
                </label>
            </div>
        </div>

        <!-- Campo observación (común para todos los estados) -->
        <div>
            <label for="observacion" class="block text-sm font-medium text-gray-700">Observación</label>
            <textarea name="observacion" id="observacion" rows="3"
                class="block w-full border-gray-300 rounded-md shadow-sm"></textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                Guardar
            </button>
        </div>
    </form>
</x-modal-show>

<!-- Modal para seleccionar el periodo -->
<x-modal-show id="modal-periodo" title="Seleccionar Periodo">
    <form id="form-periodo" method="GET" action="{{ route('recepcion.documentos.por_periodo') }}">
        <input type="hidden" name="id_empresas_clientes" value="{{ $empresa->id_empresas_clientes }}">

        <div class="mb-4">
            <label for="periodo" class="block text-sm font-medium text-gray-700">Seleccione un periodo:</label>
            <select name="fecha" id="periodo" class="block w-full border-gray-300 rounded-md shadow-sm">
                @php
                    $meses = [];
                    for ($i = 0; $i < 12; $i++) {
                        $meses[] = now()->subMonths($i)->format('Y-m');
                    }
                @endphp
                @foreach ($meses as $mes)
                    <option value="{{ $mes }}">
                        {{ ucfirst(\Carbon\Carbon::parse($mes)->isoFormat('MMMM YYYY')) }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                Buscar
            </button>
        </div>
    </form>
</x-modal-show>

<script>
    function openModal() {
        document.getElementById('modal-periodo').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modal-periodo').classList.add('hidden');
    }
</script>

<script>
    // Función para abrir el modal y configurar los campos dinámicos
    function abrirModal(estado, idEmpresa) {
        // Configurar el formulario
        document.getElementById('id_empresas_clientes').value = idEmpresa;
        document.getElementById('estado').value = estado;

        // Mostrar/ocultar campos dinámicos
        const camposSolicitud = document.getElementById('campos-solicitud');
        const camposRecepcion = document.getElementById('campos-recepcion');

        if (estado === 'solicitud') {
            camposSolicitud.classList.remove('hidden');
            camposRecepcion.classList.add('hidden');
        } else if (estado === 'recepcion') {
            camposRecepcion.classList.remove('hidden');
            camposSolicitud.classList.add('hidden');
        } else {
            camposSolicitud.classList.add('hidden');
            camposRecepcion.classList.add('hidden');
        }

        // Abrir el modal
        toggleModal('modal-accion');
    }
</script>


<script>
    $(document).ready(function() {
        $('#myTable-recepcion').DataTable({
            responsive: false,
            paging: true,
            ordering: false,
            searching: true,
            info: true,
            language: {
                url: "/js/datatables/lang/es-ES.json"
            },
            order: [
                [0, 'desc']
            ]
        });
    });
</script>
