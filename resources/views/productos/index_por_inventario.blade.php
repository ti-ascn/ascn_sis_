@php
    $unidades = \App\Models\UnidadMedida::where('estado', 1)->orderBy('nombre')->get();
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Productos del Inventario: ' . $inventario->nombre_codigo . ' – ' . $inventario->empresa->razon_social) }}
            </h2>
            <span class="text-sm text-gray-700 font-semibold">
                Precio del dólar:
                @if (Auth::user()->tipo_usuario == 1 ||
                        Auth::user()->tipo_usuario == 2 ||
                        (Auth::user()->tipo_usuario == 3 && Auth::user()->id_area == 7 && Auth::user()->puede_editar))
                    <span onclick="toggleModal('modalPrecioDolar')"
                        class="text-green-700 font-bold cursor-pointer hover:underline transition duration-200"
                        title="Haz clic para actualizar">
                        S/ {{ number_format($precioDolar->precio, 2) }}
                    </span>
                @else
                    <span class="text-green-700 font-bold">
                        S/ {{ number_format($precioDolar->precio, 2) }}
                    </span>
                @endif
            </span>
        </div>
    </x-slot>

    <!-- URL base para edición de productos (NECESARIO PARA EL AJAX) -->
    <meta name="url-edit-producto" content="{{ url('/productos') }}">


    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                {{-- <a href="{{ route('productos.create', ['id_inventario' => $inventario->id_inventario]) }}"
        class="block bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
        {{ __('Crear Producto') }}
        </a> --}}
                {{-- !considerar usar ajax --}}
                <button onclick="toggleModal('modalCrearProducto')"
                    class="flex items-center justify-center gap-2 bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 w-full md:w-auto">
                    <i class="bi bi-plus-circle-fill"></i> Crear Producto
                </button>

                <div class="relative w-full md:w-auto">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center justify-center gap-2 bg-yellow-500 px-4 py-2 rounded-md 
                                hover:bg-yellow-600 transition duration-300 text-white w-full md:w-auto">
                                <i class="bi bi-receipt-cutoff"></i> Crear Factura <i
                                    class="bi bi-chevron-down text-sm"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="absolute right-0 z-50 w-48 bg-white shadow-lg rounded-md border p-2">
                                <x-dropdown-link :href="route('factura.create_empresa', $inventario->id_inventario)">
                                    <i class="bi bi-building me-2"></i> Empresa
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('factura.create_cliente', $inventario->id_inventario)">
                                    <i class="bi bi-person me-2"></i> Cliente
                                </x-dropdown-link>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                <a href="{{ route('facturas.inventario', $inventario->id_inventario) }}"
                    class="flex items-center justify-center gap-2 bg-indigo-500 px-4 py-2 rounded-md hover:bg-indigo-600 transition duration-300 text-white text-center w-full md:w-auto">
                    <i class="bi bi-file-earmark-text-fill"></i> Ver Facturas
                </a>

                @if (Auth::user()->tipo_usuario == 1 ||
                        Auth::user()->tipo_usuario == 2 ||
                        (Auth::user()->tipo_usuario == 3 && Auth::user()->id_area == 7 && Auth::user()->puede_editar))
                    <span id="contadorSeleccionados" title="Haz clic para deseleccionar todos"
                        class="text-sm font-medium text-gray-700 hidden hover:underline cursor-pointer">
                        ✅ <span id="contador">0</span> productos seleccionados
                    </span>
                    <button onclick="toggleModal('modalTransferirProductos')"
                        class="flex items-center justify-center gap-2 bg-gray-500 px-4 py-2 rounded-md text-white hover:bg-gray-600 transition w-full md:w-auto">
                        <i class="bi bi-arrow-left-right"></i> Transferir Productos
                    </button>
                    <button id="seleccionarTodosGlobal"
                        class="flex items-center justify-center gap-2 bg-indigo-500 px-4 py-2 rounded-md text-white hover:bg-indigo-600 transition w-full md:w-auto">
                        <i class="bi bi-check-all"></i> Seleccionar todos los productos
                    </button>
                @endif

                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <button onclick="abrirModal()"
                        class="flex items-center justify-center gap-2 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 w-full md:w-auto">
                        <i class="bi bi-sliders2"></i> Permisos de Edición
                    </button>

                    <a href="{{ route('unidades.index') }}"
                        class="flex items-center justify-center gap-2 bg-orange-500 px-4 py-2 rounded-md hover:bg-orange-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-file-earmark-text-fill"></i> Unidades de Medida
                    </a>

                    <button onclick="toggleModal('modalProductosInactivos')"
                        class="flex items-center justify-center gap-2 bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition duration-300 w-full md:w-auto">
                        <i class="bi bi-x-circle-fill"></i> Pro. Inactivos
                    </button>

                    {{-- <form method="POST" action="{{ route('productos.vaciar') }}"
            onsubmit="return confirm('¿Estás seguro de que deseas eliminar todos los productos del inventario? Esta acción no se puede deshacer.')">
            @csrf
            <input type="hidden" name="id_inventario" value="{{ $inventario->id_inventario }}">
            <button type="submit"
                class="block bg-red-500 px-4 py-2 rounded-md hover:bg-red-600 transition duration-300 text-white text-center w-full md:w-auto">
                Vaciar Inventario
            </button>
        </form> --}}

                    {{-- !OCULTAR IMPORTAR POR .CSV --}}
                    {{-- <button onclick="document.getElementById('formImportar').classList.toggle('hidden')"
                        class="bg-indigo-500 px-4 py-2 rounded-md text-white hover:bg-indigo-600 transition w-full md:w-auto">
                        <i class="bi bi-upload me-1"></i> Importar Productos
                    </button>
                    <div id="formImportar" class="mt-4 hidden">
                        <form action="{{ route('productos.importar') }}" method="POST" enctype="multipart/form-data"
                            class="flex flex-col md:flex-row items-center gap-4">
                            @csrf
                            <input type="hidden" name="id_inventario" value="{{ $inventario->id_inventario }}">
                            <input type="file" name="archivo" accept=".csv" required
                                class="border border-gray-300 p-2 rounded">
                            <x-primary-button>Subir Archivo</x-primary-button>
                        </form>
                        <p class="mt-2 text-sm text-gray-500 italic">
                            Formato permitido: <strong>.csv</strong> (la primera fila se ignora).<br>
                            <a href="{{ route('descargar.plantilla_productos') }}"
                                class="text-blue-600 underline hover:text-blue-800" target="_blank">
                                Descargar plantilla de ejemplo
                            </a>
                        </p>
                    </div> --}}
                @endif
            </div>


            <br />

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-[1200px] table-auto" id="myTable-productos"
                    data-has-checkbox="{{ Auth::user()->tipo_usuario == 1 ||
                    Auth::user()->tipo_usuario == 2 ||
                    (Auth::user()->tipo_usuario == 3 && Auth::user()->id_area == 7 && Auth::user()->puede_editar)
                        ? 1
                        : 0 }}">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="hidden"></th> <!-- Columna oculta para orden -->
                            @if (Auth::user()->tipo_usuario == 1 ||
                                    Auth::user()->tipo_usuario == 2 ||
                                    (Auth::user()->tipo_usuario == 3 && Auth::user()->id_area == 7 && Auth::user()->puede_editar))
                                <th class="px-4 py-2 border-b text-center">
                                    ✅
                                </th>
                            @endif
                            <th class="px-4 py-2 border-b">Código</th>
                            <th class="px-4 py-2 border-b">Nombre</th>
                            <th class="px-4 py-2 border-b">U.M</th>
                            <th class="px-4 py-2 border-b">Precio (S/)</th>
                            <th class="px-4 py-2 border-b">Precio (USD)</th>
                            <th class="px-4 py-2 border-b">Peso</th>
                            {{-- <th class="px-4 py-2 border-b">Estado</th> --}}
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $producto)
                            <tr class="hover:bg-gray-50">
                                {{-- Columna oculta solo con número --}}
                                <td class="hidden">{{ preg_replace('/[^0-9]/', '', $producto->id_producto) }}</td>
                                @if (Auth::user()->tipo_usuario == 1 ||
                                        Auth::user()->tipo_usuario == 2 ||
                                        (Auth::user()->tipo_usuario == 3 && Auth::user()->id_area == 7 && Auth::user()->puede_editar))
                                    <td class="px-4 py-2 border-b text-center">
                                        <input type="checkbox" class="checkbox-transferencia"
                                            value="{{ $producto->id_producto }}">
                                    </td>
                                @endif
                                <td class="px-4 py-2 border-b font-mono">{{ $producto->id_producto }}</td>

                                {{-- Nombre --}}
                                {{-- <td class="px-4 py-2 border-b">
                                    <input type="text" data-id="{{ $producto->id_producto }}" data-field="nombre"
                                        class="editable-producto border-gray-300 w-full px-2 py-1 rounded"
                                        value="{{ $producto->nombre }}">
                                </td> --}}
                                <td class="px-4 py-2 border-b">{{ $producto->nombre }}</td>

                                {{-- Unidad de Medida --}}
                                <td class="px-4 py-2 border-b">
                                    <select data-producto-id="{{ $producto->id_producto }}"
                                        class="select-unidad-medida border-gray-300 rounded px-2 py-1 text-sm w-full">
                                        @foreach ($unidades as $um)
                                            <option value="{{ $um->nombre }}"
                                                {{ $producto->unidad_medida == $um->nombre ? 'selected' : '' }}>
                                                {{ $um->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- Precio S/ --}}
                                <td class="px-4 py-2 border-b">
                                    S/ <input type="number" step="0.0001" data-id="{{ $producto->id_producto }}"
                                        data-field="precio_unitario"
                                        class="editable-producto border-gray-300 w-36 px-2 py-1 rounded"
                                        value="{{ number_format($producto->precio_unitario, 4, '.', '') }}">
                                </td>

                                {{-- Precio USD (solo lectura) --}}
                                <td class="px-4 py-2 border-b">
                                    $ {{ number_format($producto->precio_unitario / $precioDolar->precio, 4) }}
                                </td>

                                {{-- Peso --}}
                                <td class="px-4 py-2 border-b">
                                    <input type="number" step="0.01" data-id="{{ $producto->id_producto }}"
                                        data-field="peso"
                                        class="editable-producto border-gray-300 w-32 px-2 py-1 rounded"
                                        value="{{ $producto->peso }}">
                                </td>

                                {{-- Acciones --}}
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('productos.edit', $producto->id_producto) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #F59E0B; transition: color 0.3s;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </a>
                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                            <form action="{{ route('productos.destroy', $producto->id_producto) }}"
                                                method="POST" style="display:inline;" class="form-desactivar-producto">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #6b7280; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#4b5563';"
                                                    onmouseout="this.style.color='#6b7280';">
                                                    <span class="tooltip-text">Desactivar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Badge de éxito --}}
        <div id="badgeGuardado"
            class="hidden fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-6 py-3 rounded-xl shadow-2xl text-base font-semibold transition-all duration-500 z-50 translate-y-10 opacity-0">
            Guardado Correctamente
        </div>

        {{-- Badge de error --}}
        <div id="badgeError"
            class="hidden fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-xl shadow-2xl text-base font-semibold transition-all duration-500 z-50 translate-y-10 opacity-0">
            Error al Guardar
        </div>
    </div>
