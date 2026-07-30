<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Todos los Pagos Generados') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('pagos_generados.index') }}"
                    class="block bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                    {{ __('Volver') }}
                </a>
            </div>

            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive nowrap" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Descripción</th>
                            <th class="px-4 py-2 border-b">Tipo Pago</th>
                            <th class="px-4 py-2 border-b">Monto</th>
                            <th class="px-4 py-2 border-b">Fecha Programada</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pagosGenerados as $pago)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $pago->id_pago_generado }}</td>
                                <td class="px-4 py-2 border-b">{{ $pago->pagoProgramado->descripcion ?? '-' }}</td>
                                <td class="px-4 py-2 border-b">{{ $pago->pagoProgramado->tipoPago->nombre }}</td>
                                <td class="px-4 py-2 border-b">S/. {{ number_format($pago->monto, 2) }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($pago->pagado)
                                        @php
                                            $fechaProgramada = \Carbon\Carbon::parse($pago->fecha)->toDateString(); // Obtener solo la fecha sin hora
                                            $fechaPago = \Carbon\Carbon::parse($pago->fecha_pago)->toDateString(); // Obtener solo la fecha sin hora
                                        @endphp

                                        @if ($fechaPago <= $fechaProgramada)
                                            <span class="text-green-600 font-semibold">Pago puntual</span>
                                        @else
                                            <span class="text-red-600 font-semibold">Pago tardío</span>
                                        @endif
                                    @else
                                        <span class="text-yellow-600 font-semibold">Pendiente</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        @if (!$pago->pagado)
                                            <form
                                                action="{{ route('pagos-generados.marcar-pagado', $pago->id_pago_generado) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #16a34a;" onmouseover="this.style.color='#15803d';"
                                                    onmouseout="this.style.color='#16a34a';">
                                                    <span class="tooltip-text">Pagar</span>
                                                    <x-activate-icon />
                                                </button>
                                            </form>
                                            <button
                                                onclick="openEditModal({{ $pago->id_pago_generado }}, '{{ $pago->monto }}', '{{ $pago->fecha }}')"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #F59E0B;" onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </button>
                                            <!-- Formulario de eliminación -->
                                            <form
                                                action="{{ route('pagos-generados.destroy', $pago->id_pago_generado) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Estás seguro de eliminar este pago?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #EF4444;" onmouseover="this.style.color='#DC2626';"
                                                    onmouseout="this.style.color='#EF4444';">
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-green-600 font-semibold">✅Completado</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Modal de Edición con el componente -->
<x-modal-show id="modal-show" title="Editar Pago">
    <form id="editForm" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <x-input-label for="editMonto" value="Monto" />
            <x-text-input type="number" name="monto" id="editMonto" step="0.01"
                class="w-full border border-gray-300 rounded-md px-3 py-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="editFecha" value="Fecha de Pago" />
            <x-text-input type="date" name="fecha" id="editFecha"
                class="w-full border border-gray-300 rounded-md px-3 py-2" />
        </div>

        <div class="flex justify-end gap-2">
            {{-- <button type="button" onclick="closeEditModal()"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">Cancelar</button> --}}
            <x-primary-button type="submit">Guardar</x-primary-button>
        </div>
    </form>
</x-modal-show>

<script>
    function openEditModal(id, monto, fecha) {
        const modal = document.getElementById('modal-show');
        document.getElementById('editMonto').value = monto;
        document.getElementById('editFecha').value = fecha.split('T')[0]; // quitar hora si viene con hora

        const form = document.getElementById('editForm');
        form.action = `/pagos-generados/${id}`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('modal-show');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }
</script>
