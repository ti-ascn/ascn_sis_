<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Inventarios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col mb-4">
                <button onclick="openExportarExcelModal()"
                    class="bg-green-800 hover:bg-green-900 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                    Exportar inventario
                </button>

                <button onclick="openExportarMasVendidosModal()"
                    class="bg-blue-800 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                    <i class="bi bi-graph-up"></i>
                    Exportar más vendidos
                </button>

                <button onclick="window.location.href='{{ route('inventario.branding') }}'"
                    class="bg-red-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded inline-flex items-center gap-2">
                    <i class="bi bi-box-seam"></i>
                    Firma y Logo
                </button>


            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-[1200px] table-auto" id="myTable">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-4 py-2 border-b">Empresa</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">Código Inventario</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventarios as $inv)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $inv->empresa->razon_social ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">{{ $inv->empresa->estado ?? '—' }}</td>
                                <td class="px-4 py-2 border-b">{{ $inv->empresa->ruc ?? '—' }}</td>
                                <td class="px-4 py-2 border-b font-mono">{{ $inv->nombre_codigo }}</td>
                                <td class="px-4 py-2 border-b flex gap-2">
                                    <a href="{{ route('facturas.mensual_proveedor', $inv->empresa->ruc) }}"
                                        class="flex items-center py-1 rounded-md tooltip"
                                        style="color: #10B981; transition: color 0.3s;"
                                        onmouseover="this.style.color='#059669';"
                                        onmouseout="this.style.color='#10B981';">
                                        <span class="tooltip-text">F. Mensuales</span>
                                        <x-facturas-mensuales-icon />
                                    </a>
                                    <a href="{{ route('productos.porInventario', $inv->id_inventario) }}"
                                        class="flex items-center py-1 rounded-md tooltip"
                                        style="color: #3B82F6; transition: color 0.3s;"
                                        onmouseover="this.style.color='#1D4ED8';"
                                        onmouseout="this.style.color='#3B82F6';">
                                        <span class="tooltip-text">Ver productos
                                            ({{ $inv->productos->where('estado', 1)->count() }})</span>
                                        <x-box-icon />
                                    </a>
                                    @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                        <a onclick="openDuplicarInventarioModal({{ $inv->id_inventario }}, '{{ $inv->nombre_codigo }}', '{{ $inv->empresa->razon_social }}')"
                                            class="flex items-center py-1 rounded-md tooltip cursor-pointer"
                                            style="color:#8B5CF6; transition: color 0.3s;"
                                            onmouseover="this.style.color='#6D28D9';"
                                            onmouseout="this.style.color='#8B5CF6';">
                                            <span class="tooltip-text">Duplicar a...</span>
                                            <x-copy-icon />
                                        </a>
                                    @endif
                                    <a onclick="openCotizarModal({{ $inv->id_inventario }})"
                                        class="flex items-center py-1 rounded-md tooltip cursor-pointer"
                                        style="color:#0ea5e9">
                                        <span class="tooltip-text">Cotizar</span>
                                        <x-cost-icon />
                                    </a>
                                    @php
                                        $branding = $inv->empresa->branding;
                                        $dataBranding = [
                                            'firma_nombre' => optional($branding)->firma_nombre,
                                            'logo_url' =>
                                                $branding && $branding->logo_empresa
                                                    ? asset($branding->logo_empresa)
                                                    : null,
                                            'firma_url' =>
                                                $branding && $branding->firma_imagen
                                                    ? asset($branding->firma_imagen)
                                                    : null,
                                            'fuente' => optional($branding)->fuente,
                                            'color' => optional($branding)->color,
                                            'header_layout' => optional($branding)->header_layout,
                                            'footer_layout' => optional($branding)->footer_layout,
                                            'footer_show_logo' => (bool) optional($branding)->footer_show_logo,
                                            'header_show_logo' => (bool) optional($branding)->header_show_logo,
                                            'updated_by' => $branding?->usuario?->name, // <- propiedad correcta es "name"
                                            'updated_at' => $branding?->updated_at
                                                ? $branding->updated_at->format('d/m/Y H:i')
                                                : null,
                                        ];
                                        $pres = $inv->empresa->presentacion;
                                        $archivoReferencia = null;
                                        if ($pres && $pres->archivo_formato_presentacion) {
                                            $archivoReferencia = [
                                                'nombre' => basename($pres->archivo_formato_presentacion),
                                                'url' => asset($pres->archivo_formato_presentacion),
                                                'path' => $pres->archivo_formato_presentacion,
                                            ];
                                        }

                                        // Preparar todas las direcciones activas para el modal de generar
                                        $direccionesEmpresa = $inv->empresa
                                            ->direcciones()
                                            ->where('estado', 1)
                                            ->orderBy('id_direccion')
                                            ->get(['id_direccion', 'nombre', 'distrito', 'provincia', 'region'])
                                            ->map(
                                                fn($d) => [
                                                    'id' => $d->id_direccion,
                                                    'texto' => trim(
                                                        $d->nombre .
                                                            ' - ' .
                                                            $d->distrito .
                                                            ' - ' .
                                                            $d->provincia .
                                                            ' - ' .
                                                            $d->region,
                                                    ),
                                                ],
                                            )
                                            ->toArray();

                                        $dataPresentacion = [
                                            'texto_presentacion' => optional($pres)->texto_presentacion,
                                            'texto_despedida' => optional($pres)->texto_despedida,
                                            'mostrar_datos' => (bool) optional($pres)->mostrar_datos,
                                            'info_empresa' => optional($pres)->info_empresa,
                                            'lugar_info' => optional($pres)->lugar_info,
                                            'mostrar_info_firma' => isset($pres)
                                                ? (bool) $pres->mostrar_info_firma
                                                : true,
                                            'archivo_referencia' => $archivoReferencia,
                                            'marca_agua' => (bool) optional($pres)->marca_agua,
                                            'margen_top' => optional($pres)->margen_top ?? 25,
                                            'margen_bottom' => optional($pres)->margen_bottom ?? 25,
                                            'margen_left' => optional($pres)->margen_left ?? 25,
                                            'margen_right' => optional($pres)->margen_right ?? 25,
                                            'tipo_presentacion_fecha' =>
                                                optional($pres)->tipo_presentacion_fecha ?? 'ciudad_minuscula_derecha',
                                            'tipo_presentacion_destinatario' =>
                                                optional($pres)->tipo_presentacion_destinatario ??
                                                'cargo_arriba_nombre_abajo',
                                        ];
                                    @endphp

                                    <a data-empresa-id="{{ $inv->empresa->id_empresas_clientes }}"
                                        data-branding="{{ base64_encode(json_encode($dataBranding)) }}"
                                        onclick="openBrandingModal(
                                            this.dataset.empresaId,
                                            JSON.parse(atob(this.dataset.branding))
                                        )"
                                        class="flex items-center py-1 rounded-md tooltip cursor-pointer"
                                        style="color:#16a34a; transition: color 0.3s;"
                                        onmouseover="this.style.color='#15803d';"
                                        onmouseout="this.style.color='#16a34a';">
                                        <span class="tooltip-text">Logo & Firma</span>
                                        <x-picture-icon />
                                    </a>
                                    {{-- Formato Presentación --}}
                                    <a data-empresa-id="{{ $inv->empresa->id_empresas_clientes }}"
                                        data-presentacion="{{ base64_encode(json_encode($dataPresentacion)) }}"
                                        onclick="openFormatoPresentacionModal(
                                            this.dataset.empresaId,
                                            JSON.parse(atob(this.dataset.presentacion))
                                        )"
                                        class="flex items-center py-1 rounded-md tooltip cursor-pointer"
                                        style="color:#7C3AED; transition: color 0.3s;"
                                        onmouseover="this.style.color='#5B21B6';"
                                        onmouseout="this.style.color='#7C3AED';">
                                        <span class="tooltip-text">Formato Presentación</span>
                                        <x-format-presentation-icon />
                                    </a>

                                    {{-- Generar Presentación --}}
                                    <a data-inv="{{ $inv->id_inventario }}"
                                        data-direcciones="{{ base64_encode(json_encode($direccionesEmpresa)) }}"
                                        onclick="openGenerarPresentacionModal(
                                            this.dataset.inv,
                                            JSON.parse(atob(this.dataset.direcciones))
                                        )"
                                        class="flex items-center py-1 rounded-md tooltip cursor-pointer"
                                        style="color:#DB2777; transition: color 0.3s;"
                                        onmouseover="this.style.color='#9D174D';"
                                        onmouseout="this.style.color='#DB2777';">
                                        <span class="tooltip-text">Generar Presentación</span>
                                        <x-generate-presentation-icon />
                                    </a>
                                    @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                        @if ($inv->productos->isEmpty())
                                            <a onclick="openEditInventarioModal({{ $inv->id_inventario }}, '{{ $inv->nombre_codigo }}', '{{ $inv->empresa->razon_social }}')"
                                                class="flex items-center py-1 rounded-md tooltip cursor-pointer"
                                                style="color: #F59E0B; transition: color 0.3s;"
                                                onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </a>
                                        @else
                                            <span
                                                class="flex items-center py-1 text-gray-400 cursor-not-allowed tooltip">
                                                <span class="tooltip-text">Tiene productos</span>
                                                <x-edit-icon />
                                            </span>
                                        @endif
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- exportarexcel --}}

