<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Estatus de Libros Contables') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            @php
                $userArea = Auth::user()->area->nombre ?? '';
                $isAdminOrGerente = in_array($userArea, ['ADMINISTRACION', 'GERENCIA']);
                $isContabilidad = $userArea === 'CONTABILIDAD';
                $isRecepcion = $userArea === 'RECEPCION';
                $isLegalArea = $userArea === 'LEGAL';
                $canCreate = $isAdminOrGerente || $isContabilidad;
            @endphp

            <div class="flex mb-4 justify-between items-center">
                @if ($canCreate)
                    <button type="button" onclick="abrirModalCrear()"
                        class="flex items-center justify-center gap-2 bg-blue-500 hover:bg-blue-600 transition duration-300 text-white px-4 py-2 rounded-md">
                        <i class="bi bi-plus-circle"></i> Registrar Estatus
                    </button>
                @endif
                {{-- <button type="button" onclick="toggleModal('modalInfoProceso')"
                    class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-full transition-colors">
                    <i class="bi bi-info-circle-fill"></i> ¿Cómo funciona el proceso?
                </button> --}}
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive" id="tabla-estatus-libros">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                            <th class="px-4 py-2 border-b">Empresa</th>
                            <th class="px-4 py-2 border-b">Régimen</th>
                            <th class="px-4 py-2 border-b">Monto</th>
                            <th class="px-4 py-2 border-b">Año Obligación</th>
                            <th class="px-4 py-2 border-b">Libros Obligados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($estatusLibros as $item)
                            <tr class="hover:bg-gray-50 text-sm">
                                <td class="px-4 py-2 border-b text-center">
                                    <div class="flex space-x-2 justify-center">
                                        {{-- Botón Detalles --}}
                                        <button type="button"
                                            onclick="abrirModalDetalles({{ $item->id_estatus_libros }})"
                                            class="flex items-center py-1 rounded-md tooltip text-emerald-500 hover:text-emerald-700 transition-colors">
                                            <span class="tooltip-text">Ver Detalles</span>
                                            <x-details-icon />
                                        </button>

                                        <button type="button"
                                            onclick="abrirModalCargos({{ $item->id_estatus_libros }})"
                                            class="flex items-center py-1 rounded-md tooltip text-blue-500 hover:text-blue-700 transition-colors">
                                            <span class="tooltip-text">Cargos Libros</span>
                                            <i class="bi bi-folder-fill text-lg"></i>
                                        </button>

                                        <button type="button"
                                            onclick="abrirModalEditar({{ $item->id_estatus_libros }})"
                                            class="flex items-center py-1 rounded-md tooltip text-amber-500 hover:text-amber-700 transition-colors">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </button>

                                        @if ($isAdminOrGerente)
                                            <form
                                                action="{{ route('estatus_libros_contables.destroy', $item->id_estatus_libros) }}"
                                                method="POST" style="display:inline;"
                                                onsubmit="return confirm('¿Estás seguro de eliminar este registro?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center py-1 rounded-md tooltip text-red-600 hover:text-red-800 transition-colors">
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2 border-b font-medium">
                                    {{ $item->empresaCliente->razon_social ?? '—' }}</td>
                                <td class="px-4 py-2 border-b uppercase font-bold text-xs">
                                    <span
                                        class="px-2 py-1 rounded {{ $item->tipo_regimen === 'especial' ? 'bg-purple-100 text-purple-800' : ($item->tipo_regimen === 'mype' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ $item->tipo_regimen }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @php
                                        $uitRow = collect($valoresUit)->firstWhere('anio', $item->anio);
                                        $uitValor = $uitRow ? $uitRow->valor : 5500;
                                        $uits = $item->monto ? number_format($item->monto / $uitValor, 0) : 0;
                                    @endphp
                                    {{ $item->monto ? 'S/ ' . number_format($item->monto, 2) . ' (' . $uits . ' UIT)' : '—' }}
                                </td>
                                <td class="px-4 py-2 border-b">{{ $item->anio_obligacion ?? '—' }}</td>
                                <td class="px-4 py-2 border-b max-w-[200px] whitespace-normal">
                                    @if (!empty($item->id_libros))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($item->libros as $l)
                                                @php
                                                    $detalles = $item->detalles_libros[$l->id_libro] ?? [];
                                                    $isElectronico = !empty($detalles['is_electronico']);
                                                    $entregadoPorCliente = ($detalles['entregado_por_cliente'] ?? 'no') === 'si';
                                                    $entregadoAlCliente = ($detalles['entregado_al_cliente'] ?? 'no') === 'si';
                                                    $legalizado = ($detalles['estatus_legalizacion'] ?? 'n/a') === 'si';

                                                    if ($entregadoAlCliente) {
                                                        $colorClass = 'bg-blue-100 text-blue-800';
                                                    } else {
                                                        $colorClass = $entregadoPorCliente
                                                            ? 'bg-green-100 text-green-800'
                                                            : 'bg-red-100 text-red-800';
                                                    }
                                                @endphp

                                                <span class="{{ $colorClass }} text-[10px] px-2 py-0.5 rounded font-medium">
                                                    {{ $l->nombre }}
                                                    {{ $isElectronico ? '(Electrónico)' : '' }}
                                                    {{ $legalizado ? '(Legalizado)' : '' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 font-italic">Ninguno</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Principal (Crear / Editar Estatus) -->
    <x-modal-show id="modalEstatusLibro" title="Estatus Libros Contables" size="large">
        <form method="POST" id="formEstatusLibro" action="{{ route('estatus_libros_contables.store') }}"
            enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="_method" id="methodEstatusLibro" value="POST">

            <!-- Grid de Secciones -->
            <div class="grid grid-cols-1 gap-6">

                <!-- Sección 1: Contabilidad -->
                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 space-y-4"
                    id="section-contabilidad">
                    <h3
                        class="text-sm font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider border-b pb-2 flex items-center gap-2">
                        <i class="bi bi-calculator"></i> 1. Contabilidad
                    </h3>

                    <div>
                        <x-input-label value="Empresa" />
                        <select name="id_empresa_cliente" id="val_id_empresa_cliente"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-[#1a1c21] dark:border-gray-700 select2-empresa"
                            required>
                            <option value="" disabled selected>Selecciona Empresa...</option>
                            @foreach ($empresas as $emp)
                                <option value="{{ $emp->id_empresas_clientes }}">{{ $emp->razon_social }}
                                    ({{ $emp->ruc }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label value="Tipo de Régimen" />
                        <select name="tipo_regimen" id="val_tipo_regimen" onchange="checkRegimen(this.value)"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-[#1a1c21] dark:border-gray-700"
                            required>
                            <option value="" disabled selected>Selecciona Régimen...</option>
                            <option value="mype">MYPE</option>
                            <option value="general">GENERAL</option>
                            <option value="especial">ESPECIAL</option>
                        </select>
                    </div>

                    <div id="wrapper-monto">
                        <x-input-label value="Monto de Ingresos Anuales" />
                        <input type="text" id="val_monto_display" oninput="formatMontoDisplay(this)"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-[#1a1c21] dark:border-gray-700 text-gray-800 dark:text-gray-100"
                            placeholder="0.00" />
                        <input type="hidden" name="monto" id="val_monto" />
                    </div>

                    <div>
                        <x-input-label value="Año Obligacion" />
                        <select name="anio_obligacion" id="val_anio_obligacion"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-[#1a1c21] dark:border-gray-700"
                            required>
                            @foreach ($anios as $a)
                                <option value="{{ $a }}">{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label value="Año UIT" />
                        <select name="anio" id="val_anio"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-[#1a1c21] dark:border-gray-700"
                            required>
                            @foreach ($anios as $a)
                                <option value="{{ $a }}">{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-2">
                        <button type="button" id="btn-select-libros" onclick="cargarLibrosDefault()"
                            class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200 shadow-sm text-sm">
                            <i class="bi bi-book-half"></i> Calcular Libros
                        </button>
                    </div>

                    <div id="wrapper-uits-info"
                        class="hidden text-xs bg-white dark:bg-[#1a1c21] p-3 rounded-md border dark:border-gray-700 space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Valor UIT:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200" id="info-valor-uit">S/
                                0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Equivalencia UITs:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200"
                                id="info-uits-calculadas">0.0000</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tramo Calculado:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400" id="info-tramo">Tramo
                                1</span>
                        </div>
                    </div>
                    <div class="mt-2 text-right text-xs text-gray-500">
                        Última Act: <span id="info_updated_contabilidad" class="font-semibold">—</span>
                    </div>
                </div>
            </div>

            <!-- Libros Obligados & Adicionales Checklist Container -->
            <div id="wrapper-checklist-libros"
                class="hidden bg-white dark:bg-[#202227] p-4 rounded-lg border dark:border-gray-700">
                <div class="flex justify-between items-center border-b pb-2 mb-4">
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-200">Libros Obligados & Adicionales</h4>
                    @if ($canCreate)
                        <button type="button" onclick="abrirModalNuevoLibro()"
                            class="flex items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1.5 rounded-md transition duration-200">
                            <i class="bi bi-plus-lg"></i> Crear nuevo libro
                        </button>
                    @endif
                </div>

                <!-- Inputs ocultos para enviar libros obligados (los cargados por defecto) -->
                <div id="hidden-obligados-inputs"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h5 class="text-xs font-bold text-emerald-600 uppercase mb-2">Obligatorios por Tramo</h5>
                        <div id="list-libros-obligados" class="space-y-2 max-h-[200px] overflow-y-auto pr-2">
                            <!-- JS populated -->
                            <p class="text-xs text-gray-400 italic">Haz clic en "Seleccionar Libros" para cargar.</p>
                        </div>
                    </div>

                    <div>
                        <h5 class="text-xs font-bold text-blue-600 uppercase mb-2">Adicionales / Todos</h5>
                        <div id="tabs-header-adicionales"
                            class="flex flex-wrap gap-1 border-b dark:border-gray-700 mb-3 pb-1">
                            <!-- JS populated tabs -->
                        </div>
                        <div id="list-libros-adicionales" class="max-h-[200px] overflow-y-auto pr-2">
                            <!-- JS populated content -->
                        </div>
                    </div>
                </div>

                <!-- Botón Generar PDF de libros pendientes por entregar -->
                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-start">
                    <button type="button" id="btn-pdf-pendientes"
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-md text-xs flex items-center gap-1.5 transition duration-200 shadow-sm">
                        <i class="bi bi-file-earmark-pdf"></i> Generar PDF de libros pendientes por entregar
                    </button>
                </div>
            </div>

            <!-- Footer del Formulario -->
            <div class="flex justify-end pt-4 border-t dark:border-gray-700">
                <x-secondary-button type="button" onclick="toggleModal('modalEstatusLibro')">
                    Cancelar
                </x-secondary-button>
                <x-primary-button class="ml-2" id="btn-submit-main">
                    Guardar Estatus
                </x-primary-button>
            </div>
        </form>
    </x-modal-show>

    <!-- Modal Secundario: Crear Nuevo Libro (Sobre el modal principal) -->
    <x-modal-show id="modalNuevoLibro" title="Crear Nuevo Libro" size="small">
        <form id="formNuevoLibro" class="space-y-4" onsubmit="guardarNuevoLibroAjax(event)">
            @csrf
            <div>
                <x-input-label value="Nombre del Libro" />
                <x-text-input name="nombre" id="new_nombre_libro" class="mt-1 block w-full" required />
            </div>

            <div>
                <x-input-label value="Tipo de Régimen" />
                <select name="tipo_regimen" id="new_regimen_libro"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-[#1a1c21] dark:border-gray-700"
                    required>
                    <option value="" disabled selected>Selecciona régimen</option>
                    <option value="mype">MYPE</option>
                    <option value="general">GENERAL</option>
                    <option value="especial">ESPECIAL</option>
                    <option value="otro">OTRO</option>
                </select>
            </div>

            <div>
                <x-input-label value="Tramo" />
                <select name="tramo" id="new_tramo_libro"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-[#1a1c21] dark:border-gray-700"
                    required>
                    <option value="" disabled selected>Selecciona tramo</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="otro">Otro</option>
                </select>
            </div>

            <div>
                <x-input-label value="Área responsable" />
                <select name="area" id="new_area_libro"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-[#1a1c21] dark:border-gray-700"
                    required>
                    <option value="contabilidad">CONTABILIDAD</option>
                    <option value="legal">LEGAL</option>
                </select>
            </div>

            <div class="flex justify-end pt-4 border-t dark:border-gray-700">
                <x-secondary-button type="button" onclick="toggleModal('modalNuevoLibro')">
                    Cancelar
                </x-secondary-button>
                <x-primary-button class="ml-2">
                    Crear Libro
                </x-primary-button>
            </div>
        </form>
    </x-modal-show>

    <!-- Modal Info Proceso -->
    <x-modal-show id="modalInfoProceso" title="¿Cómo funciona el proceso?" size="medium">
        <div class="space-y-4 text-sm text-gray-700 dark:text-gray-300">
            <p><strong>1. Contabilidad / Administración:</strong> Registran la empresa, régimen, monto (si aplica) y
                año. Al hacer esto se genera el estatus inicial de los libros y se calcula automáticamente qué libros le
                tocan según su régimen y tramo.</p>
            <p><strong>2. Legal / Administración:</strong> Toman los libros generados e indican si se legalizaron o no,
                y quién fue el responsable.</p>
            <p><strong>3. Recepción / Administración:</strong> Una vez finalizado el proceso de contabilidad/legal, esta
                área recibe los documentos físicos o digitales, registra un archivo de recepción y marca si se entregó
                al cliente final.</p>
            <p class="text-xs text-blue-600 bg-blue-50 p-2 rounded border border-blue-100">
                <i class="bi bi-info-circle"></i> Cada área debe completar sus pasos para que el estado de los libros
                contables avance correctamente. Solo puede haber un estatus por empresa cada año.
            </p>
        </div>
        <div class="flex justify-end pt-4 border-t dark:border-gray-700 mt-4">
            <x-primary-button type="button" onclick="toggleModal('modalInfoProceso')">Entendido</x-primary-button>
        </div>
    </x-modal-show>

    <!-- Modal Detalles de Libros -->
    <x-modal-show id="modalDetallesLibros" title="Detalles de Libros Contables" size="large">
        <div id="detalles-libros-loading" class="flex flex-col items-center justify-center py-12 gap-3">
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-emerald-500 border-t-transparent"></div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Cargando detalles...</span>
        </div>
        <div id="detalles-libros-content" class="hidden space-y-4">
            <!-- Encabezado resumen -->
            <div class="flex flex-wrap items-center justify-between gap-3 bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border dark:border-gray-700">
                <div class="flex flex-wrap gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 text-xs uppercase tracking-wide">Empresa</span>
                        <p class="font-semibold text-gray-800 dark:text-gray-200" id="det-empresa">—</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-xs uppercase tracking-wide">Régimen</span>
                        <p id="det-regimen" class="font-semibold">—</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-xs uppercase tracking-wide">Año UIT</span>
                        <p class="font-semibold text-gray-800 dark:text-gray-200" id="det-anio">—</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="btn-det-pdf"
                        class="flex items-center gap-1.5 bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-xs font-semibold transition-colors shadow-sm">
                        <i class="bi bi-file-earmark-pdf"></i> PDF Pendientes
                    </button>
                    <button type="button" id="btn-det-editar"
                        class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-md text-sm font-semibold transition-colors shadow-sm">
                        <i class="bi bi-pencil-square"></i> Editar
                    </button>
                </div>
            </div>

            <!-- Leyenda -->
            <div class="flex flex-wrap gap-3 text-xs">
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-blue-400"></span> Entregado al Cliente</span>
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-green-400"></span> Recibido del Cliente</span>
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-red-400"></span> Pendiente de recibir</span>
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-gray-300"></span> Sin libro</span>
            </div>

            <!-- Grid de tarjetas de libros -->
            <div id="det-libros-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <!-- JS populated -->
            </div>
        </div>
        <div class="flex justify-end pt-4 border-t dark:border-gray-700 mt-2">
            <x-secondary-button type="button" onclick="toggleModal('modalDetallesLibros')">
                Cerrar
            </x-secondary-button>
        </div>
    </x-modal-show>

</x-app-layout>

<!-- Scripts fuera de x-app-layout -->
<script>
    // Variables de sesión para controlar permisos
    const currentUserArea = "{{ Auth::user()->area->nombre ?? '' }}";
    const isUserAdminOrGerente = (currentUserArea === 'ADMINISTRACION' || currentUserArea === 'GERENCIA');
    const isContabilidad = (currentUserArea === 'CONTABILIDAD');
    const isLegal = (currentUserArea === 'LEGAL');
    const isRecepcion = (currentUserArea === 'RECEPCION');

    $(document).ready(function() {
        $('#tabla-estatus-libros').DataTable({
            paging: true,
            searching: true,
            responsive: false,
            ordering: true,
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            }
        });

        // Inicializar Select2 en el select de Empresa
        $('#val_id_empresa_cliente').select2({
            placeholder: 'Selecciona Empresa...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalEstatusLibro')
        });

        // Asegurar que los checkboxes marcados pero deshabilitados se envíen.
        $('#formEstatusLibro').on('submit', function() {
            $(this).find('input[type="checkbox"]:disabled:checked').each(function() {
                const existingHidden = $(this).closest('form').find(
                    `input[type="hidden"][name="${this.name}"][value="${this.value}"]`
                );
                if (existingHidden.length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: this.name,
                        value: this.value
                    }).appendTo('#formEstatusLibro');
                }
            });
        });
    });

    let currentDetallesLibros = {};

    function ponerFechaHoraLocal(idInput) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById(idInput).value = `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    function ponerFechaHoraActualLegal() {
        ponerFechaHoraLocal('val_fecha_entrega_legal');
    }

    function ponerFechaHoraActualRecepcion() {
        ponerFechaHoraLocal('val_fecha_hora_recepcion');
    }

    function ponerFechaHoraActualEntrega() {
        ponerFechaHoraLocal('val_fecha_hora_entrega');
    }

    // Función para alternar pestañas de Sección 3 (Recepción / Entrega)
    function switchTabSeccion3(tab) {
        const btnRecepcion = document.getElementById('tab-btn-recepcion');
        const btnEntrega = document.getElementById('tab-btn-entrega');
        const contentRecepcion = document.getElementById('tab-content-recepcion');
        const contentEntrega = document.getElementById('tab-content-entrega');

        // Clases activas e inactivas
        const activeClasses = ['border-b-2', 'font-bold'];
        const recepcionActive = ['border-orange-500', 'text-orange-600', 'dark:text-orange-400', 'bg-orange-50',
            'dark:bg-orange-900/20'
        ];
        const entregaActive = ['border-blue-500', 'text-blue-600', 'dark:text-blue-400', 'bg-blue-50',
            'dark:bg-blue-900/20'
        ];
        const inactiveClasses = ['border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-100',
            'dark:text-gray-400', 'dark:hover:bg-gray-700'
        ];

        if (tab === 'recepcion') {
            contentRecepcion.classList.remove('hidden');
            contentEntrega.classList.add('hidden');
            btnRecepcion.className =
                `px-4 py-2 text-xs font-bold uppercase border-b-2 border-orange-500 text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20 rounded-t-md transition-colors flex items-center gap-1`;
            btnEntrega.className =
                `px-4 py-2 text-xs font-bold uppercase border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 rounded-t-md transition-colors flex items-center gap-1`;
        } else {
            contentRecepcion.classList.add('hidden');
            contentEntrega.classList.remove('hidden');
            btnEntrega.className =
                `px-4 py-2 text-xs font-bold uppercase border-b-2 border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-t-md transition-colors flex items-center gap-1`;
            btnRecepcion.className =
                `px-4 py-2 text-xs font-bold uppercase border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 rounded-t-md transition-colors flex items-center gap-1`;
        }
    }

    function configurarFormularioPorArea(esEdicion = false) {
        const fieldsContabilidad = [
            'val_id_empresa_cliente',
            'val_tipo_regimen',
            'val_monto',
            'val_anio',
            'btn-select-libros'
        ];

        if (isUserAdminOrGerente || isContabilidad) {
            habilitarCampos(fieldsContabilidad, true);
        } else {
            habilitarCampos(fieldsContabilidad, false);
        }

        if (esEdicion) {
            document.getElementById('val_id_empresa_cliente').disabled = true;
        }
    }

    function habilitarCampos(arrayIds, habilitar) {
        arrayIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.disabled = !habilitar;
                if (!habilitar) {
                    el.classList.add('bg-gray-100', 'dark:bg-[#15161a]', 'cursor-not-allowed');
                } else {
                    el.classList.remove('bg-gray-100', 'dark:bg-[#15161a]', 'cursor-not-allowed');
                }
            }
        });
    }

    // Regla de Negocio: Régimen Especial no requiere monto
    function checkRegimen(regimen) {
        const wrapperMonto = document.getElementById('wrapper-monto');
        const inputMonto = document.getElementById('val_monto');
        if (regimen === 'especial') {
            wrapperMonto.classList.add('hidden');
            inputMonto.value = '';
            inputMonto.removeAttribute('required');
        } else {
            wrapperMonto.classList.remove('hidden');
            inputMonto.setAttribute('required', 'required');
        }
    }



    // Limpiar campos para registro nuevo
    function abrirModalCrear() {
        document.getElementById('formEstatusLibro').action = "{{ route('estatus_libros_contables.store') }}";
        document.getElementById('methodEstatusLibro').value = "POST";

        $('#val_id_empresa_cliente').val('').trigger('change');
        document.getElementById('val_tipo_regimen').value = "";
        document.getElementById('val_monto').value = "";
        document.getElementById('val_monto_display').value = "";

        // Poner año actual
        const anioActual = new Date().getFullYear();
        document.getElementById('val_anio').value = anioActual;

        document.getElementById('info_updated_contabilidad').textContent = '—';

        currentDetallesLibros = {};

        // Configurar botones de PDF
        document.getElementById('btn-pdf-pendientes').onclick = () => alert(
            'Debe guardar el registro primero para generar el PDF.');

        // Esconder checklists
        document.getElementById('wrapper-checklist-libros').classList.add('hidden');
        document.getElementById('wrapper-uits-info').classList.add('hidden');
        document.getElementById('hidden-obligados-inputs').innerHTML = '';

        configurarFormularioPorArea(false);
        checkRegimen("");

        toggleModal('modalEstatusLibro');
    }

    // Cargar datos en el modal de edición via AJAX
    function abrirModalEditar(id) {
        // 1. Preparar formulario base (limpiar) y abrir modal con overlay de carga
        document.getElementById('formEstatusLibro').action = `/estatus_libros_contables/${id}`;
        document.getElementById('methodEstatusLibro').value = "PUT";

        // Mostrar overlay de carga sobre el form
        const formEl = document.getElementById('formEstatusLibro');
        let overlay = document.getElementById('modal-loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'modal-loading-overlay';
            overlay.className =
                'absolute inset-0 bg-white/80 dark:bg-gray-900/80 flex items-center justify-center z-50 rounded-lg';
            overlay.innerHTML = `
                <div class="flex flex-col items-center gap-3">
                    <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-300 font-medium">Cargando datos...</span>
                </div>
            `;
            formEl.style.position = 'relative';
            formEl.appendChild(overlay);
        } else {
            overlay.classList.remove('hidden');
        }

        // Abrir modal inmediatamente (con overlay visible)
        toggleModal('modalEstatusLibro');

        // 2. Fetch data via AJAX
        fetch(`/estatus_libros_contables/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(item => {
                // Poblar formulario con datos
                $('#val_id_empresa_cliente').val(item.id_empresa_cliente).trigger('change');
                document.getElementById('val_tipo_regimen').value = item.tipo_regimen;
                document.getElementById('val_monto').value = item.monto ?? '';
                document.getElementById('val_monto_display').value = item.monto ? parseFloat(item.monto)
                    .toLocaleString(
                        'en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) : '';
                document.getElementById('val_anio').value = item.anio;
                if (document.getElementById('val_anio_obligacion')) {
                    document.getElementById('val_anio_obligacion').value = item.anio_obligacion ?? '';
                }

                checkRegimen(item.tipo_regimen);

                function formatDateDisplay(dateStr, userName) {
                    if (!dateStr) return '—';
                    const dateObj = new Date(dateStr);
                    const year = dateObj.getFullYear();
                    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const day = String(dateObj.getDate()).padStart(2, '0');
                    const hours = String(dateObj.getHours()).padStart(2, '0');
                    const minutes = String(dateObj.getMinutes()).padStart(2, '0');
                    let display = `${day}/${month}/${year} ${hours}:${minutes}`;
                    if (userName) {
                        display += ` por ${userName}`;
                    }
                    return display;
                }

                document.getElementById('info_updated_contabilidad').textContent = formatDateDisplay(item
                    .updated_at, null);

                currentDetallesLibros = item.detalles_libros || {};

                // Configurar botones de PDF
                document.getElementById('btn-pdf-pendientes').onclick = () => window.open(
                    `/estatus_libros_contables/${item.id_estatus_libros}/pdf/pendientes`, '_blank');

                configurarFormularioPorArea(true);

                // Cargar libros del tramo y libros seleccionados
                cargarLibrosEdicion(item);

                // Quitar overlay de carga
                overlay.classList.add('hidden');
            })
            .catch(err => {
                console.error('Error al cargar datos para edición:', err);
                overlay.classList.add('hidden');
                alert('Error al cargar los datos del registro.');
            });
    }

    // Obtener libros por defecto mediante AJAX
    function cargarLibrosDefault() {
        const regimen = document.getElementById('val_tipo_regimen').value;
        const monto = document.getElementById('val_monto').value;
        const anio = document.getElementById('val_anio').value;
        const idEmpresa = document.getElementById('val_id_empresa_cliente').value;

        if (!idEmpresa) {
            alert("Por favor, selecciona una empresa primero.");
            return;
        }

        if (!regimen) {
            alert("Por favor, selecciona un tipo de régimen.");
            return;
        }

        if (regimen !== 'especial' && !monto) {
            alert("Por favor, ingresa el monto de ingresos anuales.");
            return;
        }

        fetch("{{ route('estatus_libros.get_default_books') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    tipo_regimen: regimen,
                    monto: monto,
                    anio: anio,
                    id_empresa: idEmpresa
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Mostrar Info UIT
                    const wrapperInfo = document.getElementById('wrapper-uits-info');
                    if (data.valor_uit) {
                        document.getElementById('info-valor-uit').textContent =
                            `S/ ${parseFloat(data.valor_uit).toLocaleString('es-PE', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
                        document.getElementById('info-uits-calculadas').textContent = Math.round(parseFloat(data
                            .uits));
                        document.getElementById('info-tramo').textContent = data.tramo;
                        wrapperInfo.classList.remove('hidden');
                    } else {
                        wrapperInfo.classList.add('hidden');
                    }

                    // Render lists
                    renderCheckedList(data.libros_obligados, data.todos_libros, []);
                } else {
                    alert("Error al cargar los libros por defecto.");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error al procesar la solicitud.");
            });
    }

    // Carga los libros en el modo Edición, marcando los correspondientes
    function cargarLibrosEdicion(item) {
        const regimen = item.tipo_regimen;
        const monto = item.monto ?? 0;
        const anio = item.anio;
        const idEmpresa = item.id_empresa_cliente;
        const selectedIds = (item.id_libros ?? []).map(id => parseInt(id));

        fetch("{{ route('estatus_libros.get_default_books') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    tipo_regimen: regimen,
                    monto: monto,
                    anio: anio,
                    id_empresa: idEmpresa
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Info UIT
                    const wrapperInfo = document.getElementById('wrapper-uits-info');
                    if (data.valor_uit) {
                        document.getElementById('info-valor-uit').textContent =
                            `S/ ${parseFloat(data.valor_uit).toLocaleString('es-PE', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
                        document.getElementById('info-uits-calculadas').textContent = Math.round(parseFloat(data
                            .uits));
                        document.getElementById('info-tramo').textContent = data.tramo;
                        wrapperInfo.classList.remove('hidden');
                    } else {
                        wrapperInfo.classList.add('hidden');
                    }

                    renderCheckedList(data.libros_obligados, data.todos_libros, selectedIds);
                }
            })
            .catch(err => console.error(err));
    }

    // Renderizar los checklists
    function renderCheckedList(librosObligados, todosLibros, selectedIdsParam) {
        const listObligadosDiv = document.getElementById('list-libros-obligados');
        const listAdicionalesDiv = document.getElementById('list-libros-adicionales');
        const hiddenInputsDiv = document.getElementById('hidden-obligados-inputs');
        const tabsHeaderDiv = document.getElementById('tabs-header-adicionales');

        listObligadosDiv.innerHTML = '';
        listAdicionalesDiv.innerHTML = '';
        if (tabsHeaderDiv) tabsHeaderDiv.innerHTML = '';
        hiddenInputsDiv.innerHTML = '';

        const obligadosIds = librosObligados.map(l => parseInt(l.id_libro));
        const selectedIds = selectedIdsParam.map(id => parseInt(id));

        // Generar inputs ocultos para libros_obligados array
        librosObligados.forEach(l => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'libros_obligados[]';
            hiddenInput.value = l.nombre;
            hiddenInputsDiv.appendChild(hiddenInput);
        });

        // 1. Mostrar Obligados
        if (librosObligados.length === 0) {
            listObligadosDiv.innerHTML =
                '<p class="text-xs text-gray-400 italic">No hay libros cargados por defecto para este tramo.</p>';
        } else {
            librosObligados.forEach(libro => {
                const checked = (selectedIds.length === 0 || selectedIds.includes(parseInt(libro.id_libro)));

                // Para el área LEGAL en "Obligatorios por Tramo":
                // Solo pueden editar Legalización y Resp. Legal.
                let isDisabledMain; 
                let isDisabledCheckbox;
                let isDisabledLegal;

                if (isLegal) {
                    // Área LEGAL: puede editar todo
                    isDisabledMain = false;
                    isDisabledLegal = false;
                    isDisabledCheckbox = false;
                } else if (isUserAdminOrGerente) {
                    isDisabledMain = false;
                    isDisabledCheckbox = false;
                    isDisabledLegal = false;
                } else if (isContabilidad) {
                    if (libro.area === 'legal') {
                        // Contabilidad no edita libros legales
                        isDisabledMain = true;
                        isDisabledCheckbox = true;
                        isDisabledLegal = true;
                    } else {
                        isDisabledMain = false;
                        isDisabledCheckbox = false;
                        isDisabledLegal = false;
                    }
                } else {
                    isDisabledMain = true;
                    isDisabledCheckbox = true;
                    isDisabledLegal = true;
                }

                const detalles = currentDetallesLibros[libro.id_libro] || {};
                const isElectronico = (detalles['is_electronico'] == '1' || detalles['is_electronico'] === 1 ||
                    detalles['is_electronico'] === true) ? '1' : '0';
                const entregadoPorCliente = (detalles['entregado_por_cliente'] == '1' || detalles[
                        'entregado_por_cliente'] === 1 || detalles['entregado_por_cliente'] === true ||
                    detalles['entregado_por_cliente'] === 'si') ? '1' : '0';
                const estatusLegalizacion = detalles['estatus_legalizacion'] || 'n/a';
                const responsableLegalizacion = detalles['responsable_legalizacion'] || '-';
                const entregadoAlCliente = detalles['entregado_al_cliente'] || 'no';

                // Legalización bloqueada si ya está en 'si' y no es admin/gerente
                const finalDisabledLegal = (estatusLegalizacion === 'si' && !isUserAdminOrGerente) ?
                    'disabled' : isDisabledLegal ? 'disabled' : '';

                const itemDiv = document.createElement('div');
                itemDiv.className =
                    "flex flex-col gap-1 text-sm bg-gray-50 dark:bg-[#1a1c21] p-2 rounded border border-gray-200 dark:border-gray-700";
                itemDiv.innerHTML = `
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="id_libros[]" value="${libro.id_libro}" id="chk_lib_ob_${libro.id_libro}"
                            class="rounded border-gray-300 focus:ring-emerald-500 ${libro.area === 'legal' ? 'text-purple-600' : 'text-emerald-600'}" ${checked ? 'checked' : ''} ${isDisabledCheckbox ? 'disabled' : ''}>
                        <label for="chk_lib_ob_${libro.id_libro}" class="text-gray-700 dark:text-gray-300 font-medium flex-1">
                            ${libro.nombre}
                            ${libro.area === 'legal' ? '<span class="text-[10px] bg-purple-100 text-purple-800 px-1 rounded ml-1 font-normal">Legal</span>' : ''}
                        </label>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 pl-6 mt-1">
                        <div class="flex items-center gap-1 text-xs">
                            <span class="text-gray-500">¿Electrónico?</span>
                            <select name="detalles_libros[${libro.id_libro}][is_electronico]"
    class="is-electronico border-gray-300 rounded text-xs py-0 pl-1 pr-6 dark:bg-[#202227] dark:border-gray-600" ${isDisabledMain ? 'disabled' : ''}>
                                <option value="0" ${isElectronico === '0' ? 'selected' : ''}>No</option>
                                <option value="1" ${isElectronico === '1' ? 'selected' : ''}>Sí</option>
                            </select>
                            ${isDisabledMain ? `<input type="hidden" name="detalles_libros[${libro.id_libro}][is_electronico]" value="${isElectronico}">` : ''}
                        </div>
                        <div class="flex items-center gap-1 text-xs">
                            <span class="text-gray-500">¿Entregado por Cliente?</span>
                            <select name="detalles_libros[${libro.id_libro}][entregado_por_cliente]"
    class="entregado-cliente border-gray-300 rounded text-xs py-0 pl-1 pr-6 dark:bg-[#202227] dark:border-gray-600" ${isDisabledMain ? 'disabled' : ''}>
                                <option value="0" ${entregadoPorCliente === '0' ? 'selected' : ''}>No</option>
                                <option value="1" ${entregadoPorCliente === '1' ? 'selected' : ''}>Sí</option>
                            </select>
                            ${isDisabledMain ? `<input type="hidden" name="detalles_libros[${libro.id_libro}][entregado_por_cliente]" value="${entregadoPorCliente}">` : ''}
                        </div>
                        <div class="flex items-center gap-1 text-xs">
                            <span class="text-gray-500">Legalización:</span>
                            <select name="detalles_libros[${libro.id_libro}][estatus_legalizacion]" class="border-gray-300 rounded text-xs py-0 pl-1 pr-6 dark:bg-[#202227] dark:border-gray-600" ${finalDisabledLegal}>
                                <option value="n/a" ${estatusLegalizacion === 'n/a' ? 'selected' : ''}>N/A</option>
                                <option value="si" ${estatusLegalizacion === 'si' ? 'selected' : ''}>Sí</option>
                                <option value="no" ${estatusLegalizacion === 'no' ? 'selected' : ''}>No</option>
                            </select>
                            ${finalDisabledLegal ? `<input type="hidden" name="detalles_libros[${libro.id_libro}][estatus_legalizacion]" value="${estatusLegalizacion}">` : ''}
                        </div>
                        <div class="flex items-center gap-1 text-xs">
                            <span class="text-gray-500">Resp. Legal:</span>
                            <select name="detalles_libros[${libro.id_libro}][responsable_legalizacion]" class="border-gray-300 rounded text-xs py-0 pl-1 pr-6 dark:bg-[#202227] dark:border-gray-600" ${finalDisabledLegal}>
                                <option value="-" ${responsableLegalizacion === '-' ? 'selected' : ''}>-</option>
                                <option value="asecon" ${responsableLegalizacion === 'asecon' ? 'selected' : ''}>Asecon</option>
                                <option value="cliente" ${responsableLegalizacion === 'cliente' ? 'selected' : ''}>Cliente</option>
                            </select>
                            ${finalDisabledLegal ? `<input type="hidden" name="detalles_libros[${libro.id_libro}][responsable_legalizacion]" value="${responsableLegalizacion}">` : ''}
                        </div>
                        <div class="flex items-center gap-1 text-xs">
                            <span class="text-gray-500">Entregado al Cliente:</span>
                            <select name="detalles_libros[${libro.id_libro}][entregado_al_cliente]" class="border-gray-300 rounded text-xs py-0 pl-1 pr-6 dark:bg-[#202227] dark:border-gray-600" ${isDisabledMain ? 'disabled' : ''}>
                                <option value="no" ${entregadoAlCliente === 'no' ? 'selected' : ''}>No</option>
                                <option value="si" ${entregadoAlCliente === 'si' ? 'selected' : ''}>Sí</option>
                            </select>
                            ${isDisabledMain ? `<input type="hidden" name="detalles_libros[${libro.id_libro}][entregado_al_cliente]" value="${entregadoAlCliente}">` : ''}
                        </div>
                    </div>
                `;
                listObligadosDiv.appendChild(itemDiv);
            });
        }

        // 2. Mostrar Adicionales (Todos los que NO sean obligados)
        const adicionales = todosLibros.filter(l => !obligadosIds.includes(parseInt(l.id_libro)));

        if (adicionales.length === 0) {
            listAdicionalesDiv.innerHTML =
                '<p class="text-xs text-gray-400 italic">No hay libros adicionales disponibles.</p>';
        } else {
            // Group by tipo_regimen filtrando por el régimen seleccionado + legal (y 'otro' solo si NO es especial)
            const grouped = {};
            const regimenActual = document.getElementById('val_tipo_regimen').value.toLowerCase();
            const allowedTabs = regimenActual === 'especial' ? [regimenActual, 'otro', 'legal'] : [regimenActual,
                'otro', 'legal'
            ];

            adicionales.forEach(l => {
                let reg = (l.tipo_regimen || 'otro').toLowerCase();
                if (l.area === 'legal') reg = 'legal';

                if (allowedTabs.includes(reg)) {
                    if (!grouped[reg]) grouped[reg] = [];
                    grouped[reg].push(l);
                }
            });
            // Asegurar que existan siempre, aunque estén vacías, según requerimiento
            if (regimenActual !== 'especial' && !grouped['otro']) grouped['otro'] = [];
            if (!grouped['legal']) grouped['legal'] = [];
            if (!grouped[regimenActual] && regimenActual) grouped[regimenActual] = [];

            let firstTab = true;
            for (const [regimen, libros] of Object.entries(grouped)) {
                // Sort by tramo
                libros.sort((a, b) => {
                    const tramoA = a.tramo ? a.tramo.toLowerCase() : '';
                    const tramoB = b.tramo ? b.tramo.toLowerCase() : '';
                    if (tramoA === 'otro') return 1;
                    if (tramoB === 'otro') return -1;
                    return tramoA.localeCompare(tramoB);
                });

                // Create Tab button
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className =
                    `px-2 py-1 text-[11px] font-bold rounded-t-md border-b-2 transition-colors uppercase ${firstTab ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'}`;
                btn.textContent = regimen;
                btn.dataset.target = `tab-content-${regimen}`;
                btn.onclick = function() {
                    Array.from(tabsHeaderDiv.children).forEach(c => {
                        c.classList.remove('border-blue-500', 'text-blue-600', 'dark:text-blue-400',
                            'bg-blue-50', 'dark:bg-blue-900/20');
                        c.classList.add('border-transparent', 'text-gray-500');
                    });
                    this.classList.remove('border-transparent', 'text-gray-500');
                    this.classList.add('border-blue-500', 'text-blue-600', 'dark:text-blue-400', 'bg-blue-50',
                        'dark:bg-blue-900/20');

                    Array.from(listAdicionalesDiv.children).forEach(c => c.classList.add('hidden'));
                    document.getElementById(this.dataset.target).classList.remove('hidden');
                };
                if (tabsHeaderDiv) tabsHeaderDiv.appendChild(btn);

                // Create Tab Content
                const contentDiv = document.createElement('div');
                contentDiv.id = `tab-content-${regimen}`;
                contentDiv.className = `space-y-2 ${firstTab ? '' : 'hidden'}`;

                libros.forEach(libro => {
                    const checked = selectedIds.includes(parseInt(libro.id_libro));

                    let isDisabledCheckbox, isDisabledMain, isDisabledLegalFields;
                    if (libro.area === 'legal') {
                        isDisabledCheckbox    = !(isUserAdminOrGerente || isLegal);
                        isDisabledMain        = !(isUserAdminOrGerente || isLegal);
                        isDisabledLegalFields = !(isUserAdminOrGerente || isLegal);
                    } else {
                        isDisabledCheckbox    = !(isUserAdminOrGerente || isContabilidad || isLegal);
                        isDisabledMain        = !(isUserAdminOrGerente || isContabilidad || isLegal);
                        isDisabledLegalFields = !(isUserAdminOrGerente || isContabilidad || isLegal);
                    }

                    const tramoText = (libro.tramo && libro.tramo.toLowerCase() === 'otro') ? 'Otro' : 'T' + (
                        libro.tramo || '');

                    const detalles = currentDetallesLibros[libro.id_libro] || {};
                    const isElectronico = (detalles['is_electronico'] == '1' || detalles['is_electronico'] ===
                        1 || detalles['is_electronico'] === true) ? '1' : '0';
                    const entregadoPorCliente = (detalles['entregado_por_cliente'] == '1' || detalles[
                            'entregado_por_cliente'] === 1 || detalles['entregado_por_cliente'] === true ||
                        detalles['entregado_por_cliente'] === 'si') ? '1' : '0';
                    const estatusLegalizacion = detalles['estatus_legalizacion'] || 'n/a';
                    const responsableLegalizacion = detalles['responsable_legalizacion'] || '-';
                    const entregadoAlCliente = detalles['entregado_al_cliente'] || 'no';

                    const finalDisabledLegal = (estatusLegalizacion === 'si' && !isUserAdminOrGerente) ?
                        'disabled' : isDisabledLegalFields ? 'disabled' : '';

                    const itemDiv = document.createElement('div');
                    itemDiv.className =
                        "flex flex-col gap-1 text-sm bg-gray-50 dark:bg-[#1a1c21] p-2 rounded border border-gray-200 dark:border-gray-700";
                    itemDiv.innerHTML = `
                        <div class="flex items-start gap-2">
                            <input type="checkbox" name="id_libros[]" value="${libro.id_libro}" id="chk_lib_ad_${libro.id_libro}"
                                class="mt-1 rounded border-gray-300 focus:ring-blue-500 ${libro.area === 'legal' ? 'text-purple-600' : 'text-blue-600'}" ${checked ? 'checked' : ''} ${isDisabledCheckbox ? 'disabled' : ''}>
                            <label for="chk_lib_ad_${libro.id_libro}" class="text-gray-700 dark:text-gray-300 leading-tight flex-1">
                                ${libro.nombre} 
                                <span class="text-[10px] text-gray-400 font-medium ml-1">(${tramoText})</span>
                                ${libro.area === 'legal' ? '<span class="text-[10px] bg-purple-100 text-purple-800 px-1 rounded ml-1">Legal</span>' : ''}
                            </label>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 pl-6 mt-1">
                            <div class="flex items-center gap-1 text-xs">
                                <span class="text-gray-500">¿Electrónico?</span>
                                <select name="detalles_libros[${libro.id_libro}][is_electronico]" class="is-electronico border-gray-300 rounded text-xs py-0 pl-1 pr-6 dark:bg-[#202227] dark:border-gray-600" ${isDisabledMain ? 'disabled' : ''}>
                                    <option value="0" ${isElectronico === '0' ? 'selected' : ''}>No</option>
                                    <option value="1" ${isElectronico === '1' ? 'selected' : ''}>Sí</option>
                                </select>
                                ${isDisabledMain ? `<input type="hidden" name="detalles_libros[${libro.id_libro}][is_electronico]" value="${isElectronico}">` : ''}
                            </div>
                            <div class="flex items-center gap-1 text-xs">
                                <span class="text-gray-500">¿Entregado por Cliente?</span>
                                <select name="detalles_libros[${libro.id_libro}][entregado_por_cliente]" class="entregado-cliente border-gray-300 rounded text-xs py-0 pl-1 pr-6 dark:bg-[#202227] dark:border-gray-600" ${isDisabledMain ? 'disabled' : ''}>
                                    <option value="0" ${entregadoPorCliente === '0' ? 'selected' : ''}>No</option>
                                    <option value="1" ${entregadoPorCliente === '1' ? 'selected' : ''}>Sí</option>
                                </select>
                                ${isDisabledMain ? `<input type="hidden" name="detalles_libros[${libro.id_libro}][entregado_por_cliente]" value="${entregadoPorCliente}">` : ''}
                            </div>
                            <div class="flex items-center gap-1 text-xs">
                                <span class="text-gray-500">Legalización:</span>
                                <select name="detalles_libros[${libro.id_libro}][estatus_legalizacion]" class="border-gray-300 rounded text-xs py-0 pl-1 pr-6 dark:bg-[#202227] dark:border-gray-600" ${finalDisabledLegal}>
                                    <option value="n/a" ${estatusLegalizacion === 'n/a' ? 'selected' : ''}>N/A</option>
                                    <option value="si" ${estatusLegalizacion === 'si' ? 'selected' : ''}>Sí</option>
                                    <option value="no" ${estatusLegalizacion === 'no' ? 'selected' : ''}>No</option>
                                </select>
                                ${finalDisabledLegal ? `<input type="hidden" name="detalles_libros[${libro.id_libro}][estatus_legalizacion]" value="${estatusLegalizacion}">` : ''}
                            </div>
                            <div class="flex items-center gap-1 text-xs">
                                <span class="text-gray-500">Resp. Legal:</span>
                                <select name="detalles_libros[${libro.id_libro}][responsable_legalizacion]" class="border-gray-300 rounded text-xs py-0 pl-1 pr-6 dark:bg-[#202227] dark:border-gray-600" ${finalDisabledLegal}>
                                    <option value="-" ${responsableLegalizacion === '-' ? 'selected' : ''}>-</option>
                                    <option value="asecon" ${responsableLegalizacion === 'asecon' ? 'selected' : ''}>Asecon</option>
                                    <option value="cliente" ${responsableLegalizacion === 'cliente' ? 'selected' : ''}>Cliente</option>
                                </select>
                                ${finalDisabledLegal ? `<input type="hidden" name="detalles_libros[${libro.id_libro}][responsable_legalizacion]" value="${responsableLegalizacion}">` : ''}
                            </div>
                            <div class="flex items-center gap-1 text-xs">
                                <span class="text-gray-500">Entregado al Cliente:</span>
                                <select name="detalles_libros[${libro.id_libro}][entregado_al_cliente]" class="border-gray-300 rounded text-xs py-0 pl-1 pr-6 dark:bg-[#202227] dark:border-gray-600" ${isDisabledMain ? 'disabled' : ''}>
                                    <option value="no" ${entregadoAlCliente === 'no' ? 'selected' : ''}>No</option>
                                    <option value="si" ${entregadoAlCliente === 'si' ? 'selected' : ''}>Sí</option>
                                </select>
                                ${isDisabledMain ? `<input type="hidden" name="detalles_libros[${libro.id_libro}][entregado_al_cliente]" value="${entregadoAlCliente}">` : ''}
                            </div>
                        </div>
                    `;
                    contentDiv.appendChild(itemDiv);
                });

                listAdicionalesDiv.appendChild(contentDiv);
                firstTab = false;
            }
        }

        document.getElementById('wrapper-checklist-libros').classList.remove('hidden');
    }

        document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('is-electronico')) return;

        const contenedor = e.target.closest('.flex').parentElement;
        const entregadoSelect = contenedor.querySelector('.entregado-cliente');

        entregadoSelect.value = e.target.value;
    });

    // Modal Secundario para crear nuevo libro
    function abrirModalNuevoLibro() {
        // Reset form
        document.getElementById('new_nombre_libro').value = '';
        document.getElementById('new_regimen_libro').value = '';
        document.getElementById('new_tramo_libro').value = '';
        document.getElementById('new_area_libro').value = 'contabilidad';

        toggleModal('modalNuevoLibro');
    }

    // Enviar AJAX para registrar nuevo libro y añadirlo al checklist
    function guardarNuevoLibroAjax(event) {
        event.preventDefault();

        const nombre = document.getElementById('new_nombre_libro').value;
        const regimen = document.getElementById('new_regimen_libro').value;
        const tramo = document.getElementById('new_tramo_libro').value;
        const area = document.getElementById('new_area_libro').value;

        fetch("{{ route('estatus_libros.store_libro_ajax') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nombre: nombre,
                    tipo_regimen: regimen,
                    tramo: tramo,
                    area: area
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Cerrar modal secundario
                    toggleModal('modalNuevoLibro');

                    // Mostrar Toast usando ajax-toast si está disponible
                    if (typeof window.showAjaxToast !== 'undefined') {
                        window.showAjaxToast.success('Libro agregado correctamente.');
                    }

                    // Recargar listados de libros del modal principal agregando este nuevo libro y seleccionándolo
                    const selectRegimen = document.getElementById('val_tipo_regimen').value;
                    const selectMonto = document.getElementById('val_monto').value;
                    const selectAnio = document.getElementById('val_anio').value;
                    const selectIdEmpresa = document.getElementById('val_id_empresa_cliente').value;

                    // Capturar IDs actualmente seleccionados
                    const selectedCheckboxes = document.querySelectorAll('input[name="id_libros[]"]:checked');
                    const selectedIds = Array.from(selectedCheckboxes).map(chk => parseInt(chk.value));

                    // Agregar el ID del nuevo libro
                    selectedIds.push(data.libro.id_libro);

                    // Re-solicitar lista de default books para regenerar con el nuevo libro incluido en todos_libros
                    fetch("{{ route('estatus_libros.get_default_books') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                tipo_regimen: selectRegimen,
                                monto: selectMonto || 0,
                                anio: selectAnio,
                                id_empresa: selectIdEmpresa
                            })
                        })
                        .then(res => res.json())
                        .then(data2 => {
                            if (data2.success) {
                                renderCheckedList(data2.libros_obligados, data2.todos_libros, selectedIds);
                            }
                        });
                } else {
                    alert("Error al guardar el nuevo libro.");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error de conexión al guardar el libro.");
            });
    }

    function formatMontoDisplay(input) {
        let val = input.value.replace(/[^0-9.]/g, '');
        const parts = val.split('.');
        if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');

        document.getElementById('val_monto').value = val;

        if (val) {
            if (val.includes('.')) {
                const [intPart, decPart] = val.split('.');
                const formattedInt = intPart ? parseInt(intPart).toLocaleString('en-US') : '0';
                input.value = formattedInt + '.' + decPart.substring(0, 2);
            } else {
                input.value = parseInt(val).toLocaleString('en-US');
            }
        } else {
            input.value = '';
        }
    }

    // ===== MODAL DETALLES DE LIBROS =====
    let currentDetallesId = null;

    function abrirModalDetalles(id) {
        currentDetallesId = id;

        // Mostrar loading, ocultar contenido
        document.getElementById('detalles-libros-loading').classList.remove('hidden');
        document.getElementById('detalles-libros-content').classList.add('hidden');

        // Configurar botón editar
        document.getElementById('btn-det-editar').onclick = function() {
            toggleModal('modalDetallesLibros');
            abrirModalEditar(id);
        };

        // Configurar botón PDF pendientes
        document.getElementById('btn-det-pdf').onclick = function() {
            window.open(`/estatus_libros_contables/${id}/pdf/pendientes`, '_blank');
        };

        toggleModal('modalDetallesLibros');

        // Fetch datos del estatus
        fetch(`/estatus_libros_contables/${id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(item => {
            // Encabezado
            document.getElementById('det-empresa').textContent = item.empresa_nombre || '—';

            const regimenColors = {
                'especial': 'bg-purple-100 text-purple-800',
                'mype':     'bg-blue-100 text-blue-800',
                'general':  'bg-gray-100 text-gray-800'
            };
            const regimenColor = regimenColors[item.tipo_regimen] || 'bg-gray-100 text-gray-800';
            document.getElementById('det-regimen').innerHTML =
                `<span class="px-2 py-0.5 rounded text-xs font-bold uppercase ${regimenColor}">${item.tipo_regimen || '—'}</span>`;
            document.getElementById('det-anio').textContent = item.anio || '—';

            // Grid de libros
            const grid = document.getElementById('det-libros-grid');
            grid.innerHTML = '';

            const libros = item.libros || [];
            const detalles = item.detalles_libros || {};

            if (libros.length === 0) {
                grid.innerHTML = '<p class="text-sm text-gray-400 italic col-span-full text-center py-6">No hay libros asociados a este registro.</p>';
            } else {
                libros.forEach(libro => {
                    const det = detalles[libro.id_libro] || {};
                    const isElectronico  = (det.is_electronico == 1 || det.is_electronico === true);
                    const entregadoPor   = (det.entregado_por_cliente === 'si' || det.entregado_por_cliente == 1);
                    const entregadoAl    = (det.entregado_al_cliente === 'si');
                    const legalizacion   = det.estatus_legalizacion || 'n/a';
                    const respLegal      = det.responsable_legalizacion || '-';

                    // Color de la tarjeta según estado
                    let borderColor, dotColor;
                    if (entregadoAl) {
                        borderColor = 'border-blue-300 dark:border-blue-600';
                        dotColor    = 'bg-blue-400';
                    } else if (entregadoPor) {
                        borderColor = 'border-green-300 dark:border-green-600';
                        dotColor    = 'bg-green-400';
                    } else {
                        borderColor = 'border-red-200 dark:border-red-700';
                        dotColor    = 'bg-red-400';
                    }

                    // Badge legal
                    const legalBadge = libro.area === 'legal'
                        ? '<span class="text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded font-semibold">Legal</span>'
                        : '';

                    // Badge electrónico
                    const elecBadge = isElectronico
                        ? '<span class="text-[10px] bg-sky-100 text-sky-700 px-1.5 py-0.5 rounded">Electrónico</span>'
                        : '';

                    // Badge legalización
                    const legColors = { 'si': 'bg-emerald-100 text-emerald-700', 'no': 'bg-red-100 text-red-700', 'n/a': 'bg-gray-100 text-gray-500' };
                    const legLabel  = { 'si': 'Legalizado', 'no': 'No Legalizado', 'n/a': 'Legal: N/A' };
                    const legBadge  = `<span class="text-[10px] ${legColors[legalizacion] || 'bg-gray-100 text-gray-500'} px-1.5 py-0.5 rounded">${legLabel[legalizacion] || legalizacion}</span>`;

                    const card = document.createElement('div');
                    card.className = `bg-white dark:bg-[#1a1c21] border ${borderColor} rounded-lg p-3 space-y-2 shadow-sm`;
                    card.innerHTML = `
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-2.5 h-2.5 rounded-full mt-0.5 flex-shrink-0 ${dotColor}"></span>
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 leading-tight">${libro.nombre}</span>
                            </div>
                            <div class="flex flex-wrap gap-1 justify-end">
                                ${legalBadge}
                                ${elecBadge}
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1 pl-4">
                            ${legBadge}
                            ${respLegal !== '-' ? `<span class="text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded">Resp: ${respLegal}</span>` : ''}
                            ${entregadoPor ? '<span class="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded">Recibido del cliente</span>' : '<span class="text-[10px] bg-orange-100 text-orange-700 px-1.5 py-0.5 rounded">Pendiente de recibir</span>'}
                            ${entregadoAl  ? '<span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Entregado al cliente</span>' : ''}
                        </div>
                    `;
                    grid.appendChild(card);
                });
            }

            document.getElementById('detalles-libros-loading').classList.add('hidden');
            document.getElementById('detalles-libros-content').classList.remove('hidden');
        })
        .catch(err => {
            console.error('Error al cargar detalles:', err);
            document.getElementById('detalles-libros-loading').innerHTML =
                '<p class="text-red-500 text-sm text-center py-8">Error al cargar los detalles.</p>';
        });
    }
