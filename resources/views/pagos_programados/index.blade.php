<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pagos Programados') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('pagos_programados.create') }}"
                    class="block bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white text-center w-full md:w-auto">
                    {{ __('Crear Pago Programado') }}
                </a>
            </div>

            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive nowrap" id="myTable-pagos-programados">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Tipo Pago</th>
                            <th class="px-4 py-2 border-b">Descripción</th>
                            <th class="px-4 py-2 border-b">Monto</th>
                            <th class="px-4 py-2 border-b">Fecha Inicio</th>
                            <th class="px-4 py-2 border-b">Frecuencia</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pagos as $pago)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $pago->id_pago_programado }}</td>
                                <td class="px-4 py-2 border-b">{{ $pago->tipoPago->nombre }}</td>
                                <td class="px-4 py-2 border-b">{{ $pago->descripcion }}</td>
                                <td class="px-4 py-2 border-b">S/. {{ number_format($pago->monto, 2) }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($pago->fecha_inicio)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ $pago->frecuencia_detalle }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @if ($pago->activo)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                            Activo
                                        </span>
                                    @else
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('pagos_programados.edit', $pago->id_pago_programado) }}"
                                            class="flex items-center py-1 rounded-md tooltip" style="color: #F59E0B;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </a>
                                        <button class="text-blue-600 hover:text-blue-800 tooltip"
                                            onclick="openGenerarPagoModal({{ $pago->id_pago_programado }}, {{ $pago->monto }})">
                                            <span class="tooltip-text">Generar pago</span>
                                            <x-generate-icon />
                                        </button>
                                        @if ($pago->activo)
                                            {{-- Botón para desactivar --}}
                                            <form
                                                action="{{ route('pagos_programados.desactivar', $pago->id_pago_programado) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626;" onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';">
                                                    <x-deactivate-icon />
                                                    <span class="tooltip-text">Desactivar</span>
                                                </button>
                                            </form>
                                        @else
                                            {{-- Botón para reactivar --}}
                                            <form
                                                action="{{ route('pagos_programados.reactivar', $pago->id_pago_programado) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #16a34a;" onmouseover="this.style.color='#15803d';"
                                                    onmouseout="this.style.color='#16a34a';">
                                                    <span class="tooltip-text">Activar</span>
                                                    <x-activate-icon />
                                                </button>
                                            </form>
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
<x-modal-show id="generar-pago-modal" title="Generar pago manual">
    <form id="form-generar-pago" method="POST">
        @csrf
        <div class="mb-4">
            <x-input-label for="fecha" value="Fecha del pago" />
            <x-text-input id="fecha" class="mt-1 block w-full" type="date" name="fecha" required />
        </div>
        <div class="mb-4">
            <x-input-label for="monto" value="Monto" />
            <x-text-input id="monto" class="mt-1 block w-full" type="number" name="monto" value=""
                step="0.01" required />
        </div>
        <div class="flex justify-end">
            <x-primary-button class="ml-2">
                Generar
            </x-primary-button>
        </div>
    </form>
</x-modal-show>
<script>
    function openGenerarPagoModal(id, monto) {
        const form = document.getElementById('form-generar-pago');
        form.action = `/pagos-programados/${id}/generar-manual`;

        // Asignar el monto al campo del modal
        document.getElementById('monto').value = monto;

        toggleModal('generar-pago-modal'); // Aquí va tu función personalizada
    }
</script>
<script>
    $(document).ready(function() {
        $('#myTable-pagos-programados').DataTable({
            responsive: false, // Hacer la tabla responsive
            language: {
                url: "/js/datatables/lang/es-ES.json" // Textos en español
            }
        });
    });
</script>
