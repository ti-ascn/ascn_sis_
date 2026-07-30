<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Archivos Legales') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('capital.index') }}"
                    class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                    <i class="bi bi-cash-stack"></i> Capitales
                </a>
            </div>
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg p-6 mt-5">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Empresas Activas</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse border border-gray-200" id="myTable-archivos">
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Razón Social</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    RUC</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($empresas as $empresa)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-b">
                                        {{ $empresa->razon_social }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-b">
                                        {{ $empresa->ruc }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium border-b">
                                        <div class="flex space-x-3">
                                            <button data-id="{{ $empresa->id_empresas_clientes }}"
                                                data-nombre="{{ $empresa->razon_social }}"
                                                class="btn-ver-archivos flex items-center py-1 rounded-md tooltip text-blue-600 hover:text-blue-800 transition duration-300">
                                                <span class="tooltip-text">Ver Archivos</span>
                                                <i class="bi bi-folder2-open text-xl"></i>
                                            </button>

                                            <button data-id="{{ $empresa->id_empresas_clientes }}"
                                                data-nombre="{{ $empresa->razon_social }}"
                                                class="btn-subir-archivo flex items-center py-1 rounded-md tooltip text-green-600 hover:text-green-800 transition duration-300">
                                                <span class="tooltip-text">Subir Archivo</span>
                                                <i class="bi bi-cloud-upload text-xl"></i>
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
    </div>
</x-app-layout>


<!-- Modal para ver archivos (GRANDE) -->
<x-modal-show id="modalVerArchivos" title="Archivos Legales" size="large">
    <div class="space-y-4">
        <h4 class="font-semibold text-gray-700">Empresa: <span id="nombreEmpresaVer" class="text-blue-600"></span>
        </h4>
        <div class="overflow-x-auto border rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Acción</th>
                    </tr>
                </thead>
                <tbody id="listaArchivos" class="bg-white divide-y divide-gray-200">
                    <!-- Se llena con AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</x-modal-show>

<!-- Modal para subir archivo -->
<x-modal-show id="modalSubirArchivo" title="Subir Archivo Legal">
    <div class="space-y-4">
        <h4 class="font-semibold text-gray-700">Empresa: <span id="nombreEmpresaSubir" class="text-green-600"></span>
        </h4>
        <form id="formSubirArchivo" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="id_empresa_cliente" id="id_empresa_cliente_hidden">

            <div>
                <x-input-label for="tipo_archivo" :value="__('Tipo de Archivo')" />
                <select name="tipo_archivo" id="tipo_archivo"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required>
                    <option value="">Seleccione un tipo</option>
                    <option value="Constitución de empresa">Constitución de empresa</option>
                    <option value="Cambio de gerente">Cambio de gerente</option>
                    <option value="Modificación de objeto">Modificación de objeto</option>
                    <option value="Modificación de estatuto">Modificación de estatuto</option>
                    <option value="Transferencia de acciones">Transferencia de acciones</option>
                    <option value="Libro de actas">Libro de actas</option>
                </select>
            </div>

            <div>
                <x-input-label for="archivo" :value="__('Seleccionar Archivo')" />
                <input type="file" name="archivo" id="archivo"
                    class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    required>
                <p class="mt-1 text-xs text-gray-500">Formatos: PDF, JPG, PNG, DOC, DOCX. Máx 40MB.</p>
            </div>

            <div class="flex justify-end mt-6">
                <x-primary-button type="submit" id="btnGuardarArchivo" class="bg-green-600 hover:bg-green-700">
                    <i class="bi bi-check-circle mr-2"></i> {{ __('Subir Archivo') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-modal-show>

<!-- Modal para REEMPLAZAR archivo -->
<x-modal-show id="modalReemplazarArchivo" title="Reemplazar Archivo Legal" size="small">
    <div class="space-y-4">
        <p class="text-sm text-gray-600">Seleccione el nuevo archivo para reemplazar el anterior. El nombre y tipo
            se mantendrán.</p>
        <form id="formReemplazarArchivo" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="id_archivo_replace" id="id_archivo_replace_hidden">

            <div>
                <x-input-label for="archivo_replace" :value="__('Nuevo Archivo')" />
                <input type="file" name="archivo" id="archivo_replace"
                    class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"
                    required>
            </div>

            <div class="flex justify-end mt-6">
                <x-primary-button type="submit" id="btnReemplazarArchivo" class="bg-orange-600 hover:bg-orange-700">
                    <i class="bi bi-arrow-repeat mr-2"></i> {{ __('Reemplazar') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-modal-show>

<script>
    // ─── DATATABLES ──────────────────────────────────────────────────────────
    $('#myTable-archivos').DataTable({
        responsive: false,
        paging: true,
        ordering: true,
        pageLength: 100,
        language: {
            url: '/js/datatables/lang/es-ES.json'
        },
        order: [
            [0, 'asc']
        ]
    });
    const TIPO_USUARIO = {{ Auth::user()->tipo_usuario }};

    document.addEventListener('DOMContentLoaded', function() {
        let currentEmpresaId = null;

        // Función para manejar las peticiones AJAX con fetch (Vanilla JS)
        function cargarArchivos(id, nombre) {
            currentEmpresaId = id;
            const nombreVer = document.getElementById('nombreEmpresaVer');
            const lista = document.getElementById('listaArchivos');

            if (nombreVer) nombreVer.innerText = nombre;
            if (lista) lista.innerHTML =
                '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Cargando...</td></tr>';

            if (typeof toggleModal === 'function') {
                toggleModal('modalVerArchivos');
            } else {
                const modal = document.getElementById('modalVerArchivos');
                if (modal) modal.classList.remove('hidden', 'opacity-0');
            }

            fetch(`/archivos-legales/empresa/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (!lista) return;
                    let html = '';
                    if (data.length === 0) {
                        html =
                            '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">No hay archivos registrados.</td></tr>';
                    } else {
                        data.forEach(archivo => {
                            let deleteBtn = '';
                            if (TIPO_USUARIO == 1) {
                                deleteBtn = `
                                        <button class="btn-eliminar-archivo text-red-600 hover:text-red-900 tooltip" data-id="${archivo.id}">
                                            <span class="tooltip-text">Eliminar</span>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    `;
                            }

                            html += `
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-gray-900">${archivo.nombre}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">${archivo.tipo}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">${archivo.usuario}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">${archivo.fecha}</td>
                                        <td class="px-4 py-2 text-sm font-medium">
                                            <div class="flex justify-center items-center space-x-3">
                                                <a href="${archivo.url}" target="_blank" class="text-blue-600 hover:text-blue-900 tooltip">
                                                    <span class="tooltip-text">Descargar</span>
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <button class="btn-abrir-reemplazar text-orange-600 hover:text-orange-900 tooltip" data-id="${archivo.id}">
                                                    <span class="tooltip-text">Reemplazar</span>
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                                ${deleteBtn}
                                            </div>
                                        </td>
                                    </tr>
                                `;
                        });
                    }
                    lista.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (lista) lista.innerHTML =
                        '<tr><td colspan="5" class="px-4 py-4 text-center text-red-500">Error al cargar archivos.</td></tr>';
                });
        }

        // Delegación de eventos para botones de la PÁGINA PRINCIPAL (fuera de modales)
        document.addEventListener('click', function(e) {
            // Botón Ver Archivos
            const btnVer = e.target.closest('.btn-ver-archivos');
            if (btnVer) {
                const id = btnVer.getAttribute('data-id');
                const nombre = btnVer.getAttribute('data-nombre');
                cargarArchivos(id, nombre);
            }

            // Botón Subir Archivo
            const btnSubir = e.target.closest('.btn-subir-archivo');
            if (btnSubir) {
                const id = btnSubir.getAttribute('data-id');
                const nombre = btnSubir.getAttribute('data-nombre');

                const idHidden = document.getElementById('id_empresa_cliente_hidden');
                const nombreSubir = document.getElementById('nombreEmpresaSubir');
                const form = document.getElementById('formSubirArchivo');

                if (idHidden) idHidden.value = id;
                if (nombreSubir) nombreSubir.innerText = nombre;
                if (form) form.reset();

                currentEmpresaId = id; // Asegurar que tenemos el ID actual

                if (typeof toggleModal === 'function') {
                    toggleModal('modalSubirArchivo');
                } else {
                    const modal = document.getElementById('modalSubirArchivo');
                    if (modal) modal.classList.remove('hidden', 'opacity-0');
                }
            }
        });

        // Delegación de eventos DENTRO del modal de ver archivos
        // El listener va en #listaArchivos (tbody) porque el modal usa
        // event.stopPropagation() en su div interno, bloqueando bubbling al div exterior
        const listaArchivosEl = document.getElementById('listaArchivos');
        if (listaArchivosEl) {
            listaArchivosEl.addEventListener('click', function(e) {
                // Botón Eliminar Archivo
                const btnEliminar = e.target.closest('.btn-eliminar-archivo');
                if (btnEliminar) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!confirm('¿Está seguro de eliminar este archivo permanentemente?')) return;

                    const id = btnEliminar.getAttribute('data-id');
                    fetch(`/archivos-legales/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showAjaxToast(data.message, 'success');
                                cargarArchivos(currentEmpresaId, document.getElementById(
                                    'nombreEmpresaVer').innerText);
                            } else {
                                showAjaxToast(data.message || 'Error al eliminar', 'error');
                            }
                        })
                        .catch(error => {
                            showAjaxToast('Ocurrió un error al procesar la solicitud.', 'error');
                        });
                    return;
                }

                // Botón Abrir Reemplazar
                const btnAbrirReemplazar = e.target.closest('.btn-abrir-reemplazar');
                if (btnAbrirReemplazar) {
                    e.preventDefault();
                    e.stopPropagation();
                    const id = btnAbrirReemplazar.getAttribute('data-id');
                    const idHidden = document.getElementById('id_archivo_replace_hidden');
                    if (idHidden) idHidden.value = id;

                    const form = document.getElementById('formReemplazarArchivo');
                    if (form) form.reset();

                    if (typeof toggleModal === 'function') {
                        toggleModal('modalReemplazarArchivo');
                    } else {
                        const modal = document.getElementById('modalReemplazarArchivo');
                        if (modal) modal.classList.remove('hidden', 'opacity-0');
                    }
                    return;
                }
            });
        }

        // Manejar envío del formulario de SUBIDA
        const formSubir = document.getElementById('formSubirArchivo');
        if (formSubir) {
            formSubir.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('btnGuardarArchivo');
                const originalHtml = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i> Subiendo...';

                const formData = new FormData(this);

                fetch('/archivos-legales', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => {
                                throw err;
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showAjaxToast(data.message, 'success');
                            if (typeof toggleModal === 'function') {
                                toggleModal('modalSubirArchivo');
                            } else {
                                document.getElementById('modalSubirArchivo').classList.add('hidden',
                                    'opacity-0');
                            }
                        } else {
                            showAjaxToast('Error: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        let msg = 'Ocurrió un error al subir el archivo.';
                        if (error.message) msg += '<br>' + error.message;
                        if (error.errors) {
                            msg += '<br>' + Object.values(error.errors).flat().join('<br>');
                        }
                        showAjaxToast(msg, 'error');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    });
            });
        }

        // Manejar envío del formulario de REEMPLAZO
        const formReplace = document.getElementById('formReemplazarArchivo');
        if (formReplace) {
            formReplace.addEventListener('submit', function(e) {
                e.preventDefault();
                const id = document.getElementById('id_archivo_replace_hidden').value;
                const btn = document.getElementById('btnReemplazarArchivo');
                const originalHtml = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i> Reemplazando...';

                const formData = new FormData(this);

                fetch(`/archivos-legales/${id}/replace`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAjaxToast(data.message, 'success');
                            if (typeof toggleModal === 'function') {
                                toggleModal('modalReemplazarArchivo');
                            } else {
                                const modal = document.getElementById('modalReemplazarArchivo');
                                if (modal) modal.classList.add('hidden', 'opacity-0');
                            }
                            cargarArchivos(currentEmpresaId, document.getElementById(
                                'nombreEmpresaVer').innerText);
                        } else {
                            showAjaxToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAjaxToast('Ocurrió un error al reemplazar el archivo.', 'error');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    });
            });
        }
    });
</script>
