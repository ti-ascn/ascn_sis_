<style>
    #pendiente-facturar-container {
        transition: color 0.3s ease;
    }

    tr.fila-seleccionada td {
        background-color: #bfdbfe !important;
    }
</style>
@php
    $unidadesMedida = \App\Models\UnidadMedida::where('estado', 1)->orderBy('nombre')->pluck('nombre'); // solo la columna nombre

    $pendienteRaw = is_numeric($pendientePorFacturar ?? null) ? (float) $pendientePorFacturar : null;
    $precioUSDBase = is_numeric($precioDolar->precio ?? null) ? (float) $precioDolar->precio : null;
    $monedaPendiente = request('moneda') ?? 'PEN';

    $pendientePEN = 0;
    $pendienteUSD = 0;

    if ($pendienteRaw !== null && $precioUSDBase && $precioUSDBase > 0) {
        if ($monedaPendiente === 'USD') {
            $pendienteUSD = $pendienteRaw;
            $pendientePEN = $pendienteRaw * $precioUSDBase;
        } else {
            $pendientePEN = $pendienteRaw;
            $pendienteUSD = $pendienteRaw / $precioUSDBase;
        }
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-lg text-gray-800 leading-tight uppercase tracking-tight">
            Facturar desde: <span class="text-amber-600">{{ $empresa->razon_social }}</span>
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="w-full space-y-4">
            <!-- Information Card (Premium Style) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-6 relative">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
                    <!-- Basic Info -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-[#c9a227]">
                            <i class="bi bi-building font-bold"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">Información de Empresa</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-800 leading-tight">{{ $empresa->razon_social }}</h3>
                            <p class="text-sm text-gray-500 font-bold mt-1">
                                RUC: <span class="text-gray-800 cursor-pointer hover:text-amber-600 transition-colors"
                                    onclick="copiarAlPortapapeles('{{ $empresa->ruc }}', 'RUC copiado')">{{ $empresa->ruc }}</span>
                            </p>
                            <div class="mt-2.5">
                                <a href="{{ route('factura.create_cliente', $empresa->inventarios->first()->id_inventario) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-amber-800 border border-amber-200 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-[#c9a227] hover:text-white hover:border-[#c9a227] transition-all shadow-sm"
                                    title="¿Es para un cliente? Haz clic para crear una factura para un Cliente.">
                                    <i class="bi bi-person-badge-fill"></i>
                                    <span>Cambiar a Factura para Cliente</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Credentials -->
                    <div class="space-y-3 border-l border-gray-100 pl-6">
                        <div class="flex items-center gap-2 text-[#c9a227]">
                            <i class="bi bi-key-fill"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">Credenciales SUNAT</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="group cursor-pointer"
                                onclick="copiarAlPortapapeles('{{ $empresa->sol_usuario }}', 'Usuario SOL copiado')">
                                <span
                                    class="block text-[9px] text-gray-400 font-black uppercase tracking-tighter">Usuario
                                    SOL</span>
                                <span
                                    class="text-sm font-bold text-gray-700 blur-[3px] group-hover:blur-none transition-all duration-300">{{ $empresa->sol_usuario }}</span>
                            </div>
                            <div class="group cursor-pointer"
                                onclick="copiarAlPortapapeles('{{ $empresa->sol_clave }}', 'Clave SOL copiada')">
                                <span class="block text-[9px] text-gray-400 font-black uppercase tracking-tighter">Clave
                                    SOL</span>
                                <span
                                    class="text-sm font-bold text-gray-700 blur-[3px] group-hover:blur-none transition-all duration-300">{{ $empresa->sol_clave }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Active Accounts -->
                    <div class="space-y-3 border-l border-gray-100 pl-6">
                        <div class="flex items-center gap-2 text-[#c9a227]">
                            <i class="bi bi-bank2"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">Cuentas Bancarias</span>
                        </div>
                        @if ($empresa->cuentas->where('estado', 1)->isNotEmpty())
                            <div class="flex flex-col gap-2">
                                <x-dropdown align="left" width="64">
                                    <x-slot name="trigger">
                                        <button
                                            class="w-full flex items-center justify-between px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 hover:bg-white hover:border-amber-500 transition-all">
                                            <span>Selecciona una cuenta</span>
                                            <i class="bi bi-chevron-down text-[10px]"></i>
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <div class="p-1 space-y-0.5">
                                            @foreach ($empresa->cuentas->where('estado', 1) as $c)
                                                <button
                                                    onclick="seleccionarCuenta('{{ $c->nombre }}','{{ $c->tipo == 0 ? 'S/.' : '$' }}','{{ $c->nro_cuenta ?? '—' }}','{{ $c->nro_interbancario ?? '—' }}', '{{ $c->sede }}')"
                                                    class="w-full text-left px-3 py-2 text-[11px] font-bold text-gray-600 hover:bg-amber-50 hover:text-amber-600 rounded-lg transition-colors">
                                                    {{ $c->nombre }} ({{ $c->tipo == 0 ? 'S/.' : '$' }}) -
                                                    {{ $c->sede }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </x-slot>
                                </x-dropdown>

                                <div id="cuadroCuenta" class="hidden animate-fade-in">
                                    <div
                                        class="bg-amber-50 border border-amber-100 rounded-xl p-2 flex items-center justify-between">
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-black text-amber-600 uppercase" id="cuentaBanco">
                                            </p>
                                            <p class="text-xs font-bold text-gray-700 truncate cursor-pointer hover:text-amber-600"
                                                id="cuentaLinea"
                                                onclick="copiarAlPortapapeles(this.textContent, 'Cuenta copiada')"></p>
                                        </div>
                                        <span
                                            class="text-[10px] font-black bg-white px-1.5 py-0.5 rounded border border-amber-200 text-amber-700"
                                            id="cuentaMoneda"></span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">No hay cuentas activas</p>
                        @endif
                    </div>
                </div>
            </div>

            <x-success-error-bagdes />
            <div id="mensaje-dinamico" class="hidden mb-4 p-4 rounded-md text-sm font-semibold"></div>

            <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6 lg:p-8">
                <form method="POST" action="{{ route('factura.store') }}">
                    @csrf

                    <input type="hidden" name="origen_receptor" value="empresa">
                    <input type="hidden" name="id_empresa_contexto" value="{{ $empresa->id_empresas_clientes }}">
                    <input type="hidden" name="precio_dolar" value="{{ $precioDolar->precio }}">

                    <div class="mb-6">
                        <x-input-label for="select-empresa" :value="__('Empresa a facturar')" />
                        <select name="id_receptor" id="select-empresa" class="select-empresa w-full" required>
                            <option value="">Selecciona una empresa</option>
                            @foreach ($empresas as $e)
                                <option data-ruc="{{ $e->ruc }}" value="{{ $e->id_empresas_clientes }}"
                                    @if (isset($idReceptorSeleccionado) && $idReceptorSeleccionado == $e->id_empresas_clientes) selected @endif>
                                    {{ $e->razon_social }} (RUC {{ $e->ruc }})
                                </option>
                            @endforeach
                        </select>
                        @if (isset($pendienteRaw) && $pendienteRaw !== null)
                            <div id="pendiente-facturar-container" class="text-sm mt-2">
                                <strong>Pendiente por facturar este mes:</strong>
                                S/ <span id="pendiente-soles">{{ number_format($pendientePEN, 2) }}</span>
                                /
                                $ <span id="pendiente-dolares">{{ number_format($pendienteUSD, 2) }}</span>
                            </div>
                        @endif
                        <x-input-error :messages="$errors->get('id_receptor')" class="mt-2" />

                        <div id="ruc-container" class="mt-2 hidden text-gray-600">
                            <strong>RUC:</strong>
                            <span id="ruc-display" class="cursor-pointer hover:text-blue-600 transition"></span>
                        </div>
                    </div>

                    {{-- Fila 1: Información Financiera y de Pago --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 items-start">
                        <!-- Moneda -->
                        <div>
                            <x-input-label for="tipo_moneda" :value="__('Moneda')" />
                            <select name="tipo_moneda" id="tipo_moneda"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="PEN">Soles (PEN)</option>
                                <option value="USD">Dólares (USD)</option>
                            </select>
                            <div id="alerta-usd"
                                class="hidden mt-2 px-3 py-2 rounded-md text-sm font-bold bg-red-100 text-red-700 border border-red-300">
                                ⚠ Estás facturando en <span class="underline">DÓLARES</span>
                            </div>
                            <div class="text-sm text-gray-600 mt-2">
                                Precio dólar actual:
                                @if (Auth::user()->tipo_usuario == 1 ||
                                        Auth::user()->tipo_usuario == 2 ||
                                        (Auth::user()->tipo_usuario == 3 && Auth::user()->id_area == 7 && Auth::user()->puede_editar))
                                    <span onclick="toggleModal('modalPrecioDolar')"
                                        class="text-green-600 font-semibold cursor-pointer hover:underline transition duration-200"
                                        title="Haz clic para actualizar el precio del dólar">
                                        S/ {{ number_format($precioDolar->precio, 3) }}
                                    </span>
                                @else
                                    <span class="text-gray-600 font-semibold"
                                        title="Solo visible para administradores">
                                        S/ {{ number_format($precioDolar->precio, 3) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Agente Retenedor -->
                        <div>
                            <x-input-label for="agente_retenedor" :value="__('¿Agente Retenedor?')" />
                            <select id="agente_retenedor" name="agente_retenedor"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="N">No</option>
                                <option value="S">Sí</option>
                            </select>
                        </div>

                        <!-- Fecha de Emisión -->
                        <div>
                            <x-input-label for="fecha_emision" :value="__('Fecha de Emisión')" />
                            <input type="date" name="fecha_emision" id="fecha_emision"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>

                            <!-- Nuevo campo: precio_dolar_factura (solo visible cuando moneda = USD) -->
                            <div id="precio_dolar_factura_container" class="hidden mb-6">
                                <x-input-label for="precio_dolar_factura" :value="__('Precio Dólar Factura (opcional)')" class="mt-2" />
                                <x-text-input id="precio_dolar_factura" name="precio_dolar_factura" type="number"
                                    step="0.001" class="mt-1 block w-full" placeholder="Ej: 3.750"
                                    value="{{ old('precio_dolar_factura') }}" />
                                <x-input-error :messages="$errors->get('precio_dolar_factura')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Tipo de Crédito -->
                        <div>
                            <x-input-label for="tipo_credito" :value="__('Tipo de Crédito')" />
                            <select id="tipo_credito" name="tipo_credito"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="0">Al contado</option>
                                <option value="30">En 30 días</option>
                                <option value="60">En 60 días</option>
                                <option value="90">En 90 días</option>
                                <option value="personalizado">Otro (personalizado)</option>
                            </select>

                            <input type="number" id="dias_personalizados" name="tipo_credito_personalizado"
                                class="mt-2 block w-full border-gray-300 rounded-md shadow-sm hidden"
                                placeholder="Ingresa cantidad de días" min="1" />

                            <div class="text-sm text-gray-600 mt-1">
                                Fecha de pago estimada:
                                <span id="fecha_pago_dinamica"
                                    class="text-blue-600 font-semibold cursor-pointer hover:underline"
                                    onclick="copiarAlPortapapeles(this.innerText, 'Fecha copiada')">
                                    {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Fila 2: Documentos y Fechas de Referencia --}}
                    <div class="grid grid-cols-12 gap-4 mb-6 items-start">
                        <!-- Solicitud -->
                        <div class="col-span-6 md:col-span-2">
                            <div class="flex justify-between items-center mb-1">
                                <x-input-label for="numero_solicitud" :value="__('Nro Solicitud')" class="mb-0" />
                                <button type="button" onclick="consultarFormato('solicitud', 'numero_solicitud')"
                                    class="text-[10px] text-blue-600 hover:underline">
                                    <i class="bi bi-search"></i> Formato
                                </button>
                            </div>
                            <x-text-input id="numero_solicitud" name="numero_solicitud" type="text"
                                class="block w-full" required autofocus value="{{ old('numero_solicitud') }}" />
                            <span id="error-solicitud" class="text-red-600 text-xs hidden"></span>
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            <x-input-label for="fecha_solicitud" :value="__('Fecha Solicitud')" class="mb-1" />
                            <x-text-input id="fecha_solicitud" name="fecha_solicitud" type="date"
                                class="block w-full" value="{{ old('fecha_solicitud') }}" />
                        </div>

                        <!-- Cotización -->
                        <div class="col-span-6 md:col-span-2">
                            <div class="flex justify-between items-center mb-1">
                                <x-input-label for="nro_cotizacion" :value="__('Nro Cotizacion')" class="mb-0" />
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="abrirModalDisponibles()"
                                        class="text-[10px] text-green-600 hover:underline" title="Ver Disponibles">
                                        <i class="bi bi-list-ol"></i> Disponibles
                                    </button>
                                    {{-- <button type="button" onclick="consultarFormato('cotizacion', 'nro_cotizacion')"
                                        class="text-[10px] text-blue-600 hover:underline">
                                        <i class="bi bi-search"></i> Formato
                                    </button> --}}
                                </div>
                            </div>
                            <x-text-input id="nro_cotizacion" name="nro_cotizacion" type="text"
                                class="block w-full" required value="{{ old('nro_cotizacion') }}" />
                            <span id="error-nro-cotizacion" class="text-red-600 text-xs hidden"></span>
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            <x-input-label for="fecha_cotizacion" :value="__('Fecha Cotizacion')" class="mb-1" />
                            <x-text-input id="fecha_cotizacion" name="fecha_cotizacion" type="date"
                                class="block w-full" value="{{ old('fecha_cotizacion') }}" />
                        </div>

                        <!-- Orden -->
                        <div class="col-span-6 md:col-span-2">
                            <div class="flex justify-between items-center mb-1">
                                <x-input-label for="nombre_correlativo" :value="__('Nro Orden')" class="mb-0" />
                                <button type="button" onclick="consultarFormato('orden', 'nombre_correlativo')"
                                    class="text-[10px] text-blue-600 hover:underline">
                                    <i class="bi bi-search"></i> Formato
                                </button>
                            </div>
                            <x-text-input id="nombre_correlativo" name="nombre_correlativo" type="text"
                                class="block w-full" required
                                value="{{ old('nombre_correlativo', $formatoCorrelativo) }}" />
                            <span id="error-correlativo" class="text-red-600 text-xs hidden"></span>
                        </div>

                        <!-- Factura -->
                        <div class="col-span-6 md:col-span-2">
                            <x-input-label for="nro_factura" :value="__('Nro Factura')" class="mb-1" />
                            <x-text-input id="nro_factura" name="nro_factura" type="text" class="block w-full"
                                value="{{ old('nro_factura') }}" />
                        </div>
                    </div>

                    {{-- Fila 3: Logística y Acciones Rápidas --}}
                    <div class="grid grid-cols-12 gap-4 mb-6 items-end">
                        <!-- Guías -->
                        <div class="col-span-6 md:col-span-2">
                            <x-input-label for="grr" :value="__('GRR')" class="mb-1" />
                            <x-text-input id="grr" name="grr" type="text" class="block w-full"
                                value="{{ old('grr', $factura->grr ?? '') }}" />
                        </div>
                        <!-- Traslado -->
                        <div class="col-span-6 md:col-span-2">
                            <div class="flex justify-between items-center mb-1">
                                <x-input-label for="fecha_traslado" :value="__('Fecha de Traslado')" class="mb-0" />
                                <button type="button" onclick="abrirModalProgramacion()"
                                    class="text-[10px] text-blue-600 hover:underline">
                                    <i class="bi bi-calendar3"></i> Programación
                                </button>
                            </div>
                            <x-text-input id="fecha_traslado" name="fecha_traslado" type="date"
                                class="block w-full pointer-events-none bg-gray-100 dark:bg-[#1a1d24] text-gray-500 cursor-not-allowed"
                                readonly
                                tabindex="-1"
                                value="{{ old('fecha_traslado', isset($factura->fecha_traslado) ? \Carbon\Carbon::parse($factura->fecha_traslado)->format('Y-m-d') : '') }}" />
                            <input type="hidden" id="id_programacion" name="id_programacion" value="{{ old('id_programacion', $factura->id_programacion ?? '') }}">
                            <div id="chofer_seleccionado_container" class="mt-1 text-xs text-gray-500 font-medium hidden">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                                    <i class="bi bi-person-fill mr-1"></i> <span id="chofer_nombre_display"></span>
                                </span>
                            </div>
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            <x-input-label for="grt" :value="__('GRT')" class="mb-1" />
                            <x-text-input id="grt" name="grt" type="text" class="block w-full"
                                value="{{ old('grt', $factura->grt ?? '') }}" />
                        </div>

                        <!-- Botones de Acción -->
                        <div class="col-span-12 md:col-span-6 flex gap-2">
                            <button type="button" onclick="toggleModal('modalCrearProducto')"
                                class="flex-1 bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 text-white flex items-center justify-center text-sm font-bold">
                                <i class="bi bi-plus-circle mr-2"></i> Nuevo Prod.
                            </button>
                            <button type="button" onclick="toggleModal('modalPegarProductos')"
                                class="flex-1 bg-yellow-500 px-4 py-2 rounded-md hover:bg-yellow-600 text-white flex items-center justify-center text-sm font-bold">
                                <i class="bi bi-copy mr-2"></i> Pegar Excel
                            </button>
                            <button type="button" onclick="toggleModal('modalTablaProductos')"
                                class="flex-1 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 text-white flex items-center justify-center text-sm font-bold">
                                <i class="bi bi-boxes mr-2"></i> Productos
                            </button>
                        </div>
                    </div>


                    {{-- Agregar producto --}}
                    <div class="grid grid-cols-12 gap-4 mb-6 items-end">
                        <div class="col-span-12 md:col-span-5">
                            <x-input-label for="select-producto" :value="__('Producto (Shift + N)')" class="mb-1" />
                            <select id="select-producto" class="select-producto w-full">
                                <option value="">Busca un producto...</option>
                                @foreach ($empresa->inventarios as $inv)
                                    @foreach ($inv->productos->where('estado', 1) as $prod)
                                        <option value="{{ $prod->id_producto }}" data-nombre="{{ $prod->nombre }}"
                                            data-precio="{{ $prod->precio_unitario }}"
                                            data-peso="{{ $prod->peso ?? 0 }}"
                                            data-unidad="{{ $prod->unidad_medida }}">
                                            [{{ $prod->id_producto }}] {{ mb_strtoupper($prod->nombre) }} – S/
                                            {{ number_format($prod->precio_unitario, 4) }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-12 md:col-span-3">
                            <x-input-label for="cantidad_producto" :value="__('Cantidad')" class="mb-1" />
                            <input type="number" id="cantidad_producto" min="1"
                                class="border-gray-300 rounded-md w-full" />
                        </div>

                        <div class="col-span-12 md:col-span-2">
                            <x-primary-button type="button" onclick="agregarProducto()"
                                class="w-full tooltip h-[42px]" id="btn-agregar-producto">
                                Agregar Producto
                                <span class="tooltip-text">Shift + G</span>
                            </x-primary-button>
                        </div>

                        {{-- Campo porcentaje (ocupa 2/12) --}}
                        <div class="col-span-12 md:col-span-2">
                            <x-input-label for="porcentaje_producto" :value="__('Porcentaje (%)')" class="mb-1" />
                            <input type="number" id="porcentaje_producto" min="1" value="100"
                                class="border-gray-300 rounded-md w-full" oninput="aplicarPorcentajeTabla()" />
                        </div>

                    </div>


                    {{-- Tabla dinámica --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border" id="tabla-detalles">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border px-4 py-2 w-16">N°</th>
                                    <th class="border px-4 py-2">Código</th>
                                    <th class="border px-4 py-2">Producto</th>
                                    <th class="border px-4 py-2">U.M</th>
                                    <th class="border px-4 py-2">Cantidad</th>
                                    <th class="border px-4 py-2 text-right min-w-[100px]">Precio U.</th>
                                    <th class="border px-4 py-2 text-right min-w-[100px]">V.V.U</th>
                                    <th class="border px-4 py-2 text-right min-w-[100px]">Peso Unit.</th>
                                    <th class="border px-4 py-2 text-right min-w-[100px]">Peso Total</th>
                                    <th class="border px-4 py-2 text-right min-w-[100px]">Subtotal</th>
                                    <th class="border px-4 py-2">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="detalle-body"></tbody>
                            <tfoot class="font-semibold bg-gray-50">
                                <tr>
                                    <td colspan="9" class="text-right px-4 py-2">Total Bruto:</td>
                                    <td colspan="" id="total_bruto" class="text-right">S/ 0.00</td>
                                    <td colspan="" id="" class=""></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right px-4 py-2">IGV (18%):</td>
                                    <td colspan="" id="IGV" class="text-right">S/ 0.00</td>
                                    <td colspan="" id="" class=""></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right px-4 py-2">Retención (3%):</td>
                                    <td colspan="" id="monto_retencion" class="text-right">S/ 0.00</td>
                                    <td colspan="" id="" class=""></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right px-4 py-2">Total Neto:</td>
                                    <td colspan="" id="total_neto" class="text-right">S/ 0.00</td>
                                    <td colspan="" id="" class=""></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right px-4 py-2">Peso Neto Total:</td>
                                    <td colspan="" id="peso_neto" class="text-right">0.00 kg</td>
                                    <td colspan="" id="" class=""></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="flex justify-end items-center gap-4 mt-6">
                        <!-- select para tipo_factura -->
                        <div>
                            {{-- <x-input-label for="tipo_factura" :value="__('Tipo Factura')" /> --}}
                            <select name="tipo_factura" id="tipo_factura"
                                class="mt-1 block w-44 border-gray-300 rounded-md shadow-sm">
                                @php
                                    $estadoEmpresa = $empresa->estado ?? '';
                                    $tipoDefault = 1; // Por defecto Factura Clientes

                                    if ($estadoEmpresa === 'R1') {
                                        $tipoDefault = 2; // Factura R1→A1
                                    } elseif ($estadoEmpresa === 'R2') {
                                        $tipoDefault = 3; // Factura R2→R1
                                    }
                                @endphp

                                <option value="1" {{ $tipoDefault == 1 ? 'selected' : '' }}>Factura Clientes
                                </option>
                                <option value="2" {{ $tipoDefault == 2 ? 'selected' : '' }}>Factura R1→A1
                                </option>
                                <option value="3" {{ $tipoDefault == 3 ? 'selected' : '' }}>Factura R2→R1
                                </option>
                            </select>
                            <x-input-error :messages="$errors->get('tipo_factura')" class="mt-2" />
                        </div>

                        <div id="btn-crear-factura-wrapper" class="cursor-not-allowed inline-block">
                            <x-primary-button id="btn-crear-factura" class="pointer-events-none opacity-50" disabled>
                                Crear Factura
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<div id="modalTablaProductos"
    class="fixed inset-0 z-50 hidden overflow-y-auto flex items-center justify-center transition-opacity duration-300 opacity-0"
    style="background-color: rgba(0, 0, 0, 0.5);" onclick="closeModal(event, 'modalTablaProductos')">

    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-6xl transform transition-all duration-300 scale-95"
        onclick="event.stopPropagation();">
        <div class="px-4 py-2 border-b flex justify-between items-center">
            <h2 class="text-xl font-semibold">Seleccionar Productos</h2>
            <div class="flex items-center gap-4">
                <span id="contador-productos" class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">
                    0 productos seleccionados
                </span>
                <button onclick="toggleModal('modalTablaProductos')" class="text-gray-500 hover:text-gray-700">
                    &times;
                </button>
            </div>
        </div>
        <div class="p-4 overflow-auto max-h-[70vh]">
            <table id="myTable-productos" class="min-w-full table-auto border text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2">Código</th>
                        <th class="border p-2">Producto</th>
                        <th class="border p-2">Unidad</th>
                        <th class="border p-2">Precio</th>
                        <th class="border p-2">Peso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($empresa->inventarios as $inv)
                        @foreach ($inv->productos->where('estado', 1) as $prod)
                            <tr class="fila-producto cursor-pointer hover:bg-blue-50 transition"
                                data-id="{{ $prod->id_producto }}" data-nombre="{{ $prod->nombre }}"
                                data-precio="{{ $prod->precio_unitario }}" data-peso="{{ $prod->peso ?? 0 }}"
                                data-unidad="{{ $prod->unidad_medida }}">
                                <td class="border p-2">{{ $prod->id_producto }}</td>
                                <td class="border p-2">
                                    {{ mb_strtoupper($prod->nombre) }}
                                    @if (isset($conteoVentas[$prod->id_producto]))
                                        <span
                                            class="text-red-500 text-xs">({{ $conteoVentas[$prod->id_producto] }})</span>
                                    @else
                                        <span class="text-red-500 text-xs">(0)</span>
                                    @endif
                                </td>
                                <td class="border p-2">{{ $prod->unidad_medida }}</td>
                                <td class="border p-2 text-right">S/ {{ number_format($prod->precio_unitario, 4) }}
                                </td>
                                <td class="border p-2 text-right">{{ $prod->peso ?? 0 }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-2 border-t text-right">
            <button onclick="toggleModal('modalTablaProductos')"
                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cerrar</button>
        </div>
    </div>
</div>

<x-modal-show id="modalPrecioDolar" title="Actualizar Precio del Dólar">
    <form method="POST" action="{{ route('precio_dolar.update') }}">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label for="precio" class="block text-sm font-medium">Precio actual en S/</label>
            <input type="number" step="0.001" name="precio" id="precio"
                value="{{ number_format($precioDolar->precio, 3) }}" class="w-full border-gray-300 rounded-md"
                required>
            <p class="text-sm text-gray-600 mt-1">
                Última actualización por: <span class="font-semibold">{{ $precioDolar->usuario->name }}</span> el
                <span
                    class="font-semibold">{{ \Carbon\Carbon::parse($precioDolar->updated_at)->format('d/m/Y H:i') }}</span><br>
            </p>
        </div>

        <div class="flex justify-end">
            <x-primary-button>Actualizar</x-primary-button>
        </div>
    </form>
</x-modal-show>
<x-modal-show id="modalCrearProducto" title="Agregar Producto Rápido">
    <form id="formProductoRapido">
        @csrf
        <input type="hidden" name="id_inventario" value="{{ $empresa->inventarios->first()->id_inventario }}" />

        <div class="mb-4">
            <label for="nombre" class="block text-sm font-medium">Nombre</label>
            <input type="text" name="nombre" id="modal_nombre"
                class="w-full border-gray-300 rounded-md uppercase" required
                oninput="this.value = this.value.toUpperCase()">
        </div>

        <div class="mb-4">
            <label for="unidad_medida" class="block text-sm font-medium">Unidad de Medida</label>
            <select name="unidad_medida" id="modal_unidad" class="w-full border-gray-300 rounded-md" required>
                <option value="">Seleccione una unidad</option>
                @foreach ($unidadesMedida as $unidad)
                    <option value="{{ $unidad }}" {{ $unidad == 'UNID' ? 'selected' : '' }}>
                        {{ $unidad }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="precio_unitario" class="block text-sm font-medium">Precio Unitario</label>
            <input type="number" step="0.0001" name="precio_unitario" id="modal_precio"
                class="w-full border-gray-300 rounded-md" required>
        </div>

        <div class="mb-4">
            <label for="peso" class="block text-sm font-medium">Peso (opcional)</label>
            <input type="number" step="0.01" name="peso" id="modal_peso"
                class="w-full border-gray-300 rounded-md">
        </div>

        <input type="hidden" name="estado" value="1">

        <div class="flex justify-end">
            <x-primary-button id="btn-guardar-factura" type="submit">Guardar</x-primary-button>
        </div>
    </form>
    <div id="loader-crear-producto" class="hidden flex items-center justify-center gap-2 text-sm text-gray-700">
        <svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
            </path>
        </svg>
        Guardando producto...
    </div>
</x-modal-show>

<x-modal-show id="modalPegarProductos" title="Pegar productos desde Excel">
    <div class="space-y-4">
        <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 text-blue-700">
            <i class="bi bi-info-circle-fill mr-1"></i>
            Copia y pega las filas desde Excel, junto con su indice de las columnas. <a
                href="{{ asset('ejemplos/ejemplo_importar_productos_excel.jpg') }}" class="underline"
                target="_blank">Ejemplo.</a>
        </div>

        <textarea id="paste-excel-area" rows="10" class="w-full border-gray-300 rounded-md shadow-sm font-mono text-xs"></textarea>

        <div id="paste-preview" class="hidden">
            <p class="text-sm font-semibold text-gray-700 mb-2">Vista previa (<span id="paste-count">0</span>
                productos):</p>
            <div class="overflow-x-auto max-h-56 border rounded-md">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="px-2 py-1 border-b text-left">Código</th>
                            <th class="px-2 py-1 border-b text-left">Descripción</th>
                            <th class="px-2 py-1 border-b text-left">Unidad</th>
                            <th class="px-2 py-1 border-b text-right">Cant.</th>
                            <th class="px-2 py-1 border-b text-right">Peso</th>
                            <th class="px-2 py-1 border-b text-right">Precio</th>
                            <th class="px-2 py-1 border-b text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="paste-preview-body"></tbody>
                </table>
            </div>
        </div>

        <div id="paste-error" class="hidden text-sm text-red-600 bg-red-50 px-3 py-2 rounded-md"></div>

        <div class="flex justify-end gap-3 pt-2 border-t">
            <button type="button" onclick="toggleModal('modalPegarProductos')"
                class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                Cancelar
            </button>
            <button type="button" id="btn-importar-pegado"
                class="px-5 py-2 text-sm font-semibold text-white bg-yellow-500 hover:bg-yellow-600 rounded-md transition flex items-center gap-2 disabled:opacity-50"
                disabled>
                <i class="bi bi-check-lg"></i> Importar productos
            </button>
        </div>
    </div>
</x-modal-show>

<script>
    document.getElementById('formProductoRapido').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = document.getElementById('formProductoRapido');
        const formData = new FormData(form);

        const moneda = document.getElementById('tipo_moneda').value;
        if (moneda === 'USD') {
            const precioSoles = parseFloat(formData.get('precio_unitario'));
            const tipoCambio = {{ $precioDolar->precio }};
            if (!isNaN(precioSoles) && tipoCambio > 0) {
                formData.set('precio_unitario', (precioSoles * tipoCambio).toFixed(4));
            }
        }

        const loader = document.getElementById('loader-crear-producto');
        const mensaje = document.getElementById('mensaje-dinamico');

        loader.classList.remove('hidden');
        mensaje.classList.add('hidden');

        fetch("{{ route('productos.store') }}", {
                method: "POST",
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                loader.classList.add('hidden');

                if (data.success) {
                    const nuevo = data.producto;
                    const select = document.getElementById('select-producto');
                    const nombreEscapado = nuevo.nombre.toUpperCase().replace(/"/g, '&quot;');

                    // 1. Agregar al selector de productos
                    const option = document.createElement('option');
                    option.value = nuevo.id_producto;
                    option.text =
                        `[${nuevo.id_producto}] ${nuevo.nombre} – S/ ${parseFloat(nuevo.precio_unitario).toFixed(4)}`;
                    option.setAttribute('data-nombre', nuevo.nombre);
                    option.setAttribute('data-precio', nuevo.precio_unitario);
                    option.setAttribute('data-peso', nuevo.peso ?? 0);
                    option.setAttribute('data-unidad', nuevo.unidad_medida);

                    select.appendChild(option);
                    $('.select-producto').select2();
                    $('.select-producto').val(nuevo.id_producto).trigger('change');

                    // 2. Agregar a la tabla modal de productos con el mismo diseño
                    const table = $('#myTable-productos').DataTable();

                    const nuevaFila = `
                    <tr class="fila-producto cursor-pointer hover:bg-blue-50 transition"
                        data-id="${nuevo.id_producto}" data-nombre="${nuevo.nombre}"
                        data-precio="${nuevo.precio_unitario}" data-peso="${nuevo.peso ?? 0}"
                        data-unidad="${nuevo.unidad_medida}">
                        <td class="border p-2">${nuevo.id_producto}</td>
                        <td class="border p-2">
                            <div class="flex items-center justify-between">
                                <span 
                                    data-nombre="${nombreEscapado}"
                                    onclick="event.stopPropagation(); copiarAlPortapapeles(this.dataset.nombre, 'Nombre copiado')"
                                    class="cursor-pointer hover:text-amber-600 hover:underline transition-colors"
                                    title="Copiar nombre">
                                    ${nuevo.nombre}
                                </span>
                            </div>
                        </td>
                        <td class="border p-2">${nuevo.unidad_medida}</td>
                        <td class="border p-2 text-right">S/ ${parseFloat(nuevo.precio_unitario).toFixed(4)}</td>
                        <td class="border p-2 text-right">${nuevo.peso ?? 0}</td>
                    </tr>
                `;

                    // Agregar la fila al final de la tabla
                    table.row.add($(nuevaFila)).draw(false);

                    toggleModal('modalCrearProducto');

                    // Limpiar campos del formulario
                    form.reset();
                    document.getElementById('modal_nombre').value = '';
                    document.getElementById('modal_precio').value = '';
                    document.getElementById('modal_peso').value = '';
                    document.getElementById('modal_unidad').selectedIndex = 0;

                    mostrarNotificacion('Producto agregado correctamente.', 'success');
                } else {
                    mostrarNotificacion(data.error || "Ocurrió un error al crear el producto.", 'error');
                }
            })
            .catch(err => {
                loader.classList.add('hidden');
                mostrarNotificacion("Error al crear el producto.", 'error');
                console.error("Error:", err);
            });
    });
</script>


<script>
    // Catálogo global de unidades, disponible en JS
    window.unidadesMedida = @json($unidadesMedida);
</script>


<script>
    let productosSeleccionados = {};
    let preciosOriginales = {};

    function agregarProducto() {
        const select = document.getElementById('select-producto');
        const cantidad = parseInt(document.getElementById('cantidad_producto').value);
        const id = select.value;
        const nombre = select.options[select.selectedIndex].dataset.nombre;
        const precio = parseFloat(select.options[select.selectedIndex].dataset.precio);
        const peso = parseFloat(select.options[select.selectedIndex].dataset.peso);
        const unidad = select.options[select.selectedIndex].dataset.unidad;

        if (!id || cantidad < 1) return;

        productosSeleccionados[id] = {
            nombre,
            precio,
            peso,
            unidad,
            cantidad
        };
        // Guardar precio original
        if (!preciosOriginales[id]) {
            preciosOriginales[id] = precio;
        }

        select.value = '';
        $('.select-producto').val(null).trigger('change');
        document.getElementById('cantidad_producto').value = '';
        renderizarTabla();
        sincronizarCheckboxes();
        actualizarContadorProductos();
    }

    function eliminarProducto(id) {
        delete productosSeleccionados[id];
        renderizarTabla();
        actualizarContadorProductos();
    }

    function editarCantidad(id, nuevaCantidad) {
        if (productosSeleccionados[id]) {
            productosSeleccionados[id].cantidad = parseInt(nuevaCantidad);
            renderizarTabla();
        }
    }

    function renderizarTabla() {
        const body = document.getElementById('detalle-body');
        const moneda = document.getElementById('tipo_moneda').value;
        const simbolo = moneda === 'USD' ? '$' : 'S/';
        const tipoCambio = {{ $precioDolar->precio }};
        body.innerHTML = '';
        let total = 0;
        let pesoTotal = 0;
        let contador = 1; // Contador para el número secuencial

        for (const id in productosSeleccionados) {
            const p = productosSeleccionados[id];
            const nombreEscapado = p.nombre.toUpperCase().replace(/"/g, '&quot;');
            // Precio unitario en la moneda seleccionada
            const precioUnitario = moneda === 'USD' ? (p.precio / tipoCambio) : p.precio;
            const subtotal = precioUnitario * p.cantidad;
            const pesoProd = p.peso * p.cantidad;
            total += subtotal;
            pesoTotal += pesoProd;

            const conteoVentas = window.conteoVentas[id] || 0;
            const conteoText = conteoVentas > 0 ?
                `<span class="text-red-500 text-xs">(${conteoVentas})</span>` :
                '';

            // Opciones del select de unidades
            let opcionesUnidad = '';
            if (Array.isArray(window.unidadesMedida) && window.unidadesMedida.length > 0) {
                window.unidadesMedida.forEach((u) => {
                    const selected = u === p.unidad ? 'selected' : '';
                    opcionesUnidad += `<option value="${u}" ${selected}>${u}</option>`;
                });
            } else {
                // fallback por si no hay catálogo
                opcionesUnidad = `<option value="${p.unidad}" selected>${p.unidad}</option>`;
            }

            body.innerHTML += `
            <tr>
                <td class="border px-2 text-center">${contador}</td>
                <td class="border px-2">${id}</td>
                <td class="border px-2">
                    <div class="flex items-center justify-between">
                        <span 
                            data-nombre="${nombreEscapado}"
                            onclick="copiarAlPortapapeles(this.dataset.nombre, 'Nombre copiado')"
                            class="cursor-pointer hover:text-amber-600 hover:underline transition-colors"
                            title="Copiar nombre">
                            ${p.nombre.toUpperCase()} ${conteoText}
                        </span>
                    </div> 
                </td>
                <td class="border px-2">
                    <select class="border rounded w-32"
                            onchange="editarUnidad('${id}', this.value)">
                        ${opcionesUnidad}
                    </select>
                </td>
                <td class="border px-2">
                    <input type="number" min="1" value="${p.cantidad}"
                        class="border rounded w-20"
                        onchange="editarCantidad('${id}', this.value)">
                </td>
                <td class="border px-2">
                    <input type="number" step="0.0001" min="0.0001"
                        value="${precioUnitario.toFixed(4)}"
                        class="border rounded w-32 text-right"
                        onchange="editarPrecio('${id}', this.value, '${moneda}')">
                </td>
                <td class="border px-2 whitespace-nowrap min-w-[100px] text-right">
                    ${simbolo} ${(precioUnitario / 1.18).toFixed(4)}
                </td>
                <td class="border px-2">
                    <input type="number" step="0.01" min="0" value="${p.peso}"
                        class="border rounded w-24 text-right"
                        onchange="editarPeso('${id}', this.value)">
                </td>
                <td class="border px-2 whitespace-nowrap min-w-[100px] text-right">
                    ${pesoProd.toFixed(2)} kg
                </td>
                <td class="border px-2 whitespace-nowrap min-w-[100px] text-right">
                    ${simbolo} ${subtotal.toFixed(2)}
                </td>
                <td class="border px-2 text-center">
                    <button type="button" onclick="eliminarProducto('${id}')"
                        style="color: #dc2626; transition: color 0.3s;"
                        onmouseover="this.style.color='#b91c1c';"
                        onmouseout="this.style.color='#dc2626';">
                        <x-delete-icon />
                    </button>
                </td>

                <!-- Hiddens que viajan al controlador -->
                <input type="hidden" name="productos[${id}][cantidad]" value="${p.cantidad}">
                <input type="hidden" name="productos[${id}][precio]" value="${p.precio}">
                <input type="hidden" name="productos[${id}][peso]" value="${p.peso}">
                <input type="hidden" name="productos[${id}][unidad]" value="${p.unidad}">
            </tr>
        `;
            contador++; // Incrementar el contador para el próximo producto
        }

        // Cálculo del IGV (18%) basado en el total bruto
        const IGV = total * 0.18;

        const retencion = document.getElementById('agente_retenedor').value === 'S' ?
            parseFloat((total * 0.03).toFixed(2)) :
            0;
        const neto = parseFloat((total - retencion).toFixed(2));

        const hayProductos = Object.keys(productosSeleccionados).length > 0;
        setBtnFacturaEstado(hayProductos);

        document.getElementById('total_bruto').innerText = `${simbolo} ${total.toFixed(2)}`;
        document.getElementById('IGV').innerText = `${simbolo} ${IGV.toFixed(2)}`;
        document.getElementById('monto_retencion').innerText = `${simbolo} ${retencion.toFixed(2)}`;
        document.getElementById('total_neto').innerText = `${simbolo} ${neto.toFixed(2)}`;
        document.getElementById('peso_neto').innerText = `${pesoTotal.toFixed(2)} kg`;

        //PARA EL MONTO PENDIENTE QUE SE ACTUALIZA EN TIEMPO REAL
        actualizarPendientePorFacturar();
    }

    // Nuevas funciones para editar precio y peso
    function editarPrecio(id, nuevoPrecio, monedaActual) {
        if (productosSeleccionados[id]) {
            const tipoCambio = {{ $precioDolar->precio }};

            // Convertir el nuevo precio a Soles si estamos en modo USD
            if (monedaActual === 'USD') {
                productosSeleccionados[id].precio = parseFloat(nuevoPrecio) * tipoCambio;
            } else {
                productosSeleccionados[id].precio = parseFloat(nuevoPrecio);
            }

            renderizarTabla();
        }
    }

    function editarPeso(id, nuevoPeso) {
        if (productosSeleccionados[id]) {
            productosSeleccionados[id].peso = parseFloat(nuevoPeso);
            renderizarTabla();
        }
    }

    function editarUnidad(id, nuevaUnidad) {
        if (productosSeleccionados[id]) {
            productosSeleccionados[id].unidad = nuevaUnidad;

            // Actualizamos el input hidden que se manda al backend
            const hidden = document.querySelector(
                `input[name="productos[${id}][unidad]"]`
            );

            if (hidden) {
                hidden.value = nuevaUnidad;
            }
        }
    }


    function aplicarPorcentajeTabla() {
        const porcentaje = parseFloat(document.getElementById('porcentaje_producto').value) || 100;
        // Aplicar el porcentaje a todos los productos seleccionados
        for (const id in productosSeleccionados) {
            if (preciosOriginales[id]) {
                productosSeleccionados[id].precio = preciosOriginales[id] * (porcentaje / 100);
            }
        }
        renderizarTabla();
    }

    $(document).ready(function() {
        $('.select-producto').select2({
            placeholder: "Busca un producto...",
            width: 'resolve'
        });

        $('#agente_retenedor, #tipo_moneda').change(() => renderizarTabla());
    });

    document.addEventListener('keydown', function(e) {
        // Agregar producto con Shift + Enter
        if (e.shiftKey && e.key.toLowerCase() === 'g') {
            e.preventDefault();
            agregarProducto();
        }

        // Enfocar select2 con Shift + N
        if (e.shiftKey && e.key.toLowerCase() === 'n') {
            e.preventDefault();
            $('#select-producto').select2('open');
        }
    });

    $('.select-empresa').select2({
        placeholder: 'Selecciona una empresa',
        width: 'resolve'
    });

    function consultarFormato(tipo, inputId) {
        const idReceptor = $('#select-empresa').val();
        const origen = $('input[name="origen_receptor"]').val(); // 'empresa' en esta vista

        if (!idReceptor) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor selecciona una empresa primero.'
            });
            return;
        }

        const idEmpresaInput = `${origen}_${idReceptor}`;
        let url = '';

        switch (tipo) {
            case 'solicitud':
                url = "{{ route('formato_n_solicitud.get_formato') }}";
                break;
            case 'cotizacion':
                url = "{{ route('formato_cotizaciones.get_formato') }}";
                break;
            case 'orden':
                url = "{{ route('formato_n_orden.get_formato') }}";
                break;
        }

        // Mostrar un pequeño indicador de carga
        const btn = event.currentTarget;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Consultando...';

        $.ajax({
            url: url,
            method: 'GET',
            data: {
                id_empresa: idEmpresaInput
            },
            success: function(response) {
                if (response.success && response.formato) {
                    if (response.formato === 'Números no disponibles') {
                        showAjaxToast('Sin números de cotización disponibles para esa fecha', 'error');
                    } else {
                        $(`#${inputId}`).val(response.formato);
                        // Disparar evento input para que otros listeners actúen
                        $(`#${inputId}`).trigger('input');
                    }
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin formato',
                        text: 'No se encontró un formato configurado para esta empresa.'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al consultar el formato.'
                });
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectCredito = document.getElementById('tipo_credito');
        const inputDiasPersonalizados = document.getElementById('dias_personalizados');
        const fechaPagoSpan = document.getElementById('fecha_pago_dinamica');
        const inputFechaEmision = document.getElementById('fecha_emision');

        function actualizarFechaPago() {
            let dias = 0;
            if (selectCredito.value === 'personalizado') {
                dias = parseInt(inputDiasPersonalizados.value) || 0;
            } else {
                dias = parseInt(selectCredito.value);
            }

            const fechaBase = new Date(inputFechaEmision.value + 'T00:00:00');
            if (isNaN(fechaBase)) return;

            const nuevaFecha = new Date(fechaBase);
            nuevaFecha.setDate(nuevaFecha.getDate() + dias);

            const dd = String(nuevaFecha.getDate()).padStart(2, '0');
            const mm = String(nuevaFecha.getMonth() + 1).padStart(2, '0');
            const yyyy = nuevaFecha.getFullYear();
            fechaPagoSpan.textContent = `${dd}/${mm}/${yyyy}`;
        }

        selectCredito.addEventListener('change', () => {
            const isPersonalizado = selectCredito.value === 'personalizado';
            inputDiasPersonalizados.classList.toggle('hidden', !isPersonalizado);
            actualizarFechaPago();
        });

        inputDiasPersonalizados.addEventListener('input', actualizarFechaPago);
        inputFechaEmision.addEventListener('change', actualizarFechaPago);
        actualizarFechaPago();

        // Mostrar RUC si ya viene un empresa preseleccionado al entrar
        const selectedEmpresa = document.querySelector('#select-empresa option:checked');
        if (selectedEmpresa) {
            const ruc = selectedEmpresa.dataset.ruc;
            if (ruc) {
                document.getElementById('ruc-display').textContent = ruc;
                document.getElementById('ruc-container').classList.remove('hidden');
            }
        }
    });
</script>

<script>
    let dtProductos;

    $(document).ready(function() {
        dtProductos = $('#myTable-productos').DataTable({
            responsive: false,
            pageLength: 8,
            lengthMenu: [8, 10, 25, 50, 100, 1000],
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            },
            drawCallback: function() {
                sincronizarFilasSeleccionadas();
            }
        });

        $('#myTable-productos tbody').on('click', 'tr', function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            const precio = parseFloat($(this).data('precio'));
            const peso = parseFloat($(this).data('peso'));
            const unidad = $(this).data('unidad');

            if (!id) return; // saltar filas sin data

            if (productosSeleccionados[id]) {
                delete productosSeleccionados[id];
                $(this).removeClass('fila-seleccionada');
            } else {
                productosSeleccionados[id] = {
                    nombre,
                    precio,
                    peso,
                    unidad,
                    cantidad: 1
                };
                if (!preciosOriginales[id]) preciosOriginales[id] = precio;
                $(this).addClass('fila-seleccionada');
            }

            renderizarTabla();
            actualizarContadorProductos();
        });
    });

    function sincronizarFilasSeleccionadas() {
        $('#myTable-productos tbody tr').each(function() {
            const id = $(this).data('id');
            if (!id) return;
            if (productosSeleccionados[id]) {
                $(this).addClass('fila-seleccionada');
            } else {
                $(this).removeClass('fila-seleccionada');
            }
        });
    }

    function sincronizarCheckboxes() {
        sincronizarFilasSeleccionadas();
    }
</script>

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);

        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('div').classList.remove('scale-95');
            }, 10);
            document.body.classList.add('overflow-hidden');

            // 👉 Aquí sincronizas los checkboxes solo si es el modal de productos
            if (id === 'modalTablaProductos') {
                sincronizarCheckboxes();
                actualizarContadorProductos();
            }

        } else {
            modal.classList.add('opacity-0');
            modal.querySelector('div').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }, 300);
        }
    }

    function closeModal(event, id) {
        const modal = document.getElementById(id);
        if (event.target === modal) {
            toggleModal(id);
        }
    }

    function actualizarContadorProductos() {
        const count = Object.keys(productosSeleccionados).length;
        const contador = document.getElementById('contador-productos');

        if (contador) {
            contador.textContent =
                `${count} ${count === 1 ? 'producto' : 'productos'} seleccionado${count === 1 ? '' : 's'}`;

            // Opcional: Cambiar el estilo según la cantidad
            if (count > 0) {
                contador.classList.remove('bg-blue-100', 'text-blue-800');
                contador.classList.add('bg-green-100', 'text-green-800');
            } else {
                contador.classList.remove('bg-green-100', 'text-green-800');
                contador.classList.add('bg-blue-100', 'text-blue-800');
            }
        }
    }
</script>
<script>
    function copiarAlPortapapeles(texto, mensaje = 'Copiado') {
        navigator.clipboard.writeText(texto)
            .then(() => {
                const aviso = document.createElement('div');
                aviso.textContent = mensaje;
                aviso.className =
                    'fixed bottom-8 left-1/2 -translate-x-1/2 bg-green-500 text-white text-sm px-4 py-2 rounded shadow z-50';
                document.body.appendChild(aviso);

                setTimeout(() => {
                    aviso.remove();
                }, 2000);
            })
            .catch(err => {
                console.error('Error al copiar:', err);
            });
    }
</script>
<script>
    // Función global para copiar al portapapeles (igual a tu ejemplo)
    function copiarAlPortapapeles(texto, mensaje = 'Copiado') {
        navigator.clipboard.writeText(texto)
            .then(() => {
                const aviso = document.createElement('div');
                aviso.textContent = mensaje;
                aviso.className =
                    'fixed bottom-8 left-1/2 -translate-x-1/2 bg-green-500 text-white text-sm px-4 py-2 rounded shadow z-50';
                document.body.appendChild(aviso);

                setTimeout(() => {
                    aviso.remove();
                }, 2000);
            })
            .catch(err => {
                console.error('Error al copiar:', err);
            });
    }

    // Agregar esta función para actualizar los conteos cuando cambia el empresa
    $(document).ready(function() {
        $('.select-empresa').select2();

        // Manejar cambio de empresa
        $('#select-empresa').on('change', function() {
            const empresaId = $(this).val();
            if (empresaId) {
                // Recargar la página con el nuevo empresa seleccionado
                window.location.href =
                    "{{ route('factura.create_empresa', $inventario->id_inventario) }}?receptor=" +
                    empresaId;
            }
        });

        // Mostrar RUC al seleccionar empresa
        $('#select-empresa').on('change', async function() {
            const selectedOption = $(this).find(':selected');
            const ruc = selectedOption.data('ruc');
            const empresaId = $(this).val();

            if (ruc) {
                $('#ruc-display').text(ruc);
                $('#ruc-container').removeClass('hidden');
            } else {
                $('#ruc-container').addClass('hidden');
            }
        });

        $('#ruc-display').on('click', function() {
            const ruc = $(this).text();
            copiarAlPortapapeles(ruc, 'RUC copiado');
        });
    });

    // Modificar la función que renderiza las opciones del select2
    function formatProducto(producto) {
        if (!producto.id) return producto.text;

        // Obtener el conteo de ventas para este producto (pasado desde el controlador)
        const conteoVentas = window.conteoVentas[producto.id] || 0;
        const conteoText = conteoVentas > 0 ? `<span class="text-red-500 text-xs">(${conteoVentas})</span>` : '';

        return $(
            `<div class="flex justify-between items-center">
            <span>${producto.text}</span>
            ${conteoText}
        </div>`
        );
    }

    // Inicializar select2 con el formateador personalizado
    $('.select-producto').select2({
        placeholder: "Busca un producto...",
        width: 'resolve',
        templateResult: formatProducto,
        templateSelection: formatProducto
    });

    // Pasar los datos de conteoVentas desde PHP a JavaScript
    window.conteoVentas = @json($conteoVentas ?? []);
</script>
<script>
    // También llama cuando cambia la moneda
    $('#tipo_moneda').change(() => {
        renderizarTabla();
        actualizarPendientePorFacturar();
    });

    //Para calcular el monto por facturar en tiempo real
    function actualizarPendientePorFacturar() {
        const pendienteContainer = document.getElementById('pendiente-facturar-container');
        if (!pendienteContainer) return;

        const pendienteInicial = {{ $pendientePEN ?? 0 }};
        const totalBruto = calcularTotalBrutoEnSoles();
        const precioDolar = {{ $precioDolar->precio }};
        const diferencia = pendienteInicial - totalBruto;

        // Determinar el texto a mostrar
        let textoPendienteSoles, textoPendienteDolares;

        if (diferencia >= 0) {
            // Dentro del límite o justo en el límite
            textoPendienteSoles = diferencia.toFixed(2);
            textoPendienteDolares = (diferencia / precioDolar).toFixed(2);
        } else {
            // Superado el límite (mostrar excedente en positivo)
            const excedente = Math.abs(diferencia);
            textoPendienteSoles = `+${excedente.toFixed(2)}`;
            textoPendienteDolares = `+${(excedente / precioDolar).toFixed(2)}`;
        }

        // Actualizar valores
        document.getElementById('pendiente-soles').textContent = textoPendienteSoles;
        document.getElementById('pendiente-dolares').textContent = textoPendienteDolares;

        // Cambiar color y texto según el estado
        if (diferencia <= 0) {
            pendienteContainer.classList.remove('text-red-600');
            pendienteContainer.classList.add('text-green-500');
            pendienteContainer.querySelector('strong').textContent = 'Excedente facturado:';
        } else {
            pendienteContainer.classList.remove('text-green-500');
            pendienteContainer.classList.add('text-red-600');
            pendienteContainer.querySelector('strong').textContent = 'Pendiente por facturar este mes:';
        }
    }

    function calcularTotalBrutoEnSoles() {
        let total = 0;
        const moneda = document.getElementById('tipo_moneda').value;
        const tipoCambio = {{ $precioDolar->precio }};

        for (const id in productosSeleccionados) {
            const p = productosSeleccionados[id];
            // Siempre calcular en Soles, independientemente de la moneda seleccionada
            total += p.precio * p.cantidad;
        }

        return total;
    }
</script>
<script>
    //CUANDO FACTURA EN DOLARES EL TIPO_MONEDA
    document.addEventListener("DOMContentLoaded", function() {
        const selectMoneda = document.getElementById("tipo_moneda");
        const alertaUsd = document.getElementById("alerta-usd");

        function verificarMoneda() {
            if (selectMoneda.value === "USD") {
                selectMoneda.classList.add("bg-red-50", "text-red-700", "font-bold", "border-red-300");
                alertaUsd.classList.remove("hidden");
            } else {
                selectMoneda.classList.remove("bg-red-50", "text-red-700", "font-bold", "border-red-300");
                alertaUsd.classList.add("hidden");
            }
        }

        selectMoneda.addEventListener("change", verificarMoneda);
        verificarMoneda(); // Ejecutar al cargar
    });
</script>

<script>
    // Mostrar datos de la cuenta seleccionada en el cuadro
    function seleccionarCuenta(nombre, moneda, cuenta, cci, sede) {
        document.getElementById('cuentaBanco').textContent = `${nombre} - ${sede}`;
        document.getElementById('cuentaMoneda').textContent = moneda;

        // Construimos la línea completa
        const linea = `Cuenta: ${cuenta} | CCI: ${cci}`;
        document.getElementById('cuentaLinea').textContent = linea;

        document.getElementById('cuadroCuenta').classList.remove('hidden');

        const triggerBtn = document.querySelector('#dropdownCuentaBtn');
        if (triggerBtn) {
            triggerBtn.innerHTML = `
            ${nombre} (${moneda})
            <svg class="ml-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 9l-7 7-7-7" />
            </svg>
        `;
        }
    }

    // Script para notificación tipo Toast
    window.mostrarAvisoFlotante = function(mensaje) {
        const aviso = document.createElement('div');
        aviso.textContent = mensaje;
        aviso.className =
            'fixed bottom-8 left-1/2 -translate-x-1/2 bg-green-500 text-white text-sm px-4 py-2 rounded shadow z-50';
        document.body.appendChild(aviso);

        setTimeout(() => {
            aviso.remove();
        }, 2000);
    };

    // Mostrar/ocultar campo precio_dolar_factura y cargar API
    document.addEventListener("DOMContentLoaded", function() {
        const selectMoneda = document.getElementById("tipo_moneda");
        const precioDolarContainer = document.getElementById("precio_dolar_factura_container");
        const fechaEmision = document.getElementById("fecha_emision");
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        async function cargarPrecioDolarPorFecha(fecha) {
            const inputPrecioDolar = document.getElementById('precio_dolar_factura');
            if (!inputPrecioDolar) return;

            try {
                // Estado de carga
                inputPrecioDolar.type = 'text';
                inputPrecioDolar.value = '-';
                inputPrecioDolar.disabled = true;

                const fechaObj = new Date(fecha + 'T00:00:00');
                const month = fechaObj.getMonth() + 1;
                const year = fechaObj.getFullYear();

                const response = await fetch("{{ route('consultas.tipoCambio') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        month,
                        year
                    })
                });

                const res = await response.json();

                if (!res.success || !res.data) {
                    inputPrecioDolar.value = '';
                    return;
                }

                const datos = Array.isArray(res.data) ? res.data : [res.data];
                const tipoCambioFecha = datos.find(item => item.date === fecha);

                if (!tipoCambioFecha) {
                    inputPrecioDolar.value = '';
                    return;
                }

                const venta = parseFloat(tipoCambioFecha.sell_price);
                if (!isNaN(venta)) {
                    inputPrecioDolar.value = venta.toFixed(3);
                    if (window.mostrarAvisoFlotante) window.mostrarAvisoFlotante(
                        'Precio de dólar actualizado');
                }

            } catch (error) {
                console.error('Error al obtener tipo de cambio:', error);
                inputPrecioDolar.value = '';
            } finally {
                inputPrecioDolar.type = 'number';
                inputPrecioDolar.disabled = false;
            }
        }

        const monedaGuia = "{{ $monedaPendiente ?? '' }}";

        function togglePrecioDolarFactura() {
            const esUSD = selectMoneda.value === "USD";
            const debeCargar = esUSD || monedaGuia === "USD";
            if (esUSD) {
                precioDolarContainer.classList.remove("hidden");
            } else {
                precioDolarContainer.classList.add("hidden");
            }
            if (debeCargar) {
                const fecha = fechaEmision.value;
                if (fecha) {
                    cargarPrecioDolarPorFecha(fecha);
                }
            } else if (!esUSD) {
                document.getElementById("precio_dolar_factura").value = "";
            }
        }

        // Listeners
        selectMoneda.addEventListener("change", togglePrecioDolarFactura);

        fechaEmision.addEventListener("change", function() {
            const esUSD = selectMoneda.value === "USD";
            if (esUSD || monedaGuia === "USD") {
                cargarPrecioDolarPorFecha(this.value);
            }
        });

        // Ejecutar al inicio
        togglePrecioDolarFactura();
    });
</script>

<script>
    let sistemaCorrelativo = '';
    let sistemaNroCoti = '';
    let sistemaNroSol = '';

    function actualizarCorrelativo() {
        const fechaInput = document.getElementById('fecha_emision');
        const receptorInput = document.getElementById('select-empresa');

        const fecha = fechaInput?.value;
        const receptor = receptorInput?.value;

        if (!fecha || !receptor) return;

        correlativoSetPorSistema = true;

        fetch(
                `{{ route('facturas.previsualizarCorrelativo') }}?fecha_emision=${fecha}&id_receptor=${receptor}&origen_receptor=empresa&id_empresa_contexto={{ $empresa->id_empresas_clientes }}`
            )
            .then(res => res.json())
            .then(data => {
                // Actualizar Nro Orden
                if (data.nombre_correlativo &&
                    data.nombre_correlativo !== 'SIN FORMATO' &&
                    data.nombre_correlativo !== 'Falta información') {
                    document.getElementById('nombre_correlativo').value = data.nombre_correlativo;
                    sistemaCorrelativo = data.nombre_correlativo;
                    document.getElementById('error-correlativo').classList.add('hidden');
                    document.getElementById('nombre_correlativo').classList.remove('border-red-500');
                }

                // Actualizar Nro Cotización
                if (data.nro_cotizacion &&
                    data.nro_cotizacion !== 'SIN FORMATO' &&
                    data.nro_cotizacion !== 'Falta información') {
                    if (data.nro_cotizacion === 'Números no disponibles') {
                        document.getElementById('nro_cotizacion').value = '';
                        mostrarNotificacion('Sin números de cotización disponibles para esa fecha', 'error');
                    } else {
                        document.getElementById('nro_cotizacion').value = data.nro_cotizacion;
                        sistemaNroCoti = data.nro_cotizacion;
                        document.getElementById('error-nro-cotizacion').classList.add('hidden');
                        document.getElementById('nro_cotizacion').classList.remove('border-red-500');
                    }
                }

                // Actualizar Nro Solicitud
                if (data.numero_solicitud &&
                    data.numero_solicitud !== 'SIN FORMATO' &&
                    data.numero_solicitud !== 'Falta información') {
                    document.getElementById('numero_solicitud').value = data.numero_solicitud;
                    sistemaNroSol = data.numero_solicitud;
                    document.getElementById('error-solicitud').classList.add('hidden');
                    document.getElementById('numero_solicitud').classList.remove('border-red-500');
                }
            })
            .catch(err => console.error('Error correlativo:', err));
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Listener en fecha_emision
        const fechaInput = document.getElementById('fecha_emision');
        if (fechaInput) {
            fechaInput.addEventListener('change', actualizarCorrelativo);
            fechaInput.addEventListener('input', actualizarCorrelativo);
        }

        // Si ya hay receptor preseleccionado al cargar
        const receptor = document.getElementById('select-empresa')?.value;
        if (receptor) {
            actualizarCorrelativo();
        }

        // Validar correlativo único
        const nombreCorrelativoInput = document.getElementById('nombre_correlativo');
        const errorCorrelativoSpan = document.getElementById('error-correlativo');

        if (!nombreCorrelativoInput) return;

        async function validarCorrelativoUnico() {
            const value = nombreCorrelativoInput.value;
            if (!value) return;
            if (value === sistemaCorrelativo) return;

            const idFactura = document.getElementById('id_factura')?.value ?? '';

            try {
                const response = await fetch(
                    `{{ route('facturas.check_correlativo_unico') }}?nombre_correlativo=${encodeURIComponent(value)}&id_factura=${idFactura}`
                );
                const data = await response.json();

                if (!data.unico) {
                    errorCorrelativoSpan.textContent = data.mensaje;
                    errorCorrelativoSpan.classList.remove('hidden');
                    nombreCorrelativoInput.classList.add('border-red-500');
                    setBtnFacturaEstado(false);
                } else {
                    errorCorrelativoSpan.classList.add('hidden');
                    nombreCorrelativoInput.classList.remove('border-red-500');
                    const hayProductos = Object.keys(productosSeleccionados).length > 0;
                    setBtnFacturaEstado(hayProductos);
                }
            } catch (error) {
                console.error('Error validando correlativo:', error);
            }
        }

        const nroCotiInput = document.getElementById('nro_cotizacion');
        const errorCotiSpan = document.getElementById('error-nro-cotizacion');

        async function validarCorrelativoCotiUnico() {
            const value = nroCotiInput.value;
            if (!value) return;
            if (value === sistemaNroCoti) return;

            const idFactura = document.getElementById('id_factura')?.value ?? '';

            try {
                const response = await fetch(
                    `{{ route('facturas.check_correlativo_coti_unico') }}?nro_cotizacion=${encodeURIComponent(value)}&id_factura=${idFactura}&id_empresa_contexto={{ $empresa->id_empresas_clientes }}`
                );
                const data = await response.json();

                if (!data.unico) {
                    errorCotiSpan.textContent = data.mensaje;
                    errorCotiSpan.classList.remove('hidden');
                    nroCotiInput.classList.add('border-red-500');
                    setBtnFacturaEstado(false);
                } else {
                    errorCotiSpan.classList.add('hidden');
                    nroCotiInput.classList.remove('border-red-500');
                    const hayProductos = Object.keys(productosSeleccionados).length > 0;
                    setBtnFacturaEstado(hayProductos)
                }
            } catch (error) {
                console.error('Error validando correlativo coti:', error);
            }
        }

        if (nroCotiInput) {
            nroCotiInput.addEventListener('input', validarCorrelativoCotiUnico);
        }
        if (nombreCorrelativoInput) {
            nombreCorrelativoInput.addEventListener('input', validarCorrelativoUnico);
        }

        const nroSolInput = document.getElementById('numero_solicitud');
        const errorSolSpan = document.getElementById('error-solicitud');

        async function validarSolicitudUnico() {
            const value = nroSolInput?.value;
            if (value === sistemaNroSol) return;

            const idFactura = document.getElementById('id_factura')?.value ?? '';

            try {
                const responseSol = await fetch(
                    `{{ route('facturas.check_solicitud_unico') }}?numero_solicitud=${encodeURIComponent(value)}&id_factura=${idFactura}`
                );
                const data = await responseSol.json();

                if (!data.unico) {
                    errorSolSpan.textContent = data.mensaje;
                    errorSolSpan.classList.remove('hidden');
                    nroSolInput.classList.add('border-red-500');
                    setBtnFacturaEstado(false);
                } else {
                    errorSolSpan.classList.add('hidden');
                    nroSolInput.classList.remove('border-red-500');
                    const hayProductos = typeof productosSeleccionados !== 'undefined' ? Object.keys(
                        productosSeleccionados).length > 0 : true;
                    setBtnFacturaEstado(hayProductos);
                }
            } catch (error) {
                console.error('Error validando solicitud:', error);
            }
        }

        if (nroSolInput) {
            nroSolInput.addEventListener('input', validarSolicitudUnico);
        }
    });
