@php
    $meses = [
        'January' => 'Enero',
        'February' => 'Febrero',
        'March' => 'Marzo',
        'April' => 'Abril',
        'May' => 'Mayo',
        'June' => 'Junio',
        'July' => 'Julio',
        'August' => 'Agosto',
        'September' => 'Septiembre',
        'October' => 'Octubre',
        'November' => 'Noviembre',
        'December' => 'Diciembre',
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-3">
            Deudas de Recepción -
            <select onchange="window.location.href = '{{ route('deudas_recepcion.index') }}/' + this.value"
                class="border-gray-300 rounded-md shadow-sm text-xl px-3 py-1 min-w-[155px]">
                <option value="contable" {{ $tipo === 'contable' ? 'selected' : '' }}>(Contables)</option>
                <option value="otros" {{ $tipo === 'otros' ? 'selected' : '' }}>(Otros)</option>
                <option value="ambas" {{ $tipo === 'ambas' ? 'selected' : '' }}>(Ambas)</option>
            </select>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('deudas_recepcion.lista') }}"
                    class="flex items-center gap-2 bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white text-center w-full md:w-auto">
                    <i class="bi bi-list-ul"></i> Lista Cotizaciones
                </a>
                <a href="{{ route('deudas_recepcion.cotipendientes') }}"
                    class="flex items-center gap-2 bg-orange-500 px-4 py-2 rounded-md hover:bg-orange-600 transition duration-300 text-white text-center w-full md:w-auto">
                    <i class="bi bi-credit-card-2-back-fill"></i> Deudas Pendientes
                </a>
            </div>

            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-[1200px] table-auto text-sm" id="myTable-deuda-recepcion">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-700">
                            <th class="px-3 py-2 border-b">RUC</th>
                            <th class="px-3 py-2 border-b">N° Cotización</th>
                            <th class="px-3 py-2 border-b">Empresa</th>
                            <th class="px-3 py-2 border-b">Estado de Servicio</th>
                            <th class="px-3 py-2 border-b">Saldo Pendiente <br /><span
                                    class="text-blue-600 font-medium">{{ $meses[now()->startOfMonth()->subMonths(1)->format('F')] }}
                                    {{ now()->startOfMonth()->subMonths(1)->format('Y') }}
                            </th></span>
                            <th class="px-3 py-2 border-b">Pago Pendiente <br /><span
                                    class="text-blue-600 font-medium">{{ $meses[now()->startOfMonth()->format('F')] }}
                                    {{ now()->startOfMonth()->format('Y') }}
                            </th>
                            <th class="px-3 py-2 border-b">Deuda Total<br /> Pendiente</th>
                            <th class="px-3 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($empresas as $empresa)
                            @php
                                $ultimaCotizacion = $empresa->cotizaciones->first();
                                $estadoTexto = $empresa->estado_actual == 1 ? 'Activo' : 'Pendiente/Retirado';
                                $bloqueado = $empresa->estado_actual == 0 && $empresa->estado_servicio_recepcion == 1;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">{{ $empresa->ruc }}</td>
                                <td class="px-3 py-2">
                                    @if ($bloqueado)
                                        <span class="text-gray-400">-</span>
                                    @else
                                        <button type="button"
                                            class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 hover:underline px-2 py-1 rounded transition-all cursor-pointer"
                                            onclick="abrirModalCotizacion({{ $empresa->id_empresas_clientes }}, '{{ addslashes($empresa->razon_social) }}')">
                                            {{ $ultimaCotizacion->nro_cotizacion ?? 'Crear Cotización' }}
                                        </button>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    {{ $empresa->razon_social }},
                                    @switch($empresa->estado_actual)
                                        @case(1)
                                            <span class="text-green-600">(A)</span>
                                        @break

                                        @case(2)
                                            <span class="text-yellow-500">(S)</span>
                                        @break

                                        @case(0)
                                            <span class="text-red-600">(R)</span>
                                        @break

                                        @default
                                            <span class="text-gray-500">(?)</span>
                                    @endswitch
                                </td>
                                <td class="px-3 py-2">
                                    <button type="button"
                                        onclick="toggleEstadoServicio({{ $empresa->id_empresas_clientes }}, this)"
                                        class="px-2 py-1 text-xs rounded-md cursor-pointer transition-all hover:opacity-80 {{ $empresa->estado_servicio_recepcion == 1 ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-600 hover:bg-red-200' }}">
                                        {{ $empresa->estado_servicio_recepcion == 1 ? 'Activo' : 'Pendiente/Retirado' }}
                                    </button>
                                </td>
                                <td class="px-3 py-2">S/{{ number_format($empresa->total_saldo_pendiente, 2) }}</td>
                                <td class="px-3 py-2">S/{{ number_format($empresa->total_pago_pendiente, 2) }}</td>
                                <td class="px-3 py-2">
                                    S/{{ number_format($empresa->total_deuda_pendiente, 2) }}
                                </td>
                                <td class="px-3 py-2">
                                    @if (!$bloqueado)
                                        <div class="flex space-x-2">

                                            {{-- <button type="button" 
                                                onclick="abrirModalDeuda({{ $empresa->id_empresas_clientes }},
                '{{ addslashes($empresa->razon_social) }}',
                {{ $ultimaCotizacion ? $ultimaCotizacion->id_cotizacion : 'null' }})"
                                                class="flex items-center py-1 rounded-md tooltip {{ $ultimaCotizacion ? 'text-green-600 hover:text-green-800' : 'text-gray-400 cursor-not-allowed' }}"
                                                {{ !$ultimaCotizacion ? 'disabled' : '' }}>
                                                <span class="tooltip-text">+Deuda</span>
                                                <x-tax-icon />
                                            </button>
                                            --}}

                                            <a href="{{ route('deudas_recepcion.show', ['id' => $empresa->id_empresas_clientes, 'tipo' => $tipo]) }}"
                                                class="flex items-center py-1 rounded-md tooltip text-blue-600 hover:text-blue-800">
                                                <span class="tooltip-text">Ver Detalles</span>
                                                <x-details-icon />
                                            </a>

                                            @if ($ultimaCotizacion)
                                                <a href="{{ route('deudas_recepcion.pdf', ['id' => $empresa->id_empresas_clientes, 'status_pago' => 'pendiente-parcial', 'tipo_cotizacion' => ($tipo === 'ambas' ? 'ambos' : $tipo), 'ordenar_por' => 'mes_declaracion', 'download' => 1]) }}"
                                                    class="flex items-center py-1 rounded-md tooltip text-red-600 hover:text-red-800"
                                                    target="_blank">
                                                    <span class="tooltip-text">PDF DEUDAS</span>
                                                    <x-pdf-icon />
                                                </a>
                                            @else
                                                <span class="flex items-center py-1 rounded-md tooltip text-gray-400 cursor-not-allowed">
                                                    <span class="tooltip-text">Exportar PDF</span>
                                                    <x-pdf-icon />
                                                </span>
                                            @endif

                                            @if ($ultimaCotizacion)
                                                <button type="button"
                                                    onclick="abrirModalEditarCotizacion({{ $ultimaCotizacion->id_cotizacion }},
                        '{{ addslashes($empresa->razon_social) }}',
                        '{{ addslashes($ultimaCotizacion->nro_cotizacion) }}',
                        '{{ $ultimaCotizacion->tipo_cotizacion }}')"
                                                    class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #F59E0B;" onmouseover="this.style.color='#D97706';"
                                                    onmouseout="this.style.color='#F59E0B';">
                                                    <span class="tooltip-text">Editar Cotización</span>
                                                    <x-edit-icon />
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Sin acciones</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold">
                        <tr>
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t !text-right">Total:</td>
                            <td class="px-3 py-2 border-t">S/ {{ number_format($totalDeudaPendiente, 2) }}</td>
                            <td class="px-3 py-2 border-t"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</x-app-layout>