</x-app-layout>

<x-modal-show id="modalCrearProducto" title="Crear nuevo producto">
    <form method="POST" action="{{ route('productos.store') }}" autocomplete="off">
        @csrf
        <input type="hidden" name="id_inventario" value="{{ $inventario->id_inventario }}">

        <div class="mb-4">
            <label for="nombre_modal" class="block text-sm font-medium">Nombre</label>
            <input type="text" name="nombre" id="nombre_modal" required
                class="w-full border-gray-300 rounded-md">
        </div>

        <div class="mb-4">

            <label for="unidad_medida_modal" class="block text-sm font-medium">Unidad de Medida</label>
            <select name="unidad_medida" id="unidad_medida_modal" required class="w-full border-gray-300 rounded-md">
                <option value="">Seleccione una unidad</option>

                @foreach ($unidades as $u)
                    <option value="{{ $u->nombre }}">{{ $u->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="precio_unitario_modal" class="block text-sm font-medium">Precio Unitario</label>
            <input type="number" step="0.0001" name="precio_unitario" id="precio_unitario_modal" required
                class="w-full border-gray-300 rounded-md">
        </div>

        <div class="mb-4">
            <label for="peso_modal" class="block text-sm font-medium">Peso (opcional)</label>
            <input type="number" step="0.01" name="peso" id="peso_modal"
                class="w-full border-gray-300 rounded-md">
        </div>

        <input type="hidden" name="estado" value="1">

        <div class="flex justify-end">
            <x-primary-button>Guardar</x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- !AJAX CREAR PRODUCTO --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const precioDolar = {{ $precioDolar->precio }};

        let badgeTimer = null;

        // ---- Función para mostrar badge de guardado ----
        function mostrarBadge(tipo = 'success') {
            const badge = document.getElementById(tipo === 'success' ? 'badgeGuardado' : 'badgeError');

            // Limpiar timer anterior si existe
            if (badgeTimer) {
                clearTimeout(badgeTimer);
                // Ocultar badge actual inmediatamente
                document.querySelectorAll('[id^="badge"]').forEach(b => {
                    b.classList.add('hidden', 'opacity-0', 'translate-y-10');
                    b.classList.remove('opacity-100', 'translate-y-0');
                });
            }

            // Mostrar nuevo badge
            badge.classList.remove('hidden', 'opacity-0', 'translate-y-10');
            badge.classList.add('opacity-100', 'translate-y-0');

            // Programar ocultamiento
            badgeTimer = setTimeout(() => {
                badge.classList.add('opacity-0', 'translate-y-10');
                badge.classList.remove('opacity-100', 'translate-y-0');

                setTimeout(() => {
                    badge.classList.add('hidden');
                    badgeTimer = null;
                }, 500);
            }, 3000);
        }

        // ---- Edición en línea ----
        function inicializarEdicionEnLinea() {
            document.querySelectorAll('.editable-producto').forEach(input => {
                input.addEventListener('change', function() {
                    const id = this.dataset.id;
                    const field = this.dataset.field;
                    const value = this.value;

                    const data = {};
                    data[field] = value;

                    fetch(`/productos/${id}/ajax-update`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Error en la respuesta');
                            return response.json();
                        })
                        .then(result => {
                            if (result.success) {
                                mostrarBadge();

                                if (field === 'precio_unitario') {
                                    const precioUSD = (value / precioDolar).toFixed(4);
                                    const fila = this.closest('tr');
                                    const celdaUSD = fila.querySelector('td:nth-child(6)');
                                    if (celdaUSD) {
                                        celdaUSD.textContent = `$ ${precioUSD}`;
                                    }
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            mostrarBadge('error');
                        });
                });
            });
        }

        // ---- Select de Unidad de Medida ----
        function inicializarSelectsUM() {
            document.querySelectorAll('.select-unidad-medida').forEach(select => {
                select.addEventListener('change', function() {
                    const productoId = this.dataset.productoId;
                    const nuevaUM = this.value;

                    fetch(`/productos/${productoId}/unidad-medida`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                unidad_medida: nuevaUM
                            })
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Error al actualizar');
                            return response.json();
                        })
                        .then(result => {
                            if (result.success) {
                                mostrarBadge();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            mostrarBadge('error');
                        });
                });
            });
        }

        // ---- Desactivar producto con AJAX ----
        function inicializarDesactivarAjax() {
            document.querySelectorAll('form.form-desactivar-producto').forEach(form => {
                // Remover listeners previos
                const newForm = form.cloneNode(true);
                form.parentNode.replaceChild(newForm, form);
                
                newForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (!confirm('¿Estás seguro de que deseas desactivar este producto?')) {
                        return;
                    }

                    const formData = new FormData(this);

                    fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: formData
                        })
                        .then(async (response) => {
                            const contentType = response.headers.get('content-type');
                            if (contentType && contentType.includes('application/json')) {
                                return await response.json();
                            } else {
                                throw new Error('Respuesta no JSON del servidor');
                            }
                        })
                        .then(data => {
                            if (data.success) {
                                const fila = this.closest('tr');
                                fila.style.opacity = '0.5';

                                setTimeout(() => {
                                    fila.remove();
                                    mostrarBadge();
                                }, 300);
                            } else {
                                mostrarBadge('error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            mostrarBadge('error');
                        });
                });
            });
        }

        // ---- Crear producto con AJAX ----
        const formCrear = document.querySelector('#modalCrearProducto form');
        if (formCrear) {
            formCrear.addEventListener('submit', function(e) {
                e.preventDefault();

                // Mostrar loading
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creando...';
                submitBtn.disabled = true;

                // Preparar datos
                const formData = new FormData(this);

                fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(async (response) => {
                        const contentType = response.headers.get('content-type');

                        if (contentType && contentType.includes('application/json')) {
                            const data = await response.json();
                            return {
                                data: data,
                                status: response.status,
                                ok: response.ok
                            };
                        } else {
                            const text = await response.text();
                            console.error('Respuesta no JSON:', text.substring(0, 200));
                            throw new Error('El servidor respondió con HTML en lugar de JSON');
                        }
                    })
                    .then(({
                        data,
                        status,
                        ok
                    }) => {
                        if (ok) {
                            if (data.success) {
                                // ✅ SUCCESS - Agregar producto a la tabla sin recargar
                                agregarProductoATabla(data.producto);
                                mostrarBadge();
                                toggleModal('modalCrearProducto');
                                this.reset();
                            } else {
                                throw new Error(data.error || 'Error desconocido');
                            }
                        } else {
                            throw new Error(data.error || `Error ${status}`);
                        }
                    })
                    .catch(error => {
                        console.error('Error completo:', error);
                        let mensaje = 'Error al crear el producto';

                        if (error.message.includes('HTML')) {
                            mensaje = 'Error del servidor. Verifica la consola para más detalles.';
                        } else if (error.message) {
                            mensaje = error.message;
                        }

                        alert(mensaje);
                        mostrarBadge('error');
                    })
                    .finally(() => {
                        // Restaurar botón
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });
        }

        // ---- Función para agregar producto a la tabla sin recargar ----
        function agregarProductoATabla(producto) {
            const tabla = $('#myTable-productos').DataTable();
            const hasCheckbox = document.getElementById('myTable-productos').dataset.hasCheckbox === '1';

            // Crear la fila HTML
            const nuevaFila = crearFilaProducto(producto, hasCheckbox);

            // Agregar a DataTables
            tabla.row.add(nuevaFila).draw(false);

            // Re-inicializar los event listeners para la nueva fila
            setTimeout(() => {
                inicializarEdicionEnLinea();
                inicializarSelectsUM();
                inicializarDesactivarAjax();
            }, 100);
        }

        // ---- Función para crear la fila HTML del producto ----
        function crearFilaProducto(prod, hasCheckbox) {
            const soloNumero = (prod.id_producto || '').replace(/\D/g, '');

            // Crear elemento tr
            const fila = document.createElement('tr');
            fila.className = 'hover:bg-gray-50';

            // Columna hidden
            let html = `<td class="hidden">${soloNumero}</td>`;

            // Checkbox si aplica
            if (hasCheckbox) {
                html += `
            <td class="px-4 py-2 border-b text-center">
                <input type="checkbox" class="checkbox-transferencia" value="${prod.id_producto}">
            </td>
        `;
            }

            // Código
            html += `<td class="px-4 py-2 border-b font-mono">${prod.id_producto}</td>`;

            // Nombre (editable)
            /*html += `
        <td class="px-4 py-2 border-b">
            <input type="text" data-id="${prod.id_producto}" data-field="nombre"
                class="editable-producto border-gray-300 w-full px-2 py-1 rounded"
                value="${(prod.nombre || '').toUpperCase()}">
        </td>
    `;*/
            html += `<td class="px-4 py-2 border-b font-mono">${(prod.nombre || '').toUpperCase()}</td>`;

            // Unidad de Medida (select)
            html += `
        <td class="px-4 py-2 border-b">
            <select data-producto-id="${prod.id_producto}"
                class="select-unidad-medida border-gray-300 rounded px-2 py-1 text-sm w-full">
    `;

            // Opciones del select
            @foreach ($unidades as $um)
                html +=
                    `<option value="{{ $um->nombre }}" ${prod.unidad_medida == '{{ $um->nombre }}' ? 'selected' : ''}>{{ $um->nombre }}</option>`;
            @endforeach

            html += `</select></td>`;

            // Precio S/
            html += `
        <td class="px-4 py-2 border-b">
            S/ <input type="number" step="0.0001" data-id="${prod.id_producto}"
                data-field="precio_unitario"
                class="editable-producto border-gray-300 w-36 px-2 py-1 rounded"
                value="${Number(prod.precio_unitario).toFixed(4)}">
        </td>
    `;

            // Precio USD
            html += `
        <td class="px-4 py-2 border-b">
            $ ${(Number(prod.precio_unitario) / {{ $precioDolar->precio }}).toFixed(4)}
        </td>
    `;

            // Peso
            html += `
        <td class="px-4 py-2 border-b">
            <input type="number" step="0.01" data-id="${prod.id_producto}"
                data-field="peso"
                class="editable-producto border-gray-300 w-32 px-2 py-1 rounded"
                value="${prod.peso || ''}">
        </td>
    `;

            // Acciones
            const urlBaseEditar = document.querySelector('meta[name="url-edit-producto"]').content;

            // Acciones
            html += `
            <td class="px-4 py-2 border-b">
                <div class="flex space-x-2">

                    <a href="${urlBaseEditar}/${prod.id_producto}/edit"
                        class="flex items-center py-1 rounded-md tooltip"
                        style="color: #F59E0B; transition: color 0.3s;"
                        onmouseover="this.style.color='#D97706';"
                        onmouseout="this.style.color='#F59E0B';">
                        <span class="tooltip-text">Editar</span>
                        <x-edit-icon />
                    </a>

                    @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <form action="/productos/${prod.id_producto}/destroy" method="POST"
                        style="display:inline;" class="form-desactivar-producto">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                            class="flex items-center py-1 rounded-md tooltip"
                            style="color: #6b7280; transition: color 0.3s;"
                            onmouseover="this.style.color='#4b5563';"
                            onmouseout="this.style.color='#6b7280';">
                            <span class="tooltip-text">Desactivar</span>
                            <x-delete-icon />
                        </button>
                    </form>
                    @endif

                </div>
            </td>`;

            fila.innerHTML = html;
            return fila;
        }

        // Inicializar todo cuando el DOM esté listo
        inicializarEdicionEnLinea();
        inicializarSelectsUM();
        inicializarDesactivarAjax();

        // Reinicializar cuando DataTables redibuje la tabla
        $('#myTable-productos').on('draw.dt', function() {
            setTimeout(() => {
                inicializarEdicionEnLinea();
                inicializarSelectsUM();
                inicializarDesactivarAjax();
            }, 100);
        });
    });
