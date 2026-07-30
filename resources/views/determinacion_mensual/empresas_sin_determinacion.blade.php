<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Empresas sin Determinación Mensual en el Sistema') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <!-- Tabla de empresas sin determinación mensual -->
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg mt-4">
                <table class="min-w-full table-auto" id="myTable">
                    <thead>
                        <tr class="bg-gray-100">
                            <th>UD</th>
                            <th>Razón Social</th>
                            <th>RUC</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($empresasSinDeterminacion as $cliente)
                            <tr>
                                <td>{{ substr($cliente->ruc, -1) }}</td>
                                <td>{{ $cliente->razon_social }}</td>
                                <td>{{ $cliente->ruc }}</td>
                                <td>
                                    @if ($cliente->estado_actual == 1)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                            Activo
                                        </span>
                                    @elseif($cliente->estado_actual == 2)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">
                                            Suspendido
                                        </span>
                                    @elseif($cliente->estado_actual == 0)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                            Retirado
                                        </span>
                                    @else
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-gray-500 rounded-full w-24">
                                            Sin estado
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('determinacion_mensual.create', ['id_empresa' => $cliente->id_empresas_clientes, 'mes' => 1, 'ano' => now()->year]) }}"
                                        class="flex items-center py-1 rounded-md tooltip"
                                        style="color: #10B981; transition: color 0.3s;"
                                        onmouseover="this.style.color='#059669';"
                                        onmouseout="this.style.color='#10B981';">
                                        <span class="tooltip-text">+Determinación Mensual</span>
                                        <x-tax-icon />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    No hay empresas sin determinación mensual.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
