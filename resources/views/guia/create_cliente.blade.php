<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Guía – <span class="text-blue-500">Cliente</span>
            </h2>

            <a href="{{ route('guias.create_empresa') }}"
                class="inline-flex items-center gap-1 text-green-600 text-sm font-semibold hover:underline"
                title="¿Es para una empresa? Haz clic en Crear Guía Empresa.">
                <i class="bi bi-building"></i> Guía para empresa
            </a>
        </div>
    </x-slot>


    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('guias.store') }}" autocomplete="off">
                    @csrf

                    <input type="hidden" name="id_usuario" value="{{ Auth::id() }}">
                    <input type="hidden" name="id_empresas_clientes" value="">
                    <input type="hidden" name="tipo_cambio" id="tipo_cambio_hidden">

                    {{-- Cliente --}}
                    @if (Auth::user()->tipo_usuario == 2 ||
                            Auth::user()->tipo_usuario == 1 ||
                            Auth::user()->id == 28 ||
                            Auth::user()->id == 38) {{-- !agregado para usuario ROSA LORENA SANCHEZ ID:28 en la BD
                            ! Y AHORA TAMBIEN EL USUARIO CON ID 38--}}
                        <div class="grid grid-cols-12 gap-4 mb-6 items-end">
                            <div class="col-span-12 md:col-span-10">
                                <x-input-label for="id_cliente" :value="__('Cliente')" />
                                <select id="id_cliente" name="id_cliente"
                                    class="select-cliente mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Selecciona un cliente</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id_cliente }}"
                                            {{ old('id_cliente', $clienteId) == $cliente->id_cliente ? 'selected' : '' }}>
                                            {{ $cliente->razon_social }} – {{ $cliente->ruc }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('id_cliente')" class="mt-2" />
                            </div>

                            <div class="col-span-12 md:col-span-2">
                                <button type="button" onclick="toggleModal('modalCrearCliente')"
                                    class="w-full bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 text-white flex items-center justify-center">
                                    <i class="bi bi-person-plus-fill mr-2"></i> Cliente
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="mb-6">
                            <x-input-label for="id_cliente" :value="__('Cliente')" />
                            <select id="id_cliente" name="id_cliente"
                                class="select-cliente mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Selecciona un cliente</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id_cliente }}"
                                        {{ old('id_cliente', $clienteId) == $cliente->id_cliente ? 'selected' : '' }}>
                                        {{ $cliente->razon_social }} – {{ $cliente->ruc }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('id_cliente')" class="mt-2" />
                        </div>
                    @endif

                    {{-- Campos en columnas --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        {{-- select de moneda --}}
                        <div>
                            <x-input-label for="moneda" value="Moneda" />
                            <select id="moneda" name="moneda"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="PEN" selected>Soles (PEN)</option>
                                <option value="USD">Dólares (USD)</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="monto" :value="__('Monto')" />
                            <x-text-input id="monto" name="monto" type="number" step="0.01"
                                class="mt-1 block w-full" value="{{ old('monto') }}" />
                            <x-input-error :messages="$errors->get('monto')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="recepcion" :value="__('Recepción')" />
                            <x-text-input id="recepcion" name="recepcion" type="date" class="mt-1 block w-full"
                                value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required />
                            <x-input-error :messages="$errors->get('recepcion')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="programado" :value="__('Programado')" />
                            <select id="programado" name="programado"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="listo">Listo</option>
                                <option value="pendiente" selected>Pendiente</option>
                            </select>
                            <x-input-error :messages="$errors->get('programado')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="emitido" :value="__('Emitido')" />
                            <select id="emitido" name="emitido"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="listo">Listo</option>
                                <option value="pendiente" selected>Pendiente</option>
                            </select>
                            <x-input-error :messages="$errors->get('emitido')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="grr" :value="__('GRR')" />
                            <select id="grr" name="grr"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="-">-</option>
                                <option value="si">Sí</option>
                                <option value="no">No</option>
                            </select>
                            <x-input-error :messages="$errors->get('grr')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="grt" :value="__('GRT')" />
                            <select id="grt" name="grt"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="-">-</option>
                                <option value="si">Sí</option>
                                <option value="no">No</option>
                            </select>
                            <x-input-error :messages="$errors->get('grt')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="bancarizacion" :value="__('Bancarización')" />
                            <select id="bancarizacion" name="bancarizacion"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="-">-</option>
                                <option value="si">Sí</option>
                                <option value="no">No</option>
                                <option value="entregado">Entregado</option>
                                <option value="bancarizó">Cliente Bancarizó</option>
                                <option value="archivado">Archivado</option>
                            </select>
                            <x-input-error :messages="$errors->get('bancarizacion')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="entregado" :value="__('Entregado')" />
                            <textarea id="entregado" name="entregado" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('entregado') }}</textarea>
                            <x-input-error :messages="$errors->get('entregado')" class="mt-2" />
                        </div>

                    </div>

                    <div class="mt-6 flex justify-between items-center">
                        <div class="flex items-center">
                            <label for="contingencia" class="inline-flex items-center">
                                <input type="checkbox" name="contingencia" id="contingencia"
                                    class="h-5 w-5 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    {{ old('contingencia') ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700 font-medium">CONTINGENCIA</span>
                            </label>
                        </div>
                    </div>

                    {{-- Textareas --}}
                    <div class="mt-6 grid grid-cols-1 gap-6">
                        <div>
                            <x-input-label for="observaciones" :value="__('Observaciones')" />
                            <textarea id="observaciones" name="observaciones" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('observaciones') }}</textarea>
                            <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Botón --}}
                    <div class="flex justify-end mt-6">
                        <x-primary-button>{{ __('Guardar Guía') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
<x-modal-show id="modalCrearCliente" title="Registrar Cliente Rápido">
    <form id="formClienteRapido">
        @csrf

        <div class="mb-4">
            <label for="ruc_cliente" class="block text-sm font-medium">RUC</label>
            <input type="text" name="ruc" id="ruc_cliente" maxlength="11"
                class="w-full border-gray-300 rounded-md" required inputmode="numeric" pattern="[0-9]{11}"
                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)">
        </div>


        <div class="mb-4">
            <label for="razon_cliente" class="block text-sm font-medium">Razón Social</label>
            <input type="text" name="razon_social" id="razon_cliente"
                class="w-full border-gray-300 rounded-md uppercase" required
                oninput="this.value = this.value.toUpperCase()">
        </div>

        <div class="mb-4">
            <label for="rubro_cliente" class="block text-sm font-medium">Rubro</label>
            <input type="text" name="rubro" id="rubro_cliente"
                class="w-full border-gray-300 rounded-md uppercase" oninput="this.value = this.value.toUpperCase()">
        </div>

        <div class="mb-4">
            <label for="contacto_cliente" class="block text-sm font-medium">Contacto</label>
            <input type="text" name="contacto" id="contacto_cliente"
                class="w-full border-gray-300 rounded-md uppercase" oninput="this.value = this.value.toUpperCase()">
        </div>

        <div class="mb-4">
            <label for="telefono" class="block text-sm font-medium">Telefono</label>
            <input type="text" name="telefono" id="telefono" class="w-full border-gray-300 rounded-md uppercase"
                oninput="this.value = this.value.toUpperCase()">
        </div>

        <div class="mb-4">
            <label for="correo" class="block text-sm font-medium">Email</label>
            <input type="text" name="correo" id="correo" class="w-full border-gray-300 rounded-md uppercase"
                oninput="this.value = this.value.toUpperCase()">
        </div>

        <div id="mensaje-crear-cliente" class="hidden mb-4 text-sm font-semibold p-3 rounded-md"></div>

        <div class="flex items-center justify-end gap-4">
            <div id="loader-crear-cliente" class="hidden flex items-center gap-2 text-sm text-gray-600">
                <svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                    </path>
                </svg>
                Guardando...
            </div>
            <x-primary-button id="btn-guardar-cliente" type="submit">Guardar</x-primary-button>
        </div>
    </form>
</x-modal-show>

<script>
    $(document).ready(function() {
        $('.select-cliente').select2({
            placeholder: 'Selecciona un cliente',
            allowClear: true,
            width: 'resolve'
        });
    });
</script>

<script>
    document.getElementById('formClienteRapido').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const mensaje = document.getElementById('mensaje-crear-cliente');
        const loader = document.getElementById('loader-crear-cliente');
        const btnGuardar = document.getElementById('btn-guardar-cliente');

        // Bloquear botón y mostrar loader
        loader.classList.remove('hidden');
        btnGuardar.disabled = true;
        mensaje.classList.add('hidden');

        fetch("{{ route('clientes.store') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                loader.classList.add('hidden');
                btnGuardar.disabled = false;

                if (data.success) {
                    const nuevo = data.cliente;
                    const select = document.getElementById('id_cliente');
                    const option = document.createElement('option');
                    option.value = nuevo.id_cliente;
                    option.text = `${nuevo.razon_social} – ${nuevo.ruc}`;
                    select.appendChild(option);
                    $('.select-cliente').val(nuevo.id_cliente).trigger('change');

                    mensaje.textContent = 'Cliente registrado correctamente.';
                    mensaje.className =
                        'bg-green-100 text-green-700 border border-green-300 mb-4 p-3 text-sm font-semibold rounded-md';
                    mensaje.classList.remove('hidden');

                    toggleModal('modalCrearCliente');
                    form.reset();
                } else {
                    mensaje.textContent = data.error || 'Error al registrar cliente.';
                    mensaje.className =
                        'bg-red-100 text-red-700 border border-red-300 mb-4 p-3 text-sm font-semibold rounded-md';
                    mensaje.classList.remove('hidden');
                }

                setTimeout(() => mensaje.classList.add('hidden'), 4000);
            })
            .catch(error => {
                loader.classList.add('hidden');
                btnGuardar.disabled = false;
                mensaje.textContent = 'Error inesperado.';
                mensaje.className =
                    'bg-red-100 text-red-700 border border-red-300 mb-4 p-3 text-sm font-semibold rounded-md';
                mensaje.classList.remove('hidden');
                console.error(error);
            });
    });
</script>
