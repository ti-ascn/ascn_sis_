<x-app-layout>
    @php
        $meses_es = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre',
        ];
        $nombreMesActual = $meses_es[$mes] ?? '—';
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Guías de ' . $nombreMesActual) }}
        </h2>
    </x-slot>

    <div class="py-12">
        {{-- !acomodando la vista para que ocupe todo el ancho --}}
        <div class="w-full px-4">

            <x-success-error-bagdes />


            <div class="bg-gray-100 p-4 rounded-lg shadow-md mb-6 flex flex-wrap items-end gap-4">

                {{-- Botón Crear Guía (fuera del form) --}}
                <div class="relative w-full md:w-auto">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center justify-center gap-2 bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition w-full md:w-auto">
                                <i class="bi bi-file-earmark-plus-fill"></i> Crear Guía <i
                                    class="bi bi-chevron-down text-sm"></i>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="p-2">
                                <x-dropdown-link :href="route('guias.create_empresa')">
                                    <i class="bi bi-building me-2"></i> Empresa
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('guias.create_cliente')">
                                    <i class="bi bi-person me-2"></i> Cliente
                                </x-dropdown-link>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                {{-- Botón Ver Todas las Guías --}}
                <div class="relative w-full md:w-auto">
                    <a href="{{ route('guias.all') }}"
                        class="flex items-center justify-center gap-2 bg-indigo-500 px-4 py-2 rounded-md hover:bg-indigo-600 transition duration-300 text-white w-full md:w-auto">
                        <i class="bi bi-archive-fill"></i> Todas las Guías
                    </a>
                </div>

                {{-- Botón Exportar Guías Excel --}}
                <div class="w-full md:w-auto">
                    <button onclick="toggleModal('modalExportarGuias')"
                        class="flex items-center justify-center gap-2 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition w-full md:w-auto">
                        <i class="bi bi-file-earmark-excel"></i> Exportar
                    </button>
                </div>

                {{-- Formulario de filtros --}}
                <form method="GET" action="{{ route('guias.index') }}" class="flex flex-wrap items-end gap-4">

                    {{-- Filtro Mes --}}
                    <div class="flex flex-col">
                        <label for="mes" class="text-sm font-semibold text-gray-700 mb-1">Mes</label>
                        <select name="mes" id="mes" onchange="this.form.submit()"
                            class="border-gray-300 rounded-md shadow-sm w-32 focus:ring-blue-500 focus:border-blue-500">
                            @foreach ($meses_es as $numero => $nombre)
                                <option value="{{ $numero }}" {{ $mes == $numero ? 'selected' : '' }}>
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro Año --}}
                    <div class="flex flex-col">
                        <label for="anio" class="text-sm font-semibold text-gray-700 mb-1">Año</label>
                        <select name="anio" id="anio" onchange="this.form.submit()"
                            class="border-gray-300 rounded-md shadow-sm w-32 focus:ring-blue-500 focus:border-blue-500">
                            @for ($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}" {{ $anio == $year ? 'selected' : '' }}>
                                    {{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Botón Buscar --}}
                    {{-- <div class="w-full md:w-auto">
                        <button type="submit"
                            class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 
                        text-white font-semibold px-4 py-2 rounded-md transition w-full md:w-auto">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div> --}}
                </form>

                {{-- Botón Resumen Facturación --}}
                <div class="w-full md:w-auto">
                    <button onclick="toggleModal('modalMontos')"
                        class="flex items-center justify-center gap-2 bg-gray-500 hover:bg-gray-600 
                        text-white px-4 py-2 rounded-md transition w-full md:w-auto">
                        <i class="bi bi-cash-coin"></i> Resumen Facturación
                    </button>
                </div>
                <a href="{{ route('contratos.index') }}"
                    class="flex items-center justify-center gap-2 bg-indigo-500 px-4 py-2 rounded-md 
                        hover:bg-indigo-600 transition duration-300 text-white w-full md:w-auto">
                    <i class="bi bi-diagram-3"></i> Contratos
                </a>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                {{-- !acomodando la tabla para que se muestre bien en celular de full a [1200] --}}
                <table class="min-w-[1200px] table-auto display nowrap responsive nowrap" id="myTable-guias">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Acciones</th>
                            <th class="px-4 py-2 border-b">Contacto</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">Razón Social</th>
                            <th class="px-4 py-2 border-b">Monto</th>
                            <th>Monto S/</th>
                            <th class="px-4 py-2 border-b">Recepción</th>
                            <th class="px-4 py-2 border-b">Programado</th>
                            <th class="px-4 py-2 border-b">Emitido</th>
                            <th class="px-4 py-2 border-b">GRR</th>
                            <th class="px-4 py-2 border-b">GRT</th>
                            <th class="px-4 py-2 border-b">Bancarización</th>
                            <th class="px-4 py-2 border-b">Contingencia</th>
                            {{-- <th class="px-4 py-2 border-b">Entregado</th> --}}
                            {{-- <th class="px-4 py-2 border-b">Observaciones</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($guias as $guia)
                            <tr class="hover:bg-gray-50">
                                {{-- <td class="px-4 py-2 border-b">
                                    @if ($guia->empresa)
                                        {{ $guia->empresa->razon_social }}
                                    @elseif ($guia->cliente)
                                        {{ $guia->cliente->razon_social }}
                                    @endif
                                </td> --}}
                                {{-- COLUMNA MONTO --}}
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        <a href="javascript:void(0)"
                                            class="flex items-center py-1 rounded-md tooltip btn-detalles"
                                            style="color: #6B7280; transition: color 0.3s;"
                                            onmouseover="this.style.color='#4B5563';"
                                            onmouseout="this.style.color='#6B7280';" data-id="{{ $guia->id_guia }}"
                                            data-razon="{{ $guia->empresa->razon_social ?? ($guia->cliente->razon_social ?? 'VACÍO') }}"
                                            data-ruc="{{ $guia->empresa->ruc ?? ($guia->cliente->ruc ?? 'VACÍO') }}"
                                            data-contacto="{{ $guia->empresa->contacto ?? ($guia->cliente->contacto ?? 'VACÍO') }}"
                                            data-rubro="{{ $guia->empresa->rubro ?? ($guia->cliente->rubro ?? 'VACÍO') }}"
                                            data-monto="{{ $guia->simbolo_moneda }} {{ number_format($guia->monto, 2) }}"
                                            data-recepcion="{{ \Carbon\Carbon::parse($guia->recepcion)->format('d/m/Y') }}"
                                            data-programado="{{ ucfirst($guia->programado) }}"
                                            data-emitido="{{ ucfirst($guia->emitido) }}"
                                            data-entregado="{{ $guia->entregado ?? 'VACÍO' }}"
                                            data-grr="{{ strtoupper($guia->grr) }}"
                                            data-grt="{{ strtoupper($guia->grt) }}"
                                            data-bancarizacion="{{ ucfirst($guia->bancarizacion) }}"
                                            data-contingencia="{{ $guia->contingencia ? 'Sí' : 'No' }}"
                                            data-observaciones="{{ $guia->observaciones ?? 'Sin observaciones' }}"
                                            data-usuario_sol="{{ $guia->empresa->sol_usuario ?? '-' }}"
                                            data-clave_sol="{{ $guia->empresa->sol_clave ?? '-' }}"
                                            data-usuario="{{ $guia->usuario->name ?? '—' }}"
                                            data-tipo="{{ $guia->empresa ? 'empresa' : 'cliente' }}"
                                            data-info_cuentas="{{ $guia->empresa->info_cuentas ?? '' }}"
                                            data-direcciones="{{ $guia->empresa ? $guia->empresa->direcciones->map(fn($d) => $d->tipo_domicilio . ': ' . $d->nombre . ' - ' . $d->distrito . ', ' . $d->provincia . ', ' . $d->region)->implode('|') : '' }}">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon />
                                        </a>

                                        <a href="{{ route('guias.edit', $guia->id_guia) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #F59E0B; transition: color 0.3s;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </a>
                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                            <form action="{{ route('guias.destroy', $guia->id_guia) }}" method="POST"
                                                style="display:inline;"
                                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta guía?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';">
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form>
                                        @endif
                                        <button class="btn-factura-global flex items-center py-1 rounded-md tooltip"
                                            data-id-receptor="{{ $guia->empresa->id_empresas_clientes ?? $guia->cliente->id_cliente }}"
                                            data-tipo="{{ $guia->empresa ? 'empresa' : 'cliente' }}"
                                            data-nombre="{{ $guia->empresa->razon_social ?? $guia->cliente->razon_social }}"
                                            style="color: #10b981; transition: color 0.3s;"
                                            onmouseover="this.style.color='#059669';"
                                            onmouseout="this.style.color='#10b981';">
                                            <span class="tooltip-text">+Factura</span>
                                            <x-factura-icon />
                                        </button>
                                        <a href="{{ route('facturas.detalle_mensual', [
                                            'tipo' => $guia->empresa ? 'empresa' : 'cliente',
                                            'id_receptor' => $guia->empresa->id_empresas_clientes ?? $guia->cliente->id_cliente,
                                        ]) }}?mes={{ $mes }}&anio={{ $anio }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #3b82f6; transition: color 0.3s;"
                                            onmouseover="this.style.color='#2563eb';"
                                            onmouseout="this.style.color='#3b82f6';">
                                            <span class="tooltip-text">F. Mensual</span>
                                            <x-facturas-mensuales-icon />
                                        </a>
                                    </div>
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @if ($guia->empresa)
                                        {{ $guia->empresa->contacto ?: '-' }}
                                    @elseif ($guia->cliente)
                                        {{ $guia->cliente->contacto ?: '-' }}
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @if ($guia->empresa)
                                        {{ $guia->empresa->ruc }}
                                    @elseif ($guia->cliente)
                                        {{ $guia->cliente->ruc }}
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @php
                                        $entidad = $guia->empresa ?? $guia->cliente;

                                        if ($entidad) {
                                            $tipo = $guia->empresa ? 'empresa' : 'cliente';
                                            $id = $guia->empresa
                                                ? $entidad->id_empresas_clientes
                                                : $entidad->id_cliente;
                                            $key = $tipo . '_' . $id;
                                            $data = $resumenFacturacion[$key] ?? null;
                                            $abonoPendiente = $data
                                                ? max(0, $data['monto_facturado'] - $data['monto_abonado'])
                                                : 0;
                                            $montoFacturado = $data['monto_facturado_bruto'] ?? 0;
                                            $simbolo = $data['simbolo'] ?? 'S/';
                                            if ($guia->bancarizacion === 'si') {
                                                $colorClass = 'text-green-500 hover:text-green-600';
                                            } elseif ($montoFacturado > 0) {
                                                $colorClass = 'text-blue-600 hover:text-blue-800';
                                            } else {
                                                $colorClass = 'text-gray-500 hover:text-gray-700';
                                            }
                                            $fecha = \Carbon\Carbon::parse($guia->recepcion);
                                        }
                                    @endphp
                                    @if ($entidad)
                                        <a href="{{ route('abono_facturas.index', [
                                            'buscar' => $entidad->ruc,
                                            'desde' => $fecha->format('Y-m'),
                                            'hasta' => $fecha->format('Y-m'),
                                        ]) }}"
                                            class="{{ $colorClass }} hover:underline font-medium"
                                            title="{{ $guia->bancarizacion === 'si' && $abonoPendiente <= 0
                                                ? 'Abonado'
                                                : 'Pendiente: ' . $simbolo . ' ' . number_format($abonoPendiente, 2) }}
                – {{ $entidad->razon_social }}">

                                            {{ $entidad->razon_social }}
                                        </a>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b text-right">
                                    {{-- MONTO PRINCIPAL --}}
                                    <div>
                                        @if ($guia->moneda === 'USD')
                                            $ {{ number_format($guia->monto, 2) }}
                                        @else
                                            S/ {{ number_format($guia->monto, 2) }}
                                        @endif
                                    </div>

                                    {{-- PARENTESIS SUNAT (SOLO SI HAY FACTURA) --}}
                                    @php
                                        $key = $guia->empresa
                                            ? 'empresa_' . $guia->empresa->id_empresas_clientes
                                            : 'cliente_' . $guia->cliente->id_cliente;
                                    @endphp

                                    @if ($guia->moneda === 'USD' && isset($montosSunatPorReceptor[$key]))
                                        @php
                                            $montoSunat = number_format($montosSunatPorReceptor[$key], 2);
                                        @endphp
                                        <div class="text-xs text-gray-500 cursor-pointer select-none hover:underline hover:text-blue-500 transition-colors"
                                            onclick="copiarMonto(this)" data-monto="{{ $montoSunat }}">
                                            (S/ {{ $montoSunat }})
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $montoSunatStr =
                                            $guia->moneda === 'USD' && isset($montosSunatPorReceptor[$key])
                                                ? number_format($montosSunatPorReceptor[$key], 2, '.', '')
                                                : '';
                                    @endphp
                                    {{ $montoSunatStr }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($guia->recepcion)->format('d/m/Y') }}</td>
                                <!-- Programado: "Listo" (verde), "Pendiente" (rojo) -->
                                <td class="px-4 py-2 border-b">
                                    <form action="{{ route('guias.toggleProgramado', $guia->id_guia) }}"
                                        method="POST"
                                        onsubmit="return confirm('¿Deseas cambiar el estado a {{ strtolower($guia->programado) === 'listo' ? 'PENDIENTE' : 'LISTO' }}?')">
                                        @csrf
                                        <button type="submit"
                                            class="flex justify-center items-center px-4 py-1 text-white rounded-full w-24 transition
        {{ strtolower($guia->programado) === 'listo' ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}">
                                            {{ ucfirst($guia->programado) }}
                                        </button>
                                    </form>
                                </td>

                                <!-- Emitido: "Listo" (verde), "Pendiente" (rojo) -->
                                <td class="px-4 py-2 border-b">
                                    <span
                                        class="flex justify-center items-center px-4 py-1 text-white rounded-full w-24
            {{ strtolower($guia->emitido) === 'listo' ? 'bg-green-500' : 'bg-red-500' }}">
                                        {{ ucfirst($guia->emitido) }}
                                    </span>
                                </td>

                                <!-- GRR -->
                                <td class="px-4 py-2 border-b">
                                    <span
                                        class="flex justify-center items-center px-4 py-1 text-white rounded-full w-24
            {{ $guia->grr === 'si' ? 'bg-green-500' : ($guia->grr === 'no' ? 'bg-red-500' : 'bg-gray-500') }}">
                                        {{ strtoupper($guia->grr) }}
                                    </span>
                                </td>

                                <!-- GRT -->
                                <td class="px-4 py-2 border-b">
                                    <span
                                        class="flex justify-center items-center px-4 py-1 text-white rounded-full w-24
            {{ $guia->grt === 'si' ? 'bg-green-500' : ($guia->grt === 'no' ? 'bg-red-500' : 'bg-gray-500') }}">
                                        {{ strtoupper($guia->grt) }}
                                    </span>
                                </td>

                                <!-- Bancarización -->
                                <td class="px-4 py-2 border-b">
                                    <div class="flex flex-col items-center">
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white rounded-full w-24
            {{ $guia->bancarizacion === 'si' ? 'bg-green-500' : ($guia->bancarizacion === 'no' ? 'bg-red-500' : 'bg-gray-500') }}">
                                            {{ ucfirst($guia->bancarizacion) }}
                                        </span>

                                        @php
                                            $key = $guia->empresa
                                                ? 'empresa_' . $guia->empresa->id_empresas_clientes
                                                : 'cliente_' . $guia->cliente->id_cliente;
                                            $pendienteAbono = isset($resumenFacturacion[$key])
                                                ? max(
                                                    0,
                                                    $resumenFacturacion[$key]['monto_facturado'] -
                                                        $resumenFacturacion[$key]['monto_abonado'],
                                                )
                                                : 0;
                                            $simbolo = isset($resumenFacturacion[$key])
                                                ? $resumenFacturacion[$key]['simbolo']
                                                : 'S/';
                                        @endphp

                                        @if ($pendienteAbono > 0)
                                            <span class="text-xs text-red-600 font-semibold mt-1"> {{ $simbolo }}
                                                {{ number_format($pendienteAbono, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <!-- Contingencia -->
                                <td class="px-4 py-2 border-b">
                                    <span
                                        class="flex justify-center items-center px-4 py-1 text-white rounded-full w-24
        {{ $guia->contingencia ? 'bg-green-500' : 'bg-red-500' }}">
                                        {{ $guia->contingencia ? 'SI' : 'NO' }}
                                    </span>
                                </td>
                                {{-- <td class="px-4 py-2 border-b">{{ $guia->entregado ?: '-' }}</td> --}}
                                {{-- <td class="px-4 py-2 border-b">{{ $guia->observaciones ?: 'Sin Observaciones' }}</td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold">
                        {{-- ===== SOLES ===== --}}
                        <tr>
                            <td class="px-4 py-2 border-t"></td>
                            <td class="px-4 py-2 border-t"></td>
                            <td class="px-4 py-2 border-t"></td>
                            <td class="px-4 py-2 border-t text-right">Total (S/):</td>
                            <td class="px-4 py-2 border-t">
                                S/{{ number_format(collect($resumenFacturacion)->where('moneda', 'PEN')->sum('monto_total_guias'), 2) }}
                            </td>
                            <td colspan="8" class="px-4 py-2 border-t"></td>
                        </tr>

                        {{-- ===== DÓLARES ===== --}}
                        <tr>
                            <td class="px-4 py-2 border-t"></td>
                            <td class="px-4 py-2 border-t"></td>
                            <td class="px-4 py-2 border-t"></td>

                            <td class="px-4 py-2 border-t text-right">Total ($):</td>

                            <td class="px-4 py-2 border-t">
                                $/{{ number_format(collect($resumenFacturacion)->where('moneda', 'USD')->sum('monto_total_guias'), 2) }}
                            </td>

                            <td colspan="8" class="px-4 py-2 border-t"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
<x-modal-show id="guiaModal" title="Detalles de la Guía">
    <div class="space-y-4 text-sm text-gray-800">

        {{-- Info Principal --}}
        <div class="grid grid-cols-2 gap-2">
            <p><strong>ID Guía:</strong> <span id="guiaId"></span></p>
            <p><strong>Recepción:</strong> <span id="guiaRecepcion"></span></p>

            <p><strong>Contacto:</strong> <span id="guiaContacto"></span></p>
            <p><strong>RUC:</strong> <span id="guiaRuc"></span></p>

            <p><strong>Razón Social:</strong> <span id="guiaRazon"></span></p>
            <p><strong>Rubro:</strong> <span id="guiaRubro"></span></p>

            <p><strong>Monto:</strong> <span id="guiaMonto"></span></p>
            <p><strong>Ultima edición:</strong> <span id="guiaUsuario"></span></p>
        </div>

        {{-- Usuario y Clave SOL (solo para empresas) --}}
        {{-- <div id="empresaSol" class="space-y-1 hidden border-t pt-3">
            <h4 class="font-semibold text-gray-700">Datos SUNAT:</h4>
            <p><strong>Usuario SOL:</strong> <span id="guiaUsuarioSol"></span></p>
            <p><strong>Clave SOL:</strong> <span id="guiaClaveSol"></span></p>
        </div> --}}

        {{-- Estados --}}
        <div class="grid grid-cols-2 gap-2 border-t pt-3">
            <p><strong>Programado:</strong> <span id="guiaProgramado"></span></p>
            <p><strong>Emitido:</strong> <span id="guiaEmitido"></span></p>
            <p><strong>Entregado:</strong> <span id="guiaEntregado"></span></p>
            <p><strong>GRR:</strong> <span id="guiaGrr"></span></p>
            <p><strong>GRT:</strong> <span id="guiaGrt"></span></p>
            <p><strong>Bancarización:</strong> <span id="guiaBancarizacion"></span></p>
            <p><strong>Contingencia:</strong> <span id="guiaContingencia"></span></p>
        </div>

        {{-- Observaciones --}}
        <div class="border-t pt-3">
            <p class="font-bold mb-1">Observaciones:</p>
            <div class="flex items-center gap-2">
                <input type="text" id="guiaObs"
                    class="flex-1 border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400"
                    placeholder="Sin observaciones" />
                <button id="btnGuardarObs" onclick="guardarObservacion()"
                    class="flex items-center justify-center w-8 h-8 rounded-md bg-green-500 hover:bg-green-600 text-white transition-colors"
                    title="Guardar observación">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.121-4.121a1 1 0 111.414-1.414L8.414 12.172l7.879-7.879a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <p id="msgObsStatus" class="text-xs mt-1 hidden"></p>
        </div>

        {{-- <div id="empresaCuentas" class="border-t pt-3 hidden">
            <h4 class="font-semibold text-gray-700">Información de Cuentas:</h4>
            <pre id="guiaCuentas" class="text-sm text-gray-700 whitespace-pre-wrap break-words"></pre>
        </div> --}}


        {{-- Direcciones --}}
        {{-- <div id="empresaDirecciones" class="space-y-1 border-t pt-3 hidden">
            <h4 class="font-semibold text-gray-700">Direcciones:</h4>
            <ul id="guiaDirecciones" class="list-disc list-inside space-y-1 text-gray-700 text-sm"></ul>
        </div> --}}

    </div>
</x-modal-show>
<x-modal-show id="modalInventariosGlobal" title="Selecciona un inventario para facturar">
    <div class="text-sm text-gray-800 max-h-[70vh] overflow-y-auto px-1">

        <p><strong>Destinatario:</strong> <span id="nombreDestinatario"></span></p>

        <input type="text" id="buscadorInventarios" placeholder="Buscar inventario o código..."
            class="w-full border-gray-300 rounded-md shadow-sm mb-3 mt-2 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">

        <!-- LOADER AQUI -->
        <div id="loaderInventarios" class="w-full text-center py-6 hidden">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-green-500 border-t-transparent">
            </div>
            <p class="mt-2 text-gray-700">Cargando inventarios...</p>
        </div>

        <table class="min-w-full mt-3 bg-white rounded-md text-sm hidden" id="tablaInventarios">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">Inventario</th>
                    <th class="px-4 py-2 text-left">Código</th>
                    <th class="px-4 py-2 text-left">Acción</th>
                </tr>
            </thead>

            <tbody>
                <!-- Aquí irán los resultados AJAX -->
            </tbody>
        </table>
    </div>
</x-modal-show>
<div id="modalMontos"
    class="fixed inset-0 z-50 hidden overflow-y-auto flex items-center justify-center transition-opacity duration-300 opacity-0"
    style="background-color: rgba(0, 0, 0, 0.5);" onclick="closeModal(event, 'modalMontos')">
    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-6xl transform transition-all duration-300 scale-95"
        onclick="event.stopPropagation();">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-800">Montos pendientes por facturar</h2>
            <div class="flex items-center gap-4">
                <span class="text-sm bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                    {{ $nombreMesActual }} {{ $anio }}
                </span>
                <button onclick="toggleModal('modalMontos')" class="text-gray-500 hover:text-gray-700 text-2xl">
                    &times;
                </button>
            </div>
        </div>

        <div class="p-6 overflow-auto max-h-[70vh]">
            <table id="myTable-pendientes" class="min-w-full border text-sm">
                <thead class="bg-gray-100 sticky top-0">
                    <tr class="text-left">
                        <th class="px-4 py-3 border-b-2 border-gray-200 font-semibold">Razón Social y RUC</th>
                        <th class="px-4 py-3 border-b-2 border-gray-200 font-semibold">Monto Total Guías</th>
                        <th class="px-4 py-3 border-b-2 border-gray-200 font-semibold">Monto Programado</th>
                        <th class="px-4 py-3 border-b-2 border-gray-200 font-semibold">Pendiente por Programar</th>
                        <th class="px-4 py-3 border-b-2 border-gray-200 font-semibold text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($resumenFacturacion as $key => $item)
                        @php
                            $pendiente = max(0, $item['monto_total_guias'] - $item['monto_facturado_bruto']);
                        @endphp
                        <tr class="hover:bg-gray-50 border-b">
                            <td class="px-4 py-3 border-b border-gray-200">
                                <div class="font-medium">{{ $item['razon_social'] }}</div>
                                <div class="text-gray-500 text-xs">{{ $item['ruc'] }}</div>
                            </td>
                            @php
                                $esMayor = $item['monto_facturado_bruto'] > $item['monto_total_guias'];
                            @endphp
                            <td
                                class="px-4 py-3 border-b border-gray-200 font-medium {{ $esMayor ? 'text-orange-500 font-semibold' : '' }}">
                                {{ $item['simbolo'] }} {{ number_format($item['monto_total_guias'], 2) }}
                            </td>
                            <td class="px-4 py-3 border-b border-gray-200">
                                {{ $item['simbolo'] }} {{ number_format($item['monto_facturado_bruto'], 2) }}
                            </td>
                            <td
                                class="px-4 py-3 border-b border-gray-200 font-semibold {{ $pendiente > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $item['simbolo'] }} {{ number_format($pendiente, 2) }}
                            </td>
                            <td class="px-4 py-3 border-b border-gray-200 text-center">
                                @if ($pendiente > 0)
                                    <button class="btn-factura-global flex items-center py-1 rounded-md tooltip"
                                        data-id-receptor="{{ explode('_', $key)[1] }}"
                                        data-tipo="{{ $item['tipo'] }}" data-nombre="{{ $item['razon_social'] }}"
                                        data-pendiente="{{ $pendiente }}" data-moneda="{{ $item['moneda'] }}"
                                        style="color: #10b981; transition: color 0.3s;"
                                        onmouseover="this.style.color='#059669';"
                                        onmouseout="this.style.color='#10b981';">
                                        <span class="tooltip-text">+Factura</span>
                                        <x-factura-icon />
                                    </button>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td class="px-4 py-3 border-t-2 border-gray-200">Total (S/)</td>

                        <td class="px-4 py-3 border-t-2 border-gray-200">
                            S/{{ number_format(collect($resumenFacturacion)->where('moneda', 'PEN')->sum('monto_total_guias'), 2) }}
                        </td>

                        <td class="px-4 py-3 border-t-2 border-gray-200">
                            S/{{ number_format(collect($resumenFacturacion)->where('moneda', 'PEN')->sum('monto_facturado_bruto'), 2) }}
                        </td>

                        @php
                            $pendientePEN = collect($resumenFacturacion)
                                ->where('moneda', 'PEN')
                                ->sum(fn($item) => max(0, $item['monto_total_guias'] - $item['monto_facturado_bruto']));
                        @endphp

                        <td
                            class="px-4 py-3 border-t-2 border-gray-200 {{ $pendientePEN > 0 ? 'text-red-600' : 'text-green-600' }}">
                            S/ {{ number_format(max(0, $pendientePEN), 2) }}
                        </td>
                        <td class="px-4 py-3 border-t-2 border-gray-200"></td>
                    </tr>

                    {{-- ================= DÓLARES ================= --}}
                    <tr>
                        <td class="px-4 py-3 border-t border-gray-200">Total ($/)</td>
                        <td class="px-4 py-3 border-t border-gray-200">
                            $/{{ number_format(collect($resumenFacturacion)->where('moneda', 'USD')->sum('monto_total_guias'), 2) }}
                        </td>
                        <td class="px-4 py-3 border-t border-gray-200">
                            $/{{ number_format(collect($resumenFacturacion)->where('moneda', 'USD')->sum('monto_facturado_bruto'), 2) }}
                        </td>
                        @php
                            $pendienteUSD = collect($resumenFacturacion)
                                ->where('moneda', 'USD')
                                ->sum(fn($item) => max(0, $item['monto_total_guias'] - $item['monto_facturado_bruto']));
                        @endphp
                        <td
                            class="px-4 py-3 border-t border-gray-200 {{ $pendienteUSD > 0 ? 'text-red-600' : 'text-green-600' }}">
                            $/ {{ number_format(max(0, $pendienteUSD), 2) }}
                        </td>
                        <td class="px-4 py-3 border-t border-gray-200"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="px-6 py-4 border-t bg-gray-50 text-right">
            <button onclick="toggleModal('modalMontos')"
                class="px-5 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Cerrar
            </button>
        </div>
    </div>
</div>

<x-modal-show id="modalExportarGuias" title="Exportar Guías a Excel">
    <form action="{{ route('guias.exportar_excel') }}" method="POST" id="formExportarGuias">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-800">
            <!-- Tipo de Entidad -->
            <div class="flex flex-col md:col-span-2">
                <label class="text-sm font-semibold text-gray-700 mb-2">Tipo de Entidad <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tipo_entidad" value="empresa" class="form-radio text-indigo-600 focus:ring-indigo-500" required>
                        Empresa Cliente
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tipo_entidad" value="cliente" class="form-radio text-indigo-600 focus:ring-indigo-500" required>
                        Cliente Auditoría
                    </label>
                </div>
            </div>

            <!-- Entidad (Select2) -->
            <div class="flex flex-col md:col-span-2">
                <label for="id_entidad" class="text-sm font-semibold text-gray-700 mb-1">Entidad <span class="text-red-500">*</span></label>
                <select name="id_entidad" id="id_entidad" class="select2 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required disabled>
                    <option value="" disabled selected>Seleccione la entidad</option>
                </select>
            </div>

            <!-- Desde -->
            <div class="flex flex-col">
                <label for="desde" class="text-sm font-semibold text-gray-700 mb-1">Desde</label>
                <input type="month" name="desde" id="desde" class="border-gray-300 rounded-md shadow-sm w-full focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <!-- Hasta -->
            <div class="flex flex-col">
                <label for="hasta" class="text-sm font-semibold text-gray-700 mb-1">Hasta</label>
                <input type="month" name="hasta" id="hasta" class="border-gray-300 rounded-md shadow-sm w-full focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-md transition-colors">
                <i class="bi bi-file-earmark-excel-fill"></i> Exportar Excel
            </button>
        </div>
    </form>
</x-modal-show>

<script>
    let pendientePorFacturar = null;
    document.querySelectorAll('.btn-facturar-pendiente').forEach(btn => {
        btn.addEventListener('click', function() {
            tipoFactura = this.dataset.tipo;
            idReceptor = this.dataset.id;
            pendientePorFacturar = this.dataset.pendiente;
            document.getElementById('nombreDestinatario').textContent = this.dataset.nombre;
            toggleModal('modalMontos');
            toggleModal('modalInventariosGlobal');
        });
    });
    $('#myTable-pendientes').DataTable({
        responsive: false,
        paging: true,
        ordering: true,
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50, 100, 1000],
        language: {
            url: "/js/datatables/lang/es-ES.json"
        }
    });

    // Código para el modal Exportar Guías Excel
    document.addEventListener('DOMContentLoaded', function() {
        const empresasExport = @json($empresas_export ?? []);
        const clientesExport = @json($clientes_export ?? []);
        
        const tipoEntidadRadios = document.querySelectorAll('input[name="tipo_entidad"]');
        const idEntidadSelect = $('#id_entidad'); 
        
        if ($.fn.select2) {
            idEntidadSelect.select2({
                dropdownParent: $('#modalExportarGuias'),
                width: '100%'
            });
        }

        tipoEntidadRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const tipo = this.value;
                idEntidadSelect.empty().append('<option value="" disabled selected>Seleccione la entidad</option>');
                
                let data = [];
                if (tipo === 'empresa') {
                    data = empresasExport;
                } else if (tipo === 'cliente') {
                    data = clientesExport;
                }

                data.forEach(item => {
                    const id = tipo === 'empresa' ? item.id_empresas_clientes : item.id_cliente;
                    const ruc = item.ruc ? item.ruc + ' - ' : '';
                    const text = ruc + item.razon_social;
                    const option = new Option(text, id, false, false);
                    idEntidadSelect.append(option);
                });

                idEntidadSelect.prop('disabled', false).trigger('change');
            });
        });

        const currentViewMonth = "{{ str_pad($mes ?? date('m'), 2, '0', STR_PAD_LEFT) }}";
        const currentViewYear = "{{ $anio ?? date('Y') }}";
        const currentRealMonth = "{{ date('m') }}";
        const currentRealYear = "{{ date('Y') }}";
        
        if(document.getElementById('desde')) {
            document.getElementById('desde').value = `${currentViewYear}-${currentViewMonth}`;
            document.getElementById('hasta').value = `${currentRealYear}-${currentRealMonth}`;
        }
    });
</script>

<script>
    let tipoFactura = null;
    let idReceptor = null;
    let monedaPendiente = null;

    document.querySelectorAll('.btn-factura-global').forEach(btn => {
        btn.addEventListener('click', () => {

            tipoFactura = btn.dataset.tipo;
            idReceptor = btn.dataset.idReceptor;
            pendientePorFacturar = btn.dataset.pendiente;
            monedaPendiente = btn.dataset.moneda;

            document.getElementById('nombreDestinatario').textContent = btn.dataset.nombre;

            // Resetear buscador
            const buscador = document.getElementById('buscadorInventarios');
            if (buscador) buscador.value = '';

            // CERRAR MODAL MONTOS SOLO SI ESTÁ ABIERTO
            const modalMontos = document.getElementById('modalMontos');
            if (!modalMontos.classList.contains('hidden')) {
                toggleModal('modalMontos');
            }

            const loader = document.getElementById('loaderInventarios');
            const tabla = document.getElementById('tablaInventarios');

            // Mostrar loader
            loader.classList.remove('hidden');
            tabla.classList.add('hidden');

            // Abrir modal ya visualmente
            toggleModal('modalInventariosGlobal');

            // Esperar que el navegador pinte el loader
            requestAnimationFrame(() => {

                fetch(
                        `/ajax/facturacion/inventarios-relacionados?id_receptor=${idReceptor}&tipo=${tipoFactura}`
                    )
                    .then(res => res.json())
                    .then(data => {
                        if (!Array.isArray(data)) {
                            console.error("Respuesta inválida:", data);
                            alert("Error cargando inventarios:\n" + (data.message ??
                                "Desconocido"));
                            loader.classList.add('hidden');
                            return;
                        }

                        const tbody = tabla.querySelector('tbody');
                        tbody.innerHTML = '';

                        data.forEach(inv => {
                            let colorStyle = 'color:#10b981;'; // Verde por defecto
                            let tooltipText = '+Factura';
                            let textoVence = '';
                            if (inv.fecha_fin_contrato) {
                                const hoy = new Date();
                                hoy.setHours(0, 0, 0, 0);
                                const fin = new Date(inv.fecha_fin_contrato +
                                    'T00:00:00');
                                fin.setHours(0, 0, 0, 0);
                                // Diferencia en días (positivo = futuro, 0 = hoy, negativo = pasado)
                                let diffTime = fin.getTime() - hoy.getTime();
                                let diasRestantes = Math.round(diffTime / (1000 *
                                    60 * 60 * 24));
                                // Formatear fecha a DD/MM/YYYY
                                const fechaFormateada = fin.toLocaleDateString(
                                    'es-PE', {
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric'
                                    });
                                // Lógica de colores y textos
                                if (diasRestantes === 0) {
                                    colorStyle = 'color:#f59e0b;'; // amarillo
                                    tooltipText = 'Vence hoy';
                                    textoVence = `Vence: ${fechaFormateada} (hoy)`;
                                } else if (diasRestantes === 1) {
                                    colorStyle = 'color:#f97316;'; // naranja
                                    tooltipText = 'Vence mañana';
                                    textoVence =
                                        `Vence: ${fechaFormateada} (mañana)`;
                                } else if (diasRestantes > 1 && diasRestantes <=
                                    7) {
                                    colorStyle = 'color:#f97316;'; // naranja
                                    tooltipText = 'Vence pronto';
                                    textoVence =
                                        `Vence: ${fechaFormateada} (en ${diasRestantes} días)`;
                                } else if (diasRestantes > 7) {
                                    colorStyle = 'color:#10b981;'; // verde
                                    tooltipText = '+Factura';
                                    textoVence =
                                        `Vence: ${fechaFormateada} (en ${diasRestantes} días)`;
                                } else {
                                    const diasVencido = Math.abs(diasRestantes);
                                    colorStyle = 'color:#ef4444;'; // rojo
                                    tooltipText = 'Contrato vencido';
                                    textoVence =
                                        `Venció: ${fechaFormateada} (hace ${diasVencido} día${diasVencido === 1 ? '' : 's'})`;
                                }
                            }
                            tbody.innerHTML += `
        <tr>
            <td class="px-4 py-2 border-b">
                ${inv.empresa?.razon_social ?? '—'}
                ${inv.fecha_fin_contrato ? `<br><span class="text-xs text-gray-400">${textoVence}</span>` : ''}
            </td>
            <td class="px-4 py-2 border-b">${inv.nombre_codigo}</td>
            <td class="px-4 py-2 border-b">
                <a href="#" class="tooltip btn-redirigir-factura flex items-center py-1 rounded-md" data-id="${inv.id_inventario}" style="${colorStyle}">
                    <span class="tooltip-text">${tooltipText}</span>
                    <x-factura-icon />
                </a>
            </td>
        </tr>`;
                        });

                        attachFacturaButtons();
                        loader.classList.add('hidden');
                        tabla.classList.remove('hidden');
                    })
                    .catch(err => {
                        console.error("ERROR inventarios:", err);
                        loader.classList.add('hidden');
                        alert("Error cargando inventarios. Revisa consola.");
                    });
            });
        });
    });

    function attachFacturaButtons() {
        document.querySelectorAll('.btn-redirigir-factura').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                const idInventario = btn.dataset.id;
                const urlBase = tipoFactura === 'empresa' ?
                    `/facturas/create/empresa/${idInventario}` :
                    `/facturas/create/cliente/${idInventario}`;
                window.location.href =
                    `${urlBase}?receptor=${idReceptor}&pendiente=${pendientePorFacturar}&moneda=${monedaPendiente}`;
            });
        });
    }


    document.querySelectorAll('.btn-redirigir-factura').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const idInventario = btn.dataset.id;

            const urlBase = tipoFactura === 'empresa' ?
                `/facturas/create/empresa/${idInventario}` :
                `/facturas/create/cliente/${idInventario}`;

            window.location.href =
                `${urlBase}?receptor=${idReceptor}&pendiente=${pendientePorFacturar}&moneda=${monedaPendiente}`;
        });
    });

    // Lógica del buscador
    document.getElementById('buscadorInventarios')?.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#tablaInventarios tbody tr');

        rows.forEach(row => {
            const content = row.innerText.toLowerCase();
            if (content.includes(term)) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });
    });
</script>

<script>
    let guiaIdActual = null;
    document.querySelectorAll('.btn-detalles').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;
            guiaIdActual = data.id;

            document.getElementById('guiaId').textContent = data.id;
            document.getElementById('guiaRazon').textContent = data.razon;
            document.getElementById('guiaRuc').textContent = data.ruc;
            //document.getElementById('guiaUsuarioSol').textContent = data.usuario_sol;
            //document.getElementById('guiaClaveSol').textContent = data.clave_sol;
            document.getElementById('guiaContacto').textContent = data.contacto;
            document.getElementById('guiaRubro').textContent = data.rubro;
            document.getElementById('guiaMonto').textContent = `${data.monto}`;
            document.getElementById('guiaRecepcion').textContent = data.recepcion;
            document.getElementById('guiaProgramado').textContent = data.programado;
            document.getElementById('guiaEmitido').textContent = data.emitido;
            document.getElementById('guiaEntregado').textContent = data.entregado;
            document.getElementById('guiaGrr').textContent = data.grr;
            document.getElementById('guiaGrt').textContent = data.grt;
            document.getElementById('guiaBancarizacion').textContent = data.bancarizacion;
            document.getElementById('guiaContingencia').textContent = data.contingencia;
            document.getElementById('guiaObs').textContent = data.observaciones;
            document.getElementById('guiaUsuario').textContent = data.usuario;
            document.getElementById('guiaObs').value = data.observaciones ?? '';

            const msg = document.getElementById('msgObsStatus');
            msg.textContent = '';
            msg.classList.add('hidden');
            /*
            const list = document.getElementById('guiaDirecciones');
            const box = document.getElementById('empresaDirecciones');
            list.innerHTML = '';

            if (data.tipo === 'empresa') {
                if (data.direcciones && data.direcciones !== 'Sin direcciones registradas') {
                    const direcciones = data.direcciones.split('|');
                    direcciones.forEach(dir => {
                        const li = document.createElement('li');
                        li.textContent = dir;
                        list.appendChild(li);
                    });
                    box.classList.remove('hidden');
                } else {
                    box.classList.add('hidden');
                }

                document.getElementById('empresaSol').classList.remove('hidden');
            } else {
                box.classList.add('hidden');
                document.getElementById('empresaSol').classList.add('hidden');
            }
            
            const cuentas = document.getElementById('guiaCuentas');
            const cuentaBox = document.getElementById('empresaCuentas');

            if (data.tipo === 'empresa' && data.info_cuentas && data.info_cuentas.trim() !== '') {
                cuentas.textContent = data.info_cuentas;
                cuentaBox.classList.remove('hidden');
            } else {
                cuentaBox.classList.add('hidden');
            } */


            toggleModal('guiaModal');
        });
    });
</script>

<script>
    function guardarObservacion() {
        if (!guiaIdActual) return;

        const obs = document.getElementById('guiaObs').value;
        const btn = document.getElementById('btnGuardarObs');
        const msg = document.getElementById('msgObsStatus');

        btn.disabled = true;
        btn.classList.replace('bg-green-500', 'bg-gray-400');

        fetch(`/guias/${guiaIdActual}/observacion`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    observaciones: obs
                })
            })
            .then(res => res.json())
            .then(data => {
                msg.textContent = '✓ Guardado correctamente';
                msg.className = 'text-xs mt-1 text-green-600';
                msg.classList.remove('hidden');

                const btnDetalles = document.querySelector(`.btn-detalles[data-id="${guiaIdActual}"]`);
                if (btnDetalles) btnDetalles.dataset.observaciones = obs;
            })
            .catch(() => {
                msg.textContent = '✗ Error al guardar';
                msg.className = 'text-xs mt-1 text-red-600';
                msg.classList.remove('hidden');
            })
            .finally(() => {
                btn.disabled = false;
                btn.classList.replace('bg-gray-400', 'bg-green-500');
                setTimeout(() => msg.classList.add('hidden'), 3000);
            });
    }
</script>

<script>
    function copiarMonto(elemento) {
        const texto = elemento.dataset.monto;
        navigator.clipboard.writeText(texto).then(() => {
            const original = elemento.innerHTML;
            elemento.innerHTML = 'Dato copiado';
            elemento.classList.add('text-blue-600');
            setTimeout(() => {
                elemento.innerHTML = original;
                elemento.classList.remove('text-blue-600');
            }, 1500);
        });
    }
