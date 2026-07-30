@php
    $user = Auth::user();
    $area = $user->area->nombre;
    $tipo = $user->tipo_usuario;
    $id = $user->id;
@endphp

<aside class="w-72 h-screen flex flex-col sticky top-0 z-40 overflow-hidden">
    <!-- Brand / Logo Area (Minimalist) -->
    <div class="h-28 flex items-center justify-between px-6 shrink-0 relative">
        <a href="{{ route('dashboard') }}" class="flex items-center justify-center flex-1">
            <img src="{{ asset($area === 'AUDITORIA' ? 'img/sistema-logo.png' : 'img/ASECON-HORIZONTAL.png') }}"
                alt="Logo"
                class="h-22 w-auto max-w-full object-contain hover:scale-105 transition-transform duration-300">
        </a>

        <!-- Theme Toggle Button -->
        <button id="theme-toggle" type="button"
            class="ml-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5 transition-colors">
            <i id="theme-toggle-dark-icon" class="bi bi-moon-fill hidden"></i>
            <i id="theme-toggle-light-icon" class="bi bi-sun-fill hidden"></i>
        </button>
    </div>

    <script>
        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        // Change the icons inside the button based on previous settings
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }

        var themeToggleBtn = document.getElementById('theme-toggle');

        themeToggleBtn.addEventListener('click', function() {
            // toggle icons inside button
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            // if set via local storage previously
            if (localStorage.getItem('theme')) {
                if (localStorage.getItem('theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }

                // if NOT set via local storage previously
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }
        });
    </script>

    <!-- Scrollable Menu -->
    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6 custom-scrollbar bg-white dark:bg-[#22242a]">

        <!-- Dashboard -->
        <div>
            <h3
                class="px-4 text-[10px] font-black text-[#c9a227] dark:text-[#d4af37] uppercase tracking-[2px] mb-4 flex items-center gap-3">
                <span>Principal</span>
                <div class="h-[1px] flex-1 bg-gradient-to-r from-[#c9a227]/30 to-transparent dark:from-[#d4af37]/30">
                </div>
            </h3>
            <div class="space-y-1">
                <x-nav-link-sidebar :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="bi-grid-1x2-fill">
                    Dashboard
                </x-nav-link-sidebar>
            </div>
        </div>

        <!-- CONTABILIDAD -->
        @if ($area == 'CONTABILIDAD' || $area == 'ADMINISTRACION' || $area == 'GERENCIA')
            @if ($tipo == 2 || $tipo == 3 || $area == 'ADMINISTRACION')
                <div>
                    <h3
                        class="px-4 text-[10px] font-black text-[#c9a227] dark:text-[#d4af37] uppercase tracking-[2px] mb-4 flex items-center gap-3">
                        <span>Contabilidad</span>
                        <div
                            class="h-[1px] flex-1 bg-gradient-to-r from-[#c9a227]/30 to-transparent dark:from-[#d4af37]/30">
                        </div>
                    </h3>
                    <div class="space-y-1">
                        <x-nav-link-sidebar :href="route('empresas_clientes.all')" :active="request()->routeIs('empresas_clientes.all')" icon="bi-key-fill">
                            Clave Sol
                        </x-nav-link-sidebar>

                        <x-nav-accordion-sidebar label="Gestión Contable" icon="bi-calculator-fill" :active="request()->routeIs('empresas_clientes.index') ||
                            request()->routeIs('determinacion-renta.*') ||
                            request()->routeIs('determinacion_mensual.*') ||
                            request()->routeIs('deudas.index') ||
                            request()->routeIs('fraccionamiento.*') ||
                            request()->routeIs('estatus_libros_contables.*')">

                            <a href="{{ route('empresas_clientes.index') }}"
                                class="block px-4 py-2 text-sm {{ request()->routeIs('empresas_clientes.index') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">IGV
                                - Renta</a>

                            @if ($tipo == 1)
                                <a href="{{ route('estatus_libros_contables.index') }}"
                                    class="block px-4 py-2 text-sm {{ request()->routeIs('estatus_libros_contables.*') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Estatus
                                    Libros Cont.</a>
                            @endif

                            @if ($tipo == 2 || $area == 'ADMINISTRACION')
                                <a href="{{ route('determinacion-renta.index') }}"
                                    class="block px-4 py-2 text-sm {{ request()->routeIs('determinacion-renta.index') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Pre-liquidaciones
                                    y Det. Anuales</a>
                            @endif

                            <a href="{{ route('determinacion_mensual.index') }}"
                                class="block px-4 py-2 text-sm {{ request()->routeIs('determinacion_mensual.index') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Det.
                                Mensuales</a>

                            <a href="{{ route('facturas.mes_facturacion') }}"
                                class="block px-4 py-2 text-sm {{ request()->routeIs('facturas.mes_facturacion') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Totales
                                Mensuales</a>

                            <a href="{{ route('deudas.index', 'contabilidad') }}"
                                class="block px-4 py-2 text-sm {{ request()->routeIs('deudas.index') && request()->segment(2) == 'contabilidad' ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Deudas</a>

                            <a href="{{ route('fraccionamiento.index') }}"
                                class="block px-4 py-2 text-sm {{ request()->routeIs('fraccionamiento.index') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Fraccionamiento</a>

                            @if ($area == 'ADMINISTRACION')
                                <a href="{{ route('cuentas_empresas.index') }}"
                                    class="block px-4 py-2 text-sm {{ request()->routeIs('cuentas_empresas.index') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Cuentas
                                    Empresas</a>
                            @endif
                        </x-nav-accordion-sidebar>

                        <x-nav-link-sidebar :href="route('recepcion.documentos')" :active="request()->routeIs('recepcion.documentos')" icon="bi-file-earmark-arrow-down-fill">
                            Recepción de Documentos
                        </x-nav-link-sidebar>
                        
                        @if ($tipo == 1 || $area == 'ADMINISTRACION')
                        <x-nav-link-sidebar :href="route('deudas_recepcion.index')" :active="request()->routeIs('deudas_recepcion.*')" icon="bi-cash-coin">
                            Cotizaciones Recepción
                        </x-nav-link-sidebar>
                        @endif

                        <x-nav-link-sidebar :href="route('estados_cuenta.index')" :active="request()->routeIs('estados_cuenta.index')" icon="bi-bank2">
                            Estados de Cuenta
                        </x-nav-link-sidebar>
                    </div>
                </div>
            @endif
        @endif

        <!-- AUDITORIA -->
        @if ($area == 'AUDITORIA' || $area == 'ADMINISTRACION')
            @if ($tipo == 2 || $tipo == 3 || $area == 'ADMINISTRACION')
                <div>
                    <h3
                        class="px-4 text-[10px] font-black text-[#c9a227] dark:text-[#d4af37] uppercase tracking-[2px] mb-4 flex items-center gap-3">
                        <span>Auditoría</span>
                        <div
                            class="h-[1px] flex-1 bg-gradient-to-r from-[#c9a227]/30 to-transparent dark:from-[#d4af37]/30">
                        </div>
                    </h3>
                    <div class="space-y-1">
                        <x-nav-accordion-sidebar label="Gestión Auditoría" icon="bi-shield-check" :active="request()->routeIs('empresas_clientes_auditoria.*') ||
                            request()->routeIs('cuentas_empresas.*') ||
                            request()->routeIs('clientes.*') ||
                            request()->routeIs('direcciones.*') ||
                            request()->routeIs('choferes.*')">
                            <a href="{{ route('empresas_clientes_auditoria.all') }}"
                                class="block px-4 py-2 text-sm {{ request()->routeIs('empresas_clientes_auditoria.all') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Empresas</a>

                            @if ($id != 35)
                                <a href="{{ route('gerente_socios.index') }}"
                                    class="block px-4 py-2 text-sm {{ request()->routeIs('gerente_socios.index') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Gerente/Socios</a>
                                <a href="{{ route('planillas.index') }}"
                                    class="block px-4 py-2 text-sm {{ request()->routeIs('planillas.index') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Planillas</a>
                                <a href="{{ route('cuentas_empresas.index') }}"
                                    class="block px-4 py-2 text-sm {{ request()->routeIs('cuentas_empresas.index') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Cuentas
                                    Empresas</a>
                                <a href="{{ route('clientes.index') }}"
                                    class="block px-4 py-2 text-sm {{ request()->routeIs('clientes.index') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Clientes</a>
                                <a href="{{ route('direcciones.index') }}"
                                    class="block px-4 py-2 text-sm {{ request()->routeIs('direcciones.index') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Direcciones</a>
                                <a href="{{ route('choferes.index') }}"
                                     class="block px-4 py-2 text-sm {{ request()->routeIs('choferes.index') || request()->routeIs('choferes.*') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Choferes</a>
                                 <a href="{{ route('programacion.editar') }}"
                                     class="block px-4 py-2 text-sm {{ request()->routeIs('programacion.editar') ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-amber-600' }}">Control Transporte</a>
                            @endif
                        </x-nav-accordion-sidebar>

                        @if ($id != 35)
                            <x-nav-accordion-sidebar label="Numeración" icon="bi-123" :active="request()->routeIs('formato_n_solicitud.*') ||
                                request()->routeIs('formato_cotizaciones.*') ||
                                request()->routeIs('formato_cotizaciones_2.*') ||
                                request()->routeIs('formato_n_orden.*')">
                                <a href="{{ route('formato_n_solicitud.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Nro Solicitud</a>
                                {{-- <a href="{{ route('formato_cotizaciones.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Nro Cotización (v1)</a> --}}
                                <a href="{{ route('formato_cotizaciones_2.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Nro Cotización</a>
                                <a href="{{ route('formato_n_orden.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Nro Orden</a>
                            </x-nav-accordion-sidebar>
                            <x-nav-link-sidebar :href="route('guias.index')" :active="request()->routeIs('guias.index')" icon="bi-truck">
                                Guías
                            </x-nav-link-sidebar>
                            <x-nav-link-sidebar :href="route('inventario.index')" :active="request()->routeIs('inventario.index')" icon="bi-box-seam-fill">
                                Inventarios
                            </x-nav-link-sidebar>
                        @endif

                        <x-nav-accordion-sidebar label="Facturación" icon="bi-receipt-cutoff" :active="request()->routeIs('facturas.*') || request()->routeIs('abono_facturas.*')">
                            @if ($tipo == 2 || $area == 'ADMINISTRACION' || $id == 9)
                                <a href="{{ route('abono_facturas.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Abonos
                                    Facturas</a>
                            @endif
                            <a href="{{ route('facturas.mes_actual') }}"
                                class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Facturas del mes</a>
                            @if ($id != 35)
                                <a href="{{ route('facturas.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Todas las
                                    facturas</a>
                            @endif
                            <a href="{{ route('facturas.mes_facturacion') }}"
                                class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Totales mensuales</a>
                        </x-nav-accordion-sidebar>

                        @if ($tipo == 2)
                            <x-nav-link-sidebar :href="route('usuarios.index_auditoria')" :active="request()->routeIs('usuarios.index_auditoria')" icon="bi-person-gear">
                                Usuarios Auditoría
                            </x-nav-link-sidebar>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        <!-- RECEPCION -->
        @if ($area == 'RECEPCION' || $area == 'ADMINISTRACION' || $area == 'GERENCIA')
            @if ($tipo == 2 || $tipo == 3 || $area == 'ADMINISTRACION')
                <div>
                    <h3
                        class="px-4 text-[10px] font-black text-[#c9a227] dark:text-[#d4af37] uppercase tracking-[2px] mb-4 flex items-center gap-3">
                        <span>Recepción</span>
                        <div
                            class="h-[1px] flex-1 bg-gradient-to-r from-[#c9a227]/30 to-transparent dark:from-[#d4af37]/30">
                        </div>
                    </h3>
                    <div class="space-y-1">
                        <x-nav-link-sidebar :href="route('recepcion.panel')" :active="request()->routeIs('recepcion.panel')" icon="bi-people-fill">
                            Clientes
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('recepcion.documentos')" :active="request()->routeIs('recepcion.documentos')" icon="bi-file-text-fill">
                            Recepción de Documentos
                        </x-nav-link-sidebar>
                        @if ($tipo == 2 || $tipo == 1)
                            <x-nav-link-sidebar :href="route('estatus_libros_contables.index')" :active="request()->routeIs('estatus_libros_contables.*')" icon="bi-book-half">
                                Estatus Libros Cont.
                            </x-nav-link-sidebar>
                        @endif
                        <x-nav-link-sidebar :href="route('deudas_recepcion.index')" :active="request()->routeIs('deudas_recepcion.index')" icon="bi-cash-coin">
                            Cotizaciones
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('contratos_recepcion.index')" :active="request()->routeIs('contratos_recepcion.index')" icon="bi-journal-check">
                            Contratos
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('cargos.index')" :active="request()->routeIs('cargos.*')" icon="bi-person-badge-fill">
                            Cargo
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('contactos.index')" :active="request()->routeIs('contactos.*')" icon="bi-person-lines-fill">
                            Contactos
                        </x-nav-link-sidebar>
                    </div>
                </div>
            @endif
        @endif

        <!-- LEGAL -->
        @if ($area == 'LEGAL' || $area == 'ADMINISTRACION' || $area == 'GERENCIA')
            @if ($tipo == 2 || $tipo == 3 || $area == 'ADMINISTRACION')
                <div>
                    <h3
                        class="px-4 text-[10px] font-black text-[#c9a227] dark:text-[#d4af37] uppercase tracking-[2px] mb-4 flex items-center gap-3">
                        <span>Legal</span>
                        <div
                            class="h-[1px] flex-1 bg-gradient-to-r from-[#c9a227]/30 to-transparent dark:from-[#d4af37]/30">
                        </div>
                    </h3>
                    <div class="space-y-1">
                        @if ($area != 'GERENCIA')
                            @if ($id == 33)
                                <x-nav-link-sidebar :href="route('emmpresas.c1')" :active="request()->routeIs('emmpresas.c1')" icon="bi-building-fill">
                                    Empresas
                                </x-nav-link-sidebar>
                            @else
                                <x-nav-link-sidebar :href="route('empresas_clientes_auditoria.all')" :active="request()->routeIs('empresas_clientes_auditoria.all')" icon="bi-building-fill">
                                    Empresas
                                </x-nav-link-sidebar>
                            @endif

                            <x-nav-link-sidebar :href="route('cuentas_empresas.index')" :active="request()->routeIs('cuentas_empresas.*')" icon="bi-credit-card-2-back-fill">
                                Cuentas
                            </x-nav-link-sidebar>

                            @if ($id != 33)
                                <x-nav-link-sidebar :href="route('gerente_socios.index')" :active="request()->routeIs('gerente_socios.*')" icon="bi-person-bounding-box">
                                    Gerente/Socios
                                </x-nav-link-sidebar>
                            @endif
                        @endif

                        <x-nav-link-sidebar :href="route('archivos_legales.index')" :active="request()->routeIs('archivos_legales.*')" icon="bi-folder-fill">
                            Archivos Legales
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('estatus_libros_contables.index')" :active="request()->routeIs('estatus_libros_contables.*')" icon="bi-book-half">
                            Estatus Libros Cont.
                        </x-nav-link-sidebar>

                        <x-nav-accordion-sidebar label="Contratos" icon="bi-file-earmark-lock-fill"
                            :active="request()->routeIs('contratos.*') || request()->routeIs('contratos_recepcion.*')">
                            <a href="{{ route('contratos.index') }}"
                                class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">C.
                                Abastecimiento</a>
                            <a href="{{ route('contratos_recepcion.index') }}"
                                class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">C. Recepción</a>
                        </x-nav-accordion-sidebar>

                        <x-nav-link-sidebar :href="route('capital.index')" :active="request()->routeIs('capital.*')" icon="bi-cash-coin">
                            Capital
                        </x-nav-link-sidebar>
                    </div>
                </div>
            @endif
        @endif

        <!-- RR.HH -->
        @if ($area == 'RR.HH' || $area == 'ADMINISTRACION' || $area == 'GERENCIA')
            @if ($tipo == 2 || $tipo == 3 || $area == 'ADMINISTRACION')
                <div>
                    <h3
                        class="px-4 text-[10px] font-black text-[#c9a227] dark:text-[#d4af37] uppercase tracking-[2px] mb-4 flex items-center gap-3">
                        <span>Recursos Humanos</span>
                        <div
                            class="h-[1px] flex-1 bg-gradient-to-r from-[#c9a227]/30 to-transparent dark:from-[#d4af37]/30">
                        </div>
                    </h3>
                    <div class="space-y-1">
                        <x-nav-link-sidebar :href="route('empresas_clientes.afp_plame')" :active="request()->routeIs('empresas_clientes.afp_plame')" icon="bi-person-workspace">
                            AFP - PDT Plame
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('deudas.index', 'recursos')" :active="request()->routeIs('deudas.index') && request()->segment(2) == 'recursos'" icon="bi-cash-stack">
                            Deudas Recursos
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('deuda-afp.index')" :active="request()->routeIs('deuda-afp.index')" icon="bi-bank">
                            Deudas AFP
                        </x-nav-link-sidebar>
                        
                        @if ($tipo == 1 || $tipo == 2 || $area == 'ADMINISTRACION')
                        <x-nav-link-sidebar :href="route('deudas_recepcion.index')" :active="request()->routeIs('deudas_recepcion.*')" icon="bi-cash-coin">
                            Cotizaciones Recepción
                        </x-nav-link-sidebar>
                        @endif
                        
                        <x-nav-link-sidebar :href="route('polizas.index')" :active="request()->routeIs('polizas.*')" icon="bi-shield-check">
                            Pólizas
                        </x-nav-link-sidebar>
                        @if (Auth::user()->tipo_usuario == 2 || Auth::user()->area->nombre == 'ADMINISTRACION')
                            <x-nav-link-sidebar :href="route('planillas.index')" :active="request()->routeIs('planillas.*')" icon="bi-table">
                                Planillas
                            </x-nav-link-sidebar>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        <!-- BANCAS -->
        @if ($area == 'BANCAS')
            <div>
                <h3
                    class="px-4 text-[10px] font-black text-[#c9a227] dark:text-[#d4af37] uppercase tracking-[2px] mb-4 flex items-center gap-3">
                    <span>Bancas</span>
                    <div
                        class="h-[1px] flex-1 bg-gradient-to-r from-[#c9a227]/30 to-transparent dark:from-[#d4af37]/30">
                    </div>
                </h3>
                <div class="space-y-1">
                    <x-nav-link-sidebar :href="route('abono_facturas.index')" :active="request()->routeIs('abono_facturas.*')" icon="bi-receipt">
                        Abonos Facturas
                    </x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('cuentas_empresas.index')" :active="request()->routeIs('cuentas_empresas.*')" icon="bi-credit-card-2-back">
                        Cuentas Empresas
                    </x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('estados_cuenta.index')" :active="request()->routeIs('estados_cuenta.*')" icon="bi-bank2">
                        Estados de Cuenta
                    </x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('gerente_socios.index')" :active="request()->routeIs('gerente_socios.*')" icon="bi-person-bounding-box">
                        Gerente y Socios
                    </x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('guias.bancas')" :active="request()->routeIs('guias.bancas')" icon="bi-truck">
                        Guías
                    </x-nav-link-sidebar>
                </div>
            </div>
        @endif

        <!-- ADMINISTRACION SISTEMA -->
        @if ($area == 'ADMINISTRACION')
            @if ($id != 5 && $id != 12)
                <div>
                    <h3
                        class="px-4 text-[10px] font-black text-[#c9a227] dark:text-[#d4af37] uppercase tracking-[2px] mb-4 flex items-center gap-3">
                        <span>Administración</span>
                        <div
                            class="h-[1px] flex-1 bg-gradient-to-r from-[#c9a227]/30 to-transparent dark:from-[#d4af37]/30">
                        </div>
                    </h3>
                    <div class="space-y-1">
                        <x-nav-accordion-sidebar label="Configuración" icon="bi-gear-fill" :active="request()->routeIs('usuarios.*') ||
                            request()->routeIs('areas.*') ||
                            request()->routeIs('feriados.*') ||
                            request()->routeIs('calendario.*') ||
                            request()->routeIs('consultas.*') ||
                            request()->routeIs('libros.*') ||
                            request()->routeIs('valor_uit.*')">
                            <a href="{{ route('usuarios.index') }}"
                                class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Usuarios</a>
                            @if ($tipo == 1)
                                <a href="{{ route('areas.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Áreas</a>
                            @endif
                            <a href="{{ route('feriados.index') }}"
                                class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Feriados</a>
                            <a href="{{ route('calendario.index') }}"
                                class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Calendario
                                Vencimientos</a>
                            <a href="{{ route('consultas.index') }}"
                                class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Consultas Dólar</a>
                            <a href="{{ route('libros.index') }}"
                                class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Libros</a>
                            <a href="{{ route('valor_uit.index') }}"
                                class="block px-4 py-2 text-sm text-gray-500 hover:text-amber-600">Valor UIT</a>
                        </x-nav-accordion-sidebar>
                    </div>
                </div>
            @endif
        @endif

    </div>

    <!-- User Footer -->
    <div class="p-6 bg-gray-50 dark:bg-[#1a1c21] border-t border-gray-100 dark:border-gray-800 shrink-0">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('profile.edit') }}">
                <div
                    class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-500 border border-amber-200 dark:border-amber-900/30">
                    <i class="bi bi-person-fill text-xl"></i>
                </div>
            </a>
            <div class="flex flex-col min-w-0">
                <span class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate">{{ $user->name }}</span>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ $area }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-gray-200/50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-red-500 hover:text-white transition-all font-bold text-sm">
                <i class="bi bi-power"></i>
                Cerrar Sesión
            </button>
        </form>
    </div>
</aside>

<!-- Mobile Overlay -->
<div x-show="!sidebarOpen" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-30 lg:hidden"
    @click="sidebarOpen = true"></div>

<style>
    [x-cloak] {
        display: none !important;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e5e7eb;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #d1d5db;
    }
</style>