<x-modal-show id="modalExportarExcel" title="Generar Excel de Inventario">
    <form method="POST" action="{{ route('inventario.exportar.excel') }}">
        @csrf
        <input type="hidden" name="id_inventario" id="excelIdInventario">

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Empresa</label>
            <select name="id_empresas_cliente" id="excelEmpresa" class="mt-1 block w-full" required>
                <option value="">Seleccione empresa</option>
                @foreach ($inventarios as $inv)
                    <option value="{{ $inv->id_empresas_cliente }}" data-inventario="{{ $inv->id_inventario }}">
                        {{ $inv->empresa->razon_social }} - {{ $inv->nombre_codigo }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Moneda</label>
            <select name="moneda" class="mt-1 block w-full border-gray-300 rounded-md" required>
                <option value="PEN">Soles (S/)</option>
                <option value="USD">Dólares ($)</option>
            </select>
            <small class="text-gray-500">Los precios se mostrarán con 4 decimales. Si eliges dólares, se convertirá con
                el último tipo de cambio registrado.</small>
        </div>

        <div class="flex justify-end">
            <x-primary-button>Generar Excel</x-primary-button>
        </div>
    </form>
</x-modal-show>

<script>
    function openExportarExcelModal() {
        toggleModal('modalExportarExcel');

        // Inicializar Select2 después de abrir el modal
        setTimeout(() => {
            $('#excelEmpresa').select2({
                placeholder: 'Seleccione empresa',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalExportarExcel')
            });

            // Evento cuando cambia la selección
            $('#excelEmpresa').on('change', function() {
                const selected = $(this).find(':selected');
                const inventarioId = selected.data('inventario');
                $('#excelIdInventario').val(inventarioId);
            });
        }, 100);
    }
</script>

<x-modal-show id="modalExportarMasVendidos" title="Generar Excel Productos Más Vendidos">
    <form method="POST" action="{{ route('inventario.exportar.mas_vendidos') }}">
        @csrf
        <input type="hidden" name="id_inventario" id="masVendidosIdInventario">

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Empresa (Inventario)</label>
            <select name="id_empresas_cliente" id="masVendidosEmpresa" class="mt-1 block w-full" required>
                <option value="">Seleccione inventario</option>
                @foreach ($inventarios as $inv)
                    <option value="{{ $inv->id_empresas_cliente }}" data-inventario="{{ $inv->id_inventario }}">
                        {{ $inv->empresa->razon_social ?? '' }} - {{ $inv->nombre_codigo }}
                    </option>
                @endforeach
            </select>
            <small class="text-gray-500 mt-2 block">Se exportarán los productos de este inventario, ordenados de más vendidos a menos vendidos. Solo se consideran los productos con al menos 1 venta.</small>
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>Generar Excel</x-primary-button>
        </div>
    </form>
</x-modal-show>

<script>
    function openExportarMasVendidosModal() {
        toggleModal('modalExportarMasVendidos');

        // Inicializar Select2 después de abrir el modal
        setTimeout(() => {
            $('#masVendidosEmpresa').select2({
                placeholder: 'Seleccione inventario',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalExportarMasVendidos')
            });

            // Evento cuando cambia la selección
            $('#masVendidosEmpresa').on('change', function() {
                const selected = $(this).find(':selected');
                const inventarioId = selected.data('inventario');
                $('#masVendidosIdInventario').val(inventarioId);
            });
        }, 100);
    }
</script>

<x-modal-show id="editInventarioModal" title="Editar Código de Inventario">
    <form method="POST" action="{{ route('inventario.update') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="id_inventario" id="editIdInventario">

        {{-- Empresa --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Empresa</label>
            <p id="editEmpresaNombre" class="text-gray-800 font-semibold mt-1"></p>
        </div>

        {{-- Código --}}
        <div class="mb-4">
            <label for="editNombreCodigo" class="block text-sm font-medium text-gray-700">Nuevo Código</label>
            <input type="text" name="nombre_codigo" id="editNombreCodigo" required maxlength="10"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase"
                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9&]/g, '')">
        </div>

        <div class="flex justify-end">
            <x-primary-button>Guardar Cambios</x-primary-button>
        </div>
    </form>
</x-modal-show>

<x-modal-show id="modalBranding" title="Logo y Firma de la Empresa">
    <form method="POST" action="{{ route('branding.save') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id_empresas_clientes" id="brEmpresaId">

        <!-- Información básica de la firma -->
        <div class="mb-4">
            <label class="text-sm font-semibold">Nombre que acompaña la firma - Gerente General</label>
            <input type="text" name="firma_nombre" id="brFirmaNombre" class="w-full border-gray-300 rounded-md">
        </div>

        <!-- Logo y Firma -->
        <div class="grid md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="text-sm font-semibold">Logo de empresa</label>
                <input type="file" accept="image/*" name="logo_empresa" class="block w-full">
                <div class="mt-2 flex items-center gap-3">
                    <img id="brLogoPreview" src="" alt="" class="h-12 rounded border hidden">
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" name="remove_logo" value="1" class="mr-2">Eliminar logo actual
                    </label>
                </div>
            </div>
            <div>
                <label class="text-sm font-semibold">Imagen de firma</label>
                <input type="file" accept="image/*" name="firma_imagen" class="block w-full">
                <div class="mt-2 flex items-center gap-3">
                    <img id="brFirmaPreview" src="" alt="" class="h-12 rounded border hidden">
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" name="remove_firma" value="1" class="mr-2">Eliminar firma
                        actual
                    </label>
                </div>
            </div>
        </div>

        <!-- Configuración del PDF -->
        <div class="grid md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="text-sm font-semibold">Fuente del PDF</label>
                <select name="fuente" id="brFuente" class="w-full border-gray-300 rounded-md">
                    <option style="font-family: 'DejaVu Sans'" value="DejaVu Sans">DejaVu Sans</option>
                    <option style="font-family: 'DejaVu Serif'" value="DejaVu Serif">DejaVu Serif</option>
                    <option style="font-family: 'Helvetica'" value="Helvetica">Helvetica</option>
                    <option style="font-family: 'Times new roman'" value="Times">Times New Roman</option>
                    <option style="font-family: 'Courier'" value="Courier">Courier</option>
                </select>
                <small class="text-gray-500">Fuentes disponibles.</small>
            </div>

            <div>
                <label class="text-sm font-semibold">Color del diseño</label>
                <select name="color" id="brColor" class="w-full border-gray-300 rounded-md">
                    <option value="blue">Azul Marino</option>
                    <option value="sky">Azul Cielo</option>
                    <option value="emerald">Verde Esmeralda</option>
                    <option value="teal">Verde Teal</option>
                    <option value="amber">Ámbar</option>
                    <option value="orange">Naranja</option>
                    <option value="red">Rojo Carmesí</option>
                    <option value="violet">Morado Uva</option>
                    <option value="fuchsia">Fucsia</option>
                    <option value="gray">Gris Profesional</option>
                </select>

                {{-- mini preview --}}
                <div id="brPreview" class="mt-2 text-xs">
                    <div id="brPHeader" class="h-6 rounded mb-1 border"></div>
                    <div id="brPNote" class="h-6 rounded border"></div>
                </div>
            </div>
        </div>

        <!-- Layouts -->
        <div class="grid md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="text-sm font-semibold">Encabezado</label>
                <select name="header_layout" id="brHeaderLayout" class="w-full border-gray-300 rounded-md">
                    <option value="logo_right">Logo derecha / Info izquierda</option>
                    <option value="logo_left">Logo izquierda / Info derecha</option>
                    <option value="stack_left">Apilado izquierda</option>
                    <option value="stack_right">Apilado derecha</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold">Pie de página</label>
                <select name="footer_layout" id="brFooterLayout" class="w-full border-gray-300 rounded-md">
                    <option value="info_left_page_right">Info izq / Página der</option>
                    <option value="info_center">Info centrada</option>
                    <option value="logo_left_info_right">Logo izq / Info der</option>
                    <option value="logo_right_info_left">Info izq / Logo der</option>
                </select>
            </div>


        </div>

        <!-- Logo en footer -->
        <div class="grid md:grid-cols-2 gap-4 mt-4">
            <!-- Logo en header -->
            <div>
                {{-- <label class="text-sm font-semibold">Logo</label> --}}
                <input type="hidden" name="header_show_logo" value="0">
                <label class="inline-flex items-center mt-2">
                    <input type="checkbox" name="header_show_logo" id="brHeaderShowLogo" value="1"
                        class="mr-2">
                    Mostrar logo en el encabezado
                </label>
            </div>
            <div>
                {{-- <label class="text-sm font-semibold">Logo</label> --}}
                <input type="hidden" name="footer_show_logo" value="0">
                <label class="inline-flex items-center mt-2">
                    <input type="checkbox" name="footer_show_logo" id="brFooterShowLogo" value="1"
                        class="mr-2">
                    Mostrar logo en el pie
                </label>
            </div>
        </div>
        <p id="brUpdatedInfo" class="text-sm text-gray-600 mt-1 hidden"></p>

        <!-- Botón para guardar -->
        <div class="flex justify-end mt-6">
            <x-primary-button>Guardar</x-primary-button>
        </div>
    </form>
</x-modal-show>

<script>
    // 1) Mapas de color para preview
    const BRAND_SCHEMES = {
        blue: {
            header: '#EFF6FF',
            note_bg: '#F0F9FF',
            note_border: '#DBEAFE'
        },
        sky: {
            header: '#E0F2FE',
            note_bg: '#F0F9FF',
            note_border: '#BAE6FD'
        },
        emerald: {
            header: '#D1FAE5',
            note_bg: '#ECFDF5',
            note_border: '#A7F3D0'
        },
        teal: {
            header: '#CCFBF1',
            note_bg: '#F0FDFA',
            note_border: '#99F6E4'
        },
        amber: {
            header: '#FEF3C7',
            note_bg: '#FFFBEB',
            note_border: '#FDE68A'
        },
        orange: {
            header: '#FFEDD5',
            note_bg: '#FFF7ED',
            note_border: '#FED7AA'
        },
        red: {
            header: '#FEE2E2',
            note_bg: '#FEF2F2',
            note_border: '#FCA5A5'
        },
        violet: {
            header: '#EDE9FE',
            note_bg: '#F5F3FF',
            note_border: '#DDD6FE'
        },
        fuchsia: {
            header: '#F5D0FE',
            note_bg: '#FAE8FF',
            note_border: '#F0ABFC'
        },
        gray: {
            header: '#F3F4F6',
            note_bg: '#F9FAFB',
            note_border: '#E5E7EB'
        },
    };

    function paintBrandPreview(key) {
        const s = BRAND_SCHEMES[key] || BRAND_SCHEMES.gray;
        const head = document.getElementById('brPHeader');
        const note = document.getElementById('brPNote');
        if (!head || !note) return;
        head.style.background = s.header;
        note.style.background = s.note_bg;
        note.style.borderColor = s.note_border;
    }

    // 2) ¡Deja SOLO esta función! (elimina la duplicada)
    function openBrandingModal(empresaId, data) {

        const selHeader = document.getElementById('brHeaderLayout');
        const selFooter = document.getElementById('brFooterLayout');
        const chkFooterLogo = document.getElementById('brFooterShowLogo');
        const chkHeaderLogo = document.getElementById('brHeaderShowLogo');


        selHeader.value = (data && data.header_layout) ? data.header_layout : 'logo_right';
        selFooter.value = (data && data.footer_layout) ? data.footer_layout : 'info_left_page_right';
        chkFooterLogo.checked = !!(data && data.footer_show_logo);
        chkHeaderLogo.checked = !!(data && data.header_show_logo);


        document.getElementById('brEmpresaId').value = empresaId;

        // Texto
        document.getElementById('brFirmaNombre').value = (data && data.firma_nombre) ? data.firma_nombre : '';

        // Selects
        const selFuente = document.getElementById('brFuente');
        const selColor = document.getElementById('brColor');
        selFuente.value = (data && data.fuente) ? data.fuente : 'DejaVu Sans';
        selColor.value = (data && data.color) ? data.color : 'gray';

        // Preview
        paintBrandPreview(selColor.value);
        selColor.onchange = e => paintBrandPreview(e.target.value);

        // Imágenes
        const logoPrev = document.getElementById('brLogoPreview');
        const firmaPrev = document.getElementById('brFirmaPreview');

        if (data && data.logo_url) {
            logoPrev.src = data.logo_url;
            logoPrev.classList.remove('hidden');
        } else {
            logoPrev.src = '';
            logoPrev.classList.add('hidden');
        }

        if (data && data.firma_url) {
            firmaPrev.src = data.firma_url;
            firmaPrev.classList.remove('hidden');
        } else {
            firmaPrev.src = '';
            firmaPrev.classList.add('hidden');
        }

        // Uncheck
        const rmLogo = document.querySelector('input[name="remove_logo"]');
        const rmFirma = document.querySelector('input[name="remove_firma"]');
        if (rmLogo) rmLogo.checked = false;
        if (rmFirma) rmFirma.checked = false;

        const info = document.getElementById('brUpdatedInfo');
        if (data && (data.updated_by || data.updated_at)) {
            const name = data.updated_by ?? '—';
            const when = data.updated_at ?? '';
            info.innerHTML = `Última actualización por: <span class="font-semibold">${name}</span> el
                          <span class="font-semibold">${when}</span>`;
            info.classList.remove('hidden');
        } else {
            info.textContent = '';
            info.classList.add('hidden');
        }

        toggleModal('modalBranding');
    }
</script>

<x-modal-show id="modalDuplicarInventario" title="Duplicar productos a otro inventario">
    <form method="POST" action="{{ route('productos.duplicar') }}">
        @csrf

        <input type="hidden" name="id_inventario_origen" id="dupIdInventarioOrigen">

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Inventario origen</label>
            <p id="dupInventarioOrigenTexto" class="text-gray-800 font-semibold mt-1"></p>
        </div>

        <div class="mb-4">
            <label for="dupIdInventarioDestino" class="block text-sm font-medium text-gray-700">
                Inventario destino
            </label>
            <select name="id_inventario_destino" id="dupIdInventarioDestino"
                class="mt-1 block w-full border-gray-300 rounded-md" required>
                <option value="">Selecciona inventario destino</option>
                @foreach ($inventarios as $i)
                    <option value="{{ $i->id_inventario }}">
                        {{ $i->nombre_codigo }} - {{ $i->empresa->razon_social ?? '—' }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">
                Se crearán nuevos códigos usando el prefijo del inventario destino, continuando la numeración existente.
            </p>
        </div>

        <div class="flex justify-end">
            <x-primary-button>Duplicar</x-primary-button>
        </div>
    </form>
</x-modal-show>
<x-modal-show id="modalCotizar" title="Generar cotización">
    <form method="POST" action="{{ route('cotizaciones.generar') }}" target="_blank">
        @csrf
        <input type="hidden" name="id_inventario" id="ctzIdInventario">

        {{-- Destinatario --}}
        <div class="grid md:grid-cols-2 gap-3">
            <div>
                <label class="text-sm font-semibold">Cotizar a</label>
                <select name="destinatario_tipo" id="destTipo" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option value="empresa">Empresa Cliente</option>
                    <option value="cliente">Cliente Auditoría</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold">Seleccione</label>
                <select name="destinatario_id" id="destId" class="mt-1 block border-gray-300 rounded-md w-full"
                    required>
                    @foreach (\App\Models\EmpresasClientes::orderBy('razon_social')->get() as $e)
                        <option data-tipo="empresa" value="{{ $e->id_empresas_clientes }}">
                            {{ $e->razon_social }} — {{ $e->ruc }}
                        </option>
                    @endforeach
                    @foreach (\App\Models\ClienteAuditoria::orderBy('razon_social')->get() as $c)
                        <option data-tipo="cliente" value="{{ $c->id_cliente }}">
                            {{ $c->razon_social }} — {{ $c->ruc }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Lugar y fecha (siempre "Trujillo, ...") --}}
        <input type="hidden" name="lugar" value="Trujillo">

        {{-- Moneda --}}
        <div class="mt-3">
            <label class="text-sm font-semibold">Moneda</label>
            <select name="moneda" class="mt-1 block border-gray-300 rounded-md w-full">
                <option value="PEN">Soles (S/)</option>
                <option value="USD">Dólares ($)</option>
            </select>
            <small class="text-gray-500">Si eliges $, convierte usando el último precio del dólar registrado.</small>
        </div>

        {{-- Presentación --}}
        <div class="mt-3">
            <label class="text-sm font-semibold">Mensaje de presentación</label>
            <select name="presentacion" class="mt-1 block border-gray-300 rounded-md w-full">
                @foreach ($presentaciones as $key => $txt)
                    <option value="{{ $key }}">{{ $txt }}</option>
                @endforeach
            </select>
        </div>

        {{-- Despedida (opcional) --}}
        <div class="mt-3">
            <label class="text-sm font-semibold">Despedida (opcional)</label>
            <select name="despedida" class="mt-1 block border-gray-300 rounded-md w-full">
                <option value="">— Sin despedida —</option>
                @foreach ($despedidas as $key => $txt)
                    <option value="{{ $key }}">{{ $txt }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end mt-5">
            <x-primary-button>Generar PDF</x-primary-button>
        </div>
    </form>
</x-modal-show>

<script>
    function openCotizarModal(idInv) {
        document.getElementById('ctzIdInventario').value = idInv;
        toggleModal('modalCotizar');
    }
</script>
<script>
    const tipo = document.getElementById('destTipo');
    const dest = document.getElementById('destId');

    function filtra() {
        const t = tipo.value;
        let firstVisible = null;
        [...dest.options].forEach(o => {
            const show = o.dataset.tipo === t;
            o.hidden = !show;
            if (show && !firstVisible) firstVisible = o;
        });
        if (firstVisible) dest.value = firstVisible.value;
    }
    tipo.addEventListener('change', filtra);
    filtra(); // set inicial
</script>

<script>
    function openDuplicarInventarioModal(idOrigen, codigoOrigen, razonSocialOrigen) {
        // Setea origen
        document.getElementById('dupIdInventarioOrigen').value = idOrigen;
        document.getElementById('dupInventarioOrigenTexto').textContent = `${codigoOrigen} – ${razonSocialOrigen}`;

        const select = document.getElementById('dupIdInventarioDestino');

        // 1) Reset: mostrar todas las opciones
        Array.from(select.options).forEach(opt => {
            opt.hidden = false;
        });

        // 2) Ocultar (no mostrar) la opción del origen y des-seleccionarla si estaba elegida
        const optOrigen = select.querySelector(`option[value="${idOrigen}"]`);
        if (optOrigen) {
            optOrigen.hidden = true;
            if (select.value === String(idOrigen)) select.value = "";
        }

        // 3) Abrir modal
        toggleModal('modalDuplicarInventario');
    }
</script>


<script>
    function openEditInventarioModal(id, codigoActual, razonSocial) {
        document.getElementById('editIdInventario').value = id;
        document.getElementById('editNombreCodigo').value = codigoActual;
        document.getElementById('editEmpresaNombre').textContent = razonSocial;
        toggleModal('editInventarioModal');
    }
</script>

{{-- ======== MODAL: FORMATO PRESENTACIÓN ======== --}}
<x-modal-show id="modalFormatoPresentacion" title="Formato de Presentación">
    <form method="POST" action="{{ route('presentacion.formato.save') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id_empresa_cliente" id="fpEmpresaId">

        {{-- ── Textos ── --}}
        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700">Texto de presentación</label>
            <p class="text-xs text-gray-500 mb-1">
                Usa <strong>$empresa</strong> para insertar automáticamente el nombre de la empresa.
            </p>
            <textarea name="texto_presentacion" id="fpTextoPresentacion" rows="7"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                placeholder="Ej: $empresa es una empresa dedicada a..."></textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700">Texto de despedida</label>
            <textarea name="texto_despedida" id="fpTextoDespedida" rows="3"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                placeholder="Ej: Agradeciendo su tiempo, nos despedimos..."></textarea>
        </div>

        {{-- ── Mostrar datos empresa ── --}}
        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="hidden" name="mostrar_datos" value="0">
                <input type="checkbox" name="mostrar_datos" id="fpMostrarDatos" value="1" class="mr-2">
                <span class="text-sm font-semibold">Mostrar datos de la empresa en el PDF</span>
            </label>
        </div>

        <div id="fpDatosConfig" class="hidden">
            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Info empresa (RUC + Logo)</label>
                    <select name="info_empresa" id="fpInfoEmpresa"
                        class="mt-1 block w-full border-gray-300 rounded-md">
                        <option value="">— No mostrar —</option>
                        <option value="cabecera_1">Cabecera: RUC-Web-Nro izq / Logo der</option>
                        <option value="cabecera_2">Cabecera: Logo izq / RUC-Web-Nro der</option>
                        <option value="pie_pagina_1">Pie: RUC-Web-Nro izq / Logo der</option>
                        <option value="pie_pagina_2">Pie: Logo izq / RUC-Web-Nro der</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Lugar info (Dirección + Correo)</label>
                    <select name="lugar_info" id="fpLugarInfo" class="mt-1 block w-full border-gray-300 rounded-md">
                        <option value="">— No mostrar —</option>
                        <option value="cabecera_1">Cabecera: Dirección izq / Correo der</option>
                        <option value="cabecera_2">Cabecera: Correo izq / Dirección der</option>
                        <option value="pie_pagina_1">Pie: Dirección izq / Correo der</option>
                        <option value="pie_pagina_2">Pie: Correo izq / Dirección der</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ── Firma ── --}}
        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="hidden" name="mostrar_info_firma" value="0">
                <input type="checkbox" name="mostrar_info_firma" id="fpMostrarInfoFirma" value="1"
                    class="mr-2">
                <span class="text-sm font-semibold">Mostrar info de firma (nombre + cargo)</span>
            </label>
            <p class="text-xs text-gray-500 mt-1">
                Desmarca si tu imagen de firma ya incluye nombre y cargo.
            </p>
        </div>

        {{-- ── NUEVO: Marca de agua ── --}}
        <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
            <label class="inline-flex items-center">
                <input type="hidden" name="marca_agua" value="0">
                <input type="checkbox" name="marca_agua" id="fpMarcaAgua" value="1" class="mr-2">
                <span class="text-sm font-semibold">Agregar marca de agua con el logo de la empresa</span>
            </label>
            <p class="text-xs text-gray-500 mt-1">
                El logo se mostrará de forma semitransparente en el centro del PDF.
            </p>
        </div>

        {{-- ── NUEVO: Márgenes del PDF ── --}}
        <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
            <label class="block text-sm font-semibold text-gray-700 mb-3">
                Márgenes del PDF
                <span class="text-xs font-normal text-gray-500 ml-1">(en mm)</span>
            </label>

            {{-- Presets rápidos --}}
            <div class="flex flex-wrap gap-2 mb-3">
                <span class="text-xs text-gray-500 self-center">Presets:</span>
                <button type="button" onclick="setMargenes(0,0,0,0)"
                    class="px-2 py-1 text-xs bg-white border border-gray-300 rounded hover:bg-gray-100">Sin
                    margen</button>
                <button type="button" onclick="setMargenes(15,15,15,15)"
                    class="px-2 py-1 text-xs bg-white border border-gray-300 rounded hover:bg-gray-100">Angosto
                    (15)</button>
                <button type="button" onclick="setMargenes(25,25,25,25)"
                    class="px-2 py-1 text-xs bg-white border border-gray-300 rounded hover:bg-gray-100">Normal
                    (25)</button>
                <button type="button" onclick="setMargenes(35,35,35,35)"
                    class="px-2 py-1 text-xs bg-white border border-gray-300 rounded hover:bg-gray-100">Amplio
                    (35)</button>
                <button type="button" onclick="setMargenes(50,50,50,50)"
                    class="px-2 py-1 text-xs bg-white border border-gray-300 rounded hover:bg-gray-100">Muy amplio
                    (50)</button>
            </div>

            {{-- Controles individuales --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Superior</label>
                    <div class="flex items-center gap-2">
                        <input type="range" name="margen_top" id="fpMargenTop" min="0" max="80"
                            step="5" value="25" class="w-full accent-violet-600"
                            oninput="document.getElementById('fpMargenTopVal').textContent = this.value">
                        <span id="fpMargenTopVal" class="text-xs font-mono w-8 text-right text-gray-700">25</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Inferior</label>
                    <div class="flex items-center gap-2">
                        <input type="range" name="margen_bottom" id="fpMargenBottom" min="0"
                            max="80" step="5" value="25" class="w-full accent-violet-600"
                            oninput="document.getElementById('fpMargenBottomVal').textContent = this.value">
                        <span id="fpMargenBottomVal" class="text-xs font-mono w-8 text-right text-gray-700">25</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Izquierdo</label>
                    <div class="flex items-center gap-2">
                        <input type="range" name="margen_left" id="fpMargenLeft" min="0" max="80"
                            step="5" value="25" class="w-full accent-violet-600"
                            oninput="document.getElementById('fpMargenLeftVal').textContent = this.value">
                        <span id="fpMargenLeftVal" class="text-xs font-mono w-8 text-right text-gray-700">25</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Derecho</label>
                    <div class="flex items-center gap-2">
                        <input type="range" name="margen_right" id="fpMargenRight" min="0" max="80"
                            step="5" value="25" class="w-full accent-violet-600"
                            oninput="document.getElementById('fpMargenRightVal').textContent = this.value">
                        <span id="fpMargenRightVal" class="text-xs font-mono w-8 text-right text-gray-700">25</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── NUEVO: Formato de fecha ── --}}
        <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Formato de fecha</label>

            <div class="grid md:grid-cols-2 gap-3">
                {{-- alineación izquierda --}}
                <div>
                    <p class="text-xs text-gray-500 font-medium mb-1 uppercase tracking-wide">Alineado a la izquierda
                    </p>
                    <label class="flex items-start gap-2 mb-2 cursor-pointer">
                        <input type="radio" name="tipo_presentacion_fecha" id="fpFecha_4"
                            value="ciudad_minuscula_izquierda" class="mt-0.5">
                        <span class="text-sm text-gray-700">
                            Trujillo, 27 de marzo de 2026
                            <span class="block text-xs text-gray-400">Minúsculas · izquierda</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 mb-2 cursor-pointer">
                        <input type="radio" name="tipo_presentacion_fecha" id="fpFecha_5"
                            value="ciudad_mayuscula_izquierda" class="mt-0.5">
                        <span class="text-sm text-gray-700">
                            TRUJILLO, 27 DE MARZO DE 2026
                            <span class="block text-xs text-gray-400">Mayúsculas · izquierda</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 mb-2 cursor-pointer">
                        <input type="radio" name="tipo_presentacion_fecha" id="fpFecha_6"
                            value="ciudad_bold_izquierda" class="mt-0.5">
                        <span class="text-sm text-gray-700">
                            <strong>Trujillo, 27 de marzo de 2026</strong>
                            <span class="block text-xs text-gray-400">Negrita · izquierda</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 mb-2 cursor-pointer">
                        <input type="radio" name="tipo_presentacion_fecha" id="fpFecha_8"
                            value="solo_fecha_izquierda" class="mt-0.5">
                        <span class="text-sm text-gray-700">
                            27 de marzo de 2026
                            <span class="block text-xs text-gray-400">Solo fecha, sin ciudad · izquierda</span>
                        </span>
                    </label>
                </div>
                {{-- alineación derecha --}}
                <div>
                    <p class="text-xs text-gray-500 font-medium mb-1 uppercase tracking-wide">Alineado a la derecha</p>
                    <label class="flex items-start gap-2 mb-2 cursor-pointer">
                        <input type="radio" name="tipo_presentacion_fecha" id="fpFecha_1"
                            value="ciudad_minuscula_derecha" class="mt-0.5">
                        <span class="text-sm text-gray-700">
                            Trujillo, 27 de marzo de 2026
                            <span class="block text-xs text-gray-400">Minúsculas · derecha</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 mb-2 cursor-pointer">
                        <input type="radio" name="tipo_presentacion_fecha" id="fpFecha_2"
                            value="ciudad_mayuscula_derecha" class="mt-0.5">
                        <span class="text-sm text-gray-700">
                            TRUJILLO, 27 DE MARZO DE 2026
                            <span class="block text-xs text-gray-400">Mayúsculas · derecha</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 mb-2 cursor-pointer">
                        <input type="radio" name="tipo_presentacion_fecha" id="fpFecha_3"
                            value="ciudad_bold_derecha" class="mt-0.5">
                        <span class="text-sm text-gray-700">
                            <strong>Trujillo, 27 de marzo de 2026</strong>
                            <span class="block text-xs text-gray-400">Negrita · derecha</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 mb-2 cursor-pointer">
                        <input type="radio" name="tipo_presentacion_fecha" id="fpFecha_7"
                            value="solo_fecha_derecha" class="mt-0.5">
                        <span class="text-sm text-gray-700">
                            27 de marzo de 2026
                            <span class="block text-xs text-gray-400">Solo fecha, sin ciudad · derecha</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        {{-- ── NUEVO: Presentación del destinatario ── --}}
        <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Presentación del destinatario</label>
            <div class="space-y-2">

                <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-gray-100">
                    <input type="radio" name="tipo_presentacion_destinatario" id="fpDest_1"
                        value="cargo_arriba_nombre_abajo" class="mt-0.5">
                    <span class="text-sm text-gray-700">
                        <span class="block text-xs text-gray-500 uppercase tracking-wide mb-0.5">GERENTE GENERAL ·
                            PRESENTE</span>
                        <span class="font-bold" style="color:#4B5563;">NOMBRE DE LA EMPRESA</span>
                        <span class="block text-xs text-gray-400 mt-0.5">Cargo arriba · Nombre abajo en negrita</span>
                    </span>
                </label>

                <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-gray-100">
                    <input type="radio" name="tipo_presentacion_destinatario" id="fpDest_2"
                        value="nombre_arriba_cargo_abajo" class="mt-0.5">
                    <span class="text-sm text-gray-700">
                        <span class="block font-bold" style="color:#4B5563;">NOMBRE DE LA EMPRESA</span>
                        <span class="block text-xs text-gray-500 uppercase tracking-wide mt-0.5">Gerente General ·
                            Presente</span>
                        <span class="block text-xs text-gray-400 mt-0.5">Nombre arriba en negrita · Cargo abajo</span>
                    </span>
                </label>

                <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-gray-100">
                    <input type="radio" name="tipo_presentacion_destinatario" id="fpDest_3" value="solo_cargo"
                        class="mt-0.5">
                    <span class="text-sm text-gray-700">
                        <span class="block text-xs text-gray-500 uppercase tracking-wide">GERENTE GENERAL</span>
                        <span class="block text-xs text-gray-400 mt-0.5">Solo cargo, sin nombre de empresa</span>
                    </span>
                </label>

                <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-gray-100">
                    <input type="radio" name="tipo_presentacion_destinatario" id="fpDest_4"
                        value="estimado_cargo_nombre" class="mt-0.5">
                    <span class="text-sm text-gray-700">
                        <span class="block text-xs text-gray-500 uppercase tracking-wide">Estimado:</span>
                        <span class="block text-xs text-gray-500 uppercase tracking-wide">GERENTE GENERAL</span>
                        <span class="block font-bold" style="color:#4B5563;">NOMBRE DE LA EMPRESA</span>
                        <span class="block text-xs text-gray-400 mt-0.5">Estimado · Cargo · Nombre</span>
                    </span>
                </label>

                <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-gray-100">
                    <input type="radio" name="tipo_presentacion_destinatario" id="fpDest_5"
                        value="nombre_bold_cargo_normal" class="mt-0.5">
                    <span class="text-sm text-gray-700">
                        <span class="block font-bold" style="color:#4B5563;">NOMBRE DE LA EMPRESA</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Gerente General · Presente</span>
                        <span class="block text-xs text-gray-400 mt-0.5">Nombre en negrita · Cargo en texto
                            normal</span>
                    </span>
                </label>

                <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-gray-100">
                    <input type="radio" name="tipo_presentacion_destinatario" id="fpDest_6"
                        value="nombre_normal_cargo_bold" class="mt-0.5">
                    <span class="text-sm text-gray-700">
                        <span class="block text-gray-700">Nombre de la Empresa</span>
                        <span class="block font-bold text-xs uppercase tracking-wide" style="color:#4B5563;">GERENTE
                            GENERAL · PRESENTE</span>
                        <span class="block text-xs text-gray-400 mt-0.5">Nombre normal · Cargo en negrita</span>
                    </span>
                </label>

            </div>
        </div>

        {{-- ── Archivo de referencia existente ── --}}
        <div id="fpArchivoReferenciaContainer" class="mb-4 hidden">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Archivo de referencia actual</label>
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                            <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                        </svg>
                        <span id="fpArchivoNombre" class="text-sm font-medium text-gray-700"></span>
                    </div>
                    <div class="flex space-x-2">
                        <button type="button" onclick="verArchivoReferencia()"
                            class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Ver
                        </button>
                        <button type="button" onclick="descargarArchivoReferencia()"
                            class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Descargar
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    Este archivo se usará como imagen de fondo para las presentaciones.
                </p>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700">
                Actualizar imagen de fondo (JPG, opcional) <span class="hover:underline text-blue-500"><a
                        target="_blank" href="https://www.ilovepdf.com/es/pdf_a_jpg">LINK PDF A JPG</a></span>
                <span class="text-xs font-normal text-gray-500 ml-2">(Reemplazará la existente)</span>
            </label>
            <input type="file" name="archivo_formato_presentacion" accept=".jpg"
                class="mt-1 block w-full text-sm">
            <p class="text-xs text-gray-500 mt-1">Sube una imagen JPG para usarla como fondo del PDF.</p>
        </div>

        <div class="flex justify-end space-x-2">
            <button type="button" onclick="toggleModal('modalFormatoPresentacion')"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-md transition-colors">
                Cancelar
            </button>
            <x-primary-button>Guardar Formato</x-primary-button>
        </div>
    </form>