</script>

<script>
    function setBtnFacturaEstado(habilitado) {
        const btnCrear = document.getElementById('btn-crear-factura');
        const btnWrapper = document.getElementById('btn-crear-factura-wrapper');

        btnCrear.disabled = !habilitado;
        btnCrear.classList.toggle('pointer-events-none', !habilitado);
        btnCrear.classList.toggle('opacity-50', !habilitado);
        btnWrapper.classList.toggle('cursor-not-allowed', !habilitado);
        btnWrapper.classList.toggle('cursor-pointer', habilitado);
    }
</script>

<script>
    // Eliminar espacios y forzar mayúsculas automáticamente en Nro Solicitud, Nro Cotización y Nro Orden
    document.addEventListener('DOMContentLoaded', function() {
        const camposSinEspacios = ['numero_solicitud', 'nro_cotizacion', 'nombre_correlativo'];

        camposSinEspacios.forEach(function(id) {
            const input = document.getElementById(id);
            if (!input) return;

            // Prevenir que se escriba un espacio con el teclado
            input.addEventListener('keydown', function(e) {
                if (e.key === ' ') {
                    e.preventDefault();
                }
            });

            // Quitar espacios y forzar mayúsculas al escribir o pegar
            input.addEventListener('input', function() {
                const pos = this.selectionStart;
                const original = this.value;
                const transformado = original.replace(/\s/g, '').toUpperCase();
                if (original !== transformado) {
                    this.value = transformado;
                    // Ajustar posición del cursor
                    const diff = original.length - transformado.length;
                    this.setSelectionRange(pos - diff, pos - diff);
                }
            });
        });
    });