{{-- MODAL: CREAR/EDITAR COTIZACIÓN --}}
<x-modal-show id="modal-cotizacion" title="Cotización">
    <form method="POST" action="{{ route('cotizacion_recepcion.store') }}" id="form-cotizacion"
        onsubmit="return handleFormSubmit(event)">
        @csrf
        <input type="hidden" name="_method" id="form-method" value="POST">
        <input type="hidden" name="id_empresa_cliente" id="empresa-id">
        <input type="hidden" name="id_cotizacion" id="cotizacion-id">
        <div class="space-y-4">
            <div>
                <x-input-label :value="__('Empresa')" />
                <x-text-input id="empresa-nombre" type="text" class="mt-1 block w-full bg-gray-200" disabled />
            </div>

            <div>
                <x-input-label :value="__('N° Cotización')" />
                <x-text-input name="nro_cotizacion" id="nro-cotizacion" type="text" class="mt-1 block w-full"
                    placeholder="Número de cotización" />
            </div>

            <div>
                <x-input-label :value="__('Tipo Cotización')" />
                <select name="tipo_cotizacion" id="tipo-cotizacion"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Seleccionar tipo...</option>
                    <option value="contable">Contable</option>
                    <option value="otros">Otros</option>
                </select>
            </div>

            <div class="flex justify-end pt-4">
                <x-secondary-button type="button"
                    onclick="toggleModal('modal-cotizacion')">Cancelar</x-secondary-button>
                <x-primary-button class="ml-2" id="btn-submit-cotizacion">Guardar</x-primary-button>
            </div>
        </div>
    </form>
