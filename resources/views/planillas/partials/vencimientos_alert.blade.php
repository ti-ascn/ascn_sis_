@if(isset($vencimientos) && $vencimientos->count() > 0)
    <div id="vencimientos-alert-box" data-count="{{ $vencimientos->count() }}" 
        class="mb-6 p-5 bg-gradient-to-br from-amber-50 to-orange-50/60 dark:from-amber-950/20 dark:to-orange-950/10 border border-amber-200/80 dark:border-amber-900/40 border-l-4 border-l-amber-500 rounded-r-2xl shadow-md transition-all duration-300 animate-pulse-subtle">
        <div class="flex items-start gap-4">
            <!-- Icono de advertencia con fondo sutil -->
            <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-amber-100/90 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/30 shadow-inner">
                <svg class="w-5 h-5 animate-bounce-subtle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            
            <div class="flex-grow">
                <!-- Cabecera de la alerta -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-amber-200/40 dark:border-amber-900/20 pb-2.5 mb-3">
                    <div>
                        <h3 class="text-base font-bold text-amber-950 dark:text-amber-300 flex items-center gap-1.5">
                            Contratos / Adendas por vencer este mes
                        </h3>
                        <p class="text-xs text-amber-800/90 dark:text-amber-400/80 mt-0.5">
                            Los siguientes trabajadores tienen un contrato o adenda activo que vence este mes de <span class="capitalize font-bold text-amber-950 dark:text-amber-200">{{ now()->locale('es')->monthName }}</span>:
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5 bg-amber-200/50 dark:bg-amber-950/50 border border-amber-300/40 dark:border-amber-900/40 px-3 py-1 rounded-full text-amber-950 dark:text-amber-300 text-xs font-bold shadow-sm self-start sm:self-center">
                        <i class="bi bi-clock-history text-amber-700 dark:text-amber-400"></i>
                        <span>{{ $vencimientos->count() }} pendientes</span>
                    </div>
                </div>

                <!-- Lista de trabajadores en tarjetas elegantes -->
                <div class="overflow-y-auto overflow-x-hidden pr-1" style="max-height: 12rem;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($vencimientos as $v)
                            <div class="group relative flex items-center justify-between p-3 bg-white/70 hover:bg-white dark:bg-gray-850/40 dark:hover:bg-gray-850/80 border border-amber-100 hover:border-amber-300/80 dark:border-gray-700/60 dark:hover:border-amber-500/30 rounded-xl shadow-sm hover:shadow transition-all duration-200">
                                <!-- Línea de acento superior sutil al pasar el mouse -->
                                <div class="absolute top-0 left-0 w-full h-0.5 rounded-t-xl bg-gradient-to-r {{ str_contains(strtolower($v->tipo_doc), 'adenda') ? 'from-purple-400 to-indigo-400' : 'from-blue-400 to-cyan-400' }} opacity-0 group-hover:opacity-100 transition-opacity duration-250"></div>
                                
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full {{ str_contains(strtolower($v->tipo_doc), 'adenda') ? 'bg-purple-100 text-purple-700 border border-purple-200/50 dark:bg-purple-950/30 dark:text-purple-300 dark:border-purple-800/30' : 'bg-blue-100 text-blue-700 border border-blue-200/50 dark:bg-blue-950/30 dark:text-blue-300 dark:border-blue-800/30' }} flex items-center justify-center font-bold text-xs shadow-sm">
                                        {{ strtoupper(substr($v->planilla->nombre_trabajador ?? 'T', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 group-hover:text-gray-955 dark:group-hover:text-white transition-colors">
                                            {{ $v->planilla->nombre_trabajador }}
                                        </h4>
                                        <p class="text-[10px] text-gray-550 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                                            <i class="bi bi-building text-gray-400 dark:text-gray-500"></i>
                                            <span class="truncate max-w-[150px] sm:max-w-[200px]" title="{{ $v->planilla->empresaCliente->razon_social }}">
                                                {{ $v->planilla->empresaCliente->razon_social }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold tracking-wider {{ str_contains(strtolower($v->tipo_doc), 'adenda') ? 'bg-purple-100 text-purple-800 border border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800/50' : 'bg-blue-100 text-blue-800 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800/50' }}">
                                            {{ $v->tipo_doc }}
                                        </span>
                                        <p class="text-[10px] text-amber-800 dark:text-amber-400 font-medium whitespace-nowrap">
                                            Vence: <span class="font-bold text-amber-955 dark:text-amber-200">{{ $v->fecha_fin->format('d/m/Y') }}</span>
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center gap-1.5 ml-1">
                                        <!-- Botón: Ver Contratos (Control) -->
                                        <button type="button" class="btn-ver-contrato-alerta p-1.5 rounded-lg bg-amber-100/60 hover:bg-amber-200 dark:bg-amber-950/40 dark:hover:bg-amber-900/60 text-amber-700 dark:text-amber-300 border border-amber-250/30 dark:border-amber-800/30 transition-all duration-150 shadow-sm flex items-center justify-center tooltip"
                                                data-id="{{ $v->planilla->id_empresas_clientes }}"
                                                data-worker-name="{{ $v->planilla->nombre_trabajador }}"
                                                data-worker-dni="{{ $v->planilla->dni }}">
                                            <i class="bi bi-folder2-open text-xs"></i>
                                            <span class="tooltip-text">Ver Contratos</span>
                                        </button>
                                        
                                        @if (Auth::user()->area->nombre != 'AUDITORIA')
                                            <!-- Botón: Agregar Adenda / Contrato -->
                                            <button type="button" class="btn-agregar-contrato-modal p-1.5 rounded-lg bg-emerald-100/70 hover:bg-emerald-200 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-350 border border-emerald-250/30 dark:border-emerald-800/30 transition-all duration-150 shadow-sm flex items-center justify-center tooltip"
                                                    data-id="{{ $v->planilla->id_planilla }}"
                                                    data-nombre="{{ $v->planilla->nombre_trabajador }}"
                                                    data-dni="{{ $v->planilla->dni }}">
                                                <i class="bi bi-file-earmark-plus text-xs"></i>
                                                <span class="tooltip-text">Agregar Adenda</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

