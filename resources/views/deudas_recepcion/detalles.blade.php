@php
    $meses = [
        'January' => 'Enero',
        'February' => 'Febrero',
        'March' => 'Marzo',
        'April' => 'Abril',
        'May' => 'Mayo',
        'June' => 'Junio',
        'July' => 'Julio',
        'August' => 'Agosto',
        'September' => 'Septiembre',
        'October' => 'Octubre',
        'November' => 'Noviembre',
        'December' => 'Diciembre',
    ];

    $user = auth()->user();
    $isGerencia = $user->area && strtoupper(trim($user->area->nombre)) === 'GERENCIA';
    $isAdmin = $user->tipo_usuario == 1 || $user->id_area == 1 || $isGerencia;

    // RRHH fields are blocked if NOT Admin AND NOT RRHH (4)
    $blockRRHH = !$isAdmin && $user->id_area != 4;

    // Contabilidad fields are blocked if NOT Admin AND NOT Contabilidad (2)
    $blockContabilidad = !$isAdmin && $user->id_area != 2;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Deudas Recepción — {{ $empresa->razon_social }} {{ $empresa->ruc }}
            <select
                onchange="window.location.href = '{{ route('deudas_recepcion.show', ['id' => $empresa->id_empresas_clientes]) }}?tipo=' + this.value"
                class="border-gray-300 rounded-md shadow-sm text-xl px-3 py-1 min-w-[155px]">
                <option value="contable" {{ $tipo === 'contable' ? 'selected' : '' }}>(Contables)</option>
                <option value="otros" {{ $tipo === 'otros' ? 'selected' : '' }}>(Otros)</option>
                <option value="ambas" {{ $tipo === 'ambas' ? 'selected' : '' }}>(Ambas)</option>
            </select>
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            {{-- Barra superior --}}
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <a href="{{ route('deudas_recepcion.index', $tipo ?? 'contable') }}"
                    class="inline-flex items-center px-3 py-1.5 rounded border text-sm hover:bg-gray-50">
                    ‹ Volver a la Lista
                </a>

                <div class="ml-auto flex gap-2">
                    <button type="button" onclick="toggleModal('modal-generar-pdf')"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded bg-red-500 text-white text-sm hover:bg-red-600">
                        <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                    </button>
                    <button type="button" onclick="abrirModalCotizacion({{ $empresa->id_empresas_clientes }})"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded bg-blue-500 text-white text-sm hover:bg-blue-600">
                        <i class="bi bi-plus-circle"></i> Nueva Cotización
                    </button>
                    <button type="button" onclick="toggleModal('modal-nueva-deuda')"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded bg-green-500 text-white text-sm hover:bg-green-600">
                        <i class="bi bi-plus-circle"></i> Nueva Deuda
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto bg-gray-50 shadow-lg rounded-lg">
                <table class="min-w-[1200px] table-auto text-sm" id="myTable-deuda-detalle">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-700">
                            <th class="px-3 py-2 border-b">N° COTIZACIÓN</th>
                            @if (($tipo ?? 'contable') === 'ambas')
                                <th class="px-3 py-2 border-b">TIPO COTIZACIÓN</th>
                            @endif
                            <th class="px-3 py-2 border-b">N° FACTURA</th>
                            <th class="px-3 py-2 border-b">DESCRIPCIÓN</th>
                            <th class="px-3 py-2 border-b">MES DECLARACIÓN</th>
                            <th class="px-3 py-2 border-b">TOTALES</th>
                            <th class="px-3 py-2 border-b text-center">PDF</th>
                            <th class="px-3 py-2 border-b !text-right">DEUDA TOTAL</th>
                            <th class="px-3 py-2 border-b !text-right">DEUDA PENDIENTE</th>
                            <th class="px-3 py-2 border-b">FECHA ÚLTIMO PAGO</th>
                            <th class="px-3 py-2 border-b">STATUS PAGO</th>
                            <th class="px-3 py-2 border-b">ULT. OBSERVACIÓN</th>
                            <th class="px-3 py-2 border-b">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($empresa->todasCotizaciones as $cotizacion)
                            @foreach ($cotizacion->deudas as $deuda)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 font-medium">
                                        {{ $cotizacion->nro_cotizacion }}
                                    </td>
                                    @if (($tipo ?? 'contable') === 'ambas')
                                        <td class="px-3 py-2">
                                            <span
                                                class="px-2 py-1 text-xs rounded-md {{ $cotizacion->tipo_cotizacion === 'contable' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                                {{ ucfirst($cotizacion->tipo_cotizacion) }}
                                            </span>
                                        </td>
                                    @endif
                                    <td class="px-3 py-2">
                                        @if (filled($deuda->nro_factura))
                                            <span
                                                class="font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                                {{ $deuda->nro_factura }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 text-xs font-medium">
                                                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $deuda->descripcion_servicio }}
                                    </td>
                                    <td class="px-3 py-2"
                                        data-order="{{ \Carbon\Carbon::parse($deuda->mes_declaracion)->format('Y-m') }}">
                                        @if ($deuda->tipo_declaracion === 'anual')
                                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full">
                                                Anual {{ $deuda->anio_declaracion }}
                                            </span>
                                        @else
                                            {{ ucfirst(\Carbon\Carbon::parse($deuda->mes_declaracion)->isoFormat('MMMM YYYY')) }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-col gap-1 text-xs font-semibold whitespace-nowrap">
                                            <span>Contabilidad: S/.
                                                {{ number_format($deuda->monto_contabilidad, 2, '.', ',') }}</span>
                                            <span>RR.HH: S/.
                                                {{ number_format($deuda->monto_recursos, 2, '.', ',') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if ($deuda->archivo_pdf)
                                            <a href="{{ asset($deuda->archivo_pdf) }}" target="_blank"
                                                class="text-red-500 hover:text-red-700" title="Ver PDF">
                                                <i class="bi bi-file-earmark-pdf-fill text-xl"></i>
                                            </a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 !text-right">
                                        S/. {{ number_format($deuda->monto_deuda, 2, '.', ',') }}
                                    </td>
                                    <td class="px-3 py-2 !text-right">
                                        S/. {{ number_format($deuda->monto_pendiente, 2, '.', ',') }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $deuda->pagos->max('fecha_pago') ? \Carbon\Carbon::parse($deuda->pagos->max('fecha_pago'))->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <span
                                            class="px-2 py-1 text-xs rounded font-medium
                                            {{ $deuda->estado == 0 ? 'bg-red-100 text-red-600' : ($deuda->estado == 1 ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600') }}">
                                            {{ $deuda->estado == 0 ? 'Pendiente' : ($deuda->estado == 1 ? 'Pagado' : 'Parcial') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $ultimaObservacion = $deuda->pagos
                                                ->sortByDesc('id_pago_deuda_recepcion')
                                                ->first()?->observacion;
                                        @endphp
                                        <span
                                            class="text-sm {{ $ultimaObservacion ? 'text-gray-700' : 'text-gray-400 italic' }}">
                                            {{ $ultimaObservacion ? Str::limit($ultimaObservacion, 20) : 'Sin observaciones' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                onclick="abrirModalDetallesPagos('{{ $deuda->id_deuda_recepcion }}')"
                                                class="flex items-center py-1 rounded-md tooltip text-blue-600 hover:text-blue-800">
                                                <span class="tooltip-text">Ver Pagos</span>
                                                <x-details-icon />
                                            </button>
                                            @if ($deuda->estado != 1)
                                                <button type="button"
                                                    onclick="abrirModalPago({
                                                        idDeuda: '{{ $deuda->id_deuda_recepcion }}',
                                                        cotizacion: '{{ addslashes($cotizacion->nro_cotizacion) }}',
                                                        factura: '{{ addslashes($deuda->nro_factura) }}',
                                                        descripcion: '{{ addslashes($deuda->descripcion_servicio) }}',
                                                        montoDeuda: '{{ $deuda->monto_deuda }}',
                                                        montoPendiente: '{{ $deuda->monto_pendiente }}',
                                                        mesDeclaracion: '{{ $meses[\Carbon\Carbon::parse($deuda->mes_declaracion)->format('F')] }} - {{ \Carbon\Carbon::parse($deuda->mes_declaracion)->format('Y') }}'
                                                    })"
                                                    class="flex items-center py-1 rounded-md tooltip text-green-600 hover:text-green-800">
                                                    <span class="tooltip-text">Pagar</span>
                                                    <x-tax-icon />
                                                </button>
                                            @endif
                                            <button type="button"
                                                onclick="abrirModalEditarDeuda(
                                                '{{ $deuda->id_deuda_recepcion }}',
                                                '{{ addslashes($deuda->nro_factura) }}',
                                                '{{ addslashes($deuda->descripcion_servicio) }}',
                                                '{{ $deuda->monto_deuda }}',
                                                '{{ \Carbon\Carbon::parse($deuda->mes_declaracion)->format('Y-m') }}',
                                                '{{ $deuda->tipo_declaracion ?? 'mensual' }}',
                                                '{{ $deuda->anio_declaracion }}',
                                                '{{ $deuda->nro_trabajadores }}',
                                                '{{ $deuda->precio_trabajador }}',
                                                '{{ $deuda->monto_contabilidad }}',
                                                '{{ $deuda->sin_movimiento }}',
                                                '{{ $deuda->archivo_pdf }}',
                                                '{{ $cotizacion->contratoPrincipal && $cotizacion->contratoPrincipal->archivo_cotizacion ? asset($cotizacion->contratoPrincipal->archivo_cotizacion) : '' }}'
                                                )"
                                                class="flex items-center py-1 rounded-md tooltip text-yellow-600 hover:text-yellow-800 {{ $deuda->estado == 1 && !$isAdmin ? 'hidden' : '' }}">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </button>
                                            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->area->nombre == 'ADMINISTRACION')
                                                <form
                                                    action="{{ route('deudas_recepcion.destroy', $deuda->id_deuda_recepcion) }}"
                                                    method="POST" style="display:inline;"
                                                    onsubmit="return confirm('¿Seguro que deseas eliminar esta deuda y sus pagos?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="flex items-center py-1 rounded-md tooltip text-red-600 hover:text-red-800">
                                                        <span class="tooltip-text">Eliminar</span>
                                                        <x-delete-icon />
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold">
                        <tr>
                            <td class="px-3 py-2 border-t"></td>
                            @if (($tipo ?? 'contable') === 'ambas')
                                <td class="px-3 py-2 border-t"></td>
                            @endif
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t !text-right">Total:</td>
                            <td class="px-3 py-2 border-t !text-right">S/.
                                {{ number_format($totalDeudaPendiente, 2, '.', ',') }}</td>
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t"></td>
                            <td class="px-3 py-2 border-t"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>

{{-- MODAL: Registrar pago --}}
<x-modal-show id="modal-pago-deuda" title="Registrar pago">
    <form method="POST" action="{{ route('pago_deudas_recepcion.store') }}" class="space-y-6" autocomplete="off"
        onsubmit="return handleFormSubmit(event)">
        @csrf
        <input type="hidden" name="id_deuda" id="pay-id-deuda">
        <input type="hidden" name="tipo_vista" value="{{ $tipo ?? 'contable' }}">

        <div class="bg-gray-50 p-3 rounded-md text-sm space-y-1">
            <div class="grid grid-cols-2 gap-4">
                <div><strong>N° Cotización:</strong> <span id="pay-cotizacion"></span></div>
                <div><strong>N° Factura:</strong> <span id="pay-factura"></span></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><strong>Descripción:</strong> <span id="pay-descripcion"></span></div>
                <div><strong>Mes Declaración:</strong> <span id="pay-mes-declaracion"></span></div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Deuda Total')" />
                <x-text-input id="pay-deuda-total" type="text" class="mt-1 block w-full bg-gray-200" disabled />
            </div>
            <div>
                <x-input-label :value="__('Deuda Pendiente')" />
                <x-text-input id="pay-deuda-pendiente" type="text" class="mt-1 block w-full bg-gray-200"
                    disabled />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Fecha de pago')" />
                <x-text-input name="fecha_pago" id="pay-fecha-pago" type="date" class="mt-1 block w-full"
                    required />
            </div>
            <div>
                <x-input-label :value="__('Monto a pagar')" />
                <x-text-input name="monto" id="pay-monto" type="number" step="0.01" min="0"
                    class="mt-1 block w-full" required />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Método de pago')" />
                <select name="metodo_pago" id="pay-metodo-pago"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required
                    onchange="toggleNroOperacion()">
                    <option value="">Seleccionar método</option>
                    <option value="Interbank">Interbank</option>
                    <option value="BCP">BCP</option>
                    <option value="BBVA">BBVA</option>
                    <option value="Banbif">Banbif</option>
                    <option value="Scotiabank">Scotiabank</option>
                    <option value="Yape">Yape</option>
                    <option value="Plin">Plin</option>
                    <option value="Efectivo">Efectivo</option>
                </select>
            </div>
            <div>
                <x-input-label :value="__('N° de operación')" />
                <x-text-input name="nro_operacion" id="pay-nro-operacion" type="text"
                    class="mt-1 block w-full" />
            </div>
        </div>

        <div>
            <x-input-label :value="__('Observaciones')" />
            <x-text-input name="observaciones" id="pay-observaciones" type="text" class="mt-1 block w-full" />
        </div>

        <div class="flex justify-end gap-3">
            <x-secondary-button type="button" onclick="toggleModal('modal-pago-deuda')">Cancelar</x-secondary-button>
            <x-primary-button class="px-6 py-2">Guardar pago</x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- MODAL: Ver detalles de pagos --}}
<x-modal-show id="modal-detalles-pagos" title="Historial de Pagos">
    <div id="pagos-contenido" class="space-y-4">
        <p class="text-center text-gray-400 italic">No hay pagos registrados</p>
    </div>

    <div class="flex justify-end mt-4">
        <x-secondary-button onclick="toggleModal('modal-detalles-pagos')">
            Cerrar
        </x-secondary-button>
    </div>
</x-modal-show>

{{-- MODAL: Editar pago --}}
<x-modal-show id="modal-editar-pago" title="Editar pago">
    <div id="error-message-pago" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded"></div>
    <form method="POST" id="form-editar-pago" class="space-y-4" onsubmit="return submitEditarPago(event)">
        @csrf
        @method('PUT')

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Fecha de pago')" />
                <x-text-input name="fecha_pago" id="edit-pay-fecha-pago" type="date" class="mt-1 block w-full"
                    required />
            </div>
            <div>
                <x-input-label :value="__('Monto')" />
                <x-text-input name="monto" id="edit-pay-monto" type="number" step="0.01" min="0"
                    class="mt-1 block w-full" required />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-input-label :value="__('Método de pago')" />
                <select name="metodo_pago" id="edit-pay-metodo-pago"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Seleccionar método</option>
                    <option value="Interbank">Interbank</option>
                    <option value="BCP">BCP</option>
                    <option value="BBVA">BBVA</option>
                    <option value="Banbif">Banbif</option>
                    <option value="Scotiabank">Scotiabank</option>
                    <option value="Yape">Yape</option>
                    <option value="Plin">Plin</option>
                    <option value="Efectivo">Efectivo</option>
                </select>
            </div>
            <div>
                <x-input-label :value="__('N° de operación')" />
                <x-text-input name="nro_operacion" id="edit-pay-nro-operacion" type="text"
                    class="mt-1 block w-full" />
            </div>
        </div>

        <div>
            <x-input-label :value="__('Observaciones')" />
            <x-text-input name="observaciones" id="edit-pay-observaciones" type="text"
                class="mt-1 block w-full" />
        </div>

        <div class="flex justify-end gap-3">
            <x-secondary-button type="button"
                onclick="toggleModal('modal-editar-pago')">Cancelar</x-secondary-button>
            <x-primary-button class="px-6 py-2">Actualizar pago</x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- MODAL: Registrar nueva deuda --}}
<x-modal-show id="modal-nueva-deuda" title="Registrar nueva deuda">
    <form method="POST" action="{{ route('deudas_recepcion.store') }}" class="space-y-4"
        enctype="multipart/form-data" onsubmit="return handleFormSubmit(event)">
        @csrf
        <div>
            <x-input-label :value="__('N° Cotización')" />
            <select name="id_cotizacion" id="create-id-cotizacion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required onchange="actualizarLinkCotizacion()">
                <option value="" data-archivo="">Seleccionar cotización...</option>
                @php
                    $ultimaCotizacion = $empresa->todasCotizaciones->sortByDesc('id_cotizacion')->first();
                @endphp
                @foreach ($empresa->todasCotizaciones as $cotizacion)
                    @php
                        $archivoCot = $cotizacion->contratoPrincipal ? $cotizacion->contratoPrincipal->archivo_cotizacion : '';
                    @endphp
                    <option value="{{ $cotizacion->id_cotizacion }}"
                        data-archivo="{{ $archivoCot ? asset($archivoCot) : '' }}"
                        {{ $ultimaCotizacion && $cotizacion->id_cotizacion == $ultimaCotizacion->id_cotizacion ? 'selected' : '' }}>
                        {{ $cotizacion->nro_cotizacion }}</option>
                @endforeach
            </select>
            @if(($tipo ?? 'contable') === 'contable')
            <div id="create-link-cotizacion" class="mt-2 hidden">
                <a href="#" id="create-link-cotizacion-a" target="_blank" class="text-blue-600 hover:underline text-sm flex items-center gap-1">
                    <i class="bi bi-file-earmark-pdf-fill text-red-500"></i> Ver Archivo de Cotización
                </a>
            </div>
            @endif
        </div>

        {{-- TIPO DE DECLARACIÓN --}}
        <div>
            <x-input-label :value="__('Tipo de Declaración')" />
            <select name="tipo_declaracion" id="tipo_declaracion"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                onchange="toggleTipoDeclaracion(this.value)" required>
                <option value="mensual">Mensual</option>
                <option value="anual">Anual (Declaración Anual)</option>
            </select>
        </div>

        {{-- MES (solo mensual) --}}
        <div id="campo-mes-declaracion">
            <x-input-label :value="__('Mes de Declaración')" />
            <input type="month" name="mes_declaracion" id="create-mes-declaracion"
                onchange="fetchMontoContabilidadAnterior(this.value); autoCompletarDescripcionContable(this.value);"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        {{-- AÑO (solo anual) --}}
        <div id="campo-anio-declaracion" class="hidden">
            <x-input-label :value="__('Año de Declaración')" />
            <select name="anio_declaracion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @php
                    $anioActual = now()->year;
                @endphp
                @for ($a = $anioActual; $a >= $anioActual - 5; $a--)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endfor
            </select>
        </div>

        <div>
            <x-input-label :value="__('Descripción del Servicio')" />
            <x-text-input name="descripcion_servicio" id="create-descripcion-servicio" type="text"
                class="mt-1 block w-full" placeholder="Descripción del servicio"
                oninput="this.value = this.value.toUpperCase()" />
        </div>

        @if(($tipo ?? 'contable') !== 'otros')
        <div id="create-recursos-contabilidad-container" class="grid grid-cols-2 gap-4">
            <div class="p-3 border rounded-md bg-gray-50 relative">
                <h4 class="text-sm font-bold text-gray-700 mb-2 border-b pb-1">Recursos Humanos</h4>
                <div>
                    <x-input-label :value="__('N° Trabajadores')" />
                    @if ($blockRRHH)
                        <x-text-input name="nro_trabajadores" id="create-nro-trabajadores" type="number"
                            min="0" class="mt-1 block w-full" oninput="calcularMontos()" readonly
                            tabindex="-1" />
                    @else
                        <x-text-input name="nro_trabajadores" id="create-nro-trabajadores" type="number"
                            min="0" class="mt-1 block w-full" oninput="calcularMontos()" />
                    @endif
                </div>
                <div class="mt-2">
                    <x-input-label :value="__('Precio por Trabajador')" />
                    @if ($blockRRHH)
                        <x-text-input name="precio_trabajador" id="create-precio-trabajador" type="number"
                            step="0.01" min="0" class="mt-1 block w-full" oninput="calcularMontos()"
                            readonly tabindex="-1" />
                    @else
                        <x-text-input name="precio_trabajador" id="create-precio-trabajador" type="number"
                            step="0.01" min="0" class="mt-1 block w-full" oninput="calcularMontos()" />
                    @endif
                </div>
                <div class="mt-2">
                    <x-input-label :value="__('Monto Recursos')" />
                    <x-text-input id="create-monto-recursos" type="text" class="mt-1 block w-full bg-gray-200"
                        readonly />
                </div>
            </div>

            <div class="p-3 border rounded-md bg-gray-50 relative">
                <h4 class="text-sm font-bold text-gray-700 mb-2 border-b pb-1">Contabilidad</h4>
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" name="sin_movimiento" id="create-sin-movimiento" value="1"
                        onchange="toggleSinMovimiento()"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                        @if ($blockContabilidad) onclick="return false;" tabindex="-1" @endif>
                    <label for="create-sin-movimiento" class="text-sm text-gray-700">Sin movimiento</label>
                </div>
                <div>
                    <x-input-label :value="__('Monto Contabilidad')" />
                    @if ($blockContabilidad)
                        <x-text-input name="monto_contabilidad" id="create-monto-contabilidad" type="number"
                            step="0.01" min="0" class="mt-1 block w-full" oninput="calcularMontos()"
                            readonly tabindex="-1" />
                    @else
                        <x-text-input name="monto_contabilidad" id="create-monto-contabilidad" type="number"
                            step="0.01" min="0" class="mt-1 block w-full" oninput="calcularMontos()" />
                    @endif
                    <small id="create-monto-contabilidad-help" class="text-xs text-blue-500 hidden">Auto-completado
                        del mes anterior</small>
                </div>
            </div>
        </div>
        @endif

        <div>
            <x-input-label :value="__('Monto Deuda Total')" class="text-lg font-bold" />
            @if(($tipo ?? 'contable') !== 'otros')
                <x-text-input name="monto_deuda" id="create-monto-deuda" type="number" step="0.01" min="0"
                    class="mt-1 block w-full text-lg font-bold bg-amber-50" readonly />
            @else
                <x-text-input name="monto_deuda" id="create-monto-deuda" type="number" step="0.01" min="0"
                    class="mt-1 block w-full text-lg font-bold" required />
            @endif
        </div>

        <div>
            <x-input-label :value="__('N° Factura')" />
            <x-text-input name="nro_factura" type="text" class="mt-1 block w-full"
                placeholder="Número de factura" />
        </div>

        <div>
            <x-input-label :value="__('Archivo PDF')" />
            <input type="file" name="archivo_pdf" id="create-archivo-pdf" accept=".pdf"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                onchange="previewPdf(this, 'create-pdf-preview')">
            <div id="create-pdf-preview"
                class="mt-2 hidden w-full h-[250px] border border-gray-300 rounded-md overflow-hidden">
                <iframe src="" class="w-full h-full" frameborder="0"></iframe>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <x-secondary-button type="button"
                onclick="toggleModal('modal-nueva-deuda')">Cancelar</x-secondary-button>
            <x-primary-button class="ml-2">Registrar Deuda</x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- MODAL: Editar deuda --}}
<x-modal-show id="modal-editar-deuda" title="Editar deuda">
    <div id="error-message-deuda" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded"></div>
    <form method="POST" id="form-editar-deuda" class="space-y-4" enctype="multipart/form-data"
        onsubmit="return submitEditarDeuda(event)">
        @csrf
        @method('PUT')
        <input type="hidden" name="id_deuda" id="edit-id-deuda">

        @if(($tipo ?? 'contable') === 'contable')
        <div id="edit-link-cotizacion" class="mt-2 hidden mb-4 bg-blue-50 p-2 rounded border border-blue-200 inline-block">
            <a href="#" id="edit-link-cotizacion-a" target="_blank" class="text-blue-700 hover:underline text-sm font-semibold flex items-center gap-1">
                <i class="bi bi-file-earmark-pdf-fill text-red-500 text-lg"></i> Ver Archivo de Cotización
            </a>
        </div>
        @endif

        {{-- TIPO DE DECLARACIÓN --}}
        <div>
            <x-input-label :value="__('Tipo de Declaración')" />
            <select name="tipo_declaracion" id="edit-tipo-declaracion"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                onchange="toggleTipoDeclaracionEdit(this.value)" required>
                <option value="mensual">Mensual</option>
                <option value="anual">Anual (Declaración Anual)</option>
            </select>
        </div>

        {{-- MES (solo mensual) --}}
        <div id="edit-campo-mes-declaracion">
            <x-input-label :value="__('Mes de Declaración')" />
            <input type="month" name="mes_declaracion" id="edit-mes-declaracion"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        {{-- AÑO (solo anual) --}}
        <div id="edit-campo-anio-declaracion" class="hidden">
            <x-input-label :value="__('Año de Declaración')" />
            <select name="anio_declaracion" id="edit-anio-declaracion"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @php $anioActual = now()->year; @endphp
                @for ($a = $anioActual; $a >= $anioActual - 5; $a--)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endfor
            </select>
        </div>

        <div>
            <x-input-label :value="__('Descripción del Servicio')" />
            <x-text-input name="descripcion_servicio" id="edit-descripcion-servicio" type="text"
                class="mt-1 block w-full" placeholder="Descripción del servicio"
                oninput="this.value = this.value.toUpperCase()" />
        </div>

        <div id="edit-recursos-contabilidad-container" class="grid grid-cols-2 gap-4">
            <div class="p-3 border rounded-md bg-gray-50 relative">
                <h4 class="text-sm font-bold text-gray-700 mb-2 border-b pb-1">Recursos Humanos</h4>
                <div>
                    <x-input-label :value="__('N° Trabajadores')" />
                    @if ($blockRRHH)
                        <x-text-input name="nro_trabajadores" id="edit-nro-trabajadores" type="number"
                            min="0" class="mt-1 block w-full" oninput="calcularMontosEdit()" readonly
                            tabindex="-1" />
                    @else
                        <x-text-input name="nro_trabajadores" id="edit-nro-trabajadores" type="number"
                            min="0" class="mt-1 block w-full" oninput="calcularMontosEdit()" />
                    @endif
                </div>
                <div class="mt-2">
                    <x-input-label :value="__('Precio por Trabajador')" />
                    @if ($blockRRHH)
                        <x-text-input name="precio_trabajador" id="edit-precio-trabajador" type="number"
                            step="0.01" min="0" class="mt-1 block w-full" oninput="calcularMontosEdit()"
                            readonly tabindex="-1" />
                    @else
                        <x-text-input name="precio_trabajador" id="edit-precio-trabajador" type="number"
                            step="0.01" min="0" class="mt-1 block w-full"
                            oninput="calcularMontosEdit()" />
                    @endif
                </div>
                <div class="mt-2">
                    <x-input-label :value="__('Monto Recursos')" />
                    <x-text-input id="edit-monto-recursos" type="text" class="mt-1 block w-full bg-gray-200"
                        readonly />
                </div>
            </div>

            <div class="p-3 border rounded-md bg-gray-50 relative">
                <h4 class="text-sm font-bold text-gray-700 mb-2 border-b pb-1">Contabilidad</h4>
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" name="sin_movimiento" id="edit-sin-movimiento" value="1"
                        onchange="toggleSinMovimientoEdit()"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                        @if ($blockContabilidad) onclick="return false;" tabindex="-1" @endif>
                    <label for="edit-sin-movimiento" class="text-sm text-gray-700">Sin movimiento</label>
                </div>
                <div>
                    <x-input-label :value="__('Monto Contabilidad')" />
                    @if ($blockContabilidad)
                        <x-text-input name="monto_contabilidad" id="edit-monto-contabilidad" type="number"
                            step="0.01" min="0" class="mt-1 block w-full" oninput="calcularMontosEdit()"
                            readonly tabindex="-1" />
                    @else
                        <x-text-input name="monto_contabilidad" id="edit-monto-contabilidad" type="number"
                            step="0.01" min="0" class="mt-1 block w-full"
                            oninput="calcularMontosEdit()" />
                    @endif
                </div>
            </div>
        </div>

        <div>
            <x-input-label :value="__('Monto Deuda Total')" class="text-lg font-bold" />
            <x-text-input id="edit-monto-deuda-display" type="text"
                class="mt-1 block w-full text-lg font-bold bg-amber-50" readonly />
            <input type="hidden" name="monto_deuda" id="edit-monto-deuda" />
        </div>

        <div>
            <x-input-label :value="__('N° Factura')" />
            <x-text-input name="nro_factura" id="edit-nro-factura" type="text" class="mt-1 block w-full"
                placeholder="Número de factura" />
        </div>

        <div>
            <x-input-label :value="__('Archivo PDF Actual')" />
            <div id="edit-pdf-current" class="mb-2 hidden">
                <a href="#" id="edit-pdf-link" target="_blank"
                    class="text-blue-500 hover:underline inline-flex items-center gap-1"><i
                        class="bi bi-file-earmark-pdf-fill text-red-500"></i> Ver PDF Actual</a>
            </div>
            <x-input-label :value="__('Reemplazar Archivo PDF')" />
            <input type="file" name="archivo_pdf" id="edit-archivo-pdf" accept=".pdf"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                onchange="previewPdf(this, 'edit-pdf-preview')">
            <div id="edit-pdf-preview"
                class="mt-2 hidden w-full h-[250px] border border-gray-300 rounded-md overflow-hidden">
                <iframe src="" class="w-full h-full" frameborder="0"></iframe>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <x-secondary-button type="button"
                onclick="toggleModal('modal-editar-deuda')">Cancelar</x-secondary-button>
            <x-primary-button class="ml-2">Actualizar Deuda</x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- MODAL: Registrar nueva cotización --}}