</script>

<script>
    /* =====================================================================
   PEGAR DESDE EXCEL — v2
===================================================================== */
    let filasParseadas = [];
    document.getElementById('paste-excel-area').addEventListener('input', function() {
        setTimeout(() => parsearPegado(this.value.trim()), 50);
    });
    document.getElementById('paste-excel-area').addEventListener('paste', function() {
        setTimeout(() => parsearPegado(this.value.trim()), 80);
    });

    function parsearPegado(texto) {
        const errDiv = document.getElementById('paste-error');
        const preview = document.getElementById('paste-preview');
        const btnImport = document.getElementById('btn-importar-pegado');

        errDiv.classList.add('hidden');
        preview.classList.add('hidden');
        btnImport.disabled = true;
        filasParseadas = [];

        if (!texto) return;

        const lineas = texto.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        if (lineas.length === 0) return;

        const HEADER_KEYS = ['codigo', 'descripcion', 'unidad', 'cant', 'precio', 'unit', 'total'];
        let headerIdx = -1;
        let colMap = {};

        for (let i = 0; i < Math.min(lineas.length, 5); i++) {
            const cols = lineas[i].split('\t').map(c => c.trim().toLowerCase());
            const matches = cols.filter(c => HEADER_KEYS.some(k => c.includes(k)));
            if (matches.length >= 3) {
                headerIdx = i;
                // Mapear índices de columna
                cols.forEach((c, idx) => {
                    if (c.includes('cod')) colMap.codigo = idx;
                    else if (c.includes('desc') || c.includes('prod')) colMap.nombre = idx;
                    else if (c.includes('unid') || c === 'um' || c === 'u.m') colMap.unidad = idx;
                    else if (c.includes('cant')) colMap.cantidad = idx;
                    else if ((c.includes('peso') && c.includes('unit')) || c === 'peso unit.') colMap.peso =
                        idx;
                    else if ((c.includes('p.') && c.includes('unit')) || c === 'precio unit' || c === 'p. unit')
                        colMap.precio = idx;
                });
                break;
            }
        }

        if (headerIdx === -1 || Object.keys(colMap).length < 3) {
            const primeraCols = lineas[0].split('\t').length;
            if (primeraCols >= 6) {
                colMap = {
                    codigo: 0,
                    nombre: 1,
                    unidad: 2,
                    cantidad: 3,
                    peso: 4,
                    precio: 5
                };
            } else if (primeraCols >= 5) {
                colMap = {
                    codigo: 0,
                    nombre: 1,
                    unidad: 2,
                    cantidad: 3,
                    precio: 4
                };
            } else {
                errDiv.textContent =
                    'Formato no reconocido. Se necesitan al menos 5 columnas separadas por tabulación.';
                errDiv.classList.remove('hidden');
                return;
            }
            headerIdx = -1;
        }

        // ── Parsear filas de datos ─────────────────────────────────────────
        const filas = [];
        const dataLineas = lineas.slice(headerIdx + 1);

        for (const linea of dataLineas) {
            const cols = linea.split('\t').map(c => c.trim());
            if (cols.length < 4) continue;

            const codigo = colMap.codigo !== undefined ? cols[colMap.codigo]?.toUpperCase() : '';
            const nombre = colMap.nombre !== undefined ? cols[colMap.nombre]?.toUpperCase() : '';
            const unidad = colMap.unidad !== undefined ? cols[colMap.unidad]?.toUpperCase() : 'UNID';
            const cantidad = colMap.cantidad !== undefined ? parseFloat(cols[colMap.cantidad]?.replace(',', '.')) || 1 :
                1;
            const peso = colMap.peso !== undefined ? parseFloat(cols[colMap.peso]?.replace(',', '.')) || 0 : 0;
            const precio = colMap.precio !== undefined ? parseFloat(cols[colMap.precio]?.replace(',', '.')) || 0 : 0;

            if ((!codigo && !nombre) || precio <= 0) continue;
            if (codigo.toLowerCase().includes('cod') || nombre.toLowerCase().includes('desc')) continue;

            filas.push({
                codigo,
                nombre,
                unidad,
                cantidad,
                peso,
                precio
            });
        }

        if (filas.length === 0) {
            errDiv.textContent =
                'No se reconocieron filas válidas. Verifica que el formato tenga al menos: CÓDIGO, DESCRIPCIÓN, UNIDAD, CANTIDAD, PRECIO.';
            errDiv.classList.remove('hidden');
            return;
        }

        filasParseadas = filas;

        // ── Mostrar preview ───
        const tbody = document.getElementById('paste-preview-body');
        tbody.innerHTML = filas.map(f => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-2 py-1 font-mono text-blue-700">${f.codigo || '—'}</td>
            <td class="px-2 py-1">${f.nombre}</td>
            <td class="px-2 py-1">${f.unidad}</td>
            <td class="px-2 py-1 text-right">${f.cantidad}</td>
            <td class="px-2 py-1 text-right">${f.peso}</td>
            <td class="px-2 py-1 text-right">S/ ${f.precio.toFixed(4)}</td>
            <td class="px-2 py-1 text-center">
                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">pendiente</span>
            </td>
        </tr>
    `).join('');

        document.getElementById('paste-count').textContent = filas.length;
        preview.classList.remove('hidden');
        btnImport.disabled = false;
    }

    document.getElementById('btn-importar-pegado').addEventListener('click', async function() {
        const btn = this;
        const errDiv = document.getElementById('paste-error');
        errDiv.classList.add('hidden');
        if (filasParseadas.length === 0) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Procesando...';

        const idInventario = {{ $empresa->inventarios->first()->id_inventario }};
        const tipoCambio = {{ $precioDolar->precio }};
        const moneda = document.getElementById('tipo_moneda').value;
        const existentesPorCodigo = {};
        const existentesPorNombre = {};

        const selectOpts = document.getElementById('select-producto').options;
        for (const opt of selectOpts) {
            if (!opt.value) continue;
            const id = opt.value.toUpperCase();
            const nombre = (opt.getAttribute('data-nombre') || '').toUpperCase().trim();
            const info = {
                id: opt.value,
                precio: parseFloat(opt.getAttribute('data-precio') || 0),
                peso: parseFloat(opt.getAttribute('data-peso') || 0),
                unidad: opt.getAttribute('data-unidad') || 'UNID',
                nombre,
            };
            existentesPorCodigo[id] = info;
            if (nombre) existentesPorNombre[nombre] = info;
        }

        function buscarProducto(codigo, nombre) {
            if (codigo && existentesPorCodigo[codigo]) return existentesPorCodigo[codigo];
            if (nombre && existentesPorNombre[nombre]) return existentesPorNombre[nombre];
            if (nombre) {
                for (const key of Object.keys(existentesPorNombre)) {
                    if (key.includes(nombre) || nombre.includes(key)) return existentesPorNombre[key];
                }
            }
            return null;
        }

        function actualizarOpcionSelect(id, nuevaUnidad, nuevoPrecioSoles, nuevoPeso) {
            const selectEl = document.getElementById('select-producto');
            for (const opt of selectEl.options) {
                if (opt.value === id) {
                    opt.setAttribute('data-unidad', nuevaUnidad);
                    opt.setAttribute('data-precio', nuevoPrecioSoles);
                    opt.setAttribute('data-peso', nuevoPeso);
                    break;
                }
            }
        }

        const errores = [];
        const filasRows = document.querySelectorAll('#paste-preview-body tr');

        for (let i = 0; i < filasParseadas.length; i++) {
            const f = filasParseadas[i];
            const row = filasRows[i];
            const statusCell = row?.querySelector('td:last-child span');
            const precioSoles = moneda === 'USD' ? f.precio * tipoCambio : f.precio;

            try {
                const encontrado = buscarProducto(f.codigo, f.nombre);

                if (encontrado) {
                    // ── ENCONTRADO: actualizar unidad, cantidad, precio, peso ──
                    productosSeleccionados[encontrado.id] = {
                        nombre: encontrado.nombre,
                        precio: precioSoles,
                        peso: f.peso,
                        unidad: f.unidad,
                        cantidad: f.cantidad,
                    };
                    preciosOriginales[encontrado.id] = precioSoles;
                    actualizarOpcionSelect(encontrado.id, f.unidad, precioSoles, f.peso);

                    if (statusCell) {
                        statusCell.textContent = 'encontrado';
                        statusCell.className =
                            'px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700';
                    }
                } else {
                    // ── NO EXISTE: crear producto ──────────────────────────────
                    if (statusCell) {
                        statusCell.textContent = 'creando...';
                        statusCell.className =
                            'px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700';
                    }

                    const formData = new FormData();
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                    formData.append('id_inventario', idInventario);
                    formData.append('nombre', f.nombre);
                    formData.append('unidad_medida', f.unidad);
                    formData.append('precio_unitario', precioSoles.toFixed(4));
                    formData.append('peso', f.peso);
                    formData.append('estado', 1);

                    const res = await fetch("{{ route('productos.store') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData,
                    });
                    const data = await res.json();
                    if (!data.success) throw new Error(data.error || 'Error al crear');

                    const nuevo = data.producto;

                    // Agregar al select
                    const option = document.createElement('option');
                    option.value = nuevo.id_producto;
                    option.text =
                        `[${nuevo.id_producto}] ${nuevo.nombre} – S/ ${parseFloat(nuevo.precio_unitario).toFixed(4)}`;
                    option.setAttribute('data-nombre', nuevo.nombre);
                    option.setAttribute('data-precio', nuevo.precio_unitario);
                    option.setAttribute('data-peso', nuevo.peso ?? 0);
                    option.setAttribute('data-unidad', nuevo.unidad_medida);
                    document.getElementById('select-producto').appendChild(option);
                    $('.select-producto').select2();

                    productosSeleccionados[nuevo.id_producto] = {
                        nombre: nuevo.nombre,
                        precio: parseFloat(nuevo.precio_unitario),
                        peso: parseFloat(nuevo.peso ?? 0),
                        unidad: nuevo.unidad_medida,
                        cantidad: f.cantidad,
                    };
                    preciosOriginales[nuevo.id_producto] = parseFloat(nuevo.precio_unitario);

                    if (statusCell) {
                        statusCell.textContent = 'creado';
                        statusCell.className = 'px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700';
                    }
                }
            } catch (e) {
                errores.push(`${f.codigo || f.nombre}: ${e.message}`);
                if (statusCell) {
                    statusCell.textContent = 'error';
                    statusCell.className = 'px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700';
                }
            }
        }

        renderizarTabla();
        sincronizarCheckboxes();
        actualizarContadorProductos();

        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Importar productos';

        if (errores.length > 0) {
            errDiv.textContent = 'Errores: ' + errores.join(' | ');
            errDiv.classList.remove('hidden');
        } else {
            document.getElementById('paste-excel-area').value = '';
            filasParseadas = [];
            toggleModal('modalPegarProductos');
        }
    });
</script>

<script>
    function mostrarNotificacion(mensaje, tipo = 'success') {
        showAjaxToast(mensaje, tipo);
    }

    function consultarFormato(tipo, inputId) {
        const idReceptor = $('#select-empresa').val();
        const origen = $('input[name="origen_receptor"]').val(); // 'empresa'

        if (!idReceptor) {
            mostrarNotificacion('Por favor selecciona una empresa primero.', 'error');
            return;
        }

        const idEmpresaInput = `${origen}_${idReceptor}`;
        let url = '';

        switch (tipo) {
            case 'solicitud':
                url = "{{ route('formato_n_solicitud.get_formato') }}";
                break;
            case 'cotizacion':
                url = "{{ route('formato_cotizaciones.get_formato') }}";
                break;
            case 'orden':
                url = "{{ route('formato_n_orden.get_formato') }}";
                break;
        }

        const btn = event.currentTarget;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i>...';

        $.ajax({
            url: url,
            method: 'GET',
            data: {
                id_empresa: idEmpresaInput,
                id_empresa_emisor: $('input[name="id_empresa_contexto"]').val()
            },
            success: function(response) {
                if (response.success && response.formato) {
                    if (response.formato === 'Números no disponibles') {
                        mostrarNotificacion('Sin números de cotización disponibles para esa fecha',
                            'error');
                    } else {
                        $(`#${inputId}`).val(response.formato);
                        $(`#${inputId}`).trigger('input');
                        mostrarNotificacion('Formato obtenido correctamente.');
                    }
                } else {
                    mostrarNotificacion('No se encontró un formato configurado para esta empresa.', 'info');
                }
            },
            error: function() {
                mostrarNotificacion('Ocurrió un error al consultar el formato.', 'error');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mainForm = document.querySelector('form[action*="factura.store"]');
        if (!mainForm) return;

        let isSubmitting = false;

        mainForm.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            isSubmitting = true;

            const btnCrear = document.getElementById('btn-crear-factura');
            if (btnCrear) {
                btnCrear.disabled = true;
                btnCrear.classList.add('opacity-50', 'pointer-events-none');
                btnCrear.innerHTML = <
                    svg class = "animate-spin -ml-1 mr-2 h-4 w-4 text-white inline"
                xmlns = "http://www.w3.org/2000/svg"
                fill = "none"
                viewBox = "0 0 24 24" >
                    <
                    circle class = "opacity-25"
                cx = "12"
                cy = "12"
                r = "10"
                stroke = "currentColor"
                stroke - width = "4" > < /circle> <
                path class = "opacity-75"
                fill = "currentColor"
                d = "M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" > < /path> < /
                    svg >
                    Procesando...;
            }
        });
    });
