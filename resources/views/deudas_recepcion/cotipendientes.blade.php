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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cotizaciones con Deudas Pendientes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <div class="mb-6 flex gap-4">
                <a href="{{ route('deudas_recepcion.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-300 flex items-center gap-2">
                    <i class="bi bi-arrow-left"></i> Volver a Panel
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto text-sm" id="myTable-deuda-recepcion-cotipendientes">
                        <thead>
                            <tr class="bg-gray-50 text-left text-gray-700">
                                <th class="px-4 py-2 border-b">Empresa</th>
                                <th class="px-4 py-2 border-b">Estado de Servicio</th>
                                {{-- <th class="px-4 py-2 border-b">N° Cotización</th> --}}
                                {{-- <th class="px-4 py-2 border-b">Tipo de Cotización</th>
                                <th class="px-4 py-2 border-b">Mes Declaración</th> --}}
                                {{-- <th class="px-4 py-2 border-b">N° Factura</th> --}}
                                {{-- <th class="px-4 py-2 border-b text-right">Monto Deuda</th> --}}
                                <th class="px-4 py-2 border-b text-right">Monto Total Contable</th>
                                <th class="px-4 py-2 border-b text-right">Monto Total Otros</th>
                                <th class="px-4 py-2 border-b text-right text-red-600">Monto Total</th>
                                {{-- <th class="px-4 py-2 border-b text-center">Acciones</th> --}}
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($deudasPendientes as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        <div class="font-medium text-gray-900">
                                            {{ $item->empresa->razon_social ?? 'N/A' }}
                                            @switch($item->empresa->estado_actual)
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
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            RUC: {{ $item->empresa->ruc ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span
                                            class="px-2 py-1 text-xs rounded-md transition-all hover:opacity-80 {{ $item->empresa->estado_servicio_recepcion == 1 ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-600 hover:bg-red-200' }}">
                                            {{ $item->empresa->estado_servicio_recepcion == 1 ? 'Activo' : 'Pendiente/Retirado' }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-2 text-right font-semibold">
                                        S/ {{ number_format($item->monto_total_contable, 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-right font-semibold">
                                        S/ {{ number_format($item->monto_total_otros, 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-right font-semibold text-red-600">
                                        S/ {{ number_format($item->monto_total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 font-semibold">
                            <tr>
                                <th class="px-4 py-2 border-t text-left" colspan="2">
                                    <strong>Total:</strong>
                                </th>
                                <th class="px-4 py-2 border-t text-right" style="text-align: right !important;">
                                    S/ {{ number_format($totalContable, 2) }}
                                </th>
                                <th class="px-4 py-2 border-t text-right" style="text-align: right !important;">
                                    S/ {{ number_format($totalOtros, 2) }}
                                </th>
                                <th class="px-4 py-2 border-t text-right text-red-600" style="text-align: right !important;">
                                    S/ {{ number_format($totalDeuda, 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- MODAL PAGO --}}
<x-modal-show id="modal-pago-deuda" title="Registrar pago">
    <form method="POST" action="{{ route('pago_deudas_recepcion.store') }}" class="space-y-6" autocomplete="off"
        onsubmit="return handleFormSubmit(event)">
        @csrf
        <input type="hidden" name="id_deuda" id="pay-id-deuda">
        <input type="hidden" name="tipo_vista" value="{{ $tipo ?? 'contable' }}">

        <div class="bg-gray-50 p-3 rounded-md text-sm space-y-1">
            <div class="grid grid-cols-2 gap-4">
                <div><strong>N° Cotización:</strong> <span id="pay-cotizacion"></span></div>
                <div><strong>N° Factura:</strong> <span id="pay-factura"></span></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><strong>Descripción:</strong> <span id="pay-descripcion"></span></div>
                <div><strong>Mes Declaración:</strong> <span id="pay-mes-declaracion"></span></div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Deuda Total')" />
                <x-text-input id="pay-deuda-total" type="text" class="mt-1 block w-full bg-gray-200" disabled />
            </div>
            <div>
                <x-input-label :value="__('Deuda Pendiente')" />
                <x-text-input id="pay-deuda-pendiente" type="text" class="mt-1 block w-full bg-gray-200" disabled />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Fecha de pago')" />
                <x-text-input name="fecha_pago" id="pay-fecha-pago" type="date" class="mt-1 block w-full" required />
            </div>
            <div>
                <x-input-label :value="__('Monto a pagar')" />
                <x-text-input name="monto" id="pay-monto" type="number" step="0.01" min="0"
                    class="mt-1 block w-full" required />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Método de pago')" />
                <select name="metodo_pago" id="pay-metodo-pago"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required
                    onchange="toggleNroOperacion()">
                    <option value="">Seleccionar método</option>
                    <option value="Interbank">Interbank</option>
                    <option value="BCP">BCP</option>
                    <option value="BBVA">BBVA</option>
                    <option value="Banbif">Banbif</option>
                    <option value="Scotiabank">Scotiabank</option>
                    <option value="Yape">Yape</option>
                    <option value="Plin">Plin</option>
                    <option value="Efectivo">Efectivo</option>
                </select>
            </div>
            <div>
                <x-input-label :value="__('N° de operación')" />
                <x-text-input name="nro_operacion" id="pay-nro-operacion" type="text" class="mt-1 block w-full" />
            </div>
        </div>

        <div>
            <x-input-label :value="__('Observaciones')" />
            <x-text-input name="observaciones" id="pay-observaciones" type="text" class="mt-1 block w-full" />
        </div>

        <div class="flex justify-end gap-3">
            <x-secondary-button type="button" onclick="toggleModal('modal-pago-deuda')">Cancelar</x-secondary-button>
            <x-primary-button class="px-6 py-2">Guardar pago</x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- MODAL EDITAR PAGO --}}
<x-modal-show id="modal-editar-pago" title="Editar pago">
    <div id="error-message-pago" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded"></div>
    <form method="POST" id="form-editar-pago" class="space-y-4" onsubmit="return submitEditarPago(event)">
        @csrf
        @method('PUT')

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Fecha de pago')" />
                <x-text-input name="fecha_pago" id="edit-pay-fecha-pago" type="date" class="mt-1 block w-full"
                    required />
            </div>
            <div>
                <x-input-label :value="__('Monto')" />
                <x-text-input name="monto" id="edit-pay-monto" type="number" step="0.01" min="0"
                    class="mt-1 block w-full" required />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Método de pago')" />
                <select name="metodo_pago" id="edit-pay-metodo-pago"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Seleccionar método</option>
                    <option value="Interbank">Interbank</option>
                    <option value="BCP">BCP</option>
                    <option value="BBVA">BBVA</option>
                    <option value="Banbif">Banbif</option>
                    <option value="Scotiabank">Scotiabank</option>
                    <option value="Yape">Yape</option>
                    <option value="Plin">Plin</option>
                    <option value="Efectivo">Efectivo</option>
                </select>
            </div>
            <div>
                <x-input-label :value="__('N° de operación')" />
                <x-text-input name="nro_operacion" id="edit-pay-nro-operacion" type="text"
                    class="mt-1 block w-full" />
            </div>
        </div>

        <div>
            <x-input-label :value="__('Observaciones')" />
            <x-text-input name="observaciones" id="edit-pay-observaciones" type="text"
                class="mt-1 block w-full" />
        </div>

        <div class="flex justify-end gap-3">
            <x-secondary-button type="button"
                onclick="toggleModal('modal-editar-pago')">Cancelar</x-secondary-button>
            <x-primary-button class="px-6 py-2">Actualizar pago</x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- MODAL DETALLES --}}
<x-modal-show id="modal-detalles-pago" title="Historial de Pagos">
    <div id="pagos-contenido" class="space-y-4">
        <p class="text-center text-gray-400 italic">No hay pagos registrados</p>
    </div>

    <div class="flex justify-end mt-4">
        <x-secondary-button onclick="toggleModal('modal-detalles-pago')">
            Cerrar
        </x-secondary-button>
    </div>
</x-modal-show>

<script>
    let isSubmitting = false;

    function abrirModalDetallesPagos(idDeuda) {
        const cont = document.getElementById('pagos-contenido');
        cont.innerHTML = `<p class="text-center text-gray-400 italic">Cargando detalles...</p>`;
        toggleModal('modal-detalles-pago');

        fetch(`/deudas-recepcion/${idDeuda}/pagos`)
            .then(res => res.json())
            .then(data => {
                if (!data.pagos || data.pagos.length === 0) {
                    cont.innerHTML = '<p class="text-center text-gray-400 italic">No se registran pagos.</p>';
                } else {
                    let html = `
                        <table class="w-full text-sm border">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-2 py-1 border">Fecha de pago</th>
                                    <th class="px-2 py-1 border text-right">Monto</th>
                                    <th class="px-2 py-1 border">Método</th>
                                    <th class="px-2 py-1 border">N° Operación</th>
                                    <th class="px-2 py-1 border">Observación</th>
                                    <th class="px-2 py-1 border">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.pagos.map(p => `
                                    <tr>
                                        <td class="px-2 py-1 border text-center">${p.fecha}</td>
                                        <td class="px-2 py-1 border text-right">S/. ${p.monto}</td>
                                        <td class="px-2 py-1 border text-center">${p.metodo}</td>
                                        <td class="px-2 py-1 border text-center">${p.nro_operacion}</td>
                                        <td class="px-2 py-1 border text-center">${p.observacion}</td>
                                        <td class="px-2 py-1 border text-center">
                                            <button type="button" onclick="abrirModalEditarPago('${p.id}', '${p.fecha_raw}', '${p.monto_raw}', '${p.metodo}', '${p.nro_operacion}', '${p.observacion}')"
                                                class="text-yellow-600 hover:text-yellow-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                    cont.innerHTML = html;
                }
            })
            .catch(err => {
                cont.innerHTML = `<p class="text-center text-red-500">Error al cargar los detalles.</p>`;
                console.error(err);
            });
    }

    function toggleNroOperacion() {
        const metodoPago = document.getElementById('pay-metodo-pago').value;
        const nroOperacion = document.getElementById('pay-nro-operacion');

        if (metodoPago === 'Efectivo') {
            nroOperacion.value = '';
            nroOperacion.setAttribute('readonly', true);
            nroOperacion.classList.add('bg-gray-200');
        } else {
            nroOperacion.value = '';
            nroOperacion.removeAttribute('readonly');
            nroOperacion.classList.remove('bg-gray-200');
        }
    }

    function abrirModalPago(payload) {
        document.getElementById('pay-id-deuda').value = payload.idDeuda;
        document.getElementById('pay-cotizacion').textContent = payload.cotizacion;
        document.getElementById('pay-factura').textContent = payload.factura;
        document.getElementById('pay-descripcion').textContent = payload.descripcion;
        document.getElementById('pay-mes-declaracion').textContent = payload.mesDeclaracion;
        document.getElementById('pay-deuda-total').value = 'S/. ' + parseFloat(payload.montoDeuda).toLocaleString(
            'en-US', {
                minimumFractionDigits: 2
            });
        document.getElementById('pay-deuda-pendiente').value = 'S/. ' + parseFloat(payload.montoPendiente)
            .toLocaleString('en-US', {
                minimumFractionDigits: 2
            });

        const hoy = new Date();
        const hoyStr =
            `${hoy.getFullYear()}-${String(hoy.getMonth()+1).padStart(2,'0')}-${String(hoy.getDate()).padStart(2,'0')}`;
        document.getElementById('pay-fecha-pago').value = hoyStr;
        document.getElementById('pay-monto').value = payload.montoPendiente;

        // Reset form fields
        document.getElementById('pay-metodo-pago').value = '';
        document.getElementById('pay-nro-operacion').value = '';
        document.getElementById('pay-nro-operacion').removeAttribute('readonly');
        document.getElementById('pay-nro-operacion').classList.remove('bg-gray-200');

        toggleModal('modal-pago-deuda');
    }

    function abrirModalCotizacion(empresaId) {
        document.getElementById('empresa-id').value = empresaId;
        toggleModal('modal-cotizacion');
    }

    function abrirModalEditarDeuda(idDeuda, nroFactura, descripcion, montoDeuda, mesDeclaracion) {
        document.getElementById('edit-id-deuda').value = idDeuda;
        document.getElementById('edit-nro-factura').value = nroFactura;
        document.getElementById('edit-descripcion-servicio').value = descripcion;
        document.getElementById('edit-monto-deuda').value = montoDeuda;
        document.getElementById('edit-mes-declaracion').value = mesDeclaracion;

        const errorDiv = document.getElementById('error-message-deuda');
        if (errorDiv) {
            errorDiv.classList.add('hidden');
        }

        const form = document.getElementById('form-editar-deuda');
        form.action = `/deudas_recepcion/${idDeuda}`;
        toggleModal('modal-editar-deuda');
    }

    function abrirModalEditarPago(idPago, fecha, monto, metodo, nroOperacion, observacion) {
        document.getElementById('edit-pay-fecha-pago').value = fecha;
        document.getElementById('edit-pay-monto').value = monto;
        document.getElementById('edit-pay-metodo-pago').value = metodo;
        document.getElementById('edit-pay-nro-operacion').value = nroOperacion === 'N/A' ? '' : nroOperacion;
        document.getElementById('edit-pay-observaciones').value = observacion === '—' ? '' : observacion;

        const form = document.getElementById('form-editar-pago');
        form.action = `/pago_deudas_recepcion/${idPago}`;

        toggleModal('modal-detalles-pago');
        toggleModal('modal-editar-pago');
    }

    function submitEditarPago(event) {
        event.preventDefault();

        if (isSubmitting) return false;
        isSubmitting = true;

        const form = document.getElementById('form-editar-pago');
        const formData = new FormData(form);
        const errorDiv = document.getElementById('error-message-pago');
        errorDiv.classList.add('hidden');
        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Error al actualizar');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                errorDiv.textContent = error.message || 'Error al actualizar el pago';
                errorDiv.classList.remove('hidden');
                isSubmitting = false;
            });
        return false;
    }

    function submitEditarDeuda(event) {
        event.preventDefault();

        if (isSubmitting) return false;
        isSubmitting = true;

        const form = document.getElementById('form-editar-deuda');
        const formData = new FormData(form);
        const errorDiv = document.getElementById('error-message-deuda');
        errorDiv.classList.add('hidden');

        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else if (data.error) {
                    errorDiv.textContent = data.error;
                    errorDiv.classList.remove('hidden');
                    isSubmitting = false;
                }
            })
            .catch(error => {
                errorDiv.textContent = 'Error al actualizar la deuda';
                errorDiv.classList.remove('hidden');
                isSubmitting = false;
            });
        return false;
    }

    function handleFormSubmit(event) {
        event.preventDefault();

        if (isSubmitting) return false;
        isSubmitting = true;

        const form = event.target;
        const formData = new FormData(form);

        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Error al registrar el pago');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    toggleModal('modal-pago-deuda');
                    location.reload(); // Recargamos para ver los cambios en la tabla
                }
            })
            .catch(error => {
                alert(error.message || 'Error al registrar el pago');
                isSubmitting = false;
            });
        return false;
    }
</script>

<script>
    $(document).ready(function() {
        $('#myTable-deuda-recepcion-cotipendientes').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            responsive: false,
            //dom: 'Bfrtip', // Activa el diseño para los botones
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
