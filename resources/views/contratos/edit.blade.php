<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Relaciones de Facturación de: ') }}
            <span class="text-blue-600">{{ $origen->razon_social }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('contratos.update', $origen->id_empresas_clientes) }}"
                    class="space-y-6" autocomplete="off">
                    @csrf
                    @method('PUT')
                    {{-- Empresa Origen --}}
                    <div>
                        <x-input-label :value="__('Empresa Origen')" />
                        <p class="text-gray-700 font-semibold">
                            {{ $origen->razon_social }} – {{ $origen->ruc }}
                        </p>
                        <input type="hidden" name="id_empresa_origen" value="{{ $origen->id_empresas_clientes }}">
                    </div>
                    {{-- EMPRESAS DESTINO + FECHAS --}}
                    <div id="contenedor-destinos" class="space-y-4">
                        @if (count($destinos) > 0)
                            @foreach ($destinos as $i => $destino)
                                <div class="destino-item border p-4 rounded-md bg-gray-50">
                                    <input type="hidden" class="contrato-destino-id"
                                        value="{{ $destino->id_contratos }}">
                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                        <div class="md:col-span-2">
                                            <label
                                                class="block text-sm font-medium text-gray-700 invisible">EMPRESA</label>
                                            <select name="destinos[{{ $i }}][id_empresa_destino]"
                                                class="select-empresa-destino w-full">
                                                <option value="">Selecciona una empresa</option>
                                                @foreach ($empresasDestino as $empresa)
                                                    <option value="{{ $empresa->id_empresas_clientes }}"
                                                        {{ $empresa->id_empresas_clientes == $destino->id_empresa_destino ? 'selected' : '' }}>
                                                        {{ $empresa->razon_social }} – {{ $empresa->ruc }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                                            <input type="date" name="destinos[{{ $i }}][fecha_inicio]"
                                                value="{{ $destino->fecha_inicio }}"
                                                class="w-full border-gray-300 rounded-md shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
                                            <input type="date" name="destinos[{{ $i }}][fecha_fin]"
                                                value="{{ $destino->fecha_fin }}"
                                                class="w-full border-gray-300 rounded-md shadow-sm">
                                        </div>
                                        {{-- Firmado --}}
                                        <div class="flex items-center gap-2 mt-4 md:mt-0">
                                            <input type="checkbox" name="destinos[{{ $i }}][firmado]" value="1"
                                                {{ $destino->pivot->firmado ?? ($destino->firmado ?? false) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                            <label class="text-sm font-medium text-gray-700">Firmado</label>
                                        </div>
                                    </div>
                                    <div class="text-right mt-2 btn-eliminar">
                                        <button type="button"
                                            class="text-red-600 hover:text-red-800 text-sm eliminar-destino">X</button>
                                    </div>
                                    <div class="mt-4 btn-agregar {{ $i !== count($destinos) - 1 ? 'hidden' : '' }}">
                                        <button type="button"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md btn-agregar-destino"><i
                                                class="bi bi-plus-circle"> Agregar Empresa</i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            {{-- Bloque vacío inicial si no hay destinos --}}
                            <div class="destino-item border p-4 rounded-md bg-gray-50">

                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 invisible">EMPRESA</label>
                                        <select name="destinos[0][id_empresa_destino]"
                                            class="select-empresa-destino w-full">
                                            <option value="">Selecciona una empresa</option>
                                            @foreach ($empresasDestino as $empresa)
                                                <option value="{{ $empresa->id_empresas_clientes }}">
                                                    {{ $empresa->razon_social }} – {{ $empresa->ruc }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                                        <input type="date" name="destinos[0][fecha_inicio]"
                                            value="{{ now()->toDateString() }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
                                        <input type="date" name="destinos[0][fecha_fin]"
                                            value="{{ now()->toDateString() }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    {{-- Firmado --}}
                                    <div class="flex items-center gap-2 mt-4 md:mt-0">
                                        <input type="checkbox" name="destinos[0][firmado]" value="1"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                        <label class="text-sm font-medium text-gray-700">Firmado</label>
                                    </div>
                                </div>
                                <div class="text-right mt-2 btn-eliminar hidden">
                                    <button type="button"
                                        class="text-red-600 hover:text-red-800 text-sm eliminar-destino">X</button>
                                </div>
                                <div class="mt-4 btn-agregar">
                                    <button type="button"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md 
                                        btn-agregar-destino"><i
                                            class="bi bi-plus-circle"> Agregar Empresa</i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- CLIENTES AUDITORÍA + FECHAS --}}
                    <div id="contenedor-clientes" class="space-y-4">
                        @if (count($clientesAuditoria) > 0)
                            @foreach ($clientesAuditoria as $i => $cli)
                                <div class="cliente-item border p-4 rounded-md bg-gray-50">
                                    <input type="hidden" class="contrato-cliente-id" value="{{ $cli->id_contratos }}">
                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                        <div class="md:col-span-2">
                                            <label
                                                class="block text-sm font-medium text-gray-700 invisible">CLIENTE</label>
                                            <select name="clientes[{{ $i }}][id_cliente_auditoria_destino]"
                                                class="select-cliente-auditoria w-full">
                                                <option value="">Selecciona un cliente</option>
                                                @foreach ($clientes as $cliente)
                                                    <option value="{{ $cliente->id_cliente }}"
                                                        {{ $cliente->id_cliente == $cli->id_cliente_auditoria_destino ? 'selected' : '' }}>
                                                        {{ $cliente->razon_social }} – {{ $cliente->ruc }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                                            <input type="date" name="clientes[{{ $i }}][fecha_inicio]"
                                                value="{{ $cli->fecha_inicio }}"
                                                class="w-full border-gray-300 rounded-md shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
                                            <input type="date" name="clientes[{{ $i }}][fecha_fin]"
                                                value="{{ $cli->fecha_fin }}"
                                                class="w-full border-gray-300 rounded-md shadow-sm">
                                        </div>
                                        {{-- Firmado --}}
                                        <div class="flex items-center gap-2 mt-4 md:mt-0">
                                            <input type="checkbox" name="clientes[{{ $i }}][firmado]" value="1"
                                                {{ $cli->pivot->firmado ?? ($cli->firmado ?? false) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                            <label class="text-sm font-medium text-gray-700">Firmado</label>
                                        </div>
                                    </div>
                                    <div class="text-right mt-2 btn-eliminar-cliente">
                                        <button type="button"
                                            class="text-red-600 hover:text-red-800 text-sm eliminar-cliente">X</button>
                                    </div>
                                    <div
                                        class="mt-4 btn-agregar-cliente {{ $i !== count($clientesAuditoria) - 1 ? 'hidden' : '' }}">
                                        <button type="button"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md btn-agregar-cliente-btn"><i
                                                class="bi bi-plus-circle"> Agregar Cliente</i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            {{-- Bloque vacío inicial si no hay clientes --}}
                            <div class="cliente-item border p-4 rounded-md bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                    <div class="md:col-span-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700 invisible">CLIENTE</label>
                                        <select name="clientes[0][id_cliente_auditoria_destino]"
                                            class="select-cliente-auditoria w-full">
                                            <option value="">Selecciona un cliente</option>
                                            @foreach ($clientes as $cliente)
                                                <option value="{{ $cliente->id_cliente }}">
                                                    {{ $cliente->razon_social }} – {{ $cliente->ruc }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                                        <input type="date" name="clientes[0][fecha_inicio]"
                                            value="{{ now()->toDateString() }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
                                        <input type="date" name="clientes[0][fecha_fin]"
                                            value="{{ now()->toDateString() }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    {{-- Firmado --}}
                                    <div class="flex items-center gap-2 mt-4 md:mt-0">
                                        <input type="checkbox" name="clientes[0][firmado]" value="1"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                        <label class="text-sm font-medium text-gray-700">Firmado</label>
                                    </div>
                                </div>
                                <div class="text-right mt-2 btn-eliminar-cliente hidden">
                                    <button type="button"
                                        class="text-red-600 hover:text-red-800 text-sm eliminar-cliente">X</button>
                                </div>
                                <div class="mt-4 btn-agregar-cliente">
                                    <button type="button"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md btn-agregar-cliente-btn"><i
                                            class="bi bi-plus-circle"> Agregar Cliente</i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    {{-- Botón --}}
                    <div class="flex justify-end">
                        <x-primary-button>Actualizar Contratos</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- Select2 Scripts --}}
<script>
    $(document).ready(function() {
        $('.select-multiple').select2({
            placeholder: 'Selecciona las empresas destino',
            allowClear: true,
            width: 'resolve'
        });
        $('.select-multiple-clientes').select2({
            placeholder: 'Selecciona los clientes de auditoría',
            allowClear: true,
            width: 'resolve'
        });
    });
</script>

<script>
    $(document).ready(function() {
        /* ========= DESTINOS ========= */
        function initSelect2Destinos() {
            $('.select-empresa-destino').select2({
                placeholder: 'Selecciona una empresa',
                allowClear: true,
                width: 'resolve'
            }).on('change', actualizarOpcionesd);
        }
        let index = {{ count($destinos ?? []) }};
        // Función para actualizar opciones disponibles
        function actualizarOpcionesd() {
            // Recopila los valores seleccionados
            let seleccionadas = [];
            $('.select-empresa-destino').each(function() {
                let val = $(this).val();
                if (val) seleccionadas.push(val);
            });
            $('.select-empresa-destino').select2('destroy');
            // Recorre todos los selects
            $('.select-empresa-destino').each(function() {
                let actual = $(this).val();
                $(this).find('option').each(function() {
                    if ($(this).val() === "") return;
                    // Si la opción está seleccionada en otro select, deshabilítala
                    if (seleccionadas.includes($(this).val()) && $(this).val() !== actual) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });
            });
            // Re-init
            initSelect2Destinos();
        }

        function refrescarBotonAgregarDestinos() {
            $('.btn-agregar').addClass('hidden');
            let items = $('#contenedor-destinos .destino-item');
            if (items.length > 0) {
                items.last().find('.btn-agregar').removeClass('hidden');
            }
        }



        // Función para agregar nuevo destino (refactorizada para usar en delete también)
        function agregarDestinoVacio() {
            let nuevo = `
                <div class="destino-item border p-4 rounded-md bg-gray-50">
                    <input type="hidden" class="contrato-destino-id" value="">
                        <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1fr_auto] gap-4 items-center">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 invisible">EMPRESA</label>
                                <select name="destinos[${index}][id_empresa_destino]" class="select-empresa-destino w-full">
                                    <option value="">Selecciona una empresa</option>
                                    @foreach ($empresasDestino as $empresa)
                                        <option value="{{ $empresa->id_empresas_clientes }}">
                                            {{ $empresa->razon_social }} – {{ $empresa->ruc }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                                <input type="date" name="destinos[${index}][fecha_inicio]" value="{{ now()->toDateString() }}" class="w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
                                <input type="date" name="destinos[${index}][fecha_fin]" value="{{ now()->toDateString() }}" class="w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="flex items-center gap-2 mt-4 md:mt-0">
                                <input type="checkbox" name="destinos[${index}][firmado]" value="1"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <label class="text-sm font-medium text-gray-700">Firmado</label>
                            </div>
                            <div class="text-right">
                                <button type="button" class="text-red-600 hover:text-red-800 text-sm eliminar-destino">X</button>
                            </div>
                        </div>
                        <div class="mt-4 btn-agregar">
                            <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md btn-agregar-destino"><i class="bi bi-plus-circle"> Agregar Empresa</i></button>
                        </div>
                    </input>
                </div>`;
            $('.btn-agregar').addClass('hidden');
            $('#contenedor-destinos').append(nuevo);
            initSelect2Destinos();
            index++;
            actualizarOpcionesd();
        }

        initSelect2Destinos();

        $(document).on('click', '.btn-agregar-destino', function() {
            agregarDestinoVacio();
        });

        $(document).on('click', '.eliminar-destino', function() {
            let item = $(this).closest('.destino-item');
            let contratoId = item.find('.contrato-destino-id').val();
            if (!contratoId) {
                item.remove();
                if ($('#contenedor-destinos .destino-item').length === 0) {
                    agregarDestinoVacio();
                } else {
                    $('.btn-agregar').addClass('hidden');
                    $('.destino-item:last .btn-agregar').removeClass('hidden');
                    actualizarOpcionesd();
                }
                return;
            }
            if (!confirm('¿Deseas poner inactivo este contrato?')) return;

            $.ajax({
                url: '/contratos/inactivar-individual/' + contratoId,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    item.remove();
                    if ($('#contenedor-destinos .destino-item').length === 0) {
                        agregarDestinoVacio();
                    } else {
                        $('.btn-agregar').addClass('hidden');
                        $('.destino-item:last .btn-agregar').removeClass('hidden');
                        actualizarOpcionesd();
                    }
                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast('Empresa inactivada correctamente', 'success');
                    }
                },
                error: function() {
                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast('Error al inactivar la empresa', 'error');
                    } else {
                        alert('Error al inactivar la empresa');
                    }
                }
            });
        });

        /* ========= CLIENTES ========= */
        function initSelectClientes() {
            $('.select-cliente-auditoria').select2({
                placeholder: 'Selecciona un cliente',
                allowClear: true,
                width: 'resolve'
            }).on('change', actualizarOpciones);
        }

        let indexCliente = {{ count($clientesAuditoria ?? []) }};

        // Función para actualizar opciones disponibles
        function actualizarOpciones() {
            // Recopila los valores seleccionados
            let seleccionadas = [];
            $('.select-cliente-auditoria').each(function() {
                let val = $(this).val();
                if (val) seleccionadas.push(val);
            });
            $('.select-cliente-auditoria').select2('destroy');
            // Recorre todos los selects
            $('.select-cliente-auditoria').each(function() {
                let actual = $(this).val();
                $(this).find('option').each(function() {
                    if ($(this).val() === "") return;
                    // Si la opción está seleccionada en otro select, deshabilítala
                    if (seleccionadas.includes($(this).val()) && $(this).val() !== actual) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });
            });
            // Re-init
            initSelectClientes();
        }


        function agregarClienteVacio() {
            let nuevo = `
                <div class="cliente-item border p-4 rounded-md bg-gray-50">
                    <input type="hidden" class="contrato-cliente-id" value="">
                        <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1fr_auto] gap-4 items-center">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 invisible">CLIENTE</label>
                                <select name="clientes[${indexCliente}][id_cliente_auditoria_destino]" class="select-cliente-auditoria w-full">
                                    <option value="">Selecciona un cliente</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id_cliente }}">
                                            {{ $cliente->razon_social }} – {{ $cliente->ruc }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                                <input type="date" name="clientes[${indexCliente}][fecha_inicio]" value="{{ now()->toDateString() }}" class="w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
                                <input type="date" name="clientes[${indexCliente}][fecha_fin]" value="{{ now()->toDateString() }}" class="w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="flex items-center gap-2 mt-4 md:mt-0">
                                <input type="checkbox" name="clientes[${indexCliente}][firmado]" value="1"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <label class="text-sm font-medium text-gray-700">Firmado</label>
                            </div>
                            <div class="text-right">
                                <button type="button" class="text-red-600 hover:text-red-800 text-sm eliminar-cliente">X</button>
                            </div>
                        </div>
                        <div class="mt-4 btn-agregar-cliente">
                            <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md btn-agregar-cliente-btn"><i class="bi bi-plus-circle"> Agregar Cliente</i></button>
                        </div>
                    </input>
                </div>`;
            $('.btn-agregar-cliente').addClass('hidden');
            $('#contenedor-clientes').append(nuevo);
            initSelectClientes();
            indexCliente++;
            actualizarOpciones();
        }

        initSelectClientes();
        $(document).on('click', '.btn-agregar-cliente-btn', function() {
            agregarClienteVacio();
        });

        $(document).on('click', '.eliminar-cliente', function() {
            let item = $(this).closest('.cliente-item');
            let contratoId = item.find('.contrato-cliente-id').val();

            if (!contratoId) {
                item.remove();
                if ($('#contenedor-clientes .cliente-item').length === 0) {
                    agregarClienteVacio();
                } else {
                    $('.btn-agregar-cliente').addClass('hidden');
                    $('.cliente-item:last .btn-agregar-cliente').removeClass('hidden');
                    actualizarOpciones();
                }
                return;
            }

            if (!confirm('¿Deseas poner inactivo este contrato?')) return;

            $.ajax({
                url: '/contratos/inactivar-individual/' + contratoId,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    item.remove();
                    if ($('#contenedor-clientes .cliente-item').length === 0) {
                        agregarClienteVacio();
                    } else {
                        $('.btn-agregar-cliente').addClass('hidden');
                        $('.cliente-item:last .btn-agregar-cliente').removeClass('hidden');
                        actualizarOpciones();
                    }
                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast('Cliente inactivado correctamente', 'succes');
                    }
                },
                error: function() {
                    if (typeof showAjaxToast === 'function') {
                        showAjaxToast('Error al inactivar el cliente', 'error');
                    } else {
                        alert('Error al inactivar el cliente');
                    }
                }
            });
        });
    });
</script>
