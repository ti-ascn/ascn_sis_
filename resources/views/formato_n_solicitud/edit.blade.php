<style>
    .select-formato {
        width: 140px;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .input-formato {
        width: 140px;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .select-signo {
        width: 80px;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .input-signo {
        width: 80px;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .select2-container--default .select2-selection--single {
        border-color: #d1d5db !important;
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
        border-radius: 0.375rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center">
            {{ __('Editar Formato N° Solicitud') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg sm:rounded-xl p-8">
                <form action="{{ route('formato_n_solicitud.update', $formato->id_formato_solicitudes) }}" method="POST"
                    class="max-w-4xl mx-auto">
                    @csrf
                    @method('PUT')

                    @php
                        $entidad = $formato->empresa ?? $formato->cliente;
                        $tipoEntidad = $formato->empresa ? 'Empresa' : 'Cliente';
                    @endphp

                    <h3 class="text-lg font-medium text-gray-700">
                        {{ $tipoEntidad }}: {{ $entidad->razon_social ?? 'N/A' }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        RUC: {{ $entidad->ruc ?? 'N/A' }}
                    </p>
                    <!-- Grid para Selects e Inputs -->
                    <div class="flex flex-wrap gap-x-2 gap-y-10 justify-center items-start">
                        <!-- Sección 1 -->
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_grande_1" :value="__('Formato 1')" />
                            <select id="select_grande_1" name="select_grande_1"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-formato" required>
                                <option value="NADA" {{ $pos1['tipo'] === 'NADA' ? 'selected' : '' }}>NADA</option>
                                <option value="DIGITO" {{ $pos1['tipo'] === 'DIGITO' ? 'selected' : '' }}>DIGITO
                                </option>
                                <option value="LETRA" {{ $pos1['tipo'] === 'LETRA' ? 'selected' : '' }}>LETRA</option>
                                <option value="AÑO" {{ $pos1['tipo'] === 'AÑO' ? 'selected' : '' }}>AÑO</option>
                            </select>
                            <div id="input_select_grande_1" class="{{ $pos1['tipo'] === 'NADA' ? 'hidden' : '' }}">
                                <x-text-input id="input_grande_1_value" name="input_grande_1_value"
                                    value="{{ $pos1['valor'] }}" type="text"
                                    class="border-gray-300 rounded-md shadow-sm input-formato text-center"
                                    placeholder="Valor" />
                            </div>
                        </div>

                        <!-- Sección 2 -->
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_pequeno_1" :value="__('Signo 1')" />
                            <select id="select_pequeno_1" name="select_pequeno_1"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-signo" required>
                                <option value="NADA" {{ !$signo1 ? 'selected' : '' }}>NADA</option>
                                <option value="/" {{ $signo1 === '/' ? 'selected' : '' }}>/</option>
                                <option value="-" {{ $signo1 === '-' ? 'selected' : '' }}>-</option>
                            </select>
                            <div id="input_select_pequeno_1" class="{{ !$signo1 ? 'hidden' : '' }}">
                                <x-text-input id="input_pequeno_1_value" name="input_pequeno_1_value"
                                    value="{{ $signo1 }}" type="text"
                                    class="border-gray-300 rounded-md shadow-sm input-signo text-center"
                                    placeholder="Símbolo" />
                            </div>
                        </div>

                        <!-- Sección 3 -->
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_grande_2" :value="__('Formato 2')" />
                            <select id="select_grande_2" name="select_grande_2"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-formato" required>
                                <option value="NADA" {{ $pos2['tipo'] === 'NADA' ? 'selected' : '' }}>NADA
                                </option>
                                <option value="DIGITO" {{ $pos2['tipo'] === 'DIGITO' ? 'selected' : '' }}>DIGITO
                                </option>
                                <option value="LETRA" {{ $pos2['tipo'] === 'LETRA' ? 'selected' : '' }}>LETRA
                                </option>
                                <option value="AÑO" {{ $pos2['tipo'] === 'AÑO' ? 'selected' : '' }}>AÑO
                                </option>
                            </select>
                            <div id="input_select_grande_2" class="{{ $pos2['tipo'] === 'NADA' ? 'hidden' : '' }}">
                                <x-text-input id="input_grande_2_value" name="input_grande_2_value"
                                    value="{{ $pos2['valor'] }}" type="text"
                                    class="border-gray-300 rounded-md shadow-sm input-formato text-center"
                                    placeholder="Valor" />
                            </div>
                        </div>

                        <!-- Sección 4 -->
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_pequeno_2" :value="__('Signo 2')" />
                            <select id="select_pequeno_2" name="select_pequeno_2"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-signo" required>
                                <option value="NADA" {{ !$signo2 ? 'selected' : '' }}>NADA</option>
                                <option value="/" {{ $signo2 === '/' ? 'selected' : '' }}>/</option>
                                <option value="-" {{ $signo2 === '-' ? 'selected' : '' }}>-</option>
                            </select>
                            <div id="input_select_pequeno_2" class="{{ !$signo2 ? 'hidden' : '' }}">
                                <x-text-input id="input_pequeno_2_value" name="input_pequeno_2_value"
                                    value="{{ $signo2 }}" type="text"
                                    class="border-gray-300 rounded-md shadow-sm input-signo text-center"
                                    placeholder="Símbolo" />
                            </div>
                        </div>

                        <!-- Sección 5 -->
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_grande_3" :value="__('Formato 3')" />
                            <select id="select_grande_3" name="select_grande_3"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-formato" required>
                                <option value="NADA" {{ $pos3['tipo'] === 'NADA' ? 'selected' : '' }}>NADA
                                </option>
                                <option value="DIGITO" {{ $pos3['tipo'] === 'DIGITO' ? 'selected' : '' }}>DIGITO
                                </option>
                                <option value="LETRA" {{ $pos3['tipo'] === 'LETRA' ? 'selected' : '' }}>LETRA
                                </option>
                                <option value="AÑO" {{ $pos3['tipo'] === 'AÑO' ? 'selected' : '' }}>AÑO
                                </option>
                            </select>
                            <div id="input_select_grande_3" class="{{ $pos3['tipo'] === 'NADA' ? 'hidden' : '' }}">
                                <x-text-input id="input_grande_3_value" name="input_grande_3_value"
                                    value="{{ $pos3['valor'] }}" type="text"
                                    class="border-gray-300 rounded-md shadow-sm input-formato text-center"
                                    placeholder="Valor" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg max-w-4xl mx-auto">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-info-circle-fill text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Información sobre el contador (DÍGITO)</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>
                                        Si ya existen registros previos en el historial con este mismo formato, asegúrese de ingresar en el campo <strong>DIGITO</strong> el número del último registro para mantener la secuencia.
                                        <br><br>
                                        <strong>Tenga en cuenta:</strong> La próxima factura generada será el número ingresado <strong>+ 1</strong>. Por ejemplo, si ingresa <code>0001</code>, el próximo será <code>0002</code>. Si desea que el primer registro generado sea <code>0001</code>, debe ingresar <code>0000</code>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center mt-8 border-t pt-8">
                        <x-primary-button class="px-10 py-3 text-lg">
                            {{ __('Actualizar Formato') }}
                        </x-primary-button>
                    </div>

                    <!-- Tabla de Últimas Facturas -->
                    <div id="container_ultimas_facturas" class="mt-12 border-t pt-8 hidden">
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
                            <h3 class="text-lg font-medium text-gray-700 text-center flex-1">Últimos Registros (Historial)</h3>
                            <div class="flex items-center gap-2">
                                <label for="select_limite_facturas" class="text-sm font-medium text-gray-600">Mostrar:</label>
                                <select id="select_limite_facturas" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm py-1.5">
                                    <option value="10" selected>Últimos 10</option>
                                    <option value="50">Últimos 50</option>
                                    <option value="100">Últimos 100</option>
                                    <option value="todos">Todos</option>
                                </select>
                            </div>
                        </div>
                        <p id="texto_total_registros" class="text-xs font-semibold text-gray-500 mb-2 text-right hidden"></p>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 rounded-lg shadow overflow-hidden">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-center">N° Registro</th>
                                        <th scope="col" class="px-6 py-3">Receptor</th>
                                        <th scope="col" class="px-6 py-3 text-right">Monto Neto</th>
                                        <th scope="col" class="px-6 py-3 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_ultimas_facturas" class="divide-y divide-gray-200">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Scripts -->
<script>
    $(document).ready(function() {

        const toggleInputs = (select, isInit = false) => {
            const containerId = 'input_' + select.id;
            const container = document.getElementById(containerId);
            if (container) {
                const input = container.querySelector('input');
                if (select.value === 'NADA') {
                    container.classList.add('hidden');
                    if (input) input.value = '';
                } else {
                    container.classList.remove('hidden');

                    if (!isInit) {
                        if (select.classList.contains('select-signo')) {
                            input.value = select.value; // autocompleta / o -
                        } else if (select.value === 'AÑO') {
                            input.value = new Date().getFullYear();
                        } else {
                            input.value = ''; // limpia si cambia a LETRA o DIGITO
                        }
                    }
                }
            }
        };

        const selects = document.querySelectorAll('.select-toggle');
        selects.forEach(select => {
            toggleInputs(select, true); // al cargar no sobreescribe
            select.addEventListener('change', () => toggleInputs(select, false));

            const containerId = 'input_' + select.id;
            const container = document.getElementById(containerId);
            if (container) {
                const input = container.querySelector('input');
                if (input) {
                    input.addEventListener('input', function() {
                        if (select.value === 'LETRA') {
                            this.value = this.value.replace(/[^a-zA-Z]/g, '');
                            const pos = this.selectionStart;
                            this.value = this.value.toUpperCase();
                            this.setSelectionRange(pos, pos);
                        } else if (select.value === 'DIGITO') {
                            this.value = this.value.replace(/[^0-9]/g, '');
                        }
                    });
                }
            }
        });

        // Fetch ultimas facturas
        function fetchUltimasFacturas() {
            var id_empresa = "{{ $formato->id_empresas_clientes ? 'empresa_'.$formato->id_empresas_clientes : 'cliente_'.$formato->id_cliente }}";
            var valLimite = $('#select_limite_facturas').val();

            if (id_empresa && id_empresa !== "cliente_" && id_empresa !== "empresa_") {
                $.ajax({
                    url: "{{ route('formato_n_solicitud.get_ultimas_diez') }}",
                    data: { id_empresa: id_empresa, limite: valLimite },
                    success: function(res) {
                        if (res.success && res.data.length > 0) {
                            $('#container_ultimas_facturas').removeClass('hidden');

                            if (valLimite === 'todos') {
                                $('#texto_total_registros').text('Total de registros: ' + res.total_records).removeClass('hidden');
                            } else {
                                $('#texto_total_registros').addClass('hidden');
                            }

                            var rows = '';
                            var formatterSoles = new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' });
                            var formatterDolares = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
                            
                            res.data.forEach(function(f) {
                                var monto = parseFloat(f.total_neto) || 0;
                                var montoDisplay = formatterSoles.format(monto);
                                
                                if (f.tipo_moneda === 'USD') {
                                    var tipoPrecio = f.precio_dolar_factura ? 'precio dolar diario sunat' : 'precio dolar mensual';
                                    var tasa = parseFloat(f.precio_dolar_factura) || parseFloat(f.precio_dolar) || 1;
                                    var montoUSD = monto / tasa;
                                    montoDisplay = formatterDolares.format(montoUSD) + '<br><span class="text-[10px] text-gray-400 font-normal">(' + tipoPrecio + ')</span>';
                                }

                                rows +=
                                    '<tr class="bg-white border-b hover:bg-gray-50"><td class="px-6 py-4 whitespace-nowrap">' +
                                    f.fecha +
                                    '</td><td class="px-6 py-4 text-center font-medium text-gray-900">' +
                                    f.numero + '</td><td class="px-6 py-4">' + f.receptor +
                                    '</td><td class="px-6 py-4 text-right font-medium text-gray-900">' + montoDisplay +
                                    '</td><td class="px-6 py-4 text-center">' +
                                    '<a href="' + f.url_detalle + '" class="text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition duration-200 inline-flex items-center gap-1.5 text-xs font-semibold" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Ir a Factura</a>' +
                                    '</td></tr>';
                            });
                            $('#tbody_ultimas_facturas').html(rows);

                            if (typeof showAjaxToast === 'function') {
                                var msg = valLimite === 'todos' ? 'Todo el historial cargado correctamente' : 'Últimos ' + valLimite + ' registros cargados';
                                showAjaxToast(msg, 'success');
                            }
                        } else {
                            $('#container_ultimas_facturas').addClass('hidden');
                            $('#tbody_ultimas_facturas').html('');
                        }
                    }
                });
            }
        }

        $('#select_limite_facturas').on('change', fetchUltimasFacturas);
        fetchUltimasFacturas();

        // Validación para permitir "DIGITO" solo en uno de los selects de formato
        const formatoSelects = document.querySelectorAll('.select-formato');
        const updateDigitoOptions = () => {
            let digitoSelect = null;
            formatoSelects.forEach(s => {
                if (s.value === 'DIGITO') digitoSelect = s;
            });
            formatoSelects.forEach(s => {
                Array.from(s.options).forEach(opt => {
                    if (opt.value === 'DIGITO') {
                        opt.disabled = (digitoSelect !== null && digitoSelect !== s);
                    }
                });
            });
        };
        formatoSelects.forEach(s => s.addEventListener('change', updateDigitoOptions));
        updateDigitoOptions();
    });
</script>
