<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Empresas sin Pre-Liquidación para el Año ' . ($anio ?? '')) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <div class="bg-gray-100 p-4 rounded-lg shadow-md mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    {{ __('Filtrar empresas sin pre-liquidación por año') }}
                </h3>
                <form action="{{ route('empresas_clientes.filtrar_no_preliquidacion') }}" method="GET"
                    class="flex flex-wrap gap-4 items-center">

                    <div class="flex flex-col">
                        <select name="anio" id="anio" onchange="this.form.submit()" class="block w-full border-gray-300 rounded-md shadow-sm">
                            @for ($i = date('Y'); $i >= date('Y') - 10; $i--)
                                <option value="{{ $i }}" {{ $i == $anio ? 'selected' : '' }}>
                                    {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- <div class="flex items-end">
                        <button type="submit"
                            class="bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white shadow-md">
                            {{ __('Buscar') }}
                        </button>
                    </div> --}}
                </form>
            </div>

            <!-- Tabla de empresas sin pre-liquidación -->
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
                        @forelse ($empresas_sin_preliquidacion as $cliente)
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
                                    @php
                                        // Buscar preliquidación del año seleccionado
                                        $pre = $cliente->preliquidaciones->where('anio', $anio)->first();
                                    @endphp

                                    {{-- Si no existe preliquidación para este año --}}
                                    @if (!$pre)
                                        <a href="{{ route('pre-liquidacion.create', [
                                            'id_empresa' => $cliente->id_empresas_clientes,
                                            'anio' => $anio,
                                        ]) }}"
                                            class="flex items-center justify-center py-1 rounded-md tooltip"
                                            style="color: #10B981; transition: color 0.3s;"
                                            onmouseover="this.style.color='#059669';"
                                            onmouseout="this.style.color='#10B981';" title="Crear Pre Liquidación">
                                            <x-tax-icon />
                                        </a>

                                        {{-- Si existe preliquidación pero falta determinación-renta --}}
                                    @elseif (!$pre->determinacionRenta)
                                        <a href="{{ route('determinacion-renta.create', $pre->id_pre_liquidacion) }}"
                                            class="flex items-center justify-center py-1 rounded-md tooltip"
                                            style="color: #3B82F6; transition: color 0.3s;"
                                            onmouseover="this.style.color='#1D4ED8';"
                                            onmouseout="this.style.color='#3B82F6';"
                                            title="Crear Determinación de Renta">
                                            <x-tax-icon />
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    No hay empresas sin pre-liquidación para el año seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
