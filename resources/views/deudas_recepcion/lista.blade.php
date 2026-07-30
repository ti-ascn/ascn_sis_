<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Lista de Cotizaciones - Recepción
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col mb-4">
                <a href="{{ route('deudas_recepcion.index') }}"
                    class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white text-center w-full md:w-auto">
                    <i class="bi bi-arrow-left"></i> Volver a Deudas Recepción
                </a>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto text-sm" id="myTable-lista-cotizaciones">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-700">
                            <th class="px-3 py-2 border-b">RUC</th>
                            <th class="px-3 py-2 border-b">Razón Social</th>
                            <th class="px-3 py-2 border-b">N° Cotización</th>
                            <th class="px-3 py-2 border-b">Tipo Cotización</th>
                            <th class="px-3 py-2 border-b">Total Deudas</th>
                            <th class="px-3 py-2 border-b">Monto Total (S/)</th>
                            <th class="px-3 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($cotizaciones as $cotizacion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">{{ $cotizacion['ruc'] }}</td>
                                <td class="px-3 py-2">{{ $cotizacion['razon_social'] }}</td>
                                <td class="px-3 py-2">{{ $cotizacion['nro_cotizacion'] }}</td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-1 text-xs rounded-md 
                                        {{ $cotizacion['tipo_cotizacion'] === 'Contable' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $cotizacion['tipo_cotizacion'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center">{{ $cotizacion['total_deudas'] }}</td>
                                <td class="px-3 py-2">S/ {{ number_format($cotizacion['monto_pendiente'], 2) }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex space-x-2">
                                        <button type="button"
                                            onclick="abrirModalEditarCotizacion({{ $cotizacion['id_cotizacion'] }}, '{{ addslashes($cotizacion['razon_social']) }}', '{{ addslashes($cotizacion['nro_cotizacion']) }}', '{{ strtolower($cotizacion['tipo_cotizacion']) }}')"
                                            class="flex items-center py-1 rounded-md tooltip text-yellow-600 hover:text-yellow-800">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </button>
                                        <button type="button"
                                            onclick="eliminarCotizacion({{ $cotizacion['id_cotizacion'] }}, '{{ addslashes($cotizacion['nro_cotizacion']) }}')"
                                            class="flex items-center py-1 rounded-md tooltip text-red-600 hover:text-red-800">
                                            <span class="tooltip-text">Eliminar</span>
                                            <x-delete-icon />
                                        </button>
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

{{-- MODAL: EDITAR COTIZACIÓN --}}
<x-modal-show id="modal-editar-cotizacion" title="Editar Cotización">
    <form method="POST" action="" id="form-editar-cotizacion" onsubmit="return handleFormSubmit(event)">
        @csrf
        @method('PUT')
        <input type="hidden" name="id_cotizacion" id="edit-cotizacion-id">
        <input type="hidden" name="from_lista" value="1">
        <div class="space-y-4">
            <div>
                <x-input-label :value="__('Empresa')" />
                <x-text-input id="edit-empresa-nombre" type="text" class="mt-1 block w-full bg-gray-200" disabled />
            </div>

            <div>
                <x-input-label :value="__('N° Cotización')" />
                <x-text-input name="nro_cotizacion" id="edit-nro-cotizacion" type="text" class="mt-1 block w-full"
                    placeholder="Número de cotización" required />
            </div>

            <div>
                <x-input-label :value="__('Tipo Cotización')" />
                <select name="tipo_cotizacion" id="edit-tipo-cotizacion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Seleccionar tipo...</option>
                    <option value="contable">Contable</option>
                    <option value="otros">Otros</option>
                </select>
            </div>

            <div class="flex justify-end pt-4">
                <x-secondary-button type="button"
                    onclick="toggleModal('modal-editar-cotizacion')">Cancelar</x-secondary-button>
                <x-primary-button class="ml-2">Actualizar Cotización</x-primary-button>
            </div>
        </div>
    </form>
</x-modal-show>

{{-- MODAL: CONFIRMAR ELIMINACIÓN --}}
<x-modal-show id="modal-eliminar-cotizacion" title="Confirmar Eliminación">
    <form method="POST" action="" id="form-eliminar-cotizacion" onsubmit="return handleFormSubmit(event)">
        @csrf
        @method('DELETE')
        <div class="space-y-4">
            <p class="text-gray-700">¿Está seguro que desea eliminar la cotización <strong id="eliminar-nro-cotizacion"></strong>?</p>
            <p class="text-red-600 text-sm">Esta acción eliminará también todas las deudas asociadas a esta cotización.</p>
            
            <div class="flex justify-end pt-4">
                <x-secondary-button type="button"
                    onclick="toggleModal('modal-eliminar-cotizacion')">Cancelar</x-secondary-button>
                <x-danger-button class="ml-2">Eliminar</x-danger-button>
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
        setTimeout(() => { isSubmitting = false; }, 3000);
        return true;
    }

    function abrirModalEditarCotizacion(idCotizacion, empresaNombre, nroCotizacion, tipoCotizacion) {
        const form = document.getElementById('form-editar-cotizacion');
        form.action = `/cotizacion_recepcion/${idCotizacion}`;
        document.getElementById('edit-cotizacion-id').value = idCotizacion;
        document.getElementById('edit-empresa-nombre').value = empresaNombre;
        document.getElementById('edit-nro-cotizacion').value = nroCotizacion;
        document.getElementById('edit-tipo-cotizacion').value = tipoCotizacion;
        
        toggleModal('modal-editar-cotizacion');
    }

    function eliminarCotizacion(idCotizacion, nroCotizacion) {
        const form = document.getElementById('form-eliminar-cotizacion');
        form.action = `/cotizacion_recepcion/${idCotizacion}`;
        document.getElementById('eliminar-nro-cotizacion').textContent = nroCotizacion;
        
        toggleModal('modal-eliminar-cotizacion');
    }
</script>

<script>
    $(document).ready(function() {
        $('#myTable-lista-cotizaciones').DataTable({
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