{{-- resources/views/cotizaciones/plantillas/form.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('cotizaciones.plantillas.index') }}" class="text-gray-400 hover:text-gray-600 transition" title="Volver">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Plantilla de Cotización — {{ $empresa->razon_social }}
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

            <form action="{{ route('cotizaciones.plantillas.guardar', $empresa) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- ── Archivo ── --}}
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">
                        <i class="bi bi-file-earmark-excel text-green-600 mr-1"></i>
                        Archivo de Plantilla (.xlsx)
                    </h3>

                    @if ($plantilla)
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-2 text-sm">
                        <i class="bi bi-check-circle mr-1"></i>
                        Formato Cotizacion : <strong>{{ $plantilla->nombre_original }}</strong>
                        <br>
                        <span class="text-gray-400 text-xs">Sube un nuevo archivo solo si quieres
                            reemplazarlo.</span>
                    </div>
                    @endif

                    <div>
                        <x-input-label for="archivo" :value="$plantilla ? 'Reemplazar plantilla' : 'Subir plantilla'" />
                        <input type="file" id="archivo" name="archivo" accept=".xlsx" {{ !$plantilla ? 'required' : '' }} class="mt-1 block w-full text-sm text-gray-700
                                   file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                   file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                   hover:file:bg-blue-100 cursor-pointer">
                        <p class="mt-1 text-xs text-gray-400">Solo archivos .xlsx. Máximo 5 MB.</p>
                        <x-input-error :messages="$errors->get('archivo')" class="mt-2" />
                    </div>
                </div>

                {{-- ── Botones ── --}}
                <div class="flex items-center gap-3">
                    <x-primary-button>
                        <i class="bi bi-floppy mr-1"></i>
                        Guardar Plantilla
                    </x-primary-button>
                    <a href="{{ route('cotizaciones.plantillas.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
                        Cancelar
                    </a>
                </div>

                {{-- ── Instrucciones ── --}}
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
                                <code class="text-green-700 font-semibold text-xs whitespace-nowrap">{{ $ph }}</code>
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
                                <code class="text-green-700 font-semibold text-xs whitespace-nowrap">{{ $ph }}</code>
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
                                <code class="text-green-700 font-semibold text-xs whitespace-nowrap">{{ $ph }}</code>
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
                            '{TIPO_PAGO}' => 'CONTADO o CRÉDITO (calculado desde fecha_pago vs fecha_emision)',
                            '{VIGENCIA}' => 'Plazo: "1 MES", "2 MESES", "15 DÍAS", etc. (auto-calculado)',
                            '{SUBTOTAL}' => 'Subtotal con símbolo S/ o $',
                            '{IGV}' => 'IGV 18% con símbolo S/ o $',
                            '{TOTAL}' => 'Total con símbolo S/ o $',
                            ] as $ph => $desc)
                            <div class="flex items-start gap-2 bg-gray-50 rounded px-3 py-2">
                                <code class="text-green-700 font-semibold text-xs whitespace-nowrap">{{ $ph }}</code>
                                <span class="text-gray-400 text-xs">{{ $desc }}</span>
                            </div>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-blue-500">
                            <i class="bi bi-info-circle mr-1"></i>
                            <strong>{TIPO_PAGO}</strong> y <strong>{VIGENCIA}</strong> se calculan automáticamente
                            comparando la <em>fecha de pago</em> con la <em>fecha de emisión</em> de la factura.
                            Si no hay fecha de pago o es el mismo día → <strong>CONTADO</strong>.
                            Si hay diferencia → <strong>CRÉDITO</strong> y la vigencia en meses o días.
                        </p>
                    </div>

                    {{-- Firma --}}
                    <div class="mb-5">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Firma</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ([
                            '{FIRMA_NOMBRE}' => 'GERENTE GENERAL - Nombre (del branding)',
                            '{FIRMA_CARGO}' => 'Cargo del firmante',
                            '{FIRMA_IMAGEN}' => 'Imagen de la firma (se centra en la celda)',
                            ] as $ph => $desc)
                            <div class="flex items-start gap-2 bg-gray-50 rounded px-3 py-2">
                                <code class="text-green-700 font-semibold text-xs whitespace-nowrap">{{ $ph }}</code>
                                <span class="text-gray-400 text-xs">{{ $desc }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Config de productos y cuentas --}}
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                            Configuración de Productos y Cuentas
                            <span class="font-normal normal-case text-gray-300 ml-1">(se borran solos del Excel)</span>
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ([
                            '{PRODUCTOS_FILA_INICIO}' => 'Celda en la fila donde empiezan los productos',
                            '{COL_DESCRIPCION}' => 'Columna de descripción del producto',
                            '{COL_CANTIDAD}' => 'Columna de cantidad',
                            '{COL_UNIDAD}' => 'Columna de unidad de medida',
                            '{COL_PRECIO}' => 'Columna de precio unitario',
                            '{COL_TOTAL}' => 'Columna de total por línea',
                            '{CUENTAS_BLOQUE}' => 'Celda de inicio del bloque de cuentas (col. nombre banco)',
                            '{CUENTAS_NRO}' => 'Celda de inicio para números de cuenta',
                            '{FIRMA_IMAGEN}' => 'Celda donde se insertará la imagen de firma',
                            ] as $ph => $desc)
                            <div class="flex items-start gap-2 bg-yellow-50 rounded px-3 py-2">
                                <code class="text-yellow-700 font-semibold text-xs whitespace-nowrap">{{ $ph }}</code>
                                <span class="text-gray-400 text-xs">{{ $desc }}</span>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-3 bg-yellow-50 border border-yellow-100 rounded p-3 text-xs text-gray-500 space-y-1">
                            <p>
                                <i class="bi bi-lightbulb text-yellow-500 mr-1"></i>
                                <strong>Cuentas:</strong> 2 filas por cuenta — normal + CCI.
                                Solo trae cuentas <code>sede = Trujillo</code>, <code>estado = 1</code>.
                                Facturas en <strong>USD</strong> → solo <code>tipo = 1</code>;
                                en <strong>Soles</strong> → solo <code>tipo = 0</code>.
                            </p>
                            <p>
                                <i class="bi bi-lightbulb text-yellow-500 mr-1"></i>
                                <strong>Tip:</strong> puedes escribir los placeholders de configuración
                                con o sin llaves en el Excel — <code>PRODUCTOS_FILA_INICIO</code>
                                y <code>{PRODUCTOS_FILA_INICIO}</code> funcionan igual.
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
