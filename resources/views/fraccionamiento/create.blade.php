<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center gap-2">
            {{ __('Registrar Nuevo Fraccionamiento') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-2xl p-8 space-y-8 border border-gray-100">
                <form method="POST" action="{{ route('fraccionamiento.store') }}" autocomplete="off" class="space-y-6">
                    @csrf
                    {{-- Sección Empresa --}}
                    <div>
                        <div>
                            <x-input-label for="id_empresa_cliente" :value="__('Empresa')" />
                            <select name="id_empresa_cliente" class="select-empresa w-full" required>
                                <option value="">Selecciona una empresa</option>
                                @foreach ($empresas as $empresa)
                                    <option value="{{ $empresa->id_empresas_clientes }}">
                                        {{ $empresa->razon_social }} – {{ $empresa->ruc }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('id_empresa_cliente')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Sección Resolución y Tributo --}}
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="nro_resolucion" :value="__('N° Resolución')" />
                                <x-text-input id="nro_resolucion" name="nro_resolucion" type="text"
                                    class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="tributo_asociado" :value="__('Nombre del Tributo')" />
                                <x-text-input id="tributo_asociado" name="tributo_asociado" type="text"
                                    class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="codigo" :value="__('Código del Tributo')" />
                                <x-text-input id="codigo" name="codigo" type="text" class="mt-1 block w-full"
                                    required />
                            </div>
                        </div>
                    </div>

                    {{-- Sección Periodo y Montos --}}
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="periodo" :value="__('Periodo (Año y Mes)')" />
                                <input id="periodo" name="periodo" type="month"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    value="{{ old('periodo') }}" required />
                            </div>
                            <div>
                                <x-input-label for="monto_tributo" :value="__('Monto del Tributo')" />
                                <x-text-input id="monto_tributo" name="monto_tributo" type="number" step="0.01"
                                    class="mt-1 block w-full" placeholder="0.00" required />
                            </div>
                            <div>
                                <x-input-label for="interes" :value="__('Interés')" />
                                <x-text-input id="interes" name="interes" type="number" step="0.01"
                                    class="mt-1 block w-full" placeholder="0.00" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                            <div>
                                <x-input-label for="total" :value="__('Total')" />
                                <x-text-input id="total" name="total" type="number" step="0.01"
                                    class="mt-1 block w-full" placeholder="0.00" required />
                            </div>
                            <div>
                                <x-input-label for="nro_cuotas" :value="__('Número de Cuotas')" />
                                <x-text-input id="nro_cuotas" name="nro_cuotas" type="number" class="mt-1 block w-full"
                                    required />
                            </div>
                            <div>
                                <x-input-label for="cuota_acogimiento" :value="__('Cuota de Acogimiento')" />
                                <select id="cuota_acogimiento" name="cuota_acogimiento"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                            <div>
                                <x-input-label for="refinanciamiento" :value="__('Refinanciamiento')" />
                                <select id="refinanciamiento" name="refinanciamiento"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="descripcion" :value="__('Descripción')" />
                                <x-text-input id="descripcion" name="descripcion" type="text"
                                    class="mt-1 block w-full" placeholder="Breve descripción..." />
                            </div>
                        </div>
                    </div>

                    {{-- Botón Guardar --}}
                    <div class="flex justify-end">
                        <x-primary-button>
                            <i class="bi bi-check2-circle mr-1"></i>
                            {{ __('Guardar Fraccionamiento') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>

<script>
    $(document).ready(function() {
        $('.select-empresa').select2({
            placeholder: 'Selecciona una empresa',
            allowClear: true,
            width: 'resolve'
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const montoTributo = document.getElementById('monto_tributo');
        const interes = document.getElementById('interes');
        const total = document.getElementById('total');

        function actualizarTotal() {
            const tributo = parseFloat(montoTributo.value) || 0;
            const interesVal = parseFloat(interes.value) || 0;
            const resultado = tributo + interesVal;

            // solo actualiza si el usuario no está editando manualmente
            if (!total.dataset.manualEdit) {
                total.value = resultado.toFixed(2);
            }
        }

        // Detectar cambios en los campos
        montoTributo.addEventListener('input', actualizarTotal);
        interes.addEventListener('input', actualizarTotal);

        // Permitir edición manual: si el usuario escribe algo, se pausa el auto-cálculo
        total.addEventListener('input', () => {
            total.dataset.manualEdit = true;
        });

        // Si el usuario deja el campo vacío, el sistema retoma el cálculo automático
        total.addEventListener('blur', () => {
            if (total.value.trim() === '') {
                delete total.dataset.manualEdit;
                actualizarTotal();
            }
        });
    });
</script>