</x-modal-show>

{{-- MODAL: REGISTRAR DEUDA (ACTUALIZADO CON TIPO DE DECLARACIÓN) --}}
<x-modal-show id="modal-deuda" title="Registrar nueva deuda">
    <form method="POST" action="{{ route('deudas_recepcion.store') }}" id="form-deuda-create"
        onsubmit="return handleFormSubmit(event)">
        @csrf
        <input type="hidden" name="id_cotizacion" id="deuda-cotizacion-id">
        <div class="space-y-4">
            <div>
                <x-input-label :value="__('Empresa')" />
                <x-text-input id="deuda-empresa-nombre" type="text" class="mt-1 block w-full bg-gray-200"
                    disabled />
            </div>

            {{-- TIPO DE DECLARACIÓN --}}
            <div>
                <x-input-label :value="__('Tipo de Declaración')" />
                <select name="tipo_declaracion" id="tipo_declaracion_modal"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                    onchange="toggleTipoDeclaracionModal(this.value)" required>
                    <option value="mensual">Mensual</option>
                    <option value="anual">Anual (Declaración Anual)</option>
                </select>
            </div>

            {{-- MES (solo mensual) --}}
            <div id="campo-mes-declaracion-modal">
                <x-input-label :value="__('Mes de Declaración')" />
                <input type="month" name="mes_declaracion"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>

            {{-- AÑO (solo anual) --}}
            <div id="campo-anio-declaracion-modal" class="hidden">
                <x-input-label :value="__('Año de Declaración')" />
                <select name="anio_declaracion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @php
                        $anioActual = now()->year;
                    @endphp
                    @for ($a = $anioActual; $a >= $anioActual - 5; $a--)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <x-input-label :value="__('N° Factura')" />
                <x-text-input name="nro_factura" type="text" class="mt-1 block w-full"
                    placeholder="Número de factura" required />
            </div>

            <div>
                <x-input-label :value="__('Descripción del Servicio')" />
                <x-text-input name="descripcion_servicio" type="text" class="mt-1 block w-full"
                    placeholder="Descripción del servicio" oninput="this.value = this.value.toUpperCase()" required />
            </div>

            <div>
                <x-input-label :value="__('Monto de la Deuda')" />
                <x-text-input name="monto_deuda" type="number" step="0.01" min="0"
                    class="mt-1 block w-full" placeholder="0.00" required />
            </div>

            <div class="flex justify-end pt-4">
                <x-secondary-button type="button" onclick="toggleModal('modal-deuda')">Cancelar</x-secondary-button>
                <x-primary-button class="ml-2">Registrar Deuda</x-primary-button>
            </div>
        </div>
    </form>
</x-modal-show>

