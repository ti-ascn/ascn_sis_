<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-lg font-semibold mb-4 border-b pb-2 flex items-center gap-2">
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
        </span>
        Usuarios en línea ({{ $usuarios->count() }})
    </h2>

    @if ($usuarios->isEmpty())
        <p class="text-sm text-gray-500">No hay usuarios conectados.</p>
    @else
        <ul class="text-sm text-gray-800 space-y-3">
            @foreach ($usuarios as $usuario)
                @php
                    $sesion = $sesionesCrudas->firstWhere('user_id', $usuario->id);
                    $tipoDispositivo = $sesion
                        ? \App\View\Components\UsuariosOnline::detectarDispositivo($sesion->user_agent)
                        : null;
                    
                    // Obtenemos la última ruta que visitó desde Cache
                    $ultimaActividadUrl = \Illuminate\Support\Facades\Cache::get('user_last_path_' . $usuario->id);
                    if ($ultimaActividadUrl == '/') $ultimaActividadUrl = '/dashboard';
                @endphp
                <li class="bg-gray-50 p-3 rounded-md border border-gray-100">
                    <div class="flex items-center justify-between mb-1">
                        <div class="font-medium flex items-center gap-2">
                            <span class="text-green-500 text-lg leading-none">&bull;</span>
                            {{ $usuario->name }}
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 bg-gray-200 text-gray-700 rounded-full">
                            {{ $usuario->area->nombre }}
                        </span>
                    </div>

                    @if (Auth::check() && in_array(Auth::user()->tipo_usuario, [1, 2]))
                        @if ($sesion)
                            <div class="ml-4 text-xs text-gray-600 mt-2 grid grid-cols-1 md:grid-cols-2 gap-1">
                                <div><strong>IP:</strong> {{ $sesion->ip_address }}</div>
                                <div><strong>Dispositivo:</strong> {{ $tipoDispositivo }}</div>
                                <div><strong>Visto hace:</strong> {{ \Carbon\Carbon::createFromTimestamp($sesion->last_activity)->diffForHumans() }}</div>
                                @if($ultimaActividadUrl)
                                    <div class="col-span-1 md:col-span-2 mt-1">
                                        <strong>Acción reciente:</strong> 
                                        <span class="text-blue-600 truncate inline-block max-w-full align-bottom" title="{{ $ultimaActividadUrl }}">
                                            Viendo: {{ str_replace('/', ' / ', ltrim($ultimaActividadUrl, '/')) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