<x-modal-show id="modal-cotizacion" title="Registrar nueva cotización">
    <form method="POST" action="{{ route('cotizacion_recepcion.store') }}" class="space-y-4"
        onsubmit="return handleFormSubmit(event)">
        @csrf
        <input type="hidden" name="id_empresa_cliente" id="empresa-id">

        <div>
            <x-input-label :value="__('N° Cotización')" />
            <x-text-input name="nro_cotizacion" type="text" class="mt-1 block w-full"
                placeholder="Número de cotización" />
        </div>

        <div>
            <x-input-label :value="__('Tipo Cotización')" />
            <select name="tipo_cotizacion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="">Seleccionar tipo...</option>
                <option value="contable" selected>Contable</option>
                <option value="otros">Otros</option>
            </select>
        </div>

        <div class="flex justify-end pt-4">
            <x-secondary-button type="button" onclick="toggleModal('modal-cotizacion')">Cancelar</x-secondary-button>
            <x-primary-button class="ml-2">Crear Cotización</x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- MODAL: Generar PDF --}}
<x-modal-show id="modal-generar-pdf" title="Generar PDF de Deudas">
    <form method="POST" action="{{ route('deudas_recepcion.pdf', $empresa->id_empresas_clientes) }}"
        class="space-y-4" target="_blank">
        @csrf

        <div>
            <x-input-label :value="__('Status de Pago')" />
            <select name="status_pago" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="">Seleccionar status...</option>
                <option value="pagado">Pagado</option>
                <option value="pendiente-parcial">Pendiente/Parcial</option>
                <option value="ambos">Ambos</option>
            </select>
        </div>

        <div>
            <x-input-label :value="__('Tipo de Cotización')" />
            <select name="tipo_cotizacion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="">Seleccionar tipo...</option>
                <option value="contable" {{ ($tipo ?? 'contable') === 'contable' ? 'selected' : '' }}>Contable
                </option>
                <option value="otros" {{ ($tipo ?? 'contable') === 'otros' ? 'selected' : '' }}>Otros</option>
                <option value="ambos" {{ ($tipo ?? 'contable') === 'ambas' ? 'selected' : '' }}>Ambas</option>
            </select>
        </div>

        <div>
            <x-input-label :value="__('Ordenar por')" />
            <select name="ordenar_por" id="ordenar_por"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" onchange="toggleFiltrosPDF(this.value)"
                required>
                <option value="">Seleccionar orden...</option>
                <option value="mes_declaracion">Mes Declaración</option>
                <option value="anual">Declaraciones Anuales</option>
                <option value="nro_cotizacion">N° Cotización</option>
            </select>
        </div>

        <div id="filtro-anio-anual" class="hidden mt-3">
            <x-input-label :value="__('Filtrar Año (Anual)')" />
            <select name="anio_declaracion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Todos</option>
                @php $anioActual = now()->year; @endphp
                @for ($a = $anioActual; $a >= $anioActual - 5; $a--)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endfor
            </select>
        </div>

        {{-- Rango de fechas — solo visible cuando se ordena por mes --}}
        <div id="filtros-rango-pdf" class="hidden space-y-3 p-3 bg-gray-50 rounded-md border">
            <p class="text-xs text-gray-500">
                Deja vacío para incluir todos. Las declaraciones anuales también se incluirán si caen dentro del rango.
            </p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label :value="__('Desde (mes-año)')" />
                    <input type="month" name="fecha_desde"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <x-input-label :value="__('Hasta (mes-año)')" />
                    <input type="month" name="fecha_hasta"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
            </div>
            <p class="text-xs text-blue-600">
                El reporte se ordenará de más reciente (Hasta) a más antiguo (Desde).
            </p>
        </div>

        <div class="flex justify-end pt-4">
            <x-secondary-button type="button"
                onclick="toggleModal('modal-generar-pdf')">Cancelar</x-secondary-button>
            <x-primary-button class="ml-2">Generar PDF</x-primary-button>
        </div>
    </form>
