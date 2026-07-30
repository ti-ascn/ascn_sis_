<style>
    .select-formato { width: 140px; padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    .input-formato  { width: 140px; padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    .select-signo   { width: 80px;  padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    .input-signo    { width: 80px;  padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    .select2-container--default .select2-selection--single {
        border-color: #d1d5db !important; height: 42px !important;
        display: flex !important; align-items: center !important;
        border-radius: 0.375rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px !important; }
</style>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center">
            {{ __('Editar Formato Nro Cotización (v2)') }}
        </h2>
        <a href="{{ route('formato_cotizaciones_2.index') }}"
            class="inline-flex items-center gap-2 bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded transition w-fit">
            <i class="bi bi-arrow-left"></i> Atrás
        </a>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <div class="bg-white shadow-lg sm:rounded-xl p-8">
                <form action="{{ route('formato_cotizaciones_2.update', $formato->id_formato_cotizaciones_2) }}"
                      method="POST" class="max-w-4xl mx-auto">
                    @csrf
                    @method('PUT')

                    {{-- Info del Emisor (solo lectura) --}}
                    <div class="mb-8 text-center border-b pb-6">
                        <h3 class="text-lg font-medium text-gray-700">
                            Empresa Emisora: <span class="text-indigo-700 font-semibold">{{ $formato->emisor->razon_social ?? 'N/A' }}</span>
                        </h3>
                        <p class="text-sm text-gray-500">RUC: {{ $formato->emisor->ruc ?? 'N/A' }}</p>
                    </div>

                    {{-- Reglas informativas --}}
                    <div class="mb-6 bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg max-w-4xl mx-auto">
                        <div class="flex">
                            <div class="flex-shrink-0"><i class="bi bi-info-circle-fill text-amber-400"></i></div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-amber-800">Reglas de numeración automática</h3>
                                <ul class="mt-2 text-sm text-amber-700 list-disc pl-4 space-y-1">
                                    <li><strong>Días normales:</strong> salto de +2 por factura.</li>
                                    <li><strong>Últimos 7 días del mes:</strong> salto de +6.</li>
                                    <li><strong>Huecos:</strong> se llenan antes de avanzar la secuencia.</li>
                                    <li><strong>Fecha de emisión:</strong> no se puede registrar más facturas si no hay numeros disponibles en la fecha determinada.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Grid de formato --}}
                    <div class="flex flex-wrap gap-x-2 gap-y-10 justify-center items-start">

                        {{-- Formato 1 --}}
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_grande_1" :value="__('Formato 1')" />
                            <select id="select_grande_1" name="select_grande_1"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-formato" required>
                                <option value="NADA"  {{ $pos1['tipo'] === 'NADA'   ? 'selected' : '' }}>NADA</option>
                                <option value="DIGITO"{{ $pos1['tipo'] === 'DIGITO' ? 'selected' : '' }}>DIGITO</option>
                                <option value="LETRA" {{ $pos1['tipo'] === 'LETRA'  ? 'selected' : '' }}>LETRA</option>
                                <option value="AÑO"   {{ $pos1['tipo'] === 'AÑO'   ? 'selected' : '' }}>AÑO</option>
                            </select>
                            <div id="input_select_grande_1" class="{{ $pos1['tipo'] === 'NADA' ? 'hidden' : '' }}">
                                <x-text-input id="input_grande_1_value" name="input_grande_1_value"
                                    value="{{ $pos1['valor'] }}" type="text"
                                    class="border-gray-300 rounded-md shadow-sm input-formato text-center"
                                    placeholder="Valor" />
                            </div>
                        </div>

                        {{-- Signo 1 --}}
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

                        {{-- Formato 2 --}}
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_grande_2" :value="__('Formato 2')" />
                            <select id="select_grande_2" name="select_grande_2"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-formato" required>
                                <option value="NADA"  {{ $pos2['tipo'] === 'NADA'   ? 'selected' : '' }}>NADA</option>
                                <option value="DIGITO"{{ $pos2['tipo'] === 'DIGITO' ? 'selected' : '' }}>DIGITO</option>
                                <option value="LETRA" {{ $pos2['tipo'] === 'LETRA'  ? 'selected' : '' }}>LETRA</option>
                                <option value="AÑO"   {{ $pos2['tipo'] === 'AÑO'   ? 'selected' : '' }}>AÑO</option>
                            </select>
                            <div id="input_select_grande_2" class="{{ $pos2['tipo'] === 'NADA' ? 'hidden' : '' }}">
                                <x-text-input id="input_grande_2_value" name="input_grande_2_value"
                                    value="{{ $pos2['valor'] }}" type="text"
                                    class="border-gray-300 rounded-md shadow-sm input-formato text-center"
                                    placeholder="Valor" />
                            </div>
                        </div>

                        {{-- Signo 2 --}}
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

                        {{-- Formato 3 --}}
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_grande_3" :value="__('Formato 3')" />
                            <select id="select_grande_3" name="select_grande_3"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-formato" required>
                                <option value="NADA"  {{ $pos3['tipo'] === 'NADA'   ? 'selected' : '' }}>NADA</option>
                                <option value="DIGITO"{{ $pos3['tipo'] === 'DIGITO' ? 'selected' : '' }}>DIGITO</option>
                                <option value="LETRA" {{ $pos3['tipo'] === 'LETRA'  ? 'selected' : '' }}>LETRA</option>
                                <option value="AÑO"   {{ $pos3['tipo'] === 'AÑO'   ? 'selected' : '' }}>AÑO</option>
                            </select>
                            <div id="input_select_grande_3" class="{{ $pos3['tipo'] === 'NADA' ? 'hidden' : '' }}">
                                <x-text-input id="input_grande_3_value" name="input_grande_3_value"
                                    value="{{ $pos3['valor'] }}" type="text"
                                    class="border-gray-300 rounded-md shadow-sm input-formato text-center"
                                    placeholder="Valor" />
                            </div>
                        </div>
                    </div>

                    {{-- Info DIGITO --}}
                    <div class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg max-w-4xl mx-auto">
                        <div class="flex">
                            <div class="flex-shrink-0"><i class="bi bi-info-circle-fill text-blue-400"></i></div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Información sobre el contador (DÍGITO)</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>
                                        Modifique el campo <strong>DÍGITO</strong> para ajustar desde dónde continúa la secuencia.
                                        El valor ingresado se guardará <strong>tal cual</strong> sin ser sobreescrito automáticamente.
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

                    {{-- Historial del emisor --}}
                    <div id="container_ultimas_facturas" class="mt-12 border-t pt-8 hidden">
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
                            <h3 class="text-lg font-medium text-gray-700 text-center flex-1">Últimos Nros Cotización del Emisor</h3>
                            <div class="flex items-center gap-2">
                                <label for="select_limite_facturas" class="text-sm font-medium text-gray-600">Mostrar:</label>
                                <select id="select_limite_facturas" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm py-1.5">
                                    <option value="10" selected>Últimos 10</option>
                                    <option value="50">Últimos 50</option>
                                    <option value="todos">Todos</option>
                                </select>
                            </div>
                        </div>
                        <p id="texto_total_registros" class="text-xs font-semibold text-gray-500 mb-2 text-right hidden"></p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 rounded-lg shadow overflow-hidden">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-6 py-3">ID</th>
                                        <th class="px-6 py-3">Fecha</th>
                                        <th class="px-6 py-3 text-center">Nro Cotización</th>
                                        <th class="px-6 py-3">Receptor</th>
                                        <th class="px-6 py-3 text-right">Monto Neto</th>
                                        <th class="px-6 py-3 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_ultimas_facturas" class="divide-y divide-gray-200"></tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    $(document).ready(function() {
        // Toggle inputs por tipo
        const toggleInputs = (select, isInit = false) => {
            const containerId = 'input_' + select.id;
            const container   = document.getElementById(containerId);
            if (!container) return;
            const input = container.querySelector('input');
            if (select.value === 'NADA') {
                container.classList.add('hidden');
                if (input) input.value = '';
            } else {
                container.classList.remove('hidden');
                if (!isInit) {
                    if (select.classList.contains('select-signo')) {
                        if (input) input.value = select.value;
                    } else if (select.value === 'AÑO') {
                        if (input) input.value = new Date().getFullYear();
                    } else {
                        if (input) input.value = '';
                    }
                }
            }
        };

        document.querySelectorAll('.select-toggle').forEach(select => {
            toggleInputs(select, true);
            select.addEventListener('change', () => toggleInputs(select, false));
            const container = document.getElementById('input_' + select.id);
            if (container) {
                const input = container.querySelector('input');
                if (input) {
                    input.addEventListener('input', function() {
                        if (select.value === 'LETRA') {
                            const pos = this.selectionStart;
                            this.value = this.value.replace(/[^a-zA-Z]/g, '').toUpperCase();
                            this.setSelectionRange(pos, pos);
                        } else if (select.value === 'DIGITO' || select.value === 'AÑO') {
                            this.value = this.value.replace(/[^0-9]/g, '');
                        }
                    });
                }
            }
        });

        // Solo un DIGITO, LETRA o AÑO
        const formatoSelects = document.querySelectorAll('.select-formato');
        const updateUniqueOptions = () => {
            let digitoSelect = null;
            let letraSelect = null;
            let anioSelect = null;
            
            formatoSelects.forEach(s => {
                if (s.value === 'DIGITO') digitoSelect = s;
                if (s.value === 'LETRA') letraSelect = s;
                if (s.value === 'AÑO') anioSelect = s;
            });
            
            formatoSelects.forEach(s => {
                Array.from(s.options).forEach(opt => {
                    if (opt.value === 'DIGITO') opt.disabled = (digitoSelect !== null && digitoSelect !== s);
                    if (opt.value === 'LETRA') opt.disabled = (letraSelect !== null && letraSelect !== s);
                    if (opt.value === 'AÑO') opt.disabled = (anioSelect !== null && anioSelect !== s);
                });
            });
        };
        formatoSelects.forEach(s => s.addEventListener('change', updateUniqueOptions));
        updateUniqueOptions();

        // Historial del emisor (carga al abrir la página)
        function fetchHistorial() {
            const idEmisor = "{{ $formato->id_empresas_clientes_emisor }}";
            const limite   = $('#select_limite_facturas').val();
            $.ajax({
                url: "{{ route('formato_cotizaciones_2.get_ultimas_diez') }}",
                data: { id_empresa_emisor: idEmisor, limite: limite },
                success: function(res) {
                    if (res.success && res.data.length > 0) {
                        $('#container_ultimas_facturas').removeClass('hidden');
                        const fmt   = new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' });
                        const fmtUS = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
                        let rows = '';
                        res.data.forEach(f => {
                            let monto = parseFloat(f.total_neto) || 0;
                            let montoDisplay = fmt.format(monto);
                            if (f.tipo_moneda === 'USD') {
                                const tasa = parseFloat(f.precio_dolar_factura) || parseFloat(f.precio_dolar) || 1;
                                montoDisplay = fmtUS.format(monto / tasa);
                            }
                            rows += `<tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">${f.id_factura}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${f.fecha}</td>
                                <td class="px-6 py-4 text-center font-medium text-gray-900">${f.numero}</td>
                                <td class="px-6 py-4">${f.receptor}</td>
                                <td class="px-6 py-4 text-right font-medium text-gray-900">${montoDisplay}</td>
                                <td class="px-6 py-4 text-center">
                                    <a href="${f.url_detalle}" target="_blank" class="text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition text-xs font-semibold">
                                        <i class="bi bi-box-arrow-up-right"></i> Ver
                                    </a>
                                </td>
                            </tr>`;
                        });
                        $('#tbody_ultimas_facturas').html(rows);
                        if (limite === 'todos') {
                            $('#texto_total_registros').text('Total registros: ' + res.total_records).removeClass('hidden');
                        } else {
                            $('#texto_total_registros').addClass('hidden');
                        }
                    } else {
                        $('#container_ultimas_facturas').addClass('hidden');
                    }
                }
            });
        }

        $('#select_limite_facturas').on('change', fetchHistorial);
        fetchHistorial();
    });
</script>