</script>

<!-- Modal Cargos de Libros -->
<x-modal-show id="modalCargos" title="Cargos de Libros Contables" size="large">
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Historial de Cargos</h3>
            @if ($isRecepcion || $isAdminOrGerente)
                <button type="button" onclick="prepararNuevoCargo()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md text-sm transition-colors shadow-sm flex items-center gap-1">
                    <i class="bi bi-plus-circle"></i> Nuevo Cargo
                </button>
            @endif
        </div>

        <!-- Formulario Cargo -->
        <form id="formNuevoCargo"
            class="hidden bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 space-y-4 shadow-sm"
            onsubmit="guardarCargo(event)">
            <input type="hidden" id="cargo_id_estatus" name="id_estatus_libros_contables">
            <input type="hidden" id="cargo_id" name="cargo_id">

            <h4 id="formCargoTitle" class="font-semibold text-sm text-gray-700 dark:text-gray-300 border-b pb-2">Crear Nuevo Cargo</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Tipo" />
                    <select id="cargo_tipo" name="tipo" onchange="renderizarCheckboxesLibros()"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        required>
                        <option value="">Seleccione...</option>
                        <option value="recepcion">Recepción</option>
                        <option value="entrega">Entrega</option>
                    </select>
                </div>
                <div>
                    <x-input-label value="Nro. Cargo" />
                    <x-text-input type="text" id="cargo_nro" name="nro_cargo" class="block w-full mt-1" />
                </div>
                <div>
                    <x-input-label value="Fecha y Hora" />
                    <div class="flex gap-2 items-center mt-1">
                        <x-text-input type="datetime-local" id="cargo_fecha" name="fecha" class="block w-full"
                            required step="1" />
                        <button type="button" onclick="setCargoFechaActual()"
                            class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-2 py-2 rounded text-xs whitespace-nowrap"
                            title="Establecer fecha y hora actual">Actual</button>
                    </div>
                </div>
                <div>
                    <x-input-label value="Archivo (Opcional)" />
                    <input type="file" id="cargo_archivo" name="archivo"
                        class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                    <div id="container-archivo-actual" class="mt-1 text-xs"></div>
                </div>
                <div class="md:col-span-2">
                    <x-input-label value="Seleccionar Libros para el Cargo" />
                    <div id="container-libros-cargo"
                        class="mt-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 bg-white dark:bg-gray-900 p-3 rounded border border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 italic text-center w-full sm:col-span-2 md:col-span-3">
                            Seleccione un "Tipo" primero para ver los libros disponibles.</p>
                    </div>
                </div>
            </div>

            {{-- Sección: Libros Adicionales (solo en PDF) --}}
            <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <i class="bi bi-journal-plus text-indigo-500 mr-1"></i>
                        Libros Adicionales <span class="text-xs font-normal text-gray-400">(solo aparecen en el PDF)</span>
                    </span>
                    <button type="button" onclick="agregarFilaLibroRecepcion()"
                        class="flex items-center gap-1 bg-indigo-500 hover:bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-md transition-colors shadow-sm">
                        <i class="bi bi-plus-lg"></i> Agregar libro
                    </button>
                </div>
                <div id="container-libros-recepcion-adicionales" class="space-y-2">
                    {{-- Las filas se agregan dinámicamente con JS --}}
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('formNuevoCargo').classList.add('hidden');limpiarLibrosRecepcionAdicionales()"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm hover:bg-gray-300 transition-colors">Cancelar</button>
                <button type="submit"
                    class="px-4 py-2 bg-emerald-500 text-white rounded-md text-sm hover:bg-emerald-600 transition-colors shadow flex items-center gap-1">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </form>

        <!-- Tabla de Cargos -->
        <div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg">
            <table class="min-w-full table-auto text-sm">
                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Nro Cargo</th>
                        <th class="px-4 py-2 text-left">Archivo</th>
                        <th class="px-4 py-2 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-cargos"
                    class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-600 dark:text-gray-400">
                    <!-- Llenado via JS -->
                </tbody>
            </table>
        </div>
    </div>
