<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Facturas emitidas a la empresa: {{ $empresa->razon_social }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col mb-4">
                <a href="{{ route('facturas.mes_actual') }}"
                    class="flex items-center gap-2 bg-indigo-500 px-4 py-2 rounded-md text-white hover:bg-indigo-600 transition">
                    <i class="bi bi-calendar2-week"></i> Facturas del Mes
                </a>
            </div>
            <div class="bg-white shadow-md rounded-lg overflow-x-auto">
                <table class="min-w-full table-auto border" id="myTable">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b">#</th>
                            <th class="px-4 py-2 border-b">Nro Orden</th>
                            <th class="px-4 py-2 border-b">Fecha Emisión</th>
                            <th class="px-4 py-2 border-b">Emitido Por</th>
                            <th class="px-4 py-2 border-b">Total Neto</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($facturas as $factura)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $factura->id_factura }}</td>
                                <td class="px-4 py-2 border-b">{{ $factura->nombre_correlativo ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 border-b">
                                    <span class="font-semibold">
                                        {{ $empresas[$factura->ruc_emisor]->razon_social ?? '—' }}
                                    </span><br>
                                    <span class="text-sm text-gray-600">
                                        RUC: {{ $factura->ruc_emisor }}
                                    </span>
                                </td>
                                @php
                                    $esDolar = $factura->tipo_moneda === 'USD';
                                    $montoNeto = $esDolar
                                        ? $factura->total_neto / $factura->precio_dolar
                                        : $factura->total_neto;

                                    $simbolo = $esDolar ? '$' : 'S/';
                                @endphp
                                <td class="px-4 py-2 border-b text-right">
                                    {{ $simbolo }} {{ number_format($montoNeto, 2) }}
                                </td>

                                <td class="px-4 py-2 border-b">
                                    @if ($factura->estado === 'Facturada')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                            Facturada
                                        </span>
                                    @elseif ($factura->estado === 'Pendiente')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">
                                            Pendiente
                                        </span>
                                    @elseif($factura->estado === 'Anulada')
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                            Anulada
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b text-center">
                                    <div class="flex justify-center space-x-2">
                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                            {{-- Botón Editar --}}
                                            <a href="{{ route('factura.edit', $factura->id_factura) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #F59E0B; transition: color 0.3s;"
                                                onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </a>
                                        @endif
                                        {{-- Botón Detalles --}}
                                        <a href="{{ route('factura.detalle', $factura->id_factura) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #6B7280; transition: color 0.3s;"
                                            onmouseover="this.style.color='#4B5563';"
                                            onmouseout="this.style.color='#6B7280';" target="_blank">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon />
                                        </a>

                                        {{-- Dropdown Cambiar estado --}}
                                        <div class="relative">
                                            <x-dropdown align="right" width="48">
                                                <x-slot name="trigger">
                                                    <button class="flex items-center py-1 rounded-md tooltip"
                                                        style="color: #6B7280; transition: color 0.3s;"
                                                        onmouseover="this.style.color='#4B5563';"
                                                        onmouseout="this.style.color='#6B7280';">
                                                        <x-dots-vertical-icon />
                                                    </button>
                                                </x-slot>

                                                <x-slot name="content">
                                                    <div
                                                        class="absolute right-0 z-50 w-48 bg-white shadow-lg rounded-md border p-2">
                                                        @foreach (['Facturada', 'Pendiente', 'Anulada'] as $estado)
                                                            @if ($factura->estado !== $estado)
                                                                <form method="POST"
                                                                    action="{{ route('factura.actualizar_estado', $factura->id_factura) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="estado"
                                                                        value="{{ $estado }}">
                                                                    <x-dropdown-link href="#"
                                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                                        @if ($estado === 'Facturada')
                                                                            <i
                                                                                class="bi bi-check-circle-fill text-green-500 mr-2"></i>
                                                                        @elseif ($estado === 'Pendiente')
                                                                            <i
                                                                                class="bi bi-hourglass-split text-yellow-500 mr-2"></i>
                                                                        @elseif ($estado === 'Anulada')
                                                                            <i
                                                                                class="bi bi-x-circle-fill text-red-500 mr-2"></i>
                                                                        @endif
                                                                        Marcar como {{ $estado }}
                                                                    </x-dropdown-link>
                                                                </form>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </x-slot>
                                            </x-dropdown>
                                        </div>
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
