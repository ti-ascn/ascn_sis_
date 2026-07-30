<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Recepción de Documentos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <div class="mb-4 flex flex-wrap gap-4 items-center">
                <a href="{{ route('recepcion.documentos.index') }}"
                    class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                    <i class="bi bi-archive"></i> Todos los registros
                </a>
                <!-- Botón para abrir el modal de selección de período -->
                <button onclick="openModalC1()"
                    class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white">
                    <i class="bi bi-calendar-range"></i> Todos los Registros por Período
                </button>
            </div>
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">UD</th>
                            <th class="px-4 py-2 border-b">RAZÓN SOCIAL</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $empresa)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ substr($empresa->ruc, -1) }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->razon_social }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->ruc }}</td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        {{-- ACCIONES --}}

                                        @if ($empresa->manejar_recepcion)
                                            {{-- Ícono verde activo: Ver Registros --}}
                                            <a href="{{ route('recepcion.documentos.registros_mes_actual', $empresa->id_empresas_clientes) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #10B981; transition: color 0.3s;"
                                                onmouseover="this.style.color='#059669';"
                                                onmouseout="this.style.color='#10B981';">
                                                <span class="tooltip-text">Ver Registros</span>
                                                <x-details-icon />
                                            </a>

                                            {{-- Ícono azul activo: Ver por Periodo --}}
                                            <a href="javascript:void(0);"
                                                onclick="abrirModalPeriodo({{ $empresa->id_empresas_clientes }})"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #3b82f6; transition: color 0.3s;"
                                                onmouseover="this.style.color='#2563eb';"
                                                onmouseout="this.style.color='#3b82f6';">
                                                <span class="tooltip-text">Ver por Periodo</span>
                                                <x-calendar-icon />
                                            </a>
                                        @else
                                            {{-- Ícono gris inactivo: SIN FUNCIONALIDAD, SIN TOOLTIP --}}
                                            <span class="flex items-center py-1 rounded-md"
                                                style="color: #9CA3AF; opacity: 0.5; cursor: default;">
                                                <x-details-icon />
                                            </span>

                                            <span class="flex items-center py-1 rounded-md"
                                                style="color: #9CA3AF; opacity: 0.5; cursor: default;">
                                                <x-calendar-icon />
                                            </span>
                                        @endif

                                        <div class="relative">
                                            <x-dropdown align="right" width="48">
                                                <x-slot name="trigger">
                                                    <button class="flex items-center py-1 rounded-md tooltip"
                                                        style="color: #6B7280; transition: color 0.3s;"
                                                        onmouseover="this.style.color='#4B5563';"
                                                        onmouseout="this.style.color='#6B7280';">
                                                        <x-dots-vertical-icon />
                                                    </button>
                                                </x-slot>

                                                <x-slot name="content">
                                                    <div
                                                        class="absolute right-0 z-50 w-48 bg-white shadow-lg rounded-md border p-2">
                                                        @php
                                                            $puedeGestionar = in_array(Auth::user()->tipo_usuario, [
                                                                1,
                                                                2,
                                                                3
                                                            ]);
                                                        @endphp

                                                        {{-- Si la empresa tiene la recepción activada --}}
                                                        @if ($empresa->manejar_recepcion)
                                                            {{-- Botón de Solicitud --}}
                                                            @if ($empresa->botonSolicitudActivo)
                                                                <x-dropdown-link href="javascript:void(0)"
                                                                    onclick="abrirModal('solicitud', {{ $empresa->id_empresas_clientes }})">
                                                                    <i class="bi bi-chat-dots-fill mr-2"></i>
                                                                    {{ __('Solicitud') }}
                                                                </x-dropdown-link>
                                                            @else
                                                                <x-dropdown-link href="javascript:void(0)"
                                                                    style="cursor: not-allowed; opacity: 0.5;">
                                                                    <i class="bi bi-chat-dots-fill mr-2"></i>
                                                                    {{ __('Solicitud') }}
                                                                </x-dropdown-link>
                                                            @endif

                                                            {{-- Botón de Recepción --}}
                                                            @if ($empresa->botonRecepcionActivo)
                                                                <x-dropdown-link href="javascript:void(0)"
                                                                    onclick="abrirModal('recepcion', {{ $empresa->id_empresas_clientes }})">
                                                                    <i
                                                                        class="bi bi-file-earmark-arrow-down-fill mr-2"></i>
                                                                    {{ __('Recepción') }}
                                                                </x-dropdown-link>
                                                            @else
                                                                <x-dropdown-link href="javascript:void(0)"
                                                                    style="cursor: not-allowed; opacity: 0.5;">
                                                                    <i
                                                                        class="bi bi-file-earmark-arrow-down-fill mr-2"></i>
                                                                    {{ __('Recepción') }}
                                                                </x-dropdown-link>
                                                            @endif

                                                            {{-- Botón de Entrega --}}
                                                            @if ($empresa->botonEntregaActivo)
                                                                <x-dropdown-link href="javascript:void(0)"
                                                                    onclick="abrirModal('entrega', {{ $empresa->id_empresas_clientes }})">
                                                                    <i class="bi bi-box-seam-fill mr-2"></i>
                                                                    {{ __('Entrega') }}
                                                                </x-dropdown-link>
                                                            @else
                                                                <x-dropdown-link href="javascript:void(0)"
                                                                    style="cursor: not-allowed; opacity: 0.5;">
                                                                    <i class="bi bi-box-seam-fill mr-2"></i>
                                                                    {{ __('Entrega') }}
                                                                </x-dropdown-link>
                                                            @endif
                                                        @endif

                                                        {{-- Solo visible para tipo_usuario 1 o 2 --}}
                                                        @if ($puedeGestionar)
                                                            <form method="POST"
                                                                action="{{ route('empresas-clientes.toggle-recepcion', $empresa->id_empresas_clientes) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <x-dropdown-link href="javascript:void(0)"
                                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                                    <i class="bi bi-sliders2-vertical mr-2"></i>
                                                                    {{ $empresa->manejar_recepcion ? 'Desactivar Recepción' : 'Activar Recepción' }}
                                                                </x-dropdown-link>
                                                            </form>
                                                        @elseif($empresa->manejar_recepcion == 0)
                                                            <x-dropdown-link>
                                                                <i
                                                                    class="bi bi-shield-lock-fill mr-2 text-gray-500"></i>
                                                                Recepción desactivada (solo admin)
                                                            </x-dropdown-link>
                                                        @endif
                                                    </div>
                                                </x-slot>
                                            </x-dropdown>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para seleccionar el período -->
    <x-modal-show id="modal-c1-periodo" title="Seleccionar Periodo">
        <form id="form-c1-periodo" method="GET" action="{{ route('recepcion.documentos.empresasC1') }}">
            <div class="mb-4">
                <label for="periodo" class="block text-sm font-medium text-gray-700">Seleccione un periodo:</label>
                <select name="fecha" id="periodo" onchange="this.form.submit()" class="block w-full border-gray-300 rounded-md shadow-sm">
                    @php
                        $meses = [];
                        for ($i = 0; $i < 12; $i++) {
                            $meses[] = now()->subMonths($i)->format('Y-m');
                        }
                    @endphp
                    @foreach ($meses as $mes)
                        <option value="{{ $mes }}">
                            {{ ucfirst(\Carbon\Carbon::parse($mes)->isoFormat('MMMM YYYY')) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- <div class="flex justify-end space-x-2">
                <button type="submit"
                    class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div> --}}
        </form>
    </x-modal-show>

    <x-modal-show id="modal-periodo" title="Seleccionar Periodo">
        <form id="form-periodo" method="GET" action="{{ route('recepcion.documentos.por_periodo') }}">
            <input type="hidden" name="id_empresas_clientes" id="empresa_id">

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
        function abrirModalPeriodo(idEmpresa) {
            document.getElementById('empresa_id').value = idEmpresa;
            toggleModal('modal-periodo');
        }
    </script>


    <!-- Modal para solicitud, recepción y entrega -->
    <x-modal-show id="modal-accion" title="Registrar Acción">
        <form id="form-accion" method="POST" action="{{ route('recepcion.documentos.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="id_empresas_clientes" id="id_empresas_clientes">
            <input type="hidden" name="estado" id="estado">

            <!-- Campos dinámicos para solicitud -->
            <div id="campos-solicitud" class="hidden">
                <div>
                    <label for="tipo_contacto" class="block text-sm font-medium text-gray-700">Tipo de
                        Contacto</label>
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
                    <label for="tipo_documento" class="block text-sm font-medium text-gray-700">Tipo de
                        Documento</label>
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
                        for ($i = 0; $i < 12; $i++) {
                            $meses[] = now()->subMonths($i)->format('Y-m');
                        }
                        // Obtener el mes anterior al actual
                        $mesAnterior = now()->subMonth()->format('Y-m');
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
                        <input type="radio" name="opcion" value="No" checked
                            class="form-radio text-blue-500">
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

    <script>
        function openModalC1() {
            toggleModal('modal-c1-periodo');
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
</x-app-layout>