</x-modal-show>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ordenarPor = document.getElementById('ordenar_por');
        const filtrosOrdenContainer = document.getElementById('filtros-orden-container');

        if (ordenarPor && filtrosOrdenContainer) {
            ordenarPor.addEventListener('change', function() {
                console.log("Ordenar por: ", this.value);
                if (this.value === 'mes_declaracion') {
                    filtrosOrdenContainer.classList.remove('hidden');
                    filtrosOrdenContainer.style.display = 'block';
                } else {
                    filtrosOrdenContainer.classList.add('hidden');
                    filtrosOrdenContainer.style.display = 'none';
                }
            });
        }
        
        // Initialize link for cotizacion
        if (typeof actualizarLinkCotizacion === 'function') {
            actualizarLinkCotizacion();
        }
    });
</script>

<script>
    let isSubmitting = false;

    function abrirModalDetallesPagos(idDeuda) {
        const cont = document.getElementById('pagos-contenido');
        cont.innerHTML = `<p class="text-center text-gray-400 italic">Cargando detalles...</p>`;
        toggleModal('modal-detalles-pagos');

        fetch(`/deudas-recepcion/${idDeuda}/pagos`)
            .then(res => res.json())
            .then(data => {
                if (!data.pagos || data.pagos.length === 0) {
                    cont.innerHTML = '<p class="text-center text-gray-400 italic">No se registran pagos.</p>';
                } else {
                    let html = `
                        <table class="w-full text-sm border">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-2 py-1 border">Fecha de pago</th>
                                    <th class="px-2 py-1 border text-right">Monto</th>
                                    <th class="px-2 py-1 border">Método</th>
                                    <th class="px-2 py-1 border">N° Operación</th>
                                    <th class="px-2 py-1 border">Observación</th>
                                    <th class="px-2 py-1 border">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.pagos.map(p => `
                                    <tr>
                                        <td class="px-2 py-1 border text-center">${p.fecha}</td>
                                        <td class="px-2 py-1 border text-right">S/. ${p.monto}</td>
                                        <td class="px-2 py-1 border text-center">${p.metodo}</td>
                                        <td class="px-2 py-1 border text-center">${p.nro_operacion}</td>
                                        <td class="px-2 py-1 border text-center">${p.observacion}</td>
                                        <td class="px-2 py-1 border text-center">
                                            <button type="button" onclick="abrirModalEditarPago('${p.id}', '${p.fecha_raw}', '${p.monto_raw}', '${p.metodo}', '${p.nro_operacion}', '${p.observacion}')"
                                                class="text-yellow-600 hover:text-yellow-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                    cont.innerHTML = html;
                }
            })
            .catch(err => {
                cont.innerHTML = `<p class="text-center text-red-500">Error al cargar los detalles.</p>`;
                console.error(err);
            });
    }

    function toggleNroOperacion() {
        const metodoPago = document.getElementById('pay-metodo-pago').value;
        const nroOperacion = document.getElementById('pay-nro-operacion');

        if (metodoPago === 'Efectivo') {
            nroOperacion.value = '';
            nroOperacion.setAttribute('readonly', true);
            nroOperacion.classList.add('bg-gray-200');
        } else {
            nroOperacion.value = '';
            nroOperacion.removeAttribute('readonly');
            nroOperacion.classList.remove('bg-gray-200');
        }
    }

    function abrirModalPago(payload) {
        document.getElementById('pay-id-deuda').value = payload.idDeuda;
        document.getElementById('pay-cotizacion').textContent = payload.cotizacion;
        document.getElementById('pay-factura').textContent = payload.factura;
        document.getElementById('pay-descripcion').textContent = payload.descripcion;
        document.getElementById('pay-mes-declaracion').textContent = payload.mesDeclaracion;
        document.getElementById('pay-deuda-total').value = 'S/. ' + parseFloat(payload.montoDeuda).toLocaleString(
            'en-US', {
                minimumFractionDigits: 2
            });
        document.getElementById('pay-deuda-pendiente').value = 'S/. ' + parseFloat(payload.montoPendiente)
            .toLocaleString('en-US', {
                minimumFractionDigits: 2
            });

        const hoy = new Date();
        const hoyStr =
            `${hoy.getFullYear()}-${String(hoy.getMonth()+1).padStart(2,'0')}-${String(hoy.getDate()).padStart(2,'0')}`;
        document.getElementById('pay-fecha-pago').value = hoyStr;
        document.getElementById('pay-monto').value = payload.montoPendiente;

        // Reset form fields
        document.getElementById('pay-metodo-pago').value = '';
        document.getElementById('pay-nro-operacion').value = '';
        document.getElementById('pay-nro-operacion').removeAttribute('readonly');
        document.getElementById('pay-nro-operacion').classList.remove('bg-gray-200');

        toggleModal('modal-pago-deuda');
    }

    function abrirModalCotizacion(empresaId) {
        document.getElementById('empresa-id').value = empresaId;
        toggleModal('modal-cotizacion');
    }

    function abrirModalEditarDeuda(idDeuda, nroFactura, descripcion, montoDeuda, mesDeclaracion, tipoDeclaracion,
        anioDeclaracion, nroTrabajadores, precioTrabajador, montoContabilidad, sinMovimiento, archivoPdf, archivoCotizacion) {
        document.getElementById('edit-id-deuda').value = idDeuda;
        document.getElementById('edit-nro-factura').value = nroFactura;
        document.getElementById('edit-descripcion-servicio').value = descripcion;
        document.getElementById('edit-monto-deuda-display').value = 'S/. ' + parseFloat(montoDeuda).toFixed(2);
        document.getElementById('edit-monto-deuda').value = montoDeuda;
        document.getElementById('edit-tipo-declaracion').value = tipoDeclaracion || 'mensual';
        document.getElementById('edit-nro-trabajadores').value = nroTrabajadores || '';
        document.getElementById('edit-precio-trabajador').value = precioTrabajador || '';
        document.getElementById('edit-monto-contabilidad').value = montoContabilidad || '';

        let sinMov = document.getElementById('edit-sin-movimiento');
        sinMov.checked = (sinMovimiento == '1');
        
        const editLinkContainer = document.getElementById('edit-link-cotizacion');
        const editLinkA = document.getElementById('edit-link-cotizacion-a');
        if (editLinkContainer && editLinkA) {
            if (archivoCotizacion) {
                editLinkA.href = archivoCotizacion;
                editLinkContainer.classList.remove('hidden');
            } else {
                editLinkA.href = '#';
                editLinkContainer.classList.add('hidden');
            }
        }

        let pdfCurrent = document.getElementById('edit-pdf-current');
        let pdfLink = document.getElementById('edit-pdf-link');
        if (archivoPdf) {
            pdfCurrent.classList.remove('hidden');
            pdfLink.href = '/' + archivoPdf;
        } else {
            pdfCurrent.classList.add('hidden');
            pdfLink.href = '#';
        }

        calcularMontosEdit();
        toggleSinMovimientoEdit(true);

        // Mostrar/ocultar campos según tipo
        toggleTipoDeclaracionEdit(tipoDeclaracion || 'mensual');

        if (tipoDeclaracion === 'anual') {
            document.getElementById('edit-anio-declaracion').value = anioDeclaracion;
        } else {
            document.getElementById('edit-mes-declaracion').value = mesDeclaracion;
        }

        const errorDiv = document.getElementById('error-message-deuda');
        if (errorDiv) errorDiv.classList.add('hidden');

        document.getElementById('form-editar-deuda').action = `/deudas_recepcion/${idDeuda}`;
        toggleModal('modal-editar-deuda');
    }

    function toggleTipoDeclaracionEdit(tipo) {
        const campoMes = document.getElementById('edit-campo-mes-declaracion');
        const campoAnio = document.getElementById('edit-campo-anio-declaracion');
        const container = document.getElementById('edit-recursos-contabilidad-container');
        const montoDeudaDisplay = document.getElementById('edit-monto-deuda-display');
        const montoDeuda = document.getElementById('edit-monto-deuda');

        if (tipo === 'anual') {
            campoMes.classList.add('hidden');
            campoAnio.classList.remove('hidden');
            container.classList.add('hidden');
            montoDeudaDisplay.removeAttribute('readonly');
            montoDeudaDisplay.classList.remove('bg-amber-50');
            montoDeudaDisplay.type = 'number';
            montoDeudaDisplay.step = '0.01';
            montoDeudaDisplay.oninput = function() {
                montoDeuda.value = this.value;
            };
            if (montoDeudaDisplay.value.startsWith('S/. ')) {
                montoDeudaDisplay.value = montoDeudaDisplay.value.replace('S/. ', '').trim();
            }
        } else {
            campoMes.classList.remove('hidden');
            campoAnio.classList.add('hidden');
            container.classList.remove('hidden');
            montoDeudaDisplay.setAttribute('readonly', 'true');
            montoDeudaDisplay.classList.add('bg-amber-50');
            montoDeudaDisplay.type = 'text';
            montoDeudaDisplay.oninput = null;
            calcularMontosEdit();
        }
    }

    function abrirModalEditarPago(idPago, fecha, monto, metodo, nroOperacion, observacion) {
        document.getElementById('edit-pay-fecha-pago').value = fecha;
        document.getElementById('edit-pay-monto').value = monto;
        document.getElementById('edit-pay-metodo-pago').value = metodo;
        document.getElementById('edit-pay-nro-operacion').value = nroOperacion === 'N/A' ? '' : nroOperacion;
        document.getElementById('edit-pay-observaciones').value = observacion === '—' ? '' : observacion;

        const form = document.getElementById('form-editar-pago');
        form.action = `/pago_deudas_recepcion/${idPago}`;

        toggleModal('modal-detalles-pagos');
        toggleModal('modal-editar-pago');
    }

    function submitEditarPago(event) {
        event.preventDefault();

        if (isSubmitting) return false;
        isSubmitting = true;

        const form = document.getElementById('form-editar-pago');
        const formData = new FormData(form);
        const errorDiv = document.getElementById('error-message-pago');
        errorDiv.classList.add('hidden');
        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Error al actualizar');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                errorDiv.textContent = error.message || 'Error al actualizar el pago';
                errorDiv.classList.remove('hidden');
                isSubmitting = false;
            });
        return false;
    }

    function submitEditarDeuda(event) {
        event.preventDefault();

        if (isSubmitting) return false;
        isSubmitting = true;

        const form = document.getElementById('form-editar-deuda');
        const formData = new FormData(form);
        const errorDiv = document.getElementById('error-message-deuda');
        errorDiv.classList.add('hidden');

        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else if (data.error) {
                    errorDiv.textContent = data.error;
                    errorDiv.classList.remove('hidden');
                    isSubmitting = false;
                }
            })
            .catch(error => {
                errorDiv.textContent = 'Error al actualizar la deuda';
                errorDiv.classList.remove('hidden');
                isSubmitting = false;
            });
        return false;
    }

    function handleFormSubmit(event) {
        if (isSubmitting) {
            event.preventDefault();
            return false;
        }
        isSubmitting = true;
        setTimeout(() => {
            isSubmitting = false;
        }, 3000);
        return true;
    }

    function actualizarLinkCotizacion() {
        const select = document.getElementById('create-id-cotizacion');
        const container = document.getElementById('create-link-cotizacion');
        const link = document.getElementById('create-link-cotizacion-a');
        
        if (!select || !container || !link) return;
        
        const option = select.options[select.selectedIndex];
        const archivo = option.getAttribute('data-archivo');
        
        if (archivo) {
            link.href = archivo;
            container.classList.remove('hidden');
        } else {
            link.href = '#';
            container.classList.add('hidden');
        }
    }

    function calcularMontos() {
        if (document.getElementById('tipo_declaracion').value === 'anual') return;
        if (!document.getElementById('create-nro-trabajadores')) return;
        let nro = parseFloat(document.getElementById('create-nro-trabajadores').value) || 0;
        let precio = parseFloat(document.getElementById('create-precio-trabajador').value) || 0;
        let contabilidad = parseFloat(document.getElementById('create-monto-contabilidad').value) || 0;

        let recursos = nro * precio;
        document.getElementById('create-monto-recursos').value = recursos.toFixed(2);

        let total = recursos + contabilidad;
        document.getElementById('create-monto-deuda').value = total.toFixed(2);
    }

    function calcularMontosEdit() {
        if (document.getElementById('edit-tipo-declaracion').value === 'anual') return;
        let nro = parseFloat(document.getElementById('edit-nro-trabajadores').value) || 0;
        let precio = parseFloat(document.getElementById('edit-precio-trabajador').value) || 0;
        let contabilidad = parseFloat(document.getElementById('edit-monto-contabilidad').value) || 0;

        let recursos = nro * precio;
        document.getElementById('edit-monto-recursos').value = recursos.toFixed(2);

        let total = recursos + contabilidad;
        document.getElementById('edit-monto-deuda').value = total;
        document.getElementById('edit-monto-deuda-display').value = 'S/. ' + total.toFixed(2);
    }

    function toggleSinMovimiento() {
        let check = document.getElementById('create-sin-movimiento');
        let inputContabilidad = document.getElementById('create-monto-contabilidad');

        if (check.checked) {
            inputContabilidad.dataset.prevValue = inputContabilidad.value;
            inputContabilidad.value = 100;
        } else {
            if (inputContabilidad.dataset.prevValue !== undefined) {
                inputContabilidad.value = inputContabilidad.dataset.prevValue;
            }
        }
        calcularMontos();
    }

    function toggleSinMovimientoEdit(isInit = false) {
        let check = document.getElementById('edit-sin-movimiento');
        let inputContabilidad = document.getElementById('edit-monto-contabilidad');

        if (isInit) {
            if (check.checked && inputContabilidad.value == '') {
                inputContabilidad.value = 100;
            }
        } else {
            if (check.checked) {
                inputContabilidad.dataset.prevValue = inputContabilidad.value;
                inputContabilidad.value = 100;
            } else {
                if (inputContabilidad.dataset.prevValue !== undefined) {
                    inputContabilidad.value = inputContabilidad.dataset.prevValue;
                }
            }
        }
        calcularMontosEdit();
    }

    function previewPdf(input, previewId) {
        let previewContainer = document.getElementById(previewId);
        let iframe = previewContainer.querySelector('iframe');

        if (input.files && input.files[0]) {
            let reader = new FileReader();

            reader.onload = function(e) {
                iframe.src = e.target.result;
                previewContainer.classList.remove('hidden');
            }

            reader.readAsDataURL(input.files[0]);
        } else {
            iframe.src = "";
            previewContainer.classList.add('hidden');
        }
    }

    function fetchMontoContabilidadAnterior(mesDeclaracion) {
        if (!mesDeclaracion) return;

        let idCotizacion = document.querySelector('select[name="id_cotizacion"]').value;
        if (!idCotizacion) return;

        let helpText = document.getElementById('create-monto-contabilidad-help');

        fetch(`/deudas_recepcion_monto_anterior?id_cotizacion=${idCotizacion}&mes=${mesDeclaracion}`)
            .then(res => res.json())
            .then(data => {
                if (data.encontrado) {
                    document.getElementById('create-monto-contabilidad').value = data.monto_contabilidad;
                    helpText.classList.remove('hidden');
                    calcularMontos();
                } else {
                    helpText.classList.add('hidden');
                }
            })
            .catch(err => console.error(err));
    }

    function autoCompletarDescripcionContable(mesValor) {
        const tipoVista = '{{ $tipo ?? 'contable' }}';
        const tipoDeclaracion = document.getElementById('tipo_declaracion').value;
        const descInput = document.getElementById('create-descripcion-servicio');

        if (tipoVista === 'contable' && tipoDeclaracion === 'mensual' && mesValor && descInput) {
            const partes = mesValor.split('-');
            if (partes.length === 2) {
                const year = partes[0];
                const monthIndex = parseInt(partes[1], 10) - 1;
                const meses = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE",
                    "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"
                ];

                if (monthIndex >= 0 && monthIndex < 12) {
                    descInput.value = `SERVICIO CONTABLE ${meses[monthIndex]} ${year}`;
                }
            }
        }
    }
