{{-- resources/views/cotizaciones/plantillas/form_receptor.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('cotizaciones.plantillas.index') }}" class="text-gray-400 hover:text-gray-600 transition"
                title="Volver">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Plantillas de Receptor — {{ $receptor->razon_social }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-700 rounded-lg px-4 py-3 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('cotizaciones.plantillas.receptor.guardar', $idReceptor) }}" method="POST"
                enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="tipo" value="{{ $tipo }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- ── Archivo Solicitud ── --}}
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 uppercase tracking-tighter">
                            <i class="bi bi-file-earmark-excel text-green-600 mr-1"></i>
                            Plantilla de SOLICITUD (.xlsx)
                        </h3>

                        @if ($plantilla && $plantilla->archivo_solicitud)
                            <div
                                class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-2 text-sm">
                                <i class="bi bi-check-circle mr-1"></i>
                                Actual: <strong>{{ $plantilla->nombre_original_solicitud }}</strong>
                            </div>
                        @endif

                        <div>
                            <x-input-label for="archivo_solicitud" :value="$plantilla && $plantilla->archivo_solicitud
                                ? 'Reemplazar Solicitud'
                                : 'Subir Solicitud'" />
                            <input type="file" id="archivo_solicitud" name="archivo_solicitud" accept=".xlsx"
                                class="mt-1 block w-full text-sm text-gray-700
                                       file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                       file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                       hover:file:bg-blue-100 cursor-pointer">
                            <p class="mt-1 text-xs text-gray-400">Solo archivos .xlsx. Máximo 5 MB.</p>
                        </div>
                    </div>

                    {{-- ── Archivo Orden de Compra ── --}}
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 uppercase tracking-tighter">
                            <i class="bi bi-file-earmark-excel text-green-600 mr-1"></i>
                            Plantilla de ORDEN DE COMPRA (.xlsx)
                        </h3>

                        @if ($plantilla && $plantilla->archivo_orden_compra)
                            <div
                                class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-2 text-sm">
                                <i class="bi bi-check-circle mr-1"></i>
                                Actual: <strong>{{ $plantilla->nombre_original_orden_compra }}</strong>
                            </div>
                        @endif

                        <div>
                            <x-input-label for="archivo_orden_compra" :value="$plantilla && $plantilla->archivo_orden_compra
                                ? 'Reemplazar Orden'
                                : 'Subir Orden'" />
                            <input type="file" id="archivo_orden_compra" name="archivo_orden_compra" accept=".xlsx"
                                class="mt-1 block w-full text-sm text-gray-700
                                       file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                       file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                       hover:file:bg-blue-100 cursor-pointer">
                            <p class="mt-1 text-xs text-gray-400">Solo archivos .xlsx. Máximo 5 MB.</p>
                        </div>
                    </div>
                </div>

                {{-- ── Botones ── --}}
                <div class="flex items-center gap-3">
                    <x-primary-button>
                        <i class="bi bi-floppy mr-1"></i>
                        Guardar Plantillas
                    </x-primary-button>
                    <a href="{{ route('cotizaciones.plantillas.index') }}"
                        class="text-sm text-gray-500 hover:text-gray-700 transition">
                        Cancelar
                    </a>
                </div>

                {{-- ── Instrucciones COMPLETAS (Copiadas del form emisor) ── --}}
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-700 mb-3">
                        <i class="bi bi-info-circle text-blue-500 mr-1"></i>
                        ¿Cómo preparar la plantilla Excel?
                    </h3>
                    <p class="text-sm text-gray-500 mb-5">
                        Escribe el <strong>placeholder exacto con llaves</strong> en cada celda destino.
                        El sistema lo reemplaza automáticamente al generar la cotización.
                        Las celdas de configuración (fondo amarillo) se borran solos del Excel.
                    </p>

                    {{-- Cabecera --}}
                    <div class="mb-5">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Cabecera</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ([
        '{FECHA}' => 'Fecha de emisión (dd/mm/yyyy)',
        '{NRO_COTIZACION}' => 'N° de cotización',
        '{NRO_REQUERIMIENTO}' => 'N° de requerimiento',
    ] as $ph => $desc)
                                <div class="flex items-start gap-2 bg-gray-50 rounded px-3 py-2">
                                    <code
                                        class="text-green-700 font-semibold text-xs whitespace-nowrap">{{ $ph }}</code>
                                    <span class="text-gray-400 text-xs">{{ $desc }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Emisor --}}
                    <div class="mb-5">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Emisor</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ([
        '{EMISOR_RAZON_SOCIAL}' => 'Razón social',
        '{EMISOR_RUC}' => 'RUC',
        '{EMISOR_DOMICILIO}' => 'Domicilio fiscal (nombre, distrito - provincia)',
        '{EMISOR_DEPARTAMENTO}' => 'Región / Departamento',
        '{EMISOR_TELEFONO}' => 'Teléfono',
        '{EMISOR_WEB}' => 'Página web',
        '{EMISOR_EMAIL}' => 'Email',
    ] as $ph => $desc)
                                <div class="flex items-start gap-2 bg-gray-50 rounded px-3 py-2">
                                    <code
                                        class="text-green-700 font-semibold text-xs whitespace-nowrap">{{ $ph }}</code>
                                    <span class="text-gray-400 text-xs">{{ $desc }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Cliente --}}
                    <div class="mb-5">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Cliente / Receptor</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ([
        '{CLIENTE_RAZON_SOCIAL}' => 'Razón social',
        '{CLIENTE_RUC}' => 'RUC',
        '{CLIENTE_DOMICILIO}' => 'Domicilio fiscal (si el receptor es empresa)',
        '{CLIENTE_EMAIL}' => 'Email / Contacto',
    ] as $ph => $desc)
                                <div class="flex items-start gap-2 bg-gray-50 rounded px-3 py-2">
                                    <code
                                        class="text-green-700 font-semibold text-xs whitespace-nowrap">{{ $ph }}</code>
                                    <span class="text-gray-400 text-xs">{{ $desc }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Condiciones --}}
                    <div class="mb-5">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Condiciones y Totales
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ([
        '{FECHA_PAGO_ESTIMADA}' => 'Fecha de pago estimada (dd/mm/yyyy)',
        '{FECHA_PAGO}' => 'Fecha de pago (dd/mm/yyyy)',
        '{TIPO_PAGO}' => 'CONTADO o CRÉDITO',
        '{VIGENCIA}' => 'Plazo (ej: 1 MES)',
        '{SUBTOTAL}' => 'Subtotal',
        '{IGV}' => 'IGV 18%',
        '{TOTAL}' => 'Total',
    ] as $ph => $desc)
                                <div class="flex items-start gap-2 bg-gray-50 rounded px-3 py-2">
                                    <code
                                        class="text-green-700 font-semibold text-xs whitespace-nowrap">{{ $ph }}</code>
                                    <span class="text-gray-400 text-xs">{{ $desc }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Config de productos --}}
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                            Configuración de Productos
                            <span class="font-normal normal-case text-gray-300 ml-1">(se borran solos del Excel)</span>
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ([
        '{PRODUCTOS_FILA_INICIO}' => 'Celda donde empiezan los productos',
        '{COL_DESCRIPCION}' => 'Columna de descripción',
        '{COL_CANTIDAD}' => 'Columna de cantidad',
        '{COL_UNIDAD}' => 'Columna de unidad',
        '{COL_PRECIO}' => 'Columna de precio',
        '{COL_TOTAL}' => 'Columna de total por línea',
    ] as $ph => $desc)
                                <div class="flex items-start gap-2 bg-yellow-50 rounded px-3 py-2">
                                    <code
                                        class="text-yellow-700 font-semibold text-xs whitespace-nowrap">{{ $ph }}</code>
                                    <span class="text-gray-400 text-xs">{{ $desc }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs text-gray-400 italic">
                            * Para más detalles, consulte la ayuda en la configuración de plantillas de emisor.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