</script>

<!-- Modal de Números Disponibles -->
<x-modal-show id="modalNumerosDisponibles" title="Números de Cotización Disponibles">
    <div id="lista-numeros-disponibles" class="grid grid-cols-2 md:grid-cols-4 gap-2 max-h-60 overflow-y-auto p-2">
        <!-- JS fills this -->
    </div>
    <div class="mt-4 flex justify-end">
        <x-secondary-button onclick="toggleModal('modalNumerosDisponibles')">Cerrar</x-secondary-button>
    </div>
</x-modal-show>

<script>
    function abrirModalDisponibles() {
        const idEmisor = document.querySelector('input[name="id_empresa_contexto"]')?.value;
        const fechaEmision = document.getElementById('fecha_emision')?.value;
        
        if (!idEmisor) {
            mostrarNotificacion('Seleccione empresa emisora primero', 'warning');
            return;
        }

        const btnLista = document.getElementById('lista-numeros-disponibles');
        btnLista.innerHTML = '<p class="text-sm text-gray-500 col-span-full text-center">Cargando...</p>';
        toggleModal('modalNumerosDisponibles');

        fetch(`{{ route('formato_cotizaciones_2.get_disponibles') }}?id_empresa_emisor=${idEmisor}&fecha_emision=${fechaEmision || ''}`)
            .then(res => res.json())
            .then(data => {
                btnLista.innerHTML = '';
                
                if (data.success && data.disponibles && data.disponibles.length > 0) {
                    data.disponibles.forEach(num => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'p-2 border rounded-md hover:bg-blue-50 focus:ring-2 focus:ring-blue-500 text-sm font-medium';
                        btn.textContent = num;
                        btn.onclick = () => {
                            document.getElementById('nro_cotizacion').value = num;
                            document.getElementById('nro_cotizacion').classList.remove('border-red-500');
                            const errSpan = document.getElementById('error-nro-cotizacion');
                            if(errSpan) errSpan.classList.add('hidden');
                            toggleModal('modalNumerosDisponibles');
                            mostrarNotificacion('Número asignado correctamente');
                        };
                        btnLista.appendChild(btn);
                    });
                } else {
                    btnLista.innerHTML = '<p class="text-sm text-gray-500 col-span-full text-center">No hay números disponibles (fecha llena) o formato no configurado.</p>';
                }
            })
            .catch(err => {
                console.error(err);
                btnLista.innerHTML = '<p class="text-sm text-red-500 col-span-full text-center">Error al cargar.</p>';
            });
    }
