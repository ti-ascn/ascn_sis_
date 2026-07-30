    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Cargos
            </h2>
        </x-slot>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <div class="py-12">
            <div class="w-full px-4">
                <x-success-error-bagdes />

                <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col mb-4">
                    <button type="button" onclick="toggleModal('modal-nueva-cargo')"
                        class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-plus-circle-fill"></i> Nuevo Cargo
                    </button>
                </div>

                <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                    <table class="min-w-full table-auto text-sm" id="myTable-cargo">
                        <thead>
                            <tr class="bg-gray-50 text-left text-gray-700">
                                <th class="px-3 py-2 border-b">RUC</th>
                                <th class="px-3 py-2 border-b">Razón Social</th>
                                <th class="px-3 py-2 border-b">N° Acta</th>
                                <th class="px-3 py-2 border-b">Fecha de Creacion</th>
                                <th class="px-3 py-2 border-b">Estado</th>
                                <th class="px-3 py-2 border-b">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($cargos as $cargo)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2">{{ $cargo->empresaCliente->ruc ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $cargo->empresaCliente->razon_social ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $cargo->nro_cargo }}</td>
                                    <td class="px-3 py-2">
                                        {{ $cargo->fecha ? \Carbon\Carbon::parse($cargo->fecha)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <span
                                            class="px-2 py-1 rounded text-xs {{ $cargo->estado_cargo == 0 ? 'bg-blue-100 text-blue-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $cargo->estado_cargo == 0 ? 'Recepción' : 'Entrega' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="openItemsModal({{ $cargo->id_cargo }})"
                                                class="flex items-center py-1 rounded-md tooltip text-green-600 hover:text-green-800">
                                                <span class="tooltip-text">Items</span>
                                                <x-generate-icon />
                                            </button>
                                            <button type="button" onclick="openDetailsModal({{ $cargo->id_cargo }})"
                                                class="flex items-center py-1 rounded-md tooltip text-blue-600 hover:text-blue-800">
                                                <span class="tooltip-text">Detalles</span>
                                                <x-details-icon />
                                            </button>
                                            <button type="button" onclick="openPdfModal({{ $cargo->id_cargo }})"
                                                class="flex items-center py-1 rounded-md tooltip text-red-600 hover:text-red-800">
                                                <span class="tooltip-text">PDF</span>
                                                <x-pdf-icon />
                                            </button>
                                            <form action="{{ route('cargos.destroy', $cargo->id_cargo) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="margin-top: 3px;"
                                                    class="flex items-center py-1 rounded-md tooltip text-red-600 hover:text-red-800 ">
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form>
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

    {{-- MODAL: Nuevo Cargo --}}
    <x-modal-show id="modal-nueva-cargo" title="Registrar Nuevo Cargo">
        <form method="POST" action="{{ route('cargos.store') }}" class="space-y-4"
            onsubmit="return handleFormSubmit(event, this)">
            @csrf
            <div>
                <x-input-label :value="__('Empresa')" />
                <select name="id_empresa_cliente" id="select-empresa" class="mt-1 block w-full" required>
                    <option value="">Seleccionar empresa...</option>
                    @foreach ($empresas as $empresa)
                        <option value="{{ $empresa->id_empresas_clientes }}">
                            {{ $empresa->ruc }} - {{ $empresa->razon_social }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label :value="__('N° Acta')" />
                <x-text-input name="nro_cargo" type="text" class="mt-1 block w-full" placeholder="Número de acta"
                    required />
            </div>

            <div>
                <x-input-label :value="__('Tipo de Cargo')" />
                <select name="estado_cargo" class="mt-1 block w-full" required>
                    <option value="0">Recepción</option>
                    <option value="1">Entrega</option>
                </select>
            </div>

            <div class="flex justify-end pt-4">
                <x-secondary-button type="button" onclick="toggleModal('modal-nueva-cargo')">
                    Cancelar
                </x-secondary-button>
                <x-primary-button class="ml-2" id="btn-submit-cargo">Registrar Cargo</x-primary-button>
            </div>
        </form>
    </x-modal-show>

    {{-- MODAL: Items del Cargo --}}
    <x-modal-show id="modal-items-cargo" title="Agregar Items al Cargo">
        <form id="form-items" class="space-y-4">
            <input type="hidden" id="cargo_id" name="cargo_id">

            <div id="items-container" class="space-y-2"></div>

            <button type="button" onclick="addItem()" class="btn btn-success w-full">
                <i class="bi bi-plus-circle"></i> Agregar Item
            </button>

            <div class="flex justify-end pt-4 gap-2">
                <x-secondary-button type="button" onclick="toggleModal('modal-items-cargo')">
                    Cancelar
                </x-secondary-button>
                <x-primary-button type="button" onclick="saveItems()" class="ml-2" id="btn-save-items">Guardar
                    Items</x-primary-button>
            </div>
        </form>
    </x-modal-show>

    {{-- MODAL: Detalles del Cargo --}}
    <x-modal-show id="modal-details-cargo" title="Detalles del Cargo">
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Empresa" />
                    <input type="text" id="detail-empresa" class="mt-1 block w-full bg-gray-100" readonly>
                </div>
                <div>
                    <x-input-label value="N° Acta" />
                    <input type="text" id="detail-nro-cargo" class="mt-1 block w-full" readonly>
                </div>
            </div>

            <div>
                <x-input-label value="Items del Cargo" />
                <div id="details-items-container" class="space-y-2 mt-2"></div>
            </div>

            <div class="flex justify-end pt-4 space-x-2">
                <button type="button" id="btn-edit-mode" onclick="toggleEditMode()"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">Editar
                </button>
                <x-primary-button type="button" id="btn-save" onclick="saveChanges()"
                    class="bg-green-600 hover:bg-green-700" style="display:none;">Guardar</x-primary-button>
            </div>
        </div>
    </x-modal-show>

    {{-- MODAL: Fecha para PDF --}}
    <x-modal-show id="modal-pdf-fecha" title="Generar PDF">
        <form id="form-pdf" method="GET" target="_blank">
            <div class="space-y-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <x-input-label value="Fecha del Cargo" />
                        <x-text-input name="fecha" type="datetime-local" id="pdf-fecha" class="mt-1 block w-full"
                            required />
                    </div>
                </div>
                <div class="flex justify-end pt-4 space-x-2">
                    <x-secondary-button type="button" onclick="toggleModal('modal-pdf-fecha')">
                        Cancelar
                    </x-secondary-button>
                    <x-primary-button type="submit" class="bg-green-600 hover:bg-green-700">
                        Generar PDF
                    </x-primary-button>
                </div>
            </div>
        </form>
    </x-modal-show>

    <script>
        let currentCargoId = null;
        let isEditing = false;
        let itemCounter = 0;
        let isSubmitting = false;

        function handleFormSubmit(event, form) {
            if (isSubmitting) {
                event.preventDefault();
                return false;
            }

            isSubmitting = true;
            const submitBtn = form.querySelector('button[type="submit"], #btn-submit-cargo');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }

            setTimeout(() => {
                isSubmitting = false;
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }, 3000);

            return true;
        }

        $(document).ready(function() {
            $('#select-empresa').select2({
                placeholder: 'Buscar empresa...',
                allowClear: true,
                width: '100%'
            });

            $('#myTable-cargo').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                order: [[2, 'asc']],
                responsive: false,
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }],
                language: {
                    url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
                }
            });

            $('#myTable-cargo').on('submit', 'form', function(e) {
                if (!confirm('¿Seguro que deseas eliminar este cargo?')) {
                    e.preventDefault();
                    return false;
                }
            });

            $('#form-pdf').on('submit', function() {
                setTimeout(function() {
                    location.reload();
                }, 1000);
            });
        });

        function openPdfModal(cargoId) {
            $.get(`/cargos/${cargoId}/details`, function(data) {
                $('#form-pdf').attr('action', `/cargos/${cargoId}/pdf`);

                if (data.fecha) {
                    const fechaCargo = new Date(data.fecha);
                    const year = fechaCargo.getFullYear();
                    const month = String(fechaCargo.getMonth() + 1).padStart(2, '0');
                    const day = String(fechaCargo.getDate()).padStart(2, '0');
                    const hours = String(fechaCargo.getHours()).padStart(2, '0');
                    const minutes = String(fechaCargo.getMinutes()).padStart(2, '0');

                    if (data.estado_cargo == 1) {
                        $('#pdf-fecha').attr('type', 'datetime-local');
                        $('#pdf-fecha').val(`${year}-${month}-${day}T${hours}:${minutes}`);
                    } else {
                        $('#pdf-fecha').attr('type', 'date');
                        $('#pdf-fecha').val(`${year}-${month}-${day}`);
                    }
                } else {
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');

                    if (data.estado_cargo == 1) {
                        $('#pdf-fecha').attr('type', 'datetime-local');
                        $('#pdf-fecha').val(`${year}-${month}-${day}T${hours}:${minutes}`);
                    } else {
                        $('#pdf-fecha').attr('type', 'date');
                        $('#pdf-fecha').val(`${year}-${month}-${day}`);
                    }
                }

                toggleModal('modal-pdf-fecha');
            });
        }

        function openItemsModal(cargoId) {
            $('#cargo_id').val(cargoId);
            $('#items-container').empty();
            itemCounter = 0;
            addItem();
            toggleModal('modal-items-cargo');
        }

        function addItem() {
            itemCounter++;
            const container = $('#items-container');
            container.append(`
            <div class="border p-3 rounded-md bg-gray-50" id="item-${itemCounter}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-gray-600">Nombre</label>
                        <input type="text" name="item_${itemCounter}" class="mt-1 block w-full border-gray-300 rounded-md text-sm" placeholder="Nombre del item" required oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600">Tipo de Documento</label>
                        <select name="tipo_${itemCounter}" class="mt-1 block w-full border-gray-300 rounded-md text-sm" required>
                            <option value="Digital">Digital</option>
                            <option value="Físico">Físico</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600">Cantidad</label>
                        <input type="number"
                            name="cantidad_${itemCounter}"
                            class="mt-1 block w-full border-gray-300 rounded-md text-sm"
                            min="1"
                            step="1"
                            onkeydown="return event.key !== '.' && event.key !== ','"
                            required>
                    </div>
                </div>
                <button type="button" onclick="removeItem(${itemCounter})" class="mt-2 text-red-600 hover:text-red-800 text-sm">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>
        `);
        }

        function removeItem(id) {
            $(`#item-${id}`).remove();
        }

        function openDetailsModal(cargoId) {
            currentCargoId = cargoId;
            $.get(`/cargos/${cargoId}/details`, function(data) {
                $('#detail-empresa').val(data.empresa_cliente.razon_social);
                $('#detail-nro-cargo').val(data.nro_cargo);

                const container = $('#details-items-container');
                container.empty();

                data.detalles.forEach((item) => {
                    container.append(`
                    <div class="border p-3 rounded-md bg-gray-50" data-item-id="${item.id_detalle_cargo}">
                        <div class="flex gap-3 items-end">
                            <div class="flex-1">
                                <label class="block text-xs text-gray-600">Nombre</label>
                                <input type="text" class="item-nombre mt-1 block w-full border-gray-300 rounded-md text-sm" value="${item.nombre_documento}" readonly>
                            </div>
                            <div class="w-32">
                                <label class="block text-xs text-gray-600">Tipo</label>
                                <select class="item-tipo mt-1 block w-full border-gray-300 rounded-md text-sm bg-gray-100" disabled>
                                    <option value="Digital" ${item.tipo_documento === 'Digital' ? 'selected' : ''}>Digital</option>
                                    <option value="Físico" ${item.tipo_documento === 'Físico' ? 'selected' : ''}>Físico</option>
                                </select>
                            </div>
                            <div class="w-24">
                                <label class="block text-xs text-gray-600">Cantidad</label>
                                <input type="number" class="item-cantidad mt-1 block w-full border-gray-300 rounded-md text-sm bg-gray-100" value="${item.cantidades}" disabled>
                            </div>
                            <div class="flex gap-1 pb-1">
                                <button type="button" onclick="deleteDetailItem(${item.id_detalle_cargo})" class="btn-delete-item p-2 text-red-600 hover:text-red-800" title="Eliminar">
                                    <i class="bi bi-x-circle text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `);
                });

                toggleModal('modal-details-cargo');
            });
        }

        function toggleEditMode() {
            $('#details-items-container .item-nombre').prop('readonly', false).removeClass('bg-gray-100').addClass(
                'bg-white');
            $('#details-items-container .item-tipo').prop('disabled', false).removeClass('bg-gray-100').addClass(
                'bg-white');
            $('#details-items-container .item-cantidad').prop('disabled', false).removeClass('bg-gray-100').addClass(
                'bg-white');
            $('#detail-nro-cargo').prop('readonly', false).removeClass('bg-gray-100').addClass('bg-white');

            $('#details-items-container .item-nombre').on('input', function() {
                this.value = this.value.toUpperCase();
            });

            $('#btn-edit-mode').hide();
            $('#btn-save').show();
        }

        function deleteDetailItem(itemId) {
            if (isSubmitting) return;
            if (!confirm('¿Seguro que deseas eliminar este item?')) return;
            isSubmitting = true;

            $.ajax({
                url: `/cargos/items/${itemId}/delete`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    $(`[data-item-id="${itemId}"]`).remove();
                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast('Item eliminado correctamente', 'delete');
                    } else {
                        alert('Item eliminado correctamente');
                    }
                    isSubmitting = false;
                },
                error: function() {
                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast('Error al eliminar el item', 'error');
                    } else {
                        alert('Error al eliminar el item');
                    }
                    isSubmitting = false;
                }
            });
        }

        function saveChanges() {
            if (isSubmitting) return;
            isSubmitting = true;

            const nroCargo = $('#detail-nro-cargo').val().toUpperCase();

            $.ajax({
                url: `/cargos/${currentCargoId}/update`,
                method: 'PUT',
                data: {
                    nro_cargo: nroCargo,
                    validate: true
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.error) {
                        if (typeof showAjaxToast === 'function') {
                            showAjaxToast(response.error, 'error');
                        } else {
                            alert(response.error);
                        }
                        isSubmitting = false;
                        return;
                    }
                    $('#details-items-container .border').each(function() {
                        const itemId = $(this).data('item-id');
                        const nombre = $(this).find('.item-nombre').val().toUpperCase();
                        const tipo = $(this).find('.item-tipo').val();
                        const cantidad = $(this).find('.item-cantidad').val();

                        $.ajax({
                            url: `/cargos/items/${itemId}/update`,
                            method: 'PUT',
                            data: {
                                nombre_documento: nombre,
                                tipo_documento: tipo,
                                cantidades: cantidad
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                    });

                    $(`#myTable-cargo tbody tr`).each(function() {
                        const row = $(this);
                        const cargoIdInRow = row.find('button[onclick*="openDetailsModal"]').attr(
                            'onclick').match(/\d+/)[0];
                        if (cargoIdInRow == currentCargoId) {
                            row.find('td:eq(2)').text(nroCargo);
                        }
                    });

                    $('#details-items-container .item-nombre').prop('readonly', true).removeClass('bg-white')
                        .addClass('bg-gray-100');
                    $('#details-items-container .item-tipo').prop('disabled', true).removeClass('bg-white')
                        .addClass('bg-gray-100');
                    $('#details-items-container .item-cantidad').prop('disabled', true).removeClass('bg-white')
                        .addClass('bg-gray-100');
                    $('#detail-nro-cargo').prop('readonly', true).removeClass('bg-white').addClass(
                        'bg-gray-100');

                    $('#btn-save').hide();
                    $('#btn-edit-mode').show();

                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast('Cambios guardados correctamente', 'edit');
                    } else {
                        alert('Cambios guardados correctamente');
                    }
                    isSubmitting = false;
                },
                error: function() {
                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast('Error al guardar los cambios', 'error');
                    } else {
                        alert('Error al guardar los cambios');
                    }
                    isSubmitting = false;
                }
            });
        }

        function saveItems() {
            if (isSubmitting) return;
            isSubmitting = true;

            const btn = document.getElementById('btn-save-items');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');

            const formData = new FormData(document.getElementById('form-items'));
            formData.append('nro_items', itemCounter);

            $.ajax({
                url: '{{ route('cargos.items.store') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast('Items guardados correctamente', 'success');
                    } else {
                        alert('Items guardados correctamente');
                    }
                    toggleModal('modal-items-cargo');
                },
                error: function(xhr) {
                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast('Error al guardar los items', 'error');
                    } else {
                        alert('Error al guardar los items');
                    }
                },
                complete: function() {
                    isSubmitting = false;
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
        }
    </script>
