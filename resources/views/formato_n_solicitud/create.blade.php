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

    /* Estilos para Select2 para que coincidan con el diseño */
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

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center">
            {{ __('Generar Formato N° Solicitud') }}
        </h2>

        <a href="{{ route('formato_n_solicitud.index') }}"
            class="inline-flex items-center gap-2 bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded transition w-fit">
            <i class="bi bi-arrow-left"></i>
            Atrás
        </a>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <div class="bg-white shadow-lg sm:rounded-xl p-8">
                <form action="{{ route('formato_n_solicitud.store') }}" method="POST" class="max-w-4xl mx-auto">
                    @csrf

                    <!-- Selección de Empresa -->
                    <div class="mb-10 text-center border-b pb-8">
                        <x-input-label for="id_empresa" :value="__('Empresa a la que se le hará el formato')" class="mb-2 text-lg font-medium" />
                        <div class="flex justify-center">
                            <div class="w-full max-w-md">
                                <select id="id_empresa" name="id_empresa" class="select-empresa w-full" required>
                                    <option value="">Buscar y seleccionar empresa o cliente...</option>

                                    @isset($empresas)
                                        <optgroup label="── Empresas Clientes ──">
                                            @foreach ($empresas as $empresa)
                                                <option value="empresa_{{ $empresa->id_empresas_clientes }}">
                                                    {{ $empresa->razon_social }} - {{ $empresa->ruc }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endisset

                                    @isset($clientes)
                                        <optgroup label="── Clientes Auditoría ──">
                                            @foreach ($clientes as $cliente)
                                                <option value="cliente_{{ $cliente->id_cliente }}">
                                                    {{ $cliente->razon_social }} - {{ $cliente->ruc }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endisset
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Grid para Selects e Inputs -->
                    <div class="flex flex-wrap gap-x-2 gap-y-10 justify-center items-start">
                        <!-- Columna 1: Formato 1 -->
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_grande_1" :value="__('Formato 1')" />
                            <select id="select_grande_1" name="select_grande_1"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-formato" required>
                                <option value="NADA">NADA</option>
                                <option value="DIGITO">DIGITO</option>
                                <option value="LETRA">LETRA</option>
                                <option value="AÑO">AÑO</option>
                            </select>
                            <div id="input_select_grande_1" class="hidden">
                                <x-text-input id="input_grande_1_value" name="input_grande_1_value"
                                    class="border-gray-300 rounded-md shadow-sm input-formato text-center"
                                    placeholder="Valor" type="text" />
                            </div>
                        </div>

                        <!-- Columna 2: Signo 1 -->
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_pequeno_1" :value="__('Signo 1')" />
                            <select id="select_pequeno_1" name="select_pequeno_1"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-signo" required>
                                <option value="NADA">NADA</option>
                                <option value="/">/</option>
                                <option value="-">-</option>
                            </select>
                            <div id="input_select_pequeno_1" class="hidden">
                                <x-text-input id="input_pequeno_1_value" name="input_pequeno_1_value"
                                    class="border-gray-300 rounded-md shadow-sm input-signo text-center"
                                    placeholder="Símbolo" type="text" />
                            </div>
                        </div>

                        <!-- Columna 3: Formato 2 -->
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_grande_2" :value="__('Formato 2')" />
                            <select id="select_grande_2" name="select_grande_2"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-formato" required>
                                <option value="NADA">NADA</option>
                                <option value="DIGITO">DIGITO</option>
                                <option value="LETRA">LETRA</option>
                                <option value="AÑO">AÑO</option>
                            </select>
                            <div id="input_select_grande_2" class="hidden">
                                <x-text-input id="input_grande_2_value" name="input_grande_2_value"
                                    class="border-gray-300 rounded-md shadow-sm input-formato text-center"
                                    placeholder="Valor" type="text" />
                            </div>
                        </div>

                        <!-- Columna 4: Signo 2 -->
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_pequeno_2" :value="__('Signo 2')" />
                            <select id="select_pequeno_2" name="select_pequeno_2"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-signo" required>
                                <option value="NADA">NADA</option>
                                <option value="/">/</option>
                                <option value="-">-</option>
                            </select>
                            <div id="input_select_pequeno_2" class="hidden">
                                <x-text-input id="input_pequeno_2_value" name="input_pequeno_2_value"
                                    class="border-gray-300 rounded-md shadow-sm input-signo text-center"
                                    placeholder="Símbolo" type="text" />
                            </div>
                        </div>

                        <!-- Columna 5: Formato 3 -->
                        <div class="flex flex-col items-center space-y-1.5">
                            <x-input-label for="select_grande_3" :value="__('Formato 3')" />
                            <select id="select_grande_3" name="select_grande_3"
                                class="border-gray-300 rounded-md shadow-sm select-toggle select-formato" required>
                                <option value="NADA">NADA</option>
                                <option value="DIGITO">DIGITO</option>
                                <option value="LETRA">LETRA</option>
                                <option value="AÑO">AÑO</option>
                            </select>
                            <div id="input_select_grande_3" class="hidden">
                                <x-text-input id="input_grande_3_value" name="input_grande_3_value"
                                    class="border-gray-300 rounded-md shadow-sm input-formato text-center"
                                    placeholder="Valor" type="text" />
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
                            {{ __('Generar Formato') }}
                        </x-primary-button>
                    </div>

                    <!-- Tabla de Últimas Facturas -->
                    <div id="container_ultimas_facturas" class="mt-12 border-t pt-8 hidden">
                        <h3 class="text-lg font-medium text-gray-700 text-center mb-4">Últimos Registros (Historial)</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 rounded-lg shadow overflow-hidden">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-center">N° Registro</th>
                                        <th scope="col" class="px-6 py-3">Receptor</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_ultimas_facturas">
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
        // Inicializar Select2
        $('.select-empresa').select2({
            placeholder: 'Buscar y seleccionar empresa o cliente...',
            allowClear: true,
            width: 'resolve'
        });

        const toggleInputs = (select) => {
            const containerId = 'input_' + select.id;
            const container = document.getElementById(containerId);
            if (container) {
                const input = container.querySelector('input');
                if (select.value === 'NADA') {
                    container.classList.add('hidden');
                    if (input) input.value = '';
                } else {
                    container.classList.remove('hidden');
                    if (input && select.classList.contains('select-signo')) {
                        input.value = select.value;
                    }
                    if (input && select.value === 'AÑO') {
                        input.value = new Date().getFullYear();
                    }
                    if (input && select.value === 'LETRA') {
                        input.style.textTransform = 'uppercase';
                    } else {
                        input.style.textTransform = 'none';
                    }
                }
            }
        };

        const selects = document.querySelectorAll('.select-toggle');
        selects.forEach(select => {
            toggleInputs(select);
            select.addEventListener('change', () => toggleInputs(select));

            const containerId = 'input_' + select.id;
            const container = document.getElementById(containerId);
            if (container) {
                const input = container.querySelector('input');
                if (input) {
                    // Adjuntar evento input a todos los inputs
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

        $('#id_empresa').on('change', function() {
            var val = $(this).val();
            if (!val) {
                $('#container_ultimas_facturas').addClass('hidden');
                return;
            }
            $.ajax({
                url: "{{ route('formato_n_solicitud.get_ultimas_diez') }}",
                data: { id_empresa: val },
                success: function(res) {
                    if (res.success && res.data.length > 0) {
                        $('#container_ultimas_facturas').removeClass('hidden');
                        var rows = '';
                        res.data.forEach(function(f) {
                            rows += '<tr class="bg-white border-b"><td class="px-6 py-4 whitespace-nowrap">' + f.fecha + '</td><td class="px-6 py-4 text-center font-medium text-gray-900">' + f.numero + '</td><td class="px-6 py-4">' + f.receptor + '</td></tr>';
                        });
                        $('#tbody_ultimas_facturas').html(rows);
                    } else {
                        $('#container_ultimas_facturas').addClass('hidden');
                        $('#tbody_ultimas_facturas').html('');
                    }
                }
            });
        });

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