</x-modal-show>

{{-- ======== MODAL: GENERAR PRESENTACIÓN ======== --}}
<x-modal-show id="modalGenerarPresentacion" title="Generar Presentación">
    <form method="POST" action="{{ route('presentacion.generar') }}" target="_blank">
        @csrf
        <input type="hidden" name="id_inventario" id="gpIdInventario">

        <div class="grid md:grid-cols-2 gap-3 mb-3">
            <div>
                <label class="text-sm font-semibold">Presentación dirigida a</label>
                <select name="destinatario_tipo" id="gpDestTipo"
                    class="mt-1 block w-full border-gray-300 rounded-md">
                    <option value="empresa">Empresa Cliente</option>
                    <option value="cliente">Cliente Auditoría</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold">Seleccione destinatario</label>
                <select name="destinatario_id" id="gpDestId" class="mt-1 block w-full border-gray-300 rounded-md"
                    required>
                    @foreach (\App\Models\EmpresasClientes::orderBy('razon_social')->get() as $e)
                        <option data-tipo="empresa" value="{{ $e->id_empresas_clientes }}">
                            {{ $e->razon_social }} — {{ $e->ruc }}
                        </option>
                    @endforeach
                    @foreach (\App\Models\ClienteAuditoria::orderBy('razon_social')->get() as $c)
                        <option data-tipo="cliente" value="{{ $c->id_cliente }}">
                            {{ $c->razon_social }} — {{ $c->ruc }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Dirección de la empresa generadora (no del destinatario) --}}
        <div class="mb-3">
            <label class="text-sm font-semibold">Dirección a mostrar en el PDF</label>
            <select name="id_direccion" id="gpDireccion"
                class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                <option value="">— No mostrar dirección —</option>
                {{-- Se llena dinámicamente por JS --}}
            </select>
            <p class="text-xs text-gray-500 mt-1">Elige la sucursal/sede de tu empresa que aparecerá en el documento.
            </p>
        </div>

        <div class="mb-3">
            <label class="text-sm font-semibold">Fecha del documento</label>
            <input type="date" name="fecha" id="gpFecha" class="mt-1 block w-full border-gray-300 rounded-md"
                value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>Generar PDF</x-primary-button>
        </div>
    </form>