</script>


<script>
    const nombreMes = {
        '01': 'ENERO',
        '02': 'FEBRERO',
        '03': 'MARZO',
        '04': 'ABRIL',
        '05': 'MAYO',
        '06': 'JUNIO',
        '07': 'JULIO',
        '08': 'AGOSTO',
        '09': 'SETIEMBRE',
        '10': 'OCTUBRE',
        '11': 'NOVIEMBRE',
        '12': 'DICIEMBRE'
    };

    const mesActual = '{{ $mes }}';
    const anioActual = '{{ $anio }}';

    $(document).ready(function() {
        const today = new Date();
        const dia = String(today.getDate()).padStart(2, '0');
        const mes = String(today.getMonth() + 1).padStart(2, '0');
        const anio = today.getFullYear();

        const fechaExportacion = `_EXPORTADO_HOY_${dia}_${nombreMes[mes] || mes}_${anio}`;

        $('#myTable-guias').DataTable({
            responsive: false,
            paging: true,
            ordering: true,
            pageLength: 100,
            order: [
                [3, 'desc']
            ],
            columnDefs: [{
                targets: [5],
                visible: false,
                searchable: false
            }],
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excel',
                    text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                    title: function() {
                        return `GUIAS_${nombreMes[mesActual] || mesActual}_${anioActual}_AUDITORIA${fechaExportacion}`;
                    },
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                    title: function() {
                        return `GUIAS_${nombreMes[mesActual] || mesActual}_${anioActual}_AUDITORIA${fechaExportacion}`;
                    },
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                    },
                    orientation: 'landscape',
                    pageSize: 'A4'
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer"></i> Imprimir',
                    title: function() {
                        return `GUIAS_${nombreMes[mesActual] || mesActual}_${anioActual}_AUDITORIA${fechaExportacion}`;
                    },
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                    }
                }
            ],
            language: {
                url: "/js/datatables/lang/es-ES.json"
            }
        });
    });
</script>
