<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Alquileres</h2>
    </x-slot>

    <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">

        <a href="{{ route('direcciones.index') }}"
            class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md text-white hover:bg-blue-600 transition duration-300 w-full md:w-auto">
            <i class="bi bi-geo-alt-fill"></i> Direcciones
        </a>

        <button
            class="btn-resumen-empresas bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 flex items-center">
            <i class="bi bi-bar-chart me-2"></i> Resumen por empresa
        </button>
    </div>

    <br>

    <div class="py-6 px-4 bg-white shadow rounded">
        <table class="min-w-full" id="myTable">
            <thead class="bg-gray-100">
                <tr>
                    <th>Empresa</th>
                    <th>Dirección</th>
                    <th>Tipo</th>
                    <th>Arrendador</th>
                    <th>Periodo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($alquileres as $a)
                    <tr class="border-b">
                        <td class="font-medium">
                            {{ $a->direccion->empresa->razon_social ?? '—' }}
                        </td>
                        <td>{{ $a->direccion->nombre }}</td>
                        <td>{{ $a->direccion->tipo_domicilio }}</td>
                        <td>{{ $a->nombre_arrendador }}</td>
                        <td class="text-sm">
                            <div>{{ \Carbon\Carbon::parse($a->inicio_contrato)->format('d/m/Y') }}</div>
                            <div class="text-gray-500">
                                {{ \Carbon\Carbon::parse($a->fin_contrato)->format('d/m/Y') }}
                            </div>
                        </td>
                        <td>
                            <span
                                class="px-2 py-1 text-xs rounded
        {{ $a->estado == 'activo' ? 'bg-green-500 text-white' : 'bg-yellow-500 text-white' }}">
                                {{ $a->estado == 'activo' ? 'Activo' : 'Último contrato' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 border-b">
                            <div class="flex space-x-2">
                                <a href="{{ route('alquiler.show', $a->id_alquiler) }}?tab=contratos"
                                    class="flex items-center py-1 rounded-md tooltip"
                                    style="color: #4F46E5; transition: color 0.3s;"
                                    onmouseover="this.style.color='#3730A3';" onmouseout="this.style.color='#4F46E5';">
                                    <span class="tooltip-text">Ver historial</span>
                                    <x-factura-icon />
                                </a>

                                <button class="btn-editar-alquiler flex items-center py-1 rounded-md tooltip"
                                    data-id="{{ $a->id_alquiler }}" style="color: #F59E0B; transition: color 0.3s;"
                                    onmouseover="this.style.color='#D97706';" onmouseout="this.style.color='#F59E0B';">
                                    <span class="tooltip-text">Editar</span>
                                    <x-edit-icon />
                                </button>
                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
<x-modal-show id="modalResumenEmpresas" title="Resumen de pagos por empresa">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">Empresa</th>
                    <th class="px-4 py-2 text-right">Total Pagado (S/)</th>
                    <th class="px-4 py-2 text-right">Total SUNAT (S/)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($resumenEmpresas as $e)
                    <tr class="border-b">
                        <td class="px-4 py-2 font-medium">
                            {{ $e->razon_social }}
                        </td>
                        <td class="px-4 py-2 text-right">
                            S/ {{ number_format($e->total_pagado, 2) }}
                        </td>
                        <td class="px-4 py-2 text-right text-red-600">
                            S/ {{ number_format($e->total_sunat, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-6 text-gray-500">
                            No hay pagos registrados
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-modal-show>
<script>
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-resumen-empresas')) {
            toggleModal('modalResumenEmpresas');
        }
    });
</script>