</script>

<script>
    $(document).ready(function() {
        $('#myTable-deuda-detalle').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            order: [
                [3, 'desc']
            ],
            pageLength: 200,
            responsive: false,
            dom: 'Bfrtip', // Activa el diseño para los botones
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: function() {
                    const hoy = new Date();
                    const dia = hoy.getDate().toString().padStart(2, '0');
                    const mes = (hoy.getMonth() + 1).toString().padStart(2, '0');
                    const anio = hoy.getFullYear();
                    const empresa = {!! json_encode(str_replace(' ', '_', $empresa->razon_social)) !!};
                    return `TODAS_COTIZACIONES_DE_${empresa}_ASECON_DESCARGADO_${dia}_${mes}_${anio}`;
                },
                exportOptions: {
                    columns: ':not(:last)' // Excluye la primera y la última columna
                }
            }],
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            }
        });
    });

    function toggleTipoDeclaracion(tipo) {
        const campoMes = document.getElementById('campo-mes-declaracion');
        const campoAnio = document.getElementById('campo-anio-declaracion');
        const container = document.getElementById('create-recursos-contabilidad-container');
        const montoDeuda = document.getElementById('create-monto-deuda');

        const esOtros = '{{ $tipo ?? "contable" }}' === 'otros';

        if (tipo === 'anual') {
            campoMes.classList.add('hidden');
            campoAnio.classList.remove('hidden');
            if (container) container.classList.add('hidden');
            montoDeuda.removeAttribute('readonly');
            montoDeuda.classList.remove('bg-amber-50');
            if (!esOtros) montoDeuda.value = '';
        } else {
            campoMes.classList.remove('hidden');
            campoAnio.classList.add('hidden');
            if (container) container.classList.remove('hidden');
            if (!esOtros) {
                montoDeuda.setAttribute('readonly', 'true');
                montoDeuda.classList.add('bg-amber-50');
                calcularMontos();
            }
        }
    }

    function toggleFiltrosPDF(valor) {
        const rango = document.getElementById('filtros-rango-pdf');
        const anio = document.getElementById('filtro-anio-anual');

        // Reset
        rango.classList.add('hidden');
        anio.classList.add('hidden');

        if (valor === 'mes_declaracion') {
            rango.classList.remove('hidden'); // meses
        }

        if (valor === 'anual') {
            anio.classList.remove('hidden'); // años
        }
    }
</script>
