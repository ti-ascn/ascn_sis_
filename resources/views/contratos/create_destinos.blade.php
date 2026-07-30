<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Registrar Nuevo Contrato por Destino (') }} 
                {{ $tipo === 'empresa' ? 'Empresa Destino' : 'Cliente Auditoría' }}
                {{ ')' }}
            </h2>
            <a href="{{ route('contratos.index_destinos') }}"
                class="flex items-center gap-2 bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white text-sm">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('contratos.store_destinos') }}" class="space-y-6" autocomplete="off">
                    @csrf
                    <input type="hidden" name="tipo" value="{{ $tipo }}">
                    
                    {{-- Destino Principal --}}
                    <div>
                        <x-input-label for="id_destino" 
                            :value="$tipo === 'empresa' ? __('Empresa Destino (Principal)') : __('Cliente Auditoría (Principal)')" />
                        
                        <select name="id_destino" class="select-destino w-full">
                            <option value="">Selecciona un destinatario</option>
                            @foreach ($destinos as $destino)
                                <option value="{{ $tipo === 'empresa' ? $destino->id_empresas_clientes : $destino->id_cliente }}">
                                    {{ $destino->razon_social }} – {{ $destino->ruc }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id_destino')" class="mt-2" />
                    </div>

                    {{-- EMPRESAS ORIGEN + FECHAS --}}
                    <div>
                        <x-input-label :value="__('Empresas que le facturan')" />
                        <x-input-error :messages="$errors->get('origenes')" class="mb-2" />
                        <div id="contenedor-origenes" class="space-y-4 mt-2">
                            {{-- BLOQUE 1 --}}
                            <div class="origen-item border p-4 rounded-md bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1fr_auto] gap-4 items-center">
                                    {{-- Empresa --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 invisible">EMPRESA</label>
                                        <select name="origenes[0][id_empresa_origen]"
                                            class="select-empresa-origen w-full">
                                            <option value="">Selecciona una empresa</option>
                                            @foreach ($empresasOrigen as $empresa)
                                                <option value="{{ $empresa->id_empresas_clientes }}">
                                                    {{ $empresa->razon_social }} – {{ $empresa->ruc }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Fecha inicio --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                                        <input type="date" name="origenes[0][fecha_inicio]"
                                            value="{{ now()->toDateString() }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    {{-- Fecha fin --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
                                        <input type="date" name="origenes[0][fecha_fin]"
                                            value="{{ now()->toDateString() }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    {{-- Firmado --}}
                                    <div class="flex items-center gap-2 mt-4 md:mt-0">
                                        <input type="checkbox" name="origenes[0][firmado]" value="1"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                        <label class="text-sm font-medium text-gray-700">Firmado</label>
                                    </div>
                                </div>
                                {{-- Botón agregar --}}
                                <div class="mt-4 btn-agregar">
                                    <button type="button"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md btn-agregar-origen">
                                        <i class="bi bi-plus-circle"> Agregar Empresa Origen</i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botón --}}
                    <div class="flex justify-end">
                        <x-primary-button>Crear Contratos</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    const relacionesExistentes = @json($relaciones);
    const currentTipo = "{{ $tipo }}";
</script>

<script>
    $(document).ready(function() {
        $('.select-destino').select2({
            placeholder: 'Selecciona un destinatario',
            allowClear: true,
            width: 'resolve'
        });

        function initSelect2Origenes() {
            $('.select-empresa-origen').select2({
                placeholder: 'Selecciona una empresa origen',
                allowClear: true,
                width: 'resolve'
            }).on('change', actualizarOpcionesOrigenes);
        }

        function actualizarOpcionesOrigenes() {
            const destinoId = $('select[name="id_destino"]').val();
            let seleccionadas = [];
            $('.select-empresa-origen').each(function() {
                let val = $(this).val();
                if (val) seleccionadas.push(val);
            });
            // Destroy Select2 to update the options
            $('.select-empresa-origen').select2('destroy');
            $('.select-empresa-origen').each(function() {
                let actual = $(this).val();
                let select = $(this);
                select.find('option').each(function() {
                    let v = $(this).val();
                    if (!v) return;
                    
                    let existeBD = false;
                    if (destinoId) {
                        existeBD = relacionesExistentes.some(r => {
                            let matchDestino = false;
                            if (currentTipo === 'empresa') {
                                matchDestino = String(r.id_empresa_destino) === String(destinoId);
                            } else {
                                matchDestino = String(r.id_cliente_auditoria_destino) === String(destinoId);
                            }
                            return matchDestino && String(r.id_empresa_origen) === String(v);
                        });
                    }

                    let existeForm = seleccionadas.includes(v) && v !== actual;
                    if (existeBD || existeForm) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });
            });
            // Re-bind Select2
            initSelect2Origenes();
        }

        let index = 1;
        initSelect2Origenes();

        $(document).on('change', 'select[name="id_destino"]', function() {
            actualizarOpcionesOrigenes();
        });

        $(document).on('click', '.btn-agregar-origen', function() {
            let nuevo = `
            <div class="origen-item border p-4 rounded-md bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1fr_auto] gap-4 items-center">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 invisible">EMPRESA</label>
                        <select name="origenes[${index}][id_empresa_origen]" class="select-empresa-origen w-full">
                            <option value="">Selecciona una empresa</option>
                            @foreach ($empresasOrigen as $empresa)
                                <option value="{{ $empresa->id_empresas_clientes }}">
                                    {{ $empresa->razon_social }} – {{ $empresa->ruc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                        <input type="date" name="origenes[${index}][fecha_inicio]"
                            value="{{ now()->toDateString() }}"
                            class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
                        <input type="date" name="origenes[${index}][fecha_fin]"
                            value="{{ now()->toDateString() }}"
                            class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="flex items-center gap-2 mt-4 md:mt-0">
                        <input type="checkbox" name="origenes[${index}][firmado]" value="1"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <label class="text-sm font-medium text-gray-700">Firmado</label>
                    </div>
                    <div class="text-right mt-2 btn-eliminar">
                        <button type="button" class="text-red-600 hover:text-red-800 text-sm font-bold text-xl eliminar-origen">✕</button>
                    </div>
                </div>
                <div class="mt-4 btn-agregar">
                    <button type="button"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md btn-agregar-origen">
                        <i class="bi bi-plus-circle"> Agregar Empresa Origen</i>
                    </button>
                </div>
            </div>`;
            $('.btn-agregar').addClass('hidden');
            $('#contenedor-origenes').append(nuevo);
            initSelect2Origenes();
            index++;
            actualizarOpcionesOrigenes();
        });

        $(document).on('click', '.eliminar-origen', function() {
            $(this).closest('.origen-item').remove();
            // Mostrar solo el último botón "Agregar"
            $('.btn-agregar').addClass('hidden');
            $('.origen-item:last .btn-agregar').removeClass('hidden');
            // Recalcular bloqueos
            actualizarOpcionesOrigenes();
        });
    });
</script>