</x-modal-show>


<script>
    let currentIdEstatus = null;
    let currentEstatusData = null; // Guarda los datos del estatus y libros actuales para renderizar los checkboxes
    let currentCargoEditing = null; // Guarda los datos del cargo en edición

    function renderizarCheckboxesLibros(cargoEnEdicion = null) {
        const cargoToUse = cargoEnEdicion !== null ? cargoEnEdicion : currentCargoEditing;
        const tipo = document.getElementById('cargo_tipo').value;
        const container = document.getElementById('container-libros-cargo');

        if (!tipo || !currentEstatusData) {
            container.innerHTML =
                '<p class="text-sm text-gray-500 italic text-center w-full sm:col-span-2 md:col-span-3">Seleccione un "Tipo" primero para ver los libros disponibles.</p>';
            return;
        }

        const libros = currentEstatusData.libros || [];
        const detalles = currentEstatusData.detalles_libros || {};
        let html = '';

        const cargoLibrosIds = (cargoToUse && cargoToUse.libros) ? cargoToUse.libros.map(id => Number(id)) : [];
        const cargoNroLibro = (cargoToUse && cargoToUse.nro_libro) ? cargoToUse.nro_libro : {};
        const cargoFolioLibro = (cargoToUse && cargoToUse.folio_libro) ? cargoToUse.folio_libro : {};

        let countAvailable = 0;

        libros.forEach(libro => {
            const idLibro = Number(libro.id_libro);
            const detalle = detalles[idLibro] || {};
            const entregadoPorCliente = (detalle.entregado_por_cliente || 'no') === 'si';
            const entregadoAlCliente = (detalle.entregado_al_cliente || 'no') === 'si';

            const perteneceAlCargo = cargoLibrosIds.includes(idLibro);

            let show = false;
            let statusText = '';

            if (tipo === 'recepcion') {
                if (!entregadoPorCliente || perteneceAlCargo) {
                    show = true;
                    statusText = perteneceAlCargo ? 'En este cargo' : 'Por recibir';
                }
            } else if (tipo === 'entrega') {
                if ((entregadoPorCliente && !entregadoAlCliente) || perteneceAlCargo) {
                    show = true;
                    statusText = perteneceAlCargo ? 'En este cargo' : 'Listo para entregar';
                }
            }

            if (show) {
                countAvailable++;
                const chkId = `chk_cargo_lib_${idLibro}`;
                const isChecked = perteneceAlCargo;
                let nroVals = cargoNroLibro[idLibro] || cargoNroLibro[String(idLibro)];
                let folioVals = cargoFolioLibro[idLibro] || cargoFolioLibro[String(idLibro)];
                
                if (!Array.isArray(nroVals)) nroVals = nroVals ? [nroVals] : [''];
                if (!Array.isArray(folioVals)) folioVals = folioVals ? [folioVals] : [''];
                
                const maxLen = Math.max(nroVals.length, folioVals.length);
                if (maxLen === 0) {
                    nroVals = [''];
                    folioVals = [''];
                } else {
                    while (nroVals.length < maxLen) nroVals.push('');
                    while (folioVals.length < maxLen) folioVals.push('');
                }

                let filasNroFolioHtml = '';
                for(let i=0; i<maxLen; i++) {
                    filasNroFolioHtml += generarFilaNroFolioHtml(idLibro, nroVals[i], folioVals[i], isChecked);
                }

                html += `
                    <div class="flex flex-col gap-2 p-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded transition-colors" id="cargo-libro-item-${idLibro}">
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 rounded p-1 transition-colors">
                            <input type="checkbox" name="libros_seleccionados[]" value="${idLibro}"
                                id="${chkId}" ${isChecked ? 'checked' : ''}
                                class="rounded text-indigo-600 focus:ring-indigo-500"
                                onchange="toggleCamposLibroCargo(this, ${idLibro})">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 leading-tight">${libro.nombre}</span>
                                <span class="text-[10px] text-gray-500">${statusText}</span>
                            </div>
                        </label>
                        <div id="campos-libro-${idLibro}" class="${isChecked ? '' : 'hidden'} pl-2 flex flex-col gap-2 border-l-2 border-indigo-200 ml-3">
                            <div id="lista-nro-folio-${idLibro}" class="flex flex-col gap-2">
                                ${filasNroFolioHtml}
                            </div>
                            <button type="button" onclick="agregarNroFolio(${idLibro})" class="text-[10px] text-indigo-600 font-bold uppercase w-fit hover:text-indigo-800 flex items-center gap-1 mt-1">
                                <i class="bi bi-plus-circle"></i> Agregar Nro/Folio
                            </button>
                        </div>
                    </div>
                `;
            }
        });

        if (countAvailable === 0) {
            html =
                `<p class="text-sm text-red-500 italic text-center w-full sm:col-span-2 md:col-span-3">No hay libros disponibles para este tipo de cargo en su estado actual.</p>`;
        }

        container.innerHTML = html;
    }

    function abrirModalCargos(idEstatus) {
        currentIdEstatus = idEstatus;
        currentCargoEditing = null;
        document.getElementById('cargo_id_estatus').value = idEstatus;
        document.getElementById('cargo_id').value = '';
        document.getElementById('formCargoTitle').textContent = 'Crear Nuevo Cargo';
        if (document.getElementById('container-archivo-actual')) {
            document.getElementById('container-archivo-actual').innerHTML = '';
        }

        // Reset form
        document.getElementById('formNuevoCargo').reset();
        document.getElementById('formNuevoCargo').classList.add('hidden');
        setCargoFechaActual();
        limpiarLibrosRecepcionAdicionales();

        cargarListaCargos(idEstatus);
        toggleModal('modalCargos');
    }

    function prepararNuevoCargo() {
        currentCargoEditing = null;
        document.getElementById('cargo_id').value = '';
        document.getElementById('formCargoTitle').textContent = 'Crear Nuevo Cargo';
        if (document.getElementById('container-archivo-actual')) {
            document.getElementById('container-archivo-actual').innerHTML = '';
        }
        document.getElementById('formNuevoCargo').reset();
        document.getElementById('cargo_id_estatus').value = currentIdEstatus;
        setCargoFechaActual();
        limpiarLibrosRecepcionAdicionales();
        renderizarCheckboxesLibros();
        document.getElementById('formNuevoCargo').classList.toggle('hidden');
    }

    function editarCargo(idCargo) {
        fetch(`{{ url('/cargos_libros_contables/detalle') }}/${idCargo}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.cargo) {
                alert('Error al cargar datos del cargo');
                return;
            }
            const cargo = data.cargo;
            currentCargoEditing = cargo;

            document.getElementById('cargo_id').value = cargo.id_cargo_libro;
            document.getElementById('cargo_id_estatus').value = cargo.id_estatus_libros_contables;
            document.getElementById('formCargoTitle').textContent = `Editar Cargo ${cargo.nro_cargo ? '#' + cargo.nro_cargo : ''}`;
            document.getElementById('cargo_tipo').value = cargo.tipo;
            document.getElementById('cargo_nro').value = cargo.nro_cargo || '';

            if (cargo.fecha) {
                const dt = new Date(cargo.fecha);
                dt.setMinutes(dt.getMinutes() - dt.getTimezoneOffset());
                document.getElementById('cargo_fecha').value = dt.toISOString().slice(0, 19);
            }

            const containerArchivo = document.getElementById('container-archivo-actual');
            if (containerArchivo) {
                if (cargo.archivo) {
                    containerArchivo.innerHTML = `<span class="text-gray-500">Archivo actual: </span><a href="{{ url('/') }}/${cargo.archivo}" target="_blank" class="text-blue-500 hover:underline font-medium"><i class="bi bi-file-earmark-arrow-down"></i> Ver archivo</a>`;
                } else {
                    containerArchivo.innerHTML = '';
                }
            }

            // Renderizar checkboxes con los libros pertenecientes a este cargo
            renderizarCheckboxesLibros(cargo);

            // Cargar libros adicionales
            limpiarLibrosRecepcionAdicionales();
            if (cargo.libro_cargo_recepcion && Array.isArray(cargo.libro_cargo_recepcion)) {
                cargo.libro_cargo_recepcion.forEach(item => {
                    agregarFilaLibroRecepcion(item);
                });
            }

            const form = document.getElementById('formNuevoCargo');
            form.classList.remove('hidden');
            form.scrollIntoView({ behavior: 'smooth' });
        })
        .catch(err => {
            console.error('Error al editar cargo:', err);
            alert('No se pudo cargar la información del cargo.');
        });
    }

    function setCargoFechaActual() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('cargo_fecha').value = now.toISOString().slice(0, 19);
    }

    function cargarListaCargos(idEstatus) {
        const tbody = document.getElementById('tbody-cargos');
        tbody.innerHTML =
            '<tr><td colspan="5" class="text-center py-4"><i class="bi bi-hourglass-split animate-spin text-gray-400 text-xl"></i> Cargando...</td></tr>';

        const fetchUrl = "/cargos_libros_contables/" + idEstatus;
        fetch(fetchUrl, {
                credentials: 'same-origin'
            })
            .then(res => {
                if (!res.ok) {
                    console.error('Respuesta no OK', res.status);
                    throw new Error('Respuesta no OK');
                }
                return res.json();
            })
            .then(data => {
                const cargos = data.cargos || [];
                currentEstatusData = data.estatus || null;
                renderizarCheckboxesLibros();

                tbody.innerHTML = '';
                if (cargos.length === 0) {
                    tbody.innerHTML =
                        '<tr><td colspan="5" class="text-center py-4 text-gray-500 italic">No hay cargos registrados.</td></tr>';
                    return;
                }

                cargos.forEach(cargo => {
                    const tr = document.createElement('tr');
                    tr.className = "hover:bg-gray-50 dark:hover:bg-gray-800";

                    const dateObj = new Date(cargo.fecha);
                    const formattedDate =
                        `${String(dateObj.getDate()).padStart(2, '0')}/${String(dateObj.getMonth() + 1).padStart(2, '0')}/${dateObj.getFullYear()} ${String(dateObj.getHours()).padStart(2, '0')}:${String(dateObj.getMinutes()).padStart(2, '0')}`;

                    const tipoLabel = cargo.tipo === 'recepcion' ?
                        '<span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Recepción</span>' :
                        '<span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Entrega</span>';

                    let archivoLink = '—';
                    if (cargo.archivo) {
                        archivoLink =
                            `<a href="{{ url('/') }}/${cargo.archivo}" target="_blank" class="text-blue-500 hover:text-blue-700 hover:underline flex items-center gap-1"><i class="bi bi-file-earmark-arrow-down"></i> Ver</a>`;
                    }

                    tr.innerHTML = `
                        <td class="px-4 py-2 whitespace-nowrap">${formattedDate}</td>
                        <td class="px-4 py-2">${tipoLabel}</td>
                        <td class="px-4 py-2 font-medium">${cargo.nro_cargo || '—'}</td>
                        <td class="px-4 py-2">${archivoLink}</td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ url('/cargos_libros_contables') }}/${cargo.id_cargo_libro}/pdf" target="_blank" class="text-red-500 hover:text-red-700 tooltip" title="Generar PDF">
                                    <i class="bi bi-file-earmark-pdf-fill text-lg"></i>
                                </a>
                                @if ($isRecepcion || $isAdminOrGerente)
                                    <button type="button" onclick="editarCargo(${cargo.id_cargo_libro})" class="text-blue-500 hover:text-blue-700 tooltip" title="Editar">
                                        <i class="bi bi-pencil-square text-lg"></i>
                                    </button>
                                    <button type="button" onclick="eliminarCargo(${cargo.id_cargo_libro})" class="text-red-500 hover:text-red-700 tooltip" title="Eliminar">
                                        <x-delete-icon />
                                    </button>
                                @endif
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML =
                    '<tr><td colspan="5" class="text-center py-4 text-red-500">Error al cargar historial.</td></tr>';
            });
    }

    function guardarCargo(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const cargoId = document.getElementById('cargo_id').value;
        const isEdit = cargoId !== '' && cargoId !== null;

        const url = isEdit
            ? `{{ url('/cargos_libros_contables') }}/${cargoId}/update`
            : "{{ route('cargos_libros_contables.store') }}";

        const btnSubmit = form.querySelector('button[type="submit"]');
        const originalText = btnSubmit.innerHTML;
        btnSubmit.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> Guardando...';
        btnSubmit.disabled = true;

        fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;

                if (data.success) {
                    form.reset();
                    document.getElementById('cargo_id').value = '';
                    currentCargoEditing = null;
                    if (document.getElementById('container-archivo-actual')) {
                        document.getElementById('container-archivo-actual').innerHTML = '';
                    }
                    form.classList.add('hidden');
                    limpiarLibrosRecepcionAdicionales();
                    cargarListaCargos(currentIdEstatus);

                    if (typeof window.showAjaxToast !== 'undefined') {
                        window.showAjaxToast.success(data.message);
                    } else {
                        alert(data.message);
                    }
                } else {
                    alert(data.message ||
                        'Error al guardar el cargo.');
                }
            })
            .catch(err => {
                console.error(err);
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
                alert("Error de conexión al guardar.");
            });
    }

    function generarFilaNroFolioHtml(idLibro, nroVal = '', folioVal = '', isChecked = true) {
        const disabledAttr = isChecked ? '' : 'disabled';
        return `
            <div class="fila-nro-folio flex items-end gap-2">
                <div class="flex flex-col gap-0.5 flex-1">
                    <label class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Nro. Libro</label>
                    <input type="text" name="nro_libro[${idLibro}][]" value="${nroVal}" ${disabledAttr} class="input-nro-libro block w-full border border-gray-300 dark:border-gray-600 rounded-md text-xs py-1 px-2 bg-white dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100 disabled:dark:bg-gray-800 disabled:cursor-not-allowed transition-colors">
                </div>
                <div class="flex flex-col gap-0.5 flex-1">
                    <label class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Folio</label>
                    <input type="text" name="folio_libro[${idLibro}][]" value="${folioVal}" ${disabledAttr} class="input-folio-libro block w-full border border-gray-300 dark:border-gray-600 rounded-md text-xs py-1 px-2 bg-white dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100 disabled:dark:bg-gray-800 disabled:cursor-not-allowed transition-colors">
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="mb-1 text-red-500 hover:text-red-700" title="Eliminar"><i class="bi bi-x-circle text-base"></i></button>
            </div>
        `;
    }

    function agregarNroFolio(idLibro) {
        const lista = document.getElementById(`lista-nro-folio-${idLibro}`);
        const isChecked = document.getElementById(`chk_cargo_lib_${idLibro}`).checked;
        lista.insertAdjacentHTML('beforeend', generarFilaNroFolioHtml(idLibro, '', '', isChecked));
    }

    function toggleCamposLibroCargo(checkbox, libroId) {
        const camposDiv = document.getElementById(`campos-libro-${libroId}`);
        const inputsNro = camposDiv.querySelectorAll(`.input-nro-libro`);
        const inputsFolio = camposDiv.querySelectorAll(`.input-folio-libro`);

        if (checkbox.checked) {
            camposDiv.classList.remove('hidden');
            inputsNro.forEach(inp => inp.disabled = false);
            inputsFolio.forEach(inp => inp.disabled = false);
            if (inputsNro.length > 0) inputsNro[0].focus();
        } else {
            camposDiv.classList.add('hidden');
            inputsNro.forEach(inp => {inp.disabled = true; inp.value = '';});
            inputsFolio.forEach(inp => {inp.disabled = true; inp.value = '';});
            
            const lista = document.getElementById(`lista-nro-folio-${libroId}`);
            if (lista) {
                lista.innerHTML = generarFilaNroFolioHtml(libroId, '', '', false);
            }
        }
    }

    function eliminarCargo(id) {
        if (!confirm('\u00bfEst\u00e1s seguro de eliminar este cargo?')) return;

        fetch(`{{ url('/cargos_libros_contables') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cargarListaCargos(currentIdEstatus);
                    if (typeof window.showAjaxToast !== 'undefined') {
                        window.showAjaxToast.success(data.message);
                    }
                } else {
                    alert(data.message || 'Error al eliminar');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error de conexi\u00f3n.');
            });
    }

    // ===== LIBROS ADICIONALES (libro_cargo_recepcion) =====
    let contadorLibrosAdicionales = 0;

    function agregarFilaLibroRecepcion(data = null) {
        const container = document.getElementById('container-libros-recepcion-adicionales');
        const idx = contadorLibrosAdicionales++;

        const nombreVal = data && data.nombre ? data.nombre : '';
        const nroVal = data && data.nro_libro ? data.nro_libro : '';
        const folioVal = data && data.folio_libro ? data.folio_libro : '';

        const fila = document.createElement('div');
        fila.className = 'flex flex-wrap items-start gap-2 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700 rounded-md p-2';
        fila.id = `fila-libro-adicional-${idx}`;
        fila.innerHTML = `
            <div class="flex-1 min-w-[140px] flex flex-col gap-0.5">
                <label class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Nombre del Libro</label>
                <input type="text" name="libro_cargo_recepcion[${idx}][nombre]" value="${nombreVal}" class="block w-full border border-gray-300 dark:border-gray-600 rounded-md text-xs py-1 px-2 bg-white dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 uppercase" oninput="this.value = this.value.toUpperCase();" required>
            </div>
            <div class="w-28 flex flex-col gap-0.5">
                <label class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Nro. Libro</label>
                <input type="text"
                    name="libro_cargo_recepcion[${idx}][nro_libro]" value="${nroVal}"
                    class="block w-full border border-gray-300 dark:border-gray-600 rounded-md text-xs py-1 px-2 bg-white dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="w-28 flex flex-col gap-0.5">
                <label class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Folio</label>
                <input type="text"
                    name="libro_cargo_recepcion[${idx}][folio_libro]" value="${folioVal}"
                    class="block w-full border border-gray-300 dark:border-gray-600 rounded-md text-xs py-1 px-2 bg-white dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="button"
                onclick="eliminarFilaLibroRecepcion(${idx})"
                class="mt-4 flex items-center justify-center w-7 h-7 bg-red-100 hover:bg-red-200 text-red-600 hover:text-red-800 rounded-full transition-colors text-xs flex-shrink-0"
                title="Eliminar">
                <i class="bi bi-x-lg"></i>
            </button>
        `;

        container.appendChild(fila);
    }

    function eliminarFilaLibroRecepcion(idx) {
        const fila = document.getElementById(`fila-libro-adicional-${idx}`);
        if (fila) fila.remove();
    }

    function limpiarLibrosRecepcionAdicionales() {
        const container = document.getElementById('container-libros-recepcion-adicionales');
        if (container) container.innerHTML = '';
        contadorLibrosAdicionales = 0;
    }
</script>
