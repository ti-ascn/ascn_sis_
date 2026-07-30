<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Determinaciones Mensuales del Año') }} <span class="text-blue-500">{{ $anoActual }}</span>
        </h2>
    </x-slot>

    <style>
        .ver-determinacion {
            text-decoration: none;
            color: #3b82f6;
        }

        .ver-determinacion:hover {
            text-decoration: underline;
        }
    </style>
    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />
            <!-- Formulario para seleccionar el año -->
            <div class="bg-gray-100 p-4 rounded-lg shadow-md mb-6">
                <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                    <form action="{{ route('determinacion_mensual.index') }}" method="GET"
                        class="flex items-center gap-4">
                        <select name="ano" onchange="this.form.submit()"
                            class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @for ($ano = now()->year; $ano >= 2020; $ano--)
                                <option value="{{ $ano }}" {{ $ano == $anoActual ? 'selected' : '' }}>
                                    {{ $ano }}</option>
                            @endfor
                        </select>
                        {{-- <button type="submit"
                            class="flex items-center gap-2 bg-blue-500 px-4 py-2 rounded-md text-white hover:bg-blue-600 transition duration-300">
                            <i class="bi bi-arrow-repeat"></i> Cambiar año
                        </button> --}}
                    </form>

                    <a href="{{ route('determinacion_mensual.sinDeterminacion') }}"
                        class="flex items-center gap-2 bg-red-500 px-4 py-2 rounded-md hover:bg-red-600 transition duration-300 text-white text-center w-full md:w-auto">
                        <i class="bi bi-exclamation-circle"></i> Sin Determinación Mensual
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <table class="min-w-[1200px] bg-white" id="myTable-todos-mensuales">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border-b">EMPRESA</th>
                                @for ($mes = 1; $mes <= 12; $mes++)
                                    <th class="px-4 py-2 border-b">
                                        {{ strtoupper(\Carbon\Carbon::create()->month($mes)->locale('es')->monthName) }}
                                    </th>
                                @endfor
                                <th class="px-4 py-2 border-b">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($datosTabla as $fila)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">{{ $fila['empresa'] }}</td>
                                    @for ($mes = 1; $mes <= 12; $mes++)
                                        <td class="px-4 py-2 border-b text-center"
                                            data-filter="{{ $fila[$mes]['tiene'] ? 'Realizado' : 'Pendiente' }}">
                                            @if (!$fila[$mes]['tiene'])
                                                <span>
                                                    <a href="{{ route('determinacion_mensual.create', ['id_empresa' => $fila['id_empresas_clientes'], 'mes' => $mes, 'ano' => $anoActual]) }}"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-500 "
                                                        title="Sin determinaciones">
                                                        <i class="bi bi-search"></i>
                                                    </a>
                                                </span>
                                            @else
                                                <a href="{{ $fila[$mes]['url'] }}"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white hover:bg-blue-600 transition"
                                                    title="Ver determinación">
                                                    <i class="bi bi-search"></i>
                                                </a>
                                            @endif
                                        </td>
                                    @endfor
                                    <td class="px-4 py-2 border-b">
                                        <a href="{{ route('determinacion_mensual.detalles', ['id_empresa' => $fila['id_empresas_clientes'], 'ano' => $anoActual]) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #3b82f6; transition: color 0.3s;"
                                            onmouseover="this.style.color='#2563eb';"
                                            onmouseout="this.style.color='#3b82f6';">
                                            <span class="tooltip-text">Todos los Detalles</span>
                                            <x-details-icon />
                                        </a>
                                        {{-- @if (!$fila['tiene_determinaciones_proximo_ano'])
                                            <a href="{{ route('determinacion_mensual.create', $fila['id_empresas_clientes']) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: {{ $fila['total_determinaciones'] >= 12 ? '#6B7280' : '#10B981' }}; transition: color 0.3s;"
                                                onmouseover="this.style.color='{{ $fila['total_determinaciones'] >= 12 ? '#4B5563' : '#059669' }}';"
                                                onmouseout="this.style.color='{{ $fila['total_determinaciones'] >= 12 ? '#6B7280' : '#10B981' }}';">
                                                <span class="tooltip-text">
                                                    {{ $fila['total_determinaciones'] >= 12 ? '+Próximo Año' : '+Determinación Mensual' }}
                                                </span>
                                                <x-tax-icon />
                                            </a>
                                        @endif --}}
                                        <!-- Botón para eliminar todas las determinaciones de la empresa para el año actual -->
                                        @if (Auth::user()->tipo_usuario == 1)
                                            <form
                                                action="{{ route('determinacion_mensual.destroyByEmpresaAndYear', ['id_empresa' => $fila['id_empresas_clientes'], 'ano' => $anoActual]) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';"
                                                    onclick="return confirm('¿Estás seguro de que deseas eliminar todas las determinaciones de {{ $fila['empresa'] }} para el año {{ $anoActual }}?')">
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    $(document).ready(function() {
        $('#myTable-todos-mensuales').DataTable({
            responsive: false,
            pageLength: 200,
            lengthMenu: [10, 25, 50, 100, 200],
            language: {
                url: "/js/datatables/lang/es-ES.json" // Textos en español
            },
        });
    });
</script>
