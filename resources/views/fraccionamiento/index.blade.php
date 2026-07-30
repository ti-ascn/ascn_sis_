<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Fraccionamientos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />

            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                    <a href="{{ route('fraccionamiento.create') }}"
                        class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-plus-circle"></i> Nuevo Fraccionamiento
                    </a>
                </div>

                <br />
            @endif

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-[1200px] table-auto" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            {{-- <th class="px-4 py-2 border-b">ID</th> --}}
                            <th class="px-4 py-2 border-b">Empresa</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">Periodo</th>
                            <th class="px-4 py-2 border-b">Cuota Pendiente</th>
                            <th class="px-4 py-2 border-b">Vencimiento Cuota Pendiente</th>
                            <th class="px-4 py-2 border-b">Monto Cuota Pendiente</th>
                            <th class="px-4 py-2 border-b">Cuotas Totales</th>
                            <th class="px-4 py-2 border-b">Monto Total Fraccionamiento</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fraccionamientos as $f)
                            <tr class="hover:bg-gray-50">
                                {{-- <td class="px-4 py-2 border-b">{{ $f->id_fraccionamiento }}</td> --}}
                                <td class="px-4 py-2 border-b">{{ $f->empresa->razon_social ?? 'Sin empresa' }}</td>
                                <td class="px-4 py-2 border-b">{{ $f->empresa->ruc ?? 'Sin RUC' }}</td>
                                <td class="px-4 py-2 border-b">{{ $f->periodo }}</td>
                                <td class="px-4 py-2 border-b">{{ $f->nombre_cuota_pendiente }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ $f->fecha_vencimiento_pendiente !== '-' ? \Carbon\Carbon::parse($f->fecha_vencimiento_pendiente)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    S/ {{ number_format($f->total_pendiente, 2) }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @if ($f->cuota_acogimiento)
                                        {{ $f->nro_cuotas }}<span class="text-gray-500 text-sm"> (CA)</span>
                                    @else
                                        {{ $f->nro_cuotas }}
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">S/ {{ number_format($f->total, 2) }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($f->estado === 'VIGENTE')
                                        <span class="inline-block text-center px-4 py-1 text-white bg-green-500 rounded-full w-24 text-xs font-semibold">
                                            Vigente
                                        </span>
                                    @elseif($f->estado === 'PERDIDO')
                                        <span class="inline-block text-center px-4 py-1 text-white bg-red-500 rounded-full w-24 text-xs font-semibold">
                                            Perdido
                                        </span>
                                    @elseif($f->estado === 'PAGADO')
                                        <span class="inline-block text-center px-4 py-1 text-white bg-blue-500 rounded-full w-24 text-xs font-semibold">
                                            Pagado
                                        </span>
                                    @else
                                        <span class="inline-block text-center px-4 py-1 text-white bg-gray-500 rounded-full w-24 text-xs font-semibold">
                                            {{ $f->estado }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex items-center space-x-2">
                                        @if ($f->detalles->isEmpty())
                                            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                                <a href="{{ route('detalle_fraccionamiento.create', $f->id_fraccionamiento) }}"
                                                    class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #16a34a; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#15803d';"
                                                    onmouseout="this.style.color='#16a34a';">
                                                    <span class="tooltip-text">Crear Detalle</span>
                                                    <x-generate-icon />
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('detalle_fraccionamiento.show', $f->id_fraccionamiento) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #3b82f6; transition: color 0.3s;"
                                                onmouseover="this.style.color='#2563eb';"
                                                onmouseout="this.style.color='#3b82f6';">
                                                <span class="tooltip-text">Detalles</span>
                                                <x-details-icon />
                                            </a>
                                        @endif

                                        {{-- Botón Eliminar --}}
                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                            <form
                                                action="{{ route('fraccionamiento.destroy', $f->id_fraccionamiento) }}"
                                                method="POST" style="display:inline;"
                                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar este fraccionamiento?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';">
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Dropdown para cambio de estado --}}
                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                            <div class="relative">
                                                <x-dropdown align="right" width="48">
                                                    <x-slot name="trigger">
                                                        <button class="flex items-center py-1 rounded-md tooltip"
                                                            style="color: #6B7280; transition: color 0.3s;"
                                                            onmouseover="this.style.color='#4B5563';"
                                                            onmouseout="this.style.color='#6B7280';">
                                                            <x-dots-vertical-icon />
                                                            <span class="tooltip-text">Cambiar Estado</span>
                                                        </button>
                                                    </x-slot>

                                                    <x-slot name="content">
                                                        <div class="absolute right-0 z-50 w-48 bg-white shadow-lg rounded-md border p-2 text-start">
                                                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 border-b border-gray-100">
                                                                {{ __('Cambiar Estado') }}
                                                            </div>
                                                            <form method="POST" action="{{ route('fraccionamiento.cambiar_estado', $f->id_fraccionamiento) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="estado" value="VIGENTE">
                                                                <x-dropdown-link href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                                                                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500 mr-2"></span>
                                                                    {{ __('VIGENTE') }}
                                                                </x-dropdown-link>
                                                            </form>
                                                            <form method="POST" action="{{ route('fraccionamiento.cambiar_estado', $f->id_fraccionamiento) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="estado" value="PERDIDO">
                                                                <x-dropdown-link href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                                                                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500 mr-2"></span>
                                                                    {{ __('PERDIDO') }}
                                                                </x-dropdown-link>
                                                            </form>
                                                            <form method="POST" action="{{ route('fraccionamiento.cambiar_estado', $f->id_fraccionamiento) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="estado" value="PAGADO">
                                                                <x-dropdown-link href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                                                                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500 mr-2"></span>
                                                                    {{ __('PAGADO') }}
                                                                </x-dropdown-link>
                                                            </form>
                                                        </div>
                                                    </x-slot>
                                                </x-dropdown>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