</script>

<!-- Modal de Programación de Transporte -->
<x-modal-show id="modalProgramacionTransporte" title="Calendario de Programación de Transporte" size="full">
    <div id="programacion-modal-content" class="p-2">
        <!-- Contenido dinámico cargado vía AJAX -->
    </div>
    <div class="mt-4 flex justify-end">
        <x-secondary-button onclick="toggleModal('modalProgramacionTransporte')">Cerrar</x-secondary-button>
    </div>
</x-modal-show>

<script>
    function abrirModalProgramacion() {
        const selectedChofer = document.getElementById('id_programacion').value;
        const selectedDate = document.getElementById('fecha_traslado').value;

        let year = new Date().getFullYear();
        let month = new Date().getMonth() + 1;

        if (selectedDate) {
            const parts = selectedDate.split('-');
            if (parts.length === 3) {
                year = parseInt(parts[0], 10);
                month = parseInt(parts[1], 10);
            }
        }

        document.getElementById('programacion-modal-content').innerHTML = '<p class="text-sm text-gray-500 text-center py-6">Cargando programación...</p>';
        toggleModal('modalProgramacionTransporte');

        actualizarGrillaAJAX(year, month, selectedChofer, selectedDate);
    }

    function cargarGrillaProgramacion() {
        const year = document.getElementById('modal_year').value;
        const month = document.getElementById('modal_month').value;
        const selectedChofer = document.getElementById('id_programacion').value;
        const selectedDate = document.getElementById('fecha_traslado').value;

        actualizarGrillaAJAX(year, month, selectedChofer, selectedDate);
    }

    function actualizarGrillaAJAX(year, month, selectedChofer, selectedDate) {
        const container = document.getElementById('programacion-modal-content');
        const url = `{{ route('facturas.programacion_modal') }}?year=${year}&month=${month}&selected_chofer=${selectedChofer}&selected_date=${selectedDate}`;

        fetch(url)
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<p class="text-sm text-red-500 text-center py-6">Error al cargar la programación.</p>';
            });
    }

    function seleccionarFechaProgramacion(fecha, id_chofer, nombre, placa) {
        document.getElementById('fecha_traslado').value = fecha;
        document.getElementById('id_programacion').value = id_chofer;

        const displayName = `${nombre} ${placa ? '[' + placa + ']' : ''}`;
        mostrarChoferSeleccionado(displayName);

        toggleModal('modalProgramacionTransporte');
        mostrarNotificacion('Programación seleccionada con éxito.');
    }

    function mostrarChoferSeleccionado(displayName) {
        const container = document.getElementById('chofer_seleccionado_container');
        const nameSpan = document.getElementById('chofer_nombre_display');
        if (container && nameSpan) {
            nameSpan.textContent = displayName;
            container.classList.remove('hidden');
        }
    }

    // Inicializar visualización del chofer seleccionado si existe al cargar
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalProgramacionTransporte');
        if (modal) {
            modal.onclick = null;
        }

        const idProg = document.getElementById('id_programacion')?.value;
        @php
            $selectedChoferName = '';
            if (isset($factura) && $factura->programacionChofer) {
                $selectedChoferName = $factura->programacionChofer->nombre . ($factura->programacionChofer->placa ? ' [' . $factura->programacionChofer->placa . ']' : '');
            }
        @endphp
        const selectedName = "{{ $selectedChoferName }}";
        if (idProg && selectedName) {
            mostrarChoferSeleccionado(selectedName);
        }
    });
</script>