</x-modal-show>


{{-- Script para manejar el archivo de referencia --}}
<script>
    let archivoReferenciaActual = null;

    function setMargenes(top, bottom, left, right) {
        const ids = {
            top,
            bottom,
            left,
            right
        };
        for (const [side, val] of Object.entries(ids)) {
            const input = document.getElementById('fpMargen' + side.charAt(0).toUpperCase() + side.slice(1));
            const label = document.getElementById('fpMargen' + side.charAt(0).toUpperCase() + side.slice(1) + 'Val');
            if (input) {
                input.value = val;
            }
            if (label) {
                label.textContent = val;
            }
        }
    }

    function openFormatoPresentacionModal(empresaId, data) {
        document.getElementById('fpEmpresaId').value = empresaId;
        document.getElementById('fpTextoPresentacion').value = (data && data.texto_presentacion) ? data
            .texto_presentacion : '';
        document.getElementById('fpTextoDespedida').value = (data && data.texto_despedida) ? data.texto_despedida : '';

        const chkDatos = document.getElementById('fpMostrarDatos');
        chkDatos.checked = !!(data && data.mostrar_datos);
        document.getElementById('fpDatosConfig').classList.toggle('hidden', !chkDatos.checked);
        chkDatos.onchange = () => {
            document.getElementById('fpDatosConfig').classList.toggle('hidden', !chkDatos.checked);
        };

        document.getElementById('fpInfoEmpresa').value = (data && data.info_empresa) ? data.info_empresa : '';
        document.getElementById('fpLugarInfo').value = (data && data.lugar_info) ? data.lugar_info : '';
        document.getElementById('fpMostrarInfoFirma').checked = data ? !!data.mostrar_info_firma : true;

        // Marca de agua
        document.getElementById('fpMarcaAgua').checked = !!(data && data.marca_agua);

        // Márgenes
        const margenes = {
            Top: 25,
            Bottom: 25,
            Left: 25,
            Right: 25
        };
        if (data) {
            margenes.Top = data.margen_top ?? 25;
            margenes.Bottom = data.margen_bottom ?? 25;
            margenes.Left = data.margen_left ?? 25;
            margenes.Right = data.margen_right ?? 25;
        }
        for (const [side, val] of Object.entries(margenes)) {
            const input = document.getElementById('fpMargen' + side);
            const label = document.getElementById('fpMargen' + side + 'Val');
            if (input) {
                input.value = val;
            }
            if (label) {
                label.textContent = val;
            }
        }

        // Archivo de referencia existente
        archivoReferenciaActual = (data && data.archivo_referencia) ? {
            nombre: data.archivo_referencia.nombre || 'archivo_referencia.jpg',
            url: data.archivo_referencia.url,
            path: data.archivo_referencia.path,
        } : null;

        const container = document.getElementById('fpArchivoReferenciaContainer');
        const nombreSpan = document.getElementById('fpArchivoNombre');
        if (archivoReferenciaActual && archivoReferenciaActual.url) {
            nombreSpan.textContent = archivoReferenciaActual.nombre;
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }

        // ── tipo_presentacion_fecha ──
        const tipoFechaVal = (data && data.tipo_presentacion_fecha) ?
            data.tipo_presentacion_fecha :
            'ciudad_minuscula_derecha';
        document.querySelectorAll('input[name="tipo_presentacion_fecha"]').forEach(r => {
            r.checked = (r.value === tipoFechaVal);
        });

        // ── tipo_presentacion_destinatario ──
        const tipoDestVal = (data && data.tipo_presentacion_destinatario) ?
            data.tipo_presentacion_destinatario :
            'cargo_arriba_nombre_abajo';
        document.querySelectorAll('input[name="tipo_presentacion_destinatario"]').forEach(r => {
            r.checked = (r.value === tipoDestVal);
        });


        toggleModal('modalFormatoPresentacion');
    }

    function verArchivoReferencia() {
        if (archivoReferenciaActual && archivoReferenciaActual.url) {
            window.open(archivoReferenciaActual.url, '_blank');
        } else {
            alert('No hay archivo de referencia disponible');
        }
    }

    function descargarArchivoReferencia() {
        if (archivoReferenciaActual && archivoReferenciaActual.url) {
            const link = document.createElement('a');
            link.href = archivoReferenciaActual.url;
            link.download = archivoReferenciaActual.nombre || 'archivo_referencia.jpg';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            alert('No hay archivo de referencia disponible');
        }
    }

    // ── Generar Presentación ──────────────────────────────────────────────
    let _gpDirecciones = [];

    function openGenerarPresentacionModal(idInv, direcciones) {
        document.getElementById('gpIdInventario').value = idInv;
        document.getElementById('gpFecha').value = new Date().toISOString().split('T')[0];

        // Rellenar select de direcciones
        _gpDirecciones = direcciones || [];
        const selDir = document.getElementById('gpDireccion');
        selDir.innerHTML = '<option value="">— No mostrar dirección —</option>';
        _gpDirecciones.forEach((d, i) => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = d.texto;
            if (i === 0) opt.selected = true; // preseleccionar la primera
            selDir.appendChild(opt);
        });

        toggleModal('modalGenerarPresentacion');
    }

    // Filtro tipo destinatario
    const gpTipo = document.getElementById('gpDestTipo');
    const gpDest = document.getElementById('gpDestId');

    function filtraGp() {
        const t = gpTipo.value;
        let first = null;
        [...gpDest.options].forEach(o => {
            const show = o.dataset.tipo === t;
            o.hidden = !show;
            if (show && !first) first = o;
        });
        if (first) gpDest.value = first.value;
    }
    gpTipo.addEventListener('change', filtraGp);
    filtraGp();
</script>
