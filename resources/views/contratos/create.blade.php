<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nuevo Contrato') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('contratos.store') }}" class="space-y-6" autocomplete="off">
                    @csrf
                    {{-- Empresa Origen --}}
                    <div>
                        <x-input-label for="id_empresa_origen" :value="__('Empresa que factura (Origen)')" />
                        <select name="id_empresa_origen" class="select-empresa w-full">
                            <option value="">Selecciona una empresa</option>
                            @foreach ($empresasOrigen as $empresa)
                                <option value="{{ $empresa->id_empresas_clientes }}">
                                    {{ $empresa->razon_social }} – {{ $empresa->ruc }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id_empresa_origen')" class="mt-2" />
                    </div>
                    {{-- EMPRESAS DESTINO + FECHAS --}}
                    <div>
                        <x-input-label :value="__('Empresas a las que puede facturar (Destino)')" />
                        <div id="contenedor-destinos" class="space-y-4">
                            {{-- BLOQUE 1 --}}
                            <div class="destino-item border p-4 rounded-md bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1fr_auto] gap-4 items-center">
                                    {{-- Empresa --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 invisible">EMPRESA</label>
                                        <select name="destinos[0][id_empresa_destino]"
                                            class="select-empresa-destino w-full">
                                            <option value="">Selecciona una empresa</option>
                                            @foreach ($empresasDestino as $empresa)
                                                <option value="{{ $empresa->id_empresas_clientes }}">
                                                    {{ $empresa->razon_social }} – {{ $empresa->ruc }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Fecha inicio --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                                        <input type="date" name="destinos[0][fecha_inicio]"
                                            value="{{ now()->toDateString() }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    {{-- Fecha fin --}}
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
                                    {{-- Botón eliminar --}}
                                    {{-- <div
                                        class="flex justify-end btn-eliminar {{ count($destinos ?? []) === 1 ? 'hidden' : '' }}">
                                        <button type="button"
                                            class="text-red-600 hover:text-red-800 text-xl font-bold eliminar-destino">
                                            ✕
                                        </button>
                                    </div> --}}
                                </div>
                                {{-- Botón agregar --}}
                                <div class="mt-4 btn-agregar">
                                    <button type="button" id="btn-agregar-destino"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md btn-agregar-destino">
                                        <i class="bi bi-plus-circle"> Agregar Empresa</i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- CLIENTES AUDITORÍA + FECHAS --}}
                    <div>
                        <x-input-label :value="__('Clientes de Auditoría a los que puede facturar (Destino)')" />
                        <div id="contenedor-clientes" class="space-y-4">
                            {{-- BLOQUE 1 --}}
                            <div class="cliente-item border p-4 rounded-md bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1fr_auto] gap-4 items-center">
                                    {{-- Cliente (más ancho: 2 columnas) --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 invisible">CLIENTE</label>
                                        <select
                                            name="clientes[0][id_cliente_auditoria_destino]"class="select-cliente-auditoria w-full">
                                            <option value="">Selecciona un cliente</option>
                                            @foreach ($clientes as $cliente)
                                                <option value="{{ $cliente->id_cliente }}">
                                                    {{ $cliente->razon_social }} – {{ $cliente->ruc }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Fecha inicio --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">
                                            Fecha inicio
                                        </label>
                                        <input type="date" name="clientes[0][fecha_inicio]"
                                            value="{{ now()->toDateString() }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    {{-- Fecha fin --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">
                                            Fecha fin
                                        </label>
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
                                    {{-- Botón eliminar --}}
                                    {{-- <div
                                        class="flex justify-end btn-eliminar {{ count($destinos ?? []) === 1 ? 'hidden' : '' }}">
                                        <button type="button"
                                            class="text-red-600 hover:text-red-800 text-xl font-bold eliminar-destino">
                                            ✕
                                        </button>
                                    </div> --}}
                                </div>
                                {{-- Botón agregar --}}
                                <div class="mt-4 btn-agregar-cliente">
                                    <button type="button"
                                        id="btn-agregar-cliente"class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md btn-agregar-cliente-btn"><i
                                            class="bi bi-plus-circle"> Agregar Cliente</i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Botón --}}
                    <div class="flex justify-end">
                        <x-primary-button>Crear Contrato</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    const relacionesExistentes = @json($relaciones);
</script>

<script>
    $(document).ready(function() {
        $('.select-empresa').select2({
            placeholder: 'Selecciona una empresa',
            allowClear: true,
            width: 'resolve'
        });

        function initSelect2Destinos() {
            $('.select-empresa-destino').select2({
                placeholder: 'Selecciona una empresa',
                allowClear: true,
                width: 'resolve'
            }).on('change', actualizarOpcionesDestinos);
        }

        function initSelectClientes() {
            $('.select-cliente-auditoria').select2({
                placeholder: 'Selecciona un cliente',
                allowClear: true,
                width: 'resolve'
            }).on('change', actualizarOpcionesClientes);
        }

        function actualizarOpcionesDestinos() {
            const origen = $('select[name="id_empresa_origen"]').val();
            let seleccionadas = [];
            $('.select-empresa-destino').each(function() {
                let val = $(this).val();
                if (val) seleccionadas.push(val);
            });
            // Destroy Select2 to update the options
            $('.select-empresa-destino').select2('destroy');
            $('.select-empresa-destino').each(function() {
                let actual = $(this).val();
                let select = $(this);
                select.find('option').each(function() {
                    let v = $(this).val();
                    if (!v) return;
                    let existeBD = origen && relacionesExistentes.some(r =>
                        String(r.id_empresa_origen) === String(origen) &&
                        String(r.id_empresa_destino) === String(v)
                    );
                    let existeForm = seleccionadas.includes(v) && v !== actual;
                    if (existeBD || existeForm) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });
            });
            // Re-bind Select2
            initSelect2Destinos();
        }

        function actualizarOpcionesClientes() {
            const origen = $('select[name="id_empresa_origen"]').val();
            let seleccionadas = [];
            $('.select-cliente-auditoria').each(function() {
                let val = $(this).val();
                if (val) seleccionadas.push(val);
            });
            $('.select-cliente-auditoria').select2('destroy');
            $('.select-cliente-auditoria').each(function() {
                let actual = $(this).val();
                let select = $(this);
                select.find('option').each(function() {
                    let v = $(this).val();
                    if (!v) return;
                    let existeBD = origen && relacionesExistentes.some(r =>
                        String(r.id_empresa_origen) === String(origen) &&
                        String(r.id_cliente_auditoria_destino) === String(v)
                    );
                    let existeForm = seleccionadas.includes(v) && v !== actual;
                    if (existeBD || existeForm) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });
            });
            // Re-bind Select2
            initSelectClientes();
        }

        let index = 1;
        let indexCliente = 1;
        initSelect2Destinos();
        initSelectClientes();

        $(document).on('change', 'select[name="id_empresa_origen"]', function() {
            actualizarOpcionesDestinos();
            actualizarOpcionesClientes();
        });

        $(document).on('click', '.btn-agregar-destino', function() {
            // Eliminar bloque DESTINO
            $(document).on('click', '.eliminar-destino', function() {
                $(this).closest('.destino-item').remove();
                // Mostrar solo el último botón "Agregar"
                $('.btn-agregar').addClass('hidden');
                $('.destino-item:last .btn-agregar').removeClass('hidden');
                // Recalcular bloqueos
                actualizarOpcionesDestinos();
            });
            // Eliminar bloque CLIENTE
            $(document).on('click', '.eliminar-cliente', function() {
                $(this).closest('.cliente-item').remove();
                // Mostrar solo el último botón "Agregar"
                $('.btn-agregar-cliente').addClass('hidden');
                $('.cliente-item:last .btn-agregar-cliente').removeClass('hidden');
                // Recalcular bloqueos
                actualizarOpcionesClientes();
            });
            let nuevo = `
            <div class="destino-item border p-4 rounded-md bg-gray-50">
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
                        <input type="date" name="destinos[${index}][fecha_inicio]"
                            value="{{ now()->toDateString() }}"
                            class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
                        <input type="date" name="destinos[${index}][fecha_fin]"
                            value="{{ now()->toDateString() }}"
                            class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="flex items-center gap-2 mt-4 md:mt-0">
                        <input type="checkbox" name="destinos[${index}][firmado]" value="1"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <label class="text-sm font-medium text-gray-700">Firmado</label>
                    </div>
                    <div class="text-right mt-2 btn-eliminar">
                        <button type="button" class="text-red-600 hover:text-red-800 text-sm eliminar-destino">X</button>
                    </div>
                </div>
                <div class="mt-4 btn-agregar">
                    <button type="button"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md btn-agregar-destino">
                        <i class="bi bi-plus-circle"> Agregar Empresa</i>
                    </button>
                </div>
            </div>`;
            $('.btn-agregar').addClass('hidden');
            $('#contenedor-destinos').append(nuevo);
            initSelect2Destinos();
            index++;
            actualizarOpcionesDestinos();
        });

        $(document).on('click', '.btn-agregar-cliente-btn', function() {
            // Eliminar bloque DESTINO
            $(document).on('click', '.eliminar-destino', function() {
                $(this).closest('.destino-item').remove();

                // Mostrar solo el último botón "Agregar"
                $('.btn-agregar').addClass('hidden');
                $('.destino-item:last .btn-agregar').removeClass('hidden');

                // Recalcular bloqueos
                actualizarOpcionesDestinos();
            });

            // Eliminar bloque CLIENTE
            $(document).on('click', '.eliminar-cliente', function() {
                $(this).closest('.cliente-item').remove();

                // Mostrar solo el último botón "Agregar"
                $('.btn-agregar-cliente').addClass('hidden');
                $('.cliente-item:last .btn-agregar-cliente').removeClass('hidden');

                // Recalcular bloqueos
                actualizarOpcionesClientes();
            });
            let nuevo = `
                <div class="cliente-item border p-4 rounded-md bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1fr_auto] gap-4 items-center">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 invisible">CLIENTE</label>
                            <select name="clientes[${indexCliente}][id_cliente_auditoria_destino]"
                                class="select-cliente-auditoria w-full">
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
                            <input type="date" name="clientes[${indexCliente}][fecha_inicio]"
                                value="{{ now()->toDateString() }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
                            <input type="date" name="clientes[${indexCliente}][fecha_fin]"
                                value="{{ now()->toDateString() }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="flex items-center gap-2 mt-4 md:mt-0">
                            <input type="checkbox" name="clientes[${indexCliente}][firmado]" value="1"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <label class="text-sm font-medium text-gray-700">Firmado</label>
                        </div>
                        <div class="text-right mt-2 btn-eliminar-cliente">
                            <button type="button" class="text-red-600 hover:text-red-800 text-sm eliminar-cliente">X</button>
                        </div>
                    </div>
                    <div class="mt-4 btn-agregar-cliente">
                        <button type="button"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md btn-agregar-cliente-btn">
                            <i class="bi bi-plus-circle"> Agregar Cliente</i>
                        </button>
                    </div>
                </div>`;
            $('.btn-agregar-cliente').addClass('hidden');
            $('#contenedor-clientes').append(nuevo);
            initSelectClientes();
            indexCliente++;
            actualizarOpcionesClientes();
        });
    });
</script>