</script>
{{-- !FIN AJAX CREAR PRODUCTO --}}


<x-modal-show id="modalTransferirProductos" title="Transferir productos a otro inventario">
    <form method="POST" action="{{ route('productos.transferir') }}">
        @csrf

        <div class="mb-4">
            <label for="id_inventario_destino" class="block text-sm font-medium">Inventario destino</label>
            <select name="id_inventario_destino" id="id_inventario_destino" class="w-full border-gray-300 rounded-md"
                required>
                <option value="">Selecciona inventario</option>
                @foreach (\App\Models\Inventario::with('empresa')->where('id_inventario', '!=', $inventario->id_inventario)->get() as $inv)
                    <option value="{{ $inv->id_inventario }}">
                        {{ $inv->nombre_codigo }} - {{ $inv->empresa->razon_social }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end">
            <x-primary-button>Transferir</x-primary-button>
        </div>
    </form>
</x-modal-show>

<x-modal-show id="modalPrecioDolar" title="Actualizar Precio del Dólar">
    <form method="POST" action="{{ route('precio_dolar.update') }}">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label for="precio" class="block text-sm font-medium">Precio actual en S/</label>
            <input type="number" step="0.001" name="precio" id="precio"
                value="{{ number_format($precioDolar->precio, 2) }}" class="w-full border-gray-300 rounded-md"
                required>
            <p class="text-sm text-gray-600 mt-1">
                Última actualización por: <span class="font-semibold">{{ $precioDolar->usuario->name }}</span> el
                <span
                    class="font-semibold">{{ \Carbon\Carbon::parse($precioDolar->updated_at)->format('d/m/Y H:i') }}</span><br>
            </p>
        </div>

        <div class="flex justify-end">
            <x-primary-button>Actualizar</x-primary-button>
        </div>
    </form>
</x-modal-show>

<!-- Modal para productos inactivos -->
<x-modal-show id="modalProductosInactivos" title="Productos Inactivos">
    <form method="POST" action="{{ route('productos.activar') }}">
        @csrf
        <input type="hidden" name="id_inventario" value="{{ $inventario->id_inventario }}">
        
        @php
            $productosInactivos = \App\Models\Producto::where('id_inventario', $inventario->id_inventario)
                ->where('estado', 0)
                ->orderBy('nombre')
                ->get();
        @endphp

        @if($productosInactivos->count() > 0)
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @foreach($productosInactivos as $prod)
                    <div class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded">
                        <input type="checkbox" name="productos[]" value="{{ $prod->id_producto }}" 
                            id="prod_{{ $prod->id_producto }}" class="w-4 h-4">
                        <label for="prod_{{ $prod->id_producto }}" class="flex-1 cursor-pointer">
                            <span class="font-mono text-sm text-gray-600">{{ $prod->id_producto }}</span> - 
                            <span class="font-medium">{{ $prod->nombre }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-end mt-4">
                <x-primary-button>Activar Seleccionados</x-primary-button>
            </div>
        @else
            <p class="text-gray-500 text-center py-4">No hay productos inactivos en este inventario.</p>
        @endif
    </form>
</x-modal-show>

<!-- Modal para gestionar permisos -->
<x-modal-show id="modalPermisos" title="Gestión de Permisos para AUDITORIA">
    <div class="space-y-4">
        @foreach ($usuariosAuditoria as $usuario)
            <div class="flex items-center justify-between">
                <span>{{ $usuario->name }}</span>
                <form action="{{ route('user.togglePermiso', $usuario->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 rounded-md {{ $usuario->puede_editar ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }} text-white transition duration-300">
                        {{ $usuario->puede_editar ? 'Desactivar' : 'Activar' }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</x-modal-show>
<script>
    // Función para abrir modal de permisos
    function abrirModal() {
        toggleModal('modalPermisos');
    }

    // Variables globales
    let productosSeleccionados = new Set();
    let todosProductosIDs = @json($productos->pluck('id_producto')); // Todos los IDs de productos

    // Funciones de manejo de selección
    function actualizarContador() {
        const total = productosSeleccionados.size;
        const contadorWrapper = document.getElementById('contadorSeleccionados');
        const contador = document.getElementById('contador');

        if (total > 0) {
            contadorWrapper.classList.remove('hidden');
            contador.textContent = total;
        } else {
            contadorWrapper.classList.add('hidden');
        }
    }

    function actualizarCheckboxes() {
        document.querySelectorAll('.checkbox-transferencia').forEach(cb => {
            cb.checked = productosSeleccionados.has(cb.value);
        });
    }

    function seleccionarTodosProductos() {
        // Si ya están todos seleccionados, deseleccionar
        if (productosSeleccionados.size === todosProductosIDs.length) {
            productosSeleccionados.clear();
        } else {
            // Seleccionar todos los productos
            todosProductosIDs.forEach(id => productosSeleccionados.add(id));
        }
        actualizarCheckboxes();
        actualizarContador();
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Botón para seleccionar todos los productos
        document.getElementById('seleccionarTodosGlobal')?.addEventListener('click', seleccionarTodosProductos);

        // Checkboxes individuales
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('checkbox-transferencia')) {
                const id = e.target.value;
                if (e.target.checked) {
                    productosSeleccionados.add(id);
                } else {
                    productosSeleccionados.delete(id);
                }
                actualizarContador();
            }
        });

        // Contador para deseleccionar todo
        document.getElementById('contadorSeleccionados')?.addEventListener('click', () => {
            productosSeleccionados.clear();
            actualizarCheckboxes();
            actualizarContador();
        });
    });

    // Configuración de DataTables
    $(document).ready(function() {
        const empresaNombre = @json($inventario->empresa->razon_social);

        const table = $('#myTable-productos').DataTable({
            destroy: true,
            paging: true,
            searching: true,
            ordering: true,
            responsive: true,
            /*dom: 'Bfrtip',
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: function() {
                    const hoy = new Date();
                    const dia = hoy.getDate().toString().padStart(2, '0');
                    const mes = (hoy.getMonth() + 1).toString().padStart(2, '0');
                    const anio = hoy.getFullYear();
                    return `PRODUCTOS_${empresaNombre}_EXPORTADO_EL_${dia}_${mes}_${anio}`;
                },
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6] // Excluye columna oculta (0) y acciones (7)
                }
            }], */
            order: [
                [0, 'asc']
            ],
            columnDefs: [{
                targets: 0,
                visible: false
            }],
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            }
        });

        // Actualizar checkboxes cuando cambia la página
        table.on('draw', function() {
            setTimeout(() => {
                actualizarCheckboxes();
                actualizarContador();
            }, 10);
        });

        // Manejo del formulario de transferencia
        document.querySelector('#modalTransferirProductos form')?.addEventListener('submit', function(e) {
            if (productosSeleccionados.size === 0) {
                e.preventDefault();
                alert('Selecciona al menos un producto para transferir.');
                return;
            }

            // Limpiar inputs anteriores si los hay
            document.querySelectorAll('input[name="productos[]"]').forEach(el => el.remove());

            // Agregar los productos seleccionados al formulario
            productosSeleccionados.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'productos[]';
                input.value = id;
                this.appendChild(input);
            });
        });
    });
</script>
