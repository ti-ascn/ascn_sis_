<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel de Recepción – Empresas
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-screen-2xl mx-auto px-4">
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">

                {{-- Recepción Documentos --}}
                <a href="{{ route('recepcion.documentos') }}"
                    class="flex items-center gap-2 bg-blue-600 px-4 py-2 rounded-md hover:bg-blue-700 transition duration-300 text-white text-center w-full md:w-auto shadow-sm">
                    <i class="bi bi-inbox"></i>
                    Recepción de Documentos
                </a>

                {{-- Cotizaciones --}}
                <a href="{{ route('deudas_recepcion.index') }}"
                    class="flex items-center gap-2 bg-yellow-500 px-4 py-2 rounded-md hover:bg-yellow-600 transition duration-300 text-white text-center w-full md:w-auto shadow-sm">
                    <i class="bi bi-receipt"></i>
                    Cotizaciones
                </a>

                {{-- Cargos --}}
                <a href="{{ route('cargos.index') }}"
                    class="flex items-center gap-2 bg-green-600 px-4 py-2 rounded-md hover:bg-green-700 transition duration-300 text-white text-center w-full md:w-auto shadow-sm">
                    <i class="bi bi-file-earmark-check"></i>
                    Cargos
                </a>

            </div>

            <br />
            <div class="bg-white p-6 shadow-md rounded-lg">

                <table class="table-auto w-full text-left border-collapse" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 uppercase text-sm">
                            <th class="px-4 py-2">Empresa</th>
                            <th class="px-4 py-2">Estado</th>
                            <th class="px-4 py-2">Fecha Ingreso</th>
                            <th class="px-4 py-2">Fecha Retiro</th>
                            <th class="px-4 py-2">Último movimiento</th>
                            <th class="px-4 py-2">Cotizaciones</th>
                            <th class="px-4 py-2">Cargos</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($empresas as $empresa)
                            @php
                                $estado = strtolower($empresa->ultRecepcion->estado ?? '');
                                $color = match ($estado) {
                                    'solicitud' => 'text-blue-600 hover:text-blue-800',
                                    'recepcion' => 'text-yellow-600 hover:text-yellow-800',
                                    'entrega' => 'text-green-600 hover:text-green-800',
                                    default => 'text-gray-600',
                                };
                            @endphp

                            <tr class="border-b hover:bg-gray-50">

                                {{-- Empresa --}}
                                <td class="px-4 py-3 font-semibold">
                                    {{ $empresa->razon_social }} <br>
                                    <span class="text-gray-500">{{ $empresa->ruc }}</span>
                                </td>

                                <td class="px-4 py-2 border-b">
                                    @if ($empresa->estado_actual == 1)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                            Activo
                                        </span>
                                    @elseif($empresa->estado_actual == 2)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">
                                            Suspendido
                                        </span>
                                    @elseif($empresa->estado_actual == 0)
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
                                <td class="px-4 py-3">
                                    {{ $empresa->fecha_ingreso ? \Carbon\Carbon::parse($empresa->fecha_ingreso)->format('d/m/Y') : 'Vacío' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $empresa->fecha_retiro ? \Carbon\Carbon::parse($empresa->fecha_retiro)->format('d/m/Y') : '—' }}
                                </td>
                                {{-- Último movimiento con LINK --}}
                                <td class="px-4 py-3">
                                    @if ($empresa->ultRecepcion)
                                        <a href="{{ route('recepcion.documentos.registros_mes_actual', $empresa->id_empresas_clientes) }}"
                                            target="_blank"
                                            class="flex items-center gap-1 font-bold hover:underline {{ $color }}">
                                            {{ ucfirst($empresa->ultRecepcion->estado) }}
                                        </a>

                                        <span class="text-sm text-gray-600">
                                            {{ $empresa->ultRecepcion->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Sin registros</span>
                                    @endif
                                </td>

                                {{-- Cotizaciones con LINKS según tipo --}}
                                <td class="px-4 py-3">
                                    @forelse ($empresa->cotizaciones as $cot)
                                        <a href="{{ route('deudas_recepcion.show', ['id' => $empresa->id_empresas_clientes, 'tipo' => $cot->tipo]) }}"
                                            target="_blank"
                                            class="flex items-center gap-1 text-yellow-600 hover:text-yellow-800 text-sm hover:underline">
                                            COT–{{ $cot->nro_cotizacion }}
                                            <span class="text-gray-500">({{ $cot->created_at->format('d/m') }})</span>
                                        </a>
                                    @empty
                                        <span class="text-gray-400">–</span>
                                    @endforelse
                                </td>

                                <td class="px-4 py-3">
                                    @forelse($empresa->cargos as $car)
                                        <div class="text-sm">
                                            <span class="text-gray-800">CARGO–{{ $car->nro_cargo }}</span>
                                            <span class="text-gray-500">({{ $car->created_at->format('d/m') }})</span>
                                        </div>
                                    @empty
                                        <span class="text-gray-400">–</span>
                                    @endforelse
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                </table>

            </div>
        </div>
    </div>

</x-app-layout>
