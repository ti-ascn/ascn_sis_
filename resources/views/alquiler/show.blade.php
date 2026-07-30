<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="bi bi-house-door me-2"></i>Detalle de Alquiler
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $alquiler->direccion->nombre }} - {{ $alquiler->direccion->distrito }}
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <form action="{{ route('alquiler.toggle', $alquiler->id_alquiler) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="focus:outline-none">
                        <span
                            class="cursor-pointer px-3 py-1 rounded-full text-sm font-medium flex items-center
                            {{ $alquiler->estado === 'activo'
                                ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">

                            <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>
                            {{ ucfirst($alquiler->estado) }}
                        </span>
                    </button>
                </form>
            </div>

        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 space-y-6">
            <x-success-error-bagdes />

            {{-- RESUMEN RÁPIDO --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-blue-100 mr-3 flex items-center justify-center">
                            <i class="bi bi-person text-blue-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Arrendador</p>
                            <p class="font-semibold">{{ $alquiler->nombre_arrendador }}</p>
                            <p class="text-xs text-gray-400">{{ $alquiler->dni_arrendador }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-green-100 mr-3 flex items-center justify-center">
                            <i class="bi bi-calendar-check text-green-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Período</p>
                            <p class="font-semibold">
                                {{ \Carbon\Carbon::parse($alquiler->inicio_contrato)->format('d/m/Y') }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($alquiler->fin_contrato)->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-purple-100 mr-3 flex items-center justify-center">
                            <i class="bi bi-cash-coin text-purple-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Pago Mensual</p>
                            <p class="font-semibold">S/ {{ number_format($alquiler->pago_mensual, 2) }}</p>
                            <p class="text-xs text-gray-400">Alquiler</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-orange-100 mr-3 flex items-center justify-center">
                            <i class="bi bi-wallet2 text-orange-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Pagos Totales</p>
                            <p class="font-semibold">{{ count($alquiler->pagos) }} registros</p>
                            <p class="text-xs text-gray-400">
                                Total: S/{{ number_format($alquiler->pagos->sum('monto_pago'), 2) }}<br />
                                5%: S/{{ number_format($alquiler->pagos->sum('monto_sunat'), 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- INFORMACIÓN DETALLADA EN PESTAÑAS --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="border-b">
                    <nav class="flex space-x-4 px-6 pt-4 z-0" aria-label="Tabs">
                        <button onclick="showTab('detalles')" id="tab-detalles"
                            class="tab-btn py-2 px-3 border-b-2 border-blue-500 text-blue-600 font-medium flex items-center">
                            <i class="bi bi-info-circle me-2"></i> Detalles
                        </button>
                        <button onclick="showTab('pagos')" id="tab-pagos"
                            class="tab-btn py-2 px-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium flex items-center">
                            <i class="bi bi-credit-card me-2"></i> Pagos
                        </button>
                        <button onclick="showTab('contratos')" id="tab-contratos"
                            class="tab-btn py-2 px-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium flex items-center">
                            <i class="bi bi-files me-2"></i> Contratos Históricos
                            @if (count($contratosHistoricos) > 1)
                                <span
                                    class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded-full">
                                    {{ count($contratosHistoricos) }}
                                </span>
                            @endif
                        </button>
                        <button onclick="showTab('direccion')" id="tab-direccion"
                            class="tab-btn py-2 px-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium flex items-center">
                            <i class="bi bi-geo-alt me-2"></i> Dirección
                        </button>
                    </nav>
                </div>

                {{-- PESTAÑA DETALLES --}}
                <div id="tab-content-detalles" class="tab-content p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="bi bi-file-text me-2"></i> Información del Contrato
                            </h4>
                            <dl class="space-y-3">
                                <div class="flex justify-between border-b pb-2">
                                    <dt class="text-gray-600">Estado del contrato:</dt>
                                    <dd>
                                        <span
                                            class="px-2 py-1 text-xs rounded {{ $alquiler->estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($alquiler->estado) }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <dt class="text-gray-600">Fecha Inicio:</dt>
                                    <dd class="font-medium">
                                        {{ \Carbon\Carbon::parse($alquiler->inicio_contrato)->format('d/m/Y') }}</dd>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <dt class="text-gray-600">Fecha Fin:</dt>
                                    <dd class="font-medium">
                                        {{ \Carbon\Carbon::parse($alquiler->fin_contrato)->format('d/m/Y') }}</dd>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <dt class="text-gray-600">Monto Mensual:</dt>
                                    <dd class="font-medium">S/ {{ number_format($alquiler->pago_mensual, 2) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="bi bi-person-badge me-2"></i> Información del Arrendador
                            </h4>
                            <dl class="space-y-3">
                                <div class="flex justify-between border-b pb-2">
                                    <dt class="text-gray-600">Nombre:</dt>
                                    <dd class="font-medium">{{ $alquiler->nombre_arrendador }}</dd>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <dt class="text-gray-600">DNI:</dt>
                                    <dd class="font-medium">{{ $alquiler->dni_arrendador }}</dd>
                                </div>

                                @if ($alquiler->fecha_desistimiento)
                                    <div class="flex justify-between border-b pb-2">
                                        <dt class="text-gray-600">Fecha Desistimiento:</dt>
                                        <dd class="font-medium text-red-600">
                                            {{ \Carbon\Carbon::parse($alquiler->fecha_desistimiento)->format('d/m/Y') }}
                                        </dd>
                                    </div>
                                @endif

                                @if ($alquiler->observacion)
                                    <div class="">
                                        <dt class="text-gray-600 mb-2">Observaciones:</dt>
                                        <dd class="bg-gray-50 p-3 rounded text-sm">{{ $alquiler->observacion }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- PESTAÑA PAGOS --}}
                <div id="tab-content-pagos" class="tab-content p-6 hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-semibold text-gray-700 flex items-center">
                            <i class="bi bi-credit-card-2-front me-2"></i> Historial de Pagos
                        </h4>
                        @if (count($mesesDisponibles) > 0)
                            <button type="button"
                                class="btn-open-pago bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center"
                                data-id-alquiler="{{ $alquiler->id_alquiler }}"
                                data-pago-mensual="{{ $alquiler->pago_mensual }}">
                                <i class="bi bi-plus-circle me-2"></i> Registrar Pago
                            </button>
                        @else
                            <button type="button"
                                class="bg-gray-400 text-white px-4 py-2 rounded flex items-center cursor-not-allowed"
                                disabled>
                                <i class="bi bi-check-circle me-2"></i> Todos los meses pagados
                            </button>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 border-b text-left">Periodo</th>
                                    <th class="px-4 py-3 border-b text-left">Monto</th>
                                    <th class="px-4 py-3 border-b text-left">SUNAT 5%</th>
                                    <th class="px-4 py-3 border-b text-left">Registrado por</th>
                                    <th class="px-4 py-3 border-b text-left">Estado</th>
                                    @if (auth()->user()->tipo_usuario == 1)
                                        <th class="px-4 py-3 border-b text-left">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($alquiler->pagos as $pago)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div>
                                                {{-- Periodo del contrato --}}
                                                <div class="flex items-center font-medium">
                                                    <i class="bi bi-calendar-check me-2 text-gray-400"></i>
                                                    {{ Str::ucfirst(\Carbon\Carbon::create($pago->anio, $pago->mes, 1)->translatedFormat('F Y')) }}
                                                </div>

                                                {{-- Fecha real de pago --}}
                                                <div class="text-xs text-gray-500 ml-6">
                                                    Pagado el
                                                    {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                                                </div>

                                                {{-- Comentario (si existe) --}}
                                                @if (!empty($pago->observacion))
                                                    <div
                                                        class="mt-1 ml-6 text-xs text-gray-600 italic flex items-start">
                                                        <i class="bi bi-chat-left-text me-1 mt-0.5 text-gray-400"></i>
                                                        {{ $pago->observacion }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 font-medium">
                                            S/ {{ number_format($pago->monto_pago, 2) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            S/ {{ number_format($pago->monto_sunat, 2) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <i class="bi bi-person-circle me-2 text-gray-400"></i>
                                                {{ $pago->usuario->name ?? '—' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                <i class="bi bi-check-circle me-1"></i> Pagado
                                            </span>
                                        </td>
                                        @if (auth()->user()->tipo_usuario == 1 || auth()->user()->tipo_usuario == 2)
                                            <td class="px-4 py-3">
                                                <form method="POST"
                                                    action="{{ route('pago_alquiler.destroy', $pago->id_pago_alquiler) }}"
                                                    onsubmit="return confirm('¿Eliminar este pago?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-red-600 hover:text-red-800 flex items-center">
                                                        <i class="bi bi-trash me-1"></i> Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-8">
                                            <div class="text-gray-400 mb-2">
                                                <i class="bi bi-credit-card text-4xl"></i>
                                            </div>
                                            <p class="text-gray-500">No hay pagos registrados</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if (count($alquiler->pagos) > 0)
                                <tfoot class="bg-gray-50 font-medium border-t">
                                    <tr>
                                        <td class="px-4 py-3 text-right">TOTAL:</td>
                                        <td class="px-4 py-3">
                                            S/ {{ number_format($alquiler->pagos->sum('monto_pago'), 2) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            S/ {{ number_format($alquiler->pagos->sum('monto_sunat'), 2) }}
                                        </td>
                                        <td class="px-4 py-3"></td>
                                        <td class="px-4 py-3"></td>
                                        <td class="px-4 py-3"></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- PESTAÑA CONTRATOS HISTÓRICOS --}}
                <div id="tab-content-contratos" class="tab-content p-6 hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="font-semibold text-gray-700 flex items-center">
                            <i class="bi bi-files me-2"></i> Contratos Históricos de la Dirección
                        </h4>
                        <div class="text-sm text-gray-500">
                            Mostrando {{ count($contratosHistoricos) }} contrato(s)
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach ($contratosHistoricos as $contrato)
                            <div
                                class="border rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200
                                {{ $contrato->id_alquiler == $alquiler->id_alquiler ? 'border-blue-300 bg-blue-50' : '' }}">
                                <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-lg font-semibold text-gray-700">
                                            Contrato #{{ $contratosHistoricos->count() - $loop->index }}
                                        </span>
                                        @if ($contrato->id_alquiler == $alquiler->id_alquiler)
                                            <span
                                                class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                                Actual
                                            </span>
                                        @endif
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full
                                            {{ $contrato->estado === 'activo'
                                                ? ($contrato->completado
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-blue-100 text-blue-800')
                                                : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($contrato->estado) }}
                                            @if ($contrato->estado === 'activo' && $contrato->completado)
                                                (Completado)
                                            @endif
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Creado: {{ \Carbon\Carbon::parse($contrato->created_at)->format('d/m/Y') }}
                                    </div>
                                </div>

                                <div class="p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <p class="text-sm text-gray-500 mb-1">Arrendador</p>
                                            <p class="font-medium">{{ $contrato->nombre_arrendador }}</p>
                                            <p class="text-xs text-gray-400">{{ $contrato->dni_arrendador }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500 mb-1">Periodo</p>
                                            <p class="font-medium">
                                                {{ \Carbon\Carbon::parse($contrato->inicio_contrato)->format('d/m/Y') }}
                                                -
                                                {{ \Carbon\Carbon::parse($contrato->fin_contrato)->format('d/m/Y') }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                Duración: {{ $contrato->total_meses }} meses
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500 mb-1">Pago Mensual</p>
                                            <p class="font-medium">S/ {{ number_format($contrato->pago_mensual, 2) }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                Total pagado: S/
                                                {{ number_format($contrato->pagos->sum('monto_pago'), 2) }} <br>
                                                SUNAT 5%: S/
                                                {{ number_format($contrato->pagos->sum('monto_sunat'), 2) }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Barra de progreso de pagos --}}
                                    <div class="mb-4">
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600">
                                                Progreso de pagos:
                                                {{ $contrato->pagos_count }}/{{ $contrato->total_meses }} meses
                                            </span>
                                            <span class="font-medium">{{ $contrato->porcentaje_pagado }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-green-600 h-2.5 rounded-full"
                                                style="width: {{ $contrato->porcentaje_pagado }}%"></div>
                                        </div>
                                    </div>

                                    {{-- Información adicional --}}
                                    <div class="flex flex-wrap gap-4 text-sm">
                                        <div class="flex items-center text-gray-600">
                                            <i class="bi bi-cash-coin me-2"></i>
                                            <span>Pagos: {{ $contrato->pagos_count }}</span>
                                        </div>
                                        @if ($contrato->fecha_desistimiento)
                                            <div class="flex items-center text-red-600">
                                                <i class="bi bi-calendar-x me-2"></i>
                                                <span>Desistido:
                                                    {{ \Carbon\Carbon::parse($contrato->fecha_desistimiento)->format('d/m/Y') }}</span>
                                            </div>
                                        @endif
                                        @if ($contrato->observacion)
                                            <div class="flex items-center text-gray-600">
                                                <i class="bi bi-chat-left-text me-2"></i>
                                                <span>Observación: {{ Str::limit($contrato->observacion, 50) }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Acciones --}}
                                    <div class="mt-4 pt-4 border-t flex justify-between items-center">
                                        <div class="text-xs text-gray-500">
                                            @if ($contrato->completado)
                                                <i class="bi bi-check-circle text-green-500 me-1"></i>
                                                Contrato completamente pagado
                                            @elseif($contrato->estado === 'inactivo')
                                                <i class="bi bi-x-circle text-red-500 me-1"></i>
                                                Contrato desistido
                                            @else
                                                <i class="bi bi-clock text-yellow-500 me-1"></i>
                                                {{ $contrato->total_meses - $contrato->pagos_count }} meses pendientes
                                            @endif
                                        </div>
                                        <div class="flex space-x-2">
                                            @if ($contrato->id_alquiler != $alquiler->id_alquiler)
                                                <a href="{{ route('alquiler.show', $contrato->id_alquiler) }}"
                                                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                                    <i class="bi bi-eye me-1"></i> Ver
                                                </a>
                                            @endif
                                            @if (auth()->user()->tipo_usuario == 1 && $contrato->pagos_count == 0)
                                                <form action="{{ route('alquiler.destroy', $contrato->id_alquiler) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('¿Eliminar este contrato?')"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-sm text-red-600 hover:text-red-800 flex items-center">
                                                        <i class="bi bi-trash me-1"></i> Eliminar
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if (count($contratosHistoricos) == 1)
                            <div class="text-center py-8 text-gray-500">
                                <i class="bi bi-files text-4xl mb-3"></i>
                                <p>Esta dirección solo tiene este contrato.</p>
                                <p class="text-sm">Los nuevos contratos aparecerán aquí.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Resumen estadístico --}}
                    @if (count($contratosHistoricos) > 1)
                        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                            <h5 class="font-medium text-gray-700 mb-3 flex items-center">
                                <i class="bi bi-graph-up me-2"></i> Resumen de Contratos
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-800">{{ count($contratosHistoricos) }}</p>
                                    <p class="text-sm text-gray-600">Contratos totales</p>
                                </div>
                                <div class="text-center">
                                    @php
                                        $activos = $contratosHistoricos->where('estado', 'activo')->count();
                                    @endphp
                                    <p class="text-2xl font-bold text-green-600">{{ $activos }}</p>
                                    <p class="text-sm text-gray-600">Contratos activos</p>
                                </div>
                                <div class="text-center">
                                    @php
                                        $completados = $contratosHistoricos->where('completado', true)->count();
                                    @endphp
                                    <p class="text-2xl font-bold text-blue-600">{{ $completados }}</p>
                                    <p class="text-sm text-gray-600">Contratos completados</p>
                                </div>
                                <div class="text-center">
                                    @php
                                        $totalPagado = 0;
                                        foreach ($contratosHistoricos as $contrato) {
                                            $totalPagado += $contrato->pagos->sum('monto_pago');
                                        }
                                    @endphp
                                    <p class="text-2xl font-bold text-emerald-600">S/
                                        {{ number_format($totalPagado, 0) }}</p>
                                    <p class="text-sm text-gray-600">Total pagado</p>
                                </div>
                                <div class="text-center">
                                    @php
                                        $totalSunat = 0;
                                        foreach ($contratosHistoricos as $contrato) {
                                            $totalSunat += $contrato->pagos->sum('monto_sunat');
                                        }
                                    @endphp
                                    <p class="text-2xl font-bold text-sky-600">S/ {{ number_format($totalSunat, 0) }}
                                    </p>
                                    <p class="text-sm text-gray-600">Total SUNAT (5%)</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- PESTAÑA DIRECCIÓN --}}
                <div id="tab-content-direccion" class="tab-content p-6 hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="bi bi-house me-2"></i> Información de la Propiedad
                            </h4>
                            <dl class="space-y-3">
                                <div class="flex justify-between border-b pb-2">
                                    <dt class="text-gray-600">Nombre:</dt>
                                    <dd class="font-medium">{{ $alquiler->direccion->nombre }}</dd>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <dt class="text-gray-600">Tipo:</dt>
                                    <dd class="font-medium">{{ $alquiler->direccion->tipo_domicilio }}</dd>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <dt class="text-gray-600">Estado:</dt>
                                    <dd>
                                        <span
                                            class="px-2 py-1 text-xs rounded {{ $alquiler->direccion->estado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i>
                                            {{ $alquiler->direccion->estado ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="bi bi-geo me-2"></i> Ubicación
                            </h4>
                            <dl class="space-y-3">
                                <div class="flex justify-between border-b pb-2">
                                    <dt class="text-gray-600">Distrito:</dt>
                                    <dd class="font-medium">{{ $alquiler->direccion->distrito }}</dd>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <dt class="text-gray-600">Provincia:</dt>
                                    <dd class="font-medium">{{ $alquiler->direccion->provincia }}</dd>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <dt class="text-gray-600">Región:</dt>
                                    <dd class="font-medium">{{ $alquiler->direccion->region }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACCIONES --}}
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-wrap gap-3">
                    @if (count($mesesDisponibles) > 0)
                        <button type="button"
                            class="btn-open-pago bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center"
                            data-id-alquiler="{{ $alquiler->id_alquiler }}"
                            data-pago-mensual="{{ $alquiler->pago_mensual }}">
                            <i class="bi bi-plus-circle me-2"></i> Registrar Pago
                        </button>
                    @else
                        <button type="button"
                            class="bg-gray-400 text-white px-4 py-2 rounded flex items-center cursor-not-allowed"
                            disabled>
                            <i class="bi bi-check-circle me-2"></i> Todos los meses pagados
                        </button>
                    @endif

                    {{-- Verificar si todos los meses están pagados antes de permitir editar el alquiler --}}
                    @if (!$todosPagados && $alquiler->estado === 'activo')
                        @if (Auth::user()->tipo_usuario == 1)
                            <button
                                class="btn-editar-alquiler bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 flex items-center"
                                data-id="{{ $alquiler->id_alquiler }}">
                                <i class="bi bi-pencil-square me-2"></i> Editar alquiler
                            </button>
                        @endif
                    @else
                        <button class="bg-gray-400 text-white px-4 py-2 rounded flex items-center cursor-not-allowed"
                            disabled>
                            <i class="bi bi-pencil-square me-2"></i> Contrato completado
                        </button>
                    @endif

                    {{-- Desistir solo si el contrato está activo y no todos están pagados --}}
                    @if ($alquiler->estado === 'activo' && !$todosPagados)
                        <form method="POST" action="{{ route('alquiler.desistir', $alquiler->id_alquiler) }}"
                            onsubmit="return confirm('¿Seguro que deseas desistir este alquiler?')">
                            @csrf
                            @method('PATCH')
                            <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 flex items-center">
                                <i class="bi bi-x-circle me-2"></i> Desistir alquiler
                            </button>
                        </form>
                    @endif

                    {{-- Botón para nuevo contrato --}}
                    @if ($puedeCrearNuevoContrato)
                        <button type="button"
                            class="btn-nuevo-contrato bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 flex items-center"
                            data-id-direccion="{{ $alquiler->direccion->id_direccion }}"
                            data-direccion-nombre="{{ $alquiler->direccion->nombre }}"
                            data-tipo="{{ $alquiler->direccion->tipo_domicilio }}" title="{{ $razon }}">
                            <i class="bi bi-file-earmark-plus me-2"></i> Nuevo Contrato
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .tab-btn {
            transition: all 0.2s ease;
        }

        .tab-btn:hover {
            border-bottom-color: #d1d5db;
        }

        .tab-content {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</x-app-layout>

<x-modal-show id="modalEditarAlquiler" title="Editar alquiler">

    <form method="POST" action="{{ route('alquiler.update', $alquiler->id_alquiler) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Arrendador --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label value="Nombre arrendador" />
                <x-text-input name="nombre_arrendador" value="{{ $alquiler->nombre_arrendador }}"
                    class="mt-1 block w-full" required />
            </div>

            <div>
                <x-input-label value="DNI arrendador" />
                <x-text-input name="dni_arrendador" value="{{ $alquiler->dni_arrendador }}"
                    class="mt-1 block w-full" required />
            </div>
        </div>

        {{-- Fechas --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label value="Inicio contrato" />
                <input type="date" name="inicio_contrato" value="{{ $alquiler->inicio_contrato }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div>
                <x-input-label value="Fin contrato" />
                <input type="date" name="fin_contrato" value="{{ $alquiler->fin_contrato }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>
        </div>

        {{-- Pago --}}
        <div>
            <x-input-label value="Pago mensual (S/)" />
            <input type="number" step="0.01" name="pago_mensual" value="{{ $alquiler->pago_mensual }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
        </div>

        {{-- Observación --}}
        <div>
            <x-input-label value="Observación" />
            <textarea name="observacion" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ $alquiler->observacion }}</textarea>
        </div>

        {{-- Acciones --}}
        <div class="flex justify-end gap-3 pt-4">
            <button type="button" onclick="toggleModal('modalEditarAlquiler')"
                class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">
                Cancelar
            </button>

            <x-primary-button>
                <i class="bi bi-save me-2"></i> Guardar cambios
            </x-primary-button>
        </div>

    </form>
</x-modal-show>
<script>
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-editar-alquiler');
        if (!btn) return;

        toggleModal('modalEditarAlquiler');
    });
</script>


{{-- MODAL PARA NUEVO CONTRATO --}}
<x-modal-show id="modalNuevoContrato" title="Crear Nuevo Contrato">

    <form method="POST" action="{{ route('alquiler.store') }}" class="space-y-6">
        @csrf

        {{-- ID DIRECCIÓN --}}
        <input type="hidden" name="id_direccion" id="nuevo-id-direccion">

        {{-- ID del alquiler anterior (para referencia) --}}
        <input type="hidden" name="id_alquiler_anterior" value="{{ $alquiler->id_alquiler }}">

        {{-- Dirección (solo lectura) --}}
        <div>
            <x-input-label value="Dirección" />
            <x-text-input id="nuevo-direccion-nombre" type="text" class="mt-1 block w-full" disabled />
        </div>

        {{-- Información del contrato anterior --}}
        <div class="bg-gray-50 p-4 rounded-md mb-4">
            <p class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                <i class="bi bi-info-circle me-2"></i> Información del contrato anterior
            </p>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                    <span class="text-gray-500">Arrendador:</span>
                    <span class="font-medium">{{ $alquiler->nombre_arrendador }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Periodo:</span>
                    <span class="font-medium">
                        {{ \Carbon\Carbon::parse($alquiler->inicio_contrato)->format('d/m/Y') }} -
                        {{ \Carbon\Carbon::parse($alquiler->fin_contrato)->format('d/m/Y') }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-500">Monto anterior:</span>
                    <span class="font-medium">S/ {{ number_format($alquiler->pago_mensual, 2) }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Estado:</span>
                    <span class="font-medium">{{ ucfirst($alquiler->estado) }}</span>
                </div>
            </div>
        </div>

        {{-- Arrendador --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label value="Nombre arrendador" />
                <x-text-input name="nombre_arrendador" class="mt-1 block w-full"
                    value="{{ $alquiler->nombre_arrendador }}" required />
            </div>

            <div>
                <x-input-label value="DNI arrendador" />
                <x-text-input name="dni_arrendador" class="mt-1 block w-full"
                    value="{{ $alquiler->dni_arrendador }}" required />
            </div>
        </div>

        {{-- Fechas --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label value="Inicio contrato" />
                @php
                    $sugeridoInicio = now()->addDay()->format('Y-m-d');
                    if ($alquiler->fin_contrato && now()->lt(\Carbon\Carbon::parse($alquiler->fin_contrato))) {
                        $sugeridoInicio = \Carbon\Carbon::parse($alquiler->fin_contrato)->addDay()->format('Y-m-d');
                    }
                @endphp
                <input type="date" name="inicio_contrato" value="{{ $sugeridoInicio }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div>
                <x-input-label value="Fin contrato" />
                @php
                    // Sugerir fin de contrato (1 año después del inicio sugerido)
                    $sugeridoFin = \Carbon\Carbon::parse($sugeridoInicio)->addYear()->subDay()->format('Y-m-d');
                @endphp
                <input type="date" name="fin_contrato" value="{{ $sugeridoFin }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>
        </div>

        {{-- Pago --}}
        <div>
            <x-input-label value="Pago mensual (S/)" />
            <input type="number" step="0.01" name="pago_mensual" value="{{ $alquiler->pago_mensual }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
        </div>

        {{-- Observación --}}
        <div>
            <x-input-label value="Observación" />
            <textarea name="observacion" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                placeholder="Nuevo contrato después del anterior"></textarea>
        </div>

        {{-- ACCIONES --}}
        <div class="flex justify-end pt-4 space-x-3">
            <button type="button" onclick="toggleModal('modalNuevoContrato')"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancelar
            </button>
            <x-primary-button class="px-6 py-2">
                <i class="bi bi-save me-2"></i> Crear Nuevo Contrato
            </x-primary-button>
        </div>

    </form>

</x-modal-show>

{{-- Script para nuevo contrato --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar clic en botón de nuevo contrato
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-nuevo-contrato');
            if (!btn) return;

            // Obtener datos del botón
            const idDireccion = btn.dataset.idDireccion;
            const nombreDireccion = btn.dataset.direccionNombre;
            const tipoDireccion = btn.dataset.tipo;

            // Establecer valores en el modal
            document.getElementById('nuevo-id-direccion').value = idDireccion;
            document.getElementById('nuevo-direccion-nombre').value =
                `${nombreDireccion} (${tipoDireccion})`;

            // Abrir modal
            toggleModal('modalNuevoContrato');
        });

        // Calcular automáticamente fecha fin al cambiar fecha inicio
        const inicioInput = document.querySelector('input[name="inicio_contrato"]');
        const finInput = document.querySelector('input[name="fin_contrato"]');

        if (inicioInput && finInput) {
            inicioInput.addEventListener('change', function() {
                const fechaInicio = new Date(this.value);
                if (!isNaN(fechaInicio.getTime())) {
                    const fechaFin = new Date(fechaInicio);
                    fechaFin.setFullYear(fechaFin.getFullYear() + 1);
                    fechaFin.setDate(fechaFin.getDate() - 1);

                    // Formatear a YYYY-MM-DD
                    const year = fechaFin.getFullYear();
                    const month = String(fechaFin.getMonth() + 1).padStart(2, '0');
                    const day = String(fechaFin.getDate()).padStart(2, '0');

                    finInput.value = `${year}-${month}-${day}`;
                }
            });
        }
    });
</script>

<script>
    function showTab(tabName) {
        // Ocultar todos los contenidos de pestañas
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });

        // Mostrar la pestaña seleccionada
        document.getElementById('tab-content-' + tabName).classList.remove('hidden');

        // Actualizar estilos de los botones de pestañas
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });

        document.getElementById('tab-' + tabName).classList.add('border-blue-500', 'text-blue-600');
        document.getElementById('tab-' + tabName).classList.remove('border-transparent', 'text-gray-500');
    }

    // Mostrar la primera pestaña por defecto
    document.addEventListener('DOMContentLoaded', function() {
        showTab('pagos');

        // Si hay parámetro en la URL para mostrar contratos
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'contratos') {
            showTab('contratos');
        }
    });
</script>

<x-modal-show id="modalPagoAlquiler" title="Registrar pago de alquiler">

    <form method="POST" action="{{ route('pago_alquiler.store') }}" class="space-y-6">
        @csrf

        {{-- IDs --}}
        <input type="hidden" name="id_alquiler" id="pa-id-alquiler">
        <input type="hidden" name="mes" id="pa-mes">
        <input type="hidden" name="anio" id="pa-anio">

        {{-- Mes / Año --}}
        <div>
            <x-input-label value="Periodo a pagar" />
            <select name="periodo" id="pa-periodo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                required>
                @forelse($mesesDisponibles as $m)
                    <option value="{{ $m['mes'] }}|{{ $m['anio'] }}">
                        {{ $m['label'] }}
                    </option>
                @empty
                    <option disabled selected>No hay meses pendientes</option>
                @endforelse
            </select>
        </div>

        {{-- Fecha pago --}}
        <div>
            <x-input-label value="Fecha de pago" />
            <input type="date" name="fecha_pago" value="{{ now()->format('Y-m-d') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
        </div>

        {{-- Monto --}}
        <div>
            <x-input-label value="Monto pagado" />
            <input type="number" step="0.01" name="monto_pago" id="pa-monto"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
        </div>

        {{-- SUNAT --}}
        <div class="text-sm text-gray-600">
            SUNAT (5%): <strong>S/ <span id="pa-sunat">0.00</span></strong>
        </div>

        {{-- Observación --}}
        <div>
            <x-input-label value="Observación" />
            <input type="text" name="observacion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                placeholder="Escribe una observación (opcional)">
        </div>


        {{-- Acciones --}}
        <div class="flex justify-end pt-4">
            <x-primary-button>
                Registrar pago
            </x-primary-button>
        </div>

    </form>
</x-modal-show>
<script>
    document.addEventListener('click', function(e) {

        const selectPeriodo = document.getElementById('pa-periodo');
        if (selectPeriodo) {
            selectPeriodo.addEventListener('change', function() {
                const [mes, anio] = this.value.split('|');
                document.getElementById('pa-mes').value = mes;
                document.getElementById('pa-anio').value = anio;
            });

            // Set inicial
            if (selectPeriodo.value) {
                const [mes, anio] = selectPeriodo.value.split('|');
                document.getElementById('pa-mes').value = mes;
                document.getElementById('pa-anio').value = anio;
            }
        }

        const btn = e.target.closest('.btn-open-pago');
        if (!btn) return;

        document.getElementById('pa-id-alquiler').value = btn.dataset.idAlquiler;

        // Prellenar monto con el pago mensual
        const montoInput = document.getElementById('pa-monto');
        montoInput.value = btn.dataset.pagoMensual;

        // Calcular SUNAT
        const sunatSpan = document.getElementById('pa-sunat');
        sunatSpan.textContent = (btn.dataset.pagoMensual * 0.05).toFixed(2);

        toggleModal('modalPagoAlquiler');
    });

    // Recalcular SUNAT al cambiar monto
    document.getElementById('pa-monto')?.addEventListener('input', function() {
        document.getElementById('pa-sunat').textContent =
            (this.value * 0.05).toFixed(2);
    });
</script>
