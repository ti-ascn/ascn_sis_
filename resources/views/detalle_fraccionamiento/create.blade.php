<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center gap-2">
            {{ $fraccionamiento->refinanciamiento ? 'Refinanciamiento del Fraccionamiento' : 'Fraccionamiento' }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">

                {{-- ENCABEZADO --}}
                <div class="flex flex-col md:flex-row md:justify-between md:items-center border-b pb-4 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">
                            RESOLUCIÓN N° <span class="text-blue-600">{{ $fraccionamiento->nro_resolucion }}</span>
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            <strong>Tributo:</strong> {{ $fraccionamiento->tributo_asociado }}
                            ({{ $fraccionamiento->codigo }})
                        </p>
                        <p class="text-sm text-gray-600">
                            <strong>Periodo:</strong> {{ $fraccionamiento->periodo }}
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 text-right">
                        <p class="text-sm text-gray-500 font-medium">Total Fraccionado</p>
                        <p class="text-2xl font-semibold text-green-600">
                            S/ {{ number_format($fraccionamiento->total, 2) }}
                        </p>
                    </div>
                </div>

                {{-- FORMULARIO --}}
                <form method="POST"
                    action="{{ route('detalle_fraccionamiento.store', $fraccionamiento->id_fraccionamiento) }}">
                    @csrf

                    {{-- TABLA DETALLE --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 text-sm rounded-lg">
                            <thead
                                class="bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 uppercase text-xs font-semibold">
                                <tr>
                                    <th class="px-4 py-3 border w-1/12 text-center">N° / Cuota</th>
                                    <th class="px-4 py-3 border w-2/12 text-center">Fecha de Vencimiento</th>
                                    <th class="px-4 py-3 border w-2/12 text-center">Amortización (S/)</th>
                                    <th class="px-4 py-3 border w-2/12 text-center">Interés (S/)</th>
                                    <th class="px-4 py-3 border w-2/12 text-center">Total (S/)</th>
                                    <th class="px-4 py-3 border w-2/12 text-center">Estado</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($cuotas as $i => $nombre)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="border px-3 py-2 text-center bg-gray-50 font-medium text-gray-700">
                                            <input type="text" name="nombre_cuota[]"
                                                class="w-full text-center border-0 bg-transparent"
                                                value="{{ $nombre }}" readonly>
                                        </td>
                                        <td class="border px-3 py-2 text-center">
                                            <input type="date" name="fecha_vencimiento[]" required
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                                value="{{ old('fecha_vencimiento.' . $i) }}">
                                        </td>
                                        <td class="border px-3 py-2">
                                            <input type="number" step="0.01" name="amortizacion[]" required
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-right"
                                                value="{{ old('amortizacion.' . $i) }}">
                                        </td>
                                        <td class="border px-3 py-2">
                                            <input type="number" step="0.01" name="interes[]" required
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-right"
                                                value="{{ old('interes.' . $i) }}">
                                        </td>
                                        <td class="border px-3 py-2">
                                            <input type="number" step="0.01" name="total[]" required
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-right"
                                                value="{{ old('total.' . $i) }}">
                                        </td>
                                        <td class="border px-3 py-2 text-center">
                                            <select name="estado[]"
                                                class="w-32 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                <option value="pendiente">Pendiente</option>
                                                <option value="pagado">Pagado</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- BOTÓN GUARDAR --}}
                    <div class="flex justify-end mt-8 border-t pt-6">
                        <x-primary-button class="flex items-center gap-2">
                            <i class="bi bi-save"></i>
                            Guardar Detalles
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Selecciona todas las filas del cuerpo de la tabla
        const filas = document.querySelectorAll('tbody tr');

        filas.forEach((fila) => {
            const amortizacion = fila.querySelector('input[name="amortizacion[]"]');
            const interes = fila.querySelector('input[name="interes[]"]');
            const total = fila.querySelector('input[name="total[]"]');

            // función para actualizar el total dinámicamente
            function actualizarTotal() {
                const valAmort = parseFloat(amortizacion.value) || 0;
                const valInteres = parseFloat(interes.value) || 0;
                const suma = valAmort + valInteres;

                // Solo actualiza si el usuario no está escribiendo manualmente
                if (!total.dataset.manualEdit) {
                    total.value = suma.toFixed(2);
                    total.classList.add('bg-green-50'); // pequeño feedback visual
                    setTimeout(() => total.classList.remove('bg-green-50'), 500);
                }
            }

            // Detectar cambios en amortización o interés
            amortizacion.addEventListener('input', actualizarTotal);
            interes.addEventListener('input', actualizarTotal);

            // Si el usuario borra el total, vuelve al modo automático
            total.addEventListener('blur', () => {
                if (total.value.trim() === '') {
                    delete total.dataset.manualEdit;
                    actualizarTotal();
                }
            });
        });
    });
</script>