<script>
    let isSubmitting = false;

    function handleFormSubmit(event) {
        if (isSubmitting) {
            event.preventDefault();
            return false;
        }
        isSubmitting = true;
        setTimeout(() => {
            isSubmitting = false;
        }, 3000);
        return true;
    }

    function abrirModalCotizacion(id, nombre) {
        // Resetear formulario para crear
        const form = document.getElementById('form-cotizacion');
        form.action = '{{ route('cotizacion_recepcion.store') }}';
        document.getElementById('form-method').value = 'POST';
        document.getElementById('cotizacion-id').value = '';
        document.getElementById('empresa-id').value = id;
        document.getElementById('empresa-nombre').value = nombre;
        document.getElementById('nro-cotizacion').value = '';
        document.getElementById('tipo-cotizacion').value = 'contable';

        // Cambiar título del modal
        const modalTitle = document.querySelector('#modal-cotizacion .modal-title');
        if (modalTitle) modalTitle.textContent = 'Registrar nueva cotización';

        document.getElementById('btn-submit-cotizacion').textContent = 'Crear Cotización';
        toggleModal('modal-cotizacion');
    }

    function abrirModalEditarCotizacion(idCotizacion, nombre, nroCotizacion, tipoCotizacion) {
        // Configurar formulario para editar
        const form = document.getElementById('form-cotizacion');
        form.action = `/cotizacion_recepcion/${idCotizacion}`;
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('cotizacion-id').value = idCotizacion;
        document.getElementById('empresa-nombre').value = nombre;
        document.getElementById('nro-cotizacion').value = nroCotizacion;
        document.getElementById('tipo-cotizacion').value = tipoCotizacion;

        // Cambiar título del modal
        const modalTitle = document.querySelector('#modal-cotizacion .modal-title');
        if (modalTitle) modalTitle.textContent = 'Editar cotización';

        document.getElementById('btn-submit-cotizacion').textContent = 'Actualizar Cotización';
        toggleModal('modal-cotizacion');
    }

    function abrirModalDeuda(empresaId, empresaNombre, cotizacionId) {
        if (!cotizacionId) {
            alert('Esta empresa no tiene cotización. Debe crear una cotización primero.');
            return;
        }
        document.getElementById('deuda-cotizacion-id').value = cotizacionId;
        document.getElementById('deuda-empresa-nombre').value = empresaNombre;

        // Resetear campos del modal
        document.getElementById('tipo_declaracion_modal').value = 'mensual';
        toggleTipoDeclaracionModal('mensual');

        toggleModal('modal-deuda');
    }

    function toggleEstadoServicio(empresaId, button) {

        fetch(`/deudas_recepcion/${empresaId}/toggle-estado-servicio`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {

                if (!data.success) return;

                const row = button.closest('tr');
                const colCot = row.children[1];
                const colAcciones = row.children[7];

                // Actualizar color/texto del botón
                if (data.nuevo_estado == 1) {
                    button.className =
                        'px-2 py-1 text-xs rounded-md cursor-pointer transition-all hover:opacity-80 bg-green-100 text-green-700 hover:bg-green-200';
                    button.textContent = 'Activo';
                } else {
                    button.className =
                        'px-2 py-1 text-xs rounded-md cursor-pointer transition-all hover:opacity-80 bg-red-100 text-red-600 hover:bg-red-200';
                    button.textContent = 'Pendiente/Retirado';
                }

                // ---------------------------------------------------------
                //  ** REGLA CORRECTA **
                //  Bloquear = estado_actual = 0  AND estado_servicio = 1
                // ---------------------------------------------------------
                const bloquear = (data.estado_actual == 0 && data.nuevo_estado == 1);
                const permitirAcciones = !bloquear;


                // SI BLOQUEA
                if (!permitirAcciones) {
                    colCot.innerHTML = `<span class="text-gray-400">-</span>`;
                    colAcciones.innerHTML = `<span class="text-gray-300"></span>`;
                    return;
                }

                // SI PERMITE ACCIONES
                if (data.ultima_cotizacion) {

                    colCot.innerHTML = `
                <button type="button"
                    class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 hover:underline px-2 py-1 rounded transition-all cursor-pointer"
                    onclick="abrirModalCotizacion(${empresaId}, '${data.razon_social}')">
                    ${data.ultima_cotizacion.nro}
                </button>
            `;

                    const pdfUrl = `{{ route('deudas_recepcion.pdf', ['id' => ':id', 'status_pago' => 'pendiente-parcial', 'tipo_cotizacion' => ($tipo === 'ambas' ? 'ambos' : $tipo), 'ordenar_por' => 'mes_declaracion', 'download' => 1]) }}`.replace(':id', empresaId);

                    colAcciones.innerHTML = `
                <div class="flex space-x-2">
                {{--  
                    <button type="button"
                        onclick="abrirModalDeuda(${empresaId}, '${data.razon_social}', ${data.ultima_cotizacion.id})"
                        class="flex items-center py-1 rounded-md tooltip text-green-600 hover:text-green-800">
                        <span class="tooltip-text">+Deuda</span>
                        <x-tax-icon />
                    </button>
                --}}

                    <a href="/deudas_recepcion/show/${empresaId}?tipo={{ $tipo }}"
                        class="flex items-center py-1 rounded-md tooltip text-blue-600 hover:text-blue-800">
                        <span class="tooltip-text">Ver Detalles</span>
                        <x-details-icon />
                    </a>

                    <a href="${pdfUrl}"
                        class="flex items-center py-1 rounded-md tooltip text-red-600 hover:text-red-800"
                        target="_blank">
                        <span class="tooltip-text">Exportar PDF</span>
                        <x-pdf-icon />
                    </a>

                    <button type="button"
                        onclick="abrirModalEditarCotizacion(
                            ${data.ultima_cotizacion.id},
                            '${data.razon_social}',
                            '${data.ultima_cotizacion.nro}',
                            '${data.ultima_cotizacion.tipo}')"
                        class="flex items-center py-1 rounded-md tooltip"
                        style="color: #F59E0B;"
                        onmouseover="this.style.color='#D97706';"
                        onmouseout="this.style.color='#F59E0B';">
                        <span class="tooltip-text">Editar Cotización</span>
                        <x-edit-icon />
                    </button>
                </div>
            `;

                } else {

                    colCot.innerHTML = `
                <button type="button"
                    class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 hover:underline px-2 py-1 rounded transition-all cursor-pointer"
                    onclick="abrirModalCotizacion(${empresaId}, '${data.razon_social}')">
                    Crear Cotización
                </button>
            `;

                    colAcciones.innerHTML = `
                <div class="flex space-x-2">
                    <span class="flex items-center py-1 rounded-md tooltip text-gray-400 cursor-not-allowed">
                        <span class="tooltip-text">Exportar PDF</span>
                        <x-pdf-icon />
                    </span>
                    <span class="text-gray-400 text-xs italic">Sin acciones</span>
                </div>
            `;
                }
            });
    }

    function toggleTipoDeclaracionModal(tipo) {
        const campoMes = document.getElementById('campo-mes-declaracion-modal');
        const campoAnio = document.getElementById('campo-anio-declaracion-modal');
        if (tipo === 'anual') {
            campoMes.classList.add('hidden');
            campoAnio.classList.remove('hidden');
            // Remover required del campo mes
            const inputMes = campoMes.querySelector('input');
            if (inputMes) inputMes.removeAttribute('required');
        } else {
            campoMes.classList.remove('hidden');
            campoAnio.classList.add('hidden');
            // Agregar required al campo mes
            const inputMes = campoMes.querySelector('input');
            if (inputMes) inputMes.setAttribute('required', 'required');
        }
    }
</script>

<script>
    $(document).ready(function() {
        $('#myTable-deuda-recepcion').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            responsive: false,
            dom: 'Bfrtip', // Activa el diseño para los botones
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: function() {
                    const hoy = new Date();
                    const dia = hoy.getDate().toString().padStart(2, '0');
                    const mes = (hoy.getMonth() + 1).toString().padStart(2, '0');
                    const anio = hoy.getFullYear();
                    return `TODAS_LAS_EMPRESAS_COTIZACIONES_RECEPCION_SISTEMA_ASECON_DESCARGADO_${dia}_${mes}_${anio}`;
                },
                exportOptions: {
                    columns: ':not(:last)' // Excluye la primera y la última columna
                }
            }],
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            }
        });
    });
</script>
