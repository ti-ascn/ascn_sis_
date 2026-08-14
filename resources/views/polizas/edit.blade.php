<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar póliza — {{ $poliza->empresa->razon_social ?? 'Sin empresa asociada' }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6 space-y-6">

                @php
                    $aseguradoras = [
                        'SIN ASEGURADORA',
                        'CRECER SEGUROS S.A. COMPAÑIA DE SEGUROS',
                        'PACIFICO COMPAÑIA DE SEGUROS Y REASEGUROS',
                        'LA POSITIVA VIDA SEGUROS Y REASEGUROS',
                        'MAPFRE PERU COMPAÑIA DE SEGUROS Y REASEGUROS',
                        'RIMAC SEGUROS Y REASEGUROS',
                    ];
                @endphp

                {{-- FORMULARIO --}}
                <form method="POST" action="{{ route('polizas.update', $poliza->id_poliza) }}" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="tipo" value="{{ $tipo }}">

                    <div class="grid grid-cols-2 gap-6">
                        {{-- EMPRESA (solo visual, nunca editable) --}}
                        <div>
                            <x-input-label :value="__('Empresa')" />
                            <select name="id_empresa_cliente"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed"
                                disabled>
                                @foreach ($empresas as $empresa)
                                    <option value="{{ $empresa->id_empresas_clientes }}"
                                        {{ $empresa->id_empresas_clientes == $poliza->id_empresa_cliente ? 'selected' : '' }}>
                                        {{ $empresa->razon_social }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- Mantén el valor oculto para el submit --}}
                            <input type="hidden" name="id_empresa_cliente" value="{{ $poliza->id_empresa_cliente }}">
                        </div>

                        {{-- BROKER (solo usuarios tipo 1 o 2 pueden editar) --}}
                        <div class="hidden">
                            <x-input-label :value="__('Broker')" />
                            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                <x-text-input name="broker" class="mt-1 block w-full" value="{{ $poliza->broker }}" />
                            @else
                                <x-text-input name="broker" class="mt-1 block w-full bg-gray-100 cursor-not-allowed"
                                    value="{{ $poliza->broker }}" disabled />
                                <input type="hidden" name="broker" value="{{ $poliza->broker }}">
                            @endif
                        </div>
                        {{-- ESTADO GENERAL (solo usuarios tipo 1 o 2 pueden editar) --}}
                        <div>
                            <x-input-label :value="__('Estado general de la póliza')" />
                            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                <select name="estado_general"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="1" {{ $poliza->estado ? 'selected' : '' }}>Activa</option>
                                    <option value="0" {{ !$poliza->estado ? 'selected' : '' }}>Inactiva
                                    </option>
                                </select>
                            @else
                                <select name="estado_general"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed"
                                    disabled>
                                    <option value="1" {{ $poliza->estado ? 'selected' : '' }}>Activa</option>
                                    <option value="0" {{ !$poliza->estado ? 'selected' : '' }}>Inactiva
                                    </option>
                                </select>
                                <input type="hidden" name="estado_general" value="{{ $poliza->estado ? 1 : 0 }}">
                            @endif
                        </div>
                    </div>



                    {{-- ========================= --}}
                    {{-- BLOQUE VIDA LEY --}}
                    {{-- ========================= --}}
                    @if ($poliza->vida && (!$tipo || $tipo === 'vida'))
                        <div class="border rounded-lg p-4 bg-gray-50 mt-6">
                            <h3 class="font-semibold mb-3 text-blue-600">Póliza Vida Ley</h3>

                            <input type="hidden" name="id_poliza_vida" value="{{ $poliza->vida->id_poliza_vida }}">

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label :value="__('Aseguradora')" />
                                    <select name="vida_aseguradora"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="" disabled>Selecciona aseguradora</option>
                                        @foreach ($aseguradoras as $aseg)
                                            <option value="{{ $aseg }}"
                                                {{ $poliza->vida->aseguradora == $aseg ? 'selected' : '' }}>
                                                {{ $aseg }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :value="__('N° Póliza')" />
                                    <x-text-input name="vida_nro_poliza" value="{{ $poliza->vida->nro_poliza }}"
                                        class="mt-1 block w-full" />
                                </div>
                            </div>

                            <div class="mt-3">
                                <x-input-label :value="__('Broker Vida Ley')" />
                                <x-text-input name="vida_broker" value="{{ $poliza->vida->broker ?? '' }}"
                                    class="mt-1 block w-full" />
                            </div>

                            <div class="grid grid-cols-2 gap-4 mt-3">
                                <div>
                                    <x-input-label :value="__('Fecha inicio')" />
                                    <x-text-input name="vida_fecha_inicio" type="date" class="mt-1 block w-full"
                                        value="{{ $poliza->vida->fecha_inicio ? \Carbon\Carbon::parse($poliza->vida->fecha_inicio)->format('Y-m-d') : '' }}" />
                                </div>
                                <div>
                                    <x-input-label :value="__('Fecha fin')" />
                                    <x-text-input name="vida_fecha_fin" type="date" class="mt-1 block w-full"
                                        value="{{ $poliza->vida->fecha_fin ? \Carbon\Carbon::parse($poliza->vida->fecha_fin)->format('Y-m-d') : '' }}" />
                                </div>
                            </div>

                            <div class="mt-3">
                                <x-input-label :value="__('Estado de la póliza Vida')" />
                                <select name="vida_estado"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="ACTIVO" {{ $poliza->vida->estado == 'ACTIVO' ? 'selected' : '' }}>
                                        ACTIVO</option>
                                    <option value="INACTIVO"
                                        {{ $poliza->vida->estado == 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
                                    <option value="COTIZAR" {{ $poliza->vida->estado == 'COTIZAR' ? 'selected' : '' }}>
                                        COTIZAR</option>
                                    <option value="COTIZ. ENVIADA"
                                        {{ $poliza->vida->estado == 'COTIZ. ENVIADA' ? 'selected' : '' }}>COTIZ.
                                        ENVIADA</option>
                                    <option value="COTIZ. RECIBIDA"
                                        {{ $poliza->vida->estado == 'COTIZ. RECIBIDA' ? 'selected' : '' }}>COTIZ.
                                        RECIBIDA</option>
                                </select>
                            </div>

                            <div class="mt-3">
                                <x-input-label :value="__('Observación')" />
                                <x-text-input name="vida_observacion"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    value="{{ $poliza->vida->observacion }}" />
                            </div>
                        </div>
                    @endif

                    {{-- ========================= --}}
                    {{-- BLOQUE SCTR --}}
                    {{-- ========================= --}}
                    @if ($poliza->sctr && (!$tipo || $tipo === 'sctr'))
                        <div class="border rounded-lg p-4 bg-gray-50 mt-6">
                            <input type="hidden" name="id_poliza_sctr" value="{{ $poliza->sctr->id_poliza_sctr }}">
                            <h3 class="font-semibold mb-3 text-green-600">Póliza SCTR</h3>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label :value="__('Aseguradora')" />
                                    <select name="sctr_aseguradora"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="" disabled>Selecciona aseguradora</option>
                                        @foreach ($aseguradoras as $aseg)
                                            <option value="{{ $aseg }}"
                                                {{ $poliza->sctr->aseguradora == $aseg ? 'selected' : '' }}>
                                                {{ $aseg }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :value="__('Broker SCTR')" />
                                    <x-text-input name="sctr_broker" value="{{ $poliza->sctr->broker ?? '' }}"
                                        class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label :value="__('Fecha inicio')" />
                                    <x-text-input name="sctr_fecha_inicio" type="date" class="mt-1 block w-full"
                                        value="{{ $poliza->sctr->fecha_inicio ? \Carbon\Carbon::parse($poliza->sctr->fecha_inicio)->format('Y-m-d') : '' }}" />
                                </div>
                                <div>
                                    <x-input-label :value="__('Fecha fin')" />
                                    <x-text-input name="sctr_fecha_fin" type="date" class="mt-1 block w-full"
                                        value="{{ $poliza->sctr->fecha_fin ? \Carbon\Carbon::parse($poliza->sctr->fecha_fin)->format('Y-m-d') : '' }}" />
                                </div>
                            </div>
                            <div>
                                <x-input-label :value="__('Estado de la póliza SCTR')" />
                                <select name="sctr_estado"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="ACTIVO" {{ $poliza->sctr->estado == 'ACTIVO' ? 'selected' : '' }}>
                                        ACTIVO</option>
                                    <option value="INACTIVO"
                                        {{ $poliza->sctr->estado == 'INACTIVO' ? 'selected' : '' }}>INACTIVO
                                    </option>
                                    <option value="COTIZAR" {{ $poliza->sctr->estado == 'COTIZAR' ? 'selected' : '' }}>
                                        COTIZAR</option>
                                    <option value="COTIZ. ENVIADA"
                                        {{ $poliza->sctr->estado == 'COTIZ. ENVIADA' ? 'selected' : '' }}>COTIZ.
                                        ENVIADA</option>
                                    <option value="COTIZ. RECIBIDA"
                                        {{ $poliza->sctr->estado == 'COTIZ. RECIBIDA' ? 'selected' : '' }}>COTIZ.
                                        RECIBIDA</option>
                                    <option value="BAJO RIESGO"
                                        {{ $poliza->sctr->estado == 'BAJO RIESGO' ? 'selected' : '' }}>BAJO RIESGO
                                    </option>
                                </select>
                            </div>

                            <div class="mt-3">
                                <x-input-label :value="__('Observación')" />
                                <x-text-input name="sctr_observacion"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    value="{{ $poliza->sctr->observacion }}" />
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end mt-6">
                        <x-secondary-button
                            onclick="window.location.href='{{ route('polizas.index') }}'">Cancelar</x-secondary-button>
                        <x-primary-button class="ml-3">Guardar cambios</x-primary-button>
                    </div>
                </form>

                {{-- =============== --}}
                {{-- HISTORIAL --}}
                {{-- =============== --}}
                @if ($poliza->historial && $poliza->historial->count())
                    @php
                        // Filtrar el historial según el tipo actual (si existe)
                        $historialFiltrado = $tipo
                            ? $poliza->historial->where('tipo_poliza', strtoupper($tipo))
                            : $poliza->historial;
                    @endphp

                    @if ($historialFiltrado->count())
                        <div class="border rounded-lg p-4 bg-gray-50 mt-8 shadow-sm">
                            <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="bi bi-clock-history text-blue-600 text-lg"></i>
                                Historial de cambios
                            </h3>
                            {{-- CONTENEDOR CON SCROLL --}}
                            <div
                                class="max-h-72 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200 hover:scrollbar-thumb-gray-500 rounded-md">
                                <ul class="divide-y text-sm text-gray-700">
                                    @foreach ($historialFiltrado->sortByDesc('fecha_registro') as $h)
                                        <li class="py-3 px-2 hover:bg-gray-100 transition-colors rounded-md">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <strong class="text-blue-600">{{ $h->tipo_poliza }}</strong>
                                                    — <em
                                                        class="text-gray-500">{{ ucfirst(strtolower($h->cambio)) }}</em>
                                                    por <strong>{{ $h->usuario->name ?? 'Sistema' }}</strong>
                                                </div>
                                                <span class="text-xs text-gray-400 whitespace-nowrap">
                                                    {{ \Carbon\Carbon::parse($h->fecha_registro)->format('d/m/Y H:i') }}
                                                </span>
                                            </div>

                                            {{-- Texto descriptivo --}}
                                            @if ($h->detalle)
                                                <p class="mt-2 text-gray-600 leading-snug italic">
                                                    {!! preg_replace(
                                                        [
                                                            '/<strong>(.*?)<\/strong>/i', // campos (aseguradora, estado, etc.)
                                                            '/de "(.*?)"/', // valores antiguos
                                                            '/a "(.*?)"/', // valores nuevos
                                                        ],
                                                        [
                                                            '<strong class="text-gray-900">$1</strong>', // campo destacado
                                                            'de <span class="text-red-600 font-medium">"$1"</span>', // antiguo (rojo)
                                                            'a <span class="text-green-700 font-semibold">"$1"</span>', // nuevo (verde)
                                                        ],
                                                        $h->detalle, // ya no usamos e() para permitir <strong> renderizado
                                                    ) !!}
                                                </p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
