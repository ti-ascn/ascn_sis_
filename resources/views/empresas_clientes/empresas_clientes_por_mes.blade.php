<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registros Mensuales de Compras, Ventas, Sire y PDT de las Empresas') }}
        </h2>
    </x-slot>
    <input type="hidden" name="mesActual" id="mesActual" value="{{ $mes }}">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <!-- Formulario para filtrar por mes -->
            <div class="bg-gray-100 p-4 rounded-lg shadow-md mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    <i class="bi bi-calendar-range"></i> {{ __('Filtrar por mes') }}
                </h3>
                <form action="{{ route('empresas_clientes.filtrar_por_mes') }}" method="GET"
                    class="flex gap-4 items-center">
                    <!-- Selector de mes -->
                    <div class="flex flex-col w-48">
                        <select name="mes" id="mesSeleccionado"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($meses as $key => $mesNombre)
                                <option value="{{ $key }}" {{ isset($mes) && $mes == $key ? 'selected' : '' }}>
                                    {{ $mesNombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Selector de año -->
                    <div class="flex flex-col w-48">
                        <select name="anio" id="anioSeleccionado"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                <option value="{{ $i }}" {{ isset($anio) && $anio == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- Botón de búsqueda -->
                    <div class="flex items-center">
                        <button type="submit"
                            class="bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white shadow-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="bi bi-search"></i> {{ __('Buscar') }}
                        </button>
                    </div>
                    @if (Auth::user()->tipo_usuario == 1)
                        <!-- Botón para abrir el modal -->
                        <button onclick="event.preventDefault(); toggleModal('modalPermisos')"
                            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300">
                            <i class="bi bi-sliders2"></i> Permisos de Edición
                        </button>
                    @endif
                    <button onclick="event.preventDefault(); toggleModal('modalGrafico')" type="button"
                        class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 transition duration-300">
                        <i class="bi bi-bar-chart-line-fill"></i> Estadísticas Mensuales
                    </button>
                </form>
            </div>

            <!-- Tabla de registros -->
            @if (isset($empresas))
                <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                    <table class="min-w-full table-auto" id="table-filtrar-mes">
                        <thead>
                            <tr class="bg-gray-100 text-left">
                                <th class="px-4 py-2 border-b">UD</th>
                                <th class="px-4 py-2 border-b">RAZÓN SOCIAL</th>
                                <th class="px-4 py-2 border-b">ESTADO</th>
                                <th class="px-4 py-2 border-b">SIRE</th>
                                <th class="px-4 py-2 border-b">PLAZO SIRE</th>
                                <th class="px-4 py-2 border-b">PDT</th>
                                <th class="px-4 py-2 border-b">PLAZO PDT</th>
                                <th class="px-4 py-2 border-b">COMPRAS</th>
                                <th class="px-4 py-2 border-b">VENTAS</th>
                                <th class="px-4 py-2 border-b">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($empresas as $empresa)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">{{ substr($empresa->ruc, -1) }}</td>
                                    <td class="px-4 py-2 border-b">{{ $empresa->razon_social }}</td>
                                    <td class="px-4 py-2 border-b">{{ $empresa->estado }}</td>
                                    <td class="px-4 py-2 border-b">
                                        @if ($empresa->registrosSire->isNotEmpty())
                                            @php
                                                $registroSire = $empresa->registrosSire->first()->opcion_si_no;
                                                $autorsire = explode(
                                                    ' ',
                                                    $empresa->registrosSire->first()->usuario->name,
                                                )[0];
                                            @endphp
                                            @if ($registroSire === 'si')
                                                Sí | {{ $autorsire }}
                                            @elseif ($registroSire === 'no')
                                                No | {{ $autorsire }}
                                            @else
                                                Sin registro
                                            @endif
                                        @else
                                            Sin registro
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @if ($empresa->registrosSire->isNotEmpty())
                                            @if ($empresa->registrosSire->first()->estado == 'dentro')
                                                <span
                                                    class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                                    Dentro
                                                </span>
                                            @elseif($empresa->registrosSire->first()->estado == 'fuera')
                                                <span
                                                    class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                                    Fuera
                                                </span>
                                            @endif
                                        @else
                                            Sin registro
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @if ($empresa->registrosPdt->isNotEmpty())
                                            @php
                                                $registroPdt = $empresa->registrosPdt->first()->opcion_si_no;
                                                $autorpdt = explode(
                                                    ' ',
                                                    $empresa->registrosPdt->first()->usuario->name,
                                                )[0];
                                            @endphp
                                            @if ($registroPdt === 'si')
                                                Sí | {{ $autorpdt }}
                                            @elseif ($registroPdt === 'no')
                                                No | {{ $autorpdt }}
                                            @else
                                                Sin registro
                                            @endif
                                        @else
                                            Sin registro
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @if ($empresa->registrosPdt->isNotEmpty())
                                            @if ($empresa->registrosPdt->first()->estado == 'dentro')
                                                <span
                                                    class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                                    Dentro
                                                </span>
                                            @elseif($empresa->registrosPdt->first()->estado == 'fuera')
                                                <span
                                                    class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                                    Fuera
                                                </span>
                                            @else
                                            @endif
                                        @else
                                            Sin registro
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @if ($empresa->registrosCompras->isNotEmpty())
                                            @php
                                                $registroCompras = $empresa->registrosCompras->first()->registro;
                                                $autorcompras = explode(
                                                    ' ',
                                                    $empresa->registrosCompras->first()->usuario->name,
                                                )[0];
                                            @endphp
                                            @if ($registroCompras === 'si')
                                                Sí | {{ $autorcompras }}
                                            @elseif ($registroCompras === 'no')
                                                No | {{ $autorcompras }}
                                            @elseif ($registroCompras === 'na')
                                                N/A | {{ $autorcompras }}
                                            @else
                                                Sin registro
                                            @endif
                                        @else
                                            Sin registro
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @if ($empresa->registrosVentas->isNotEmpty())
                                            @php
                                                $registroVentas = $empresa->registrosVentas->first()->registro;
                                                $autorventas = explode(
                                                    ' ',
                                                    $empresa->registrosVentas->first()->usuario->name,
                                                )[0];
                                            @endphp
                                            @if ($registroVentas === 'si')
                                                Sí | {{ $autorventas }}
                                            @elseif ($registroVentas === 'no')
                                                No | {{ $autorventas }}
                                            @elseif ($registroVentas === 'na')
                                                N/A | {{ $autorventas }}
                                            @else
                                                Sin registro
                                            @endif
                                        @else
                                            Sin registro
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        <div class="flex items-center space-x-2">
                                            <!-- Botón "Ver Datos" -->
                                            <a onclick="mostrarDatos({{ $empresa->id_empresas_clientes }})"
                                                class="flex items-center py-1 rounded-md tooltip cursor-pointer"
                                                style="color: #10B981; transition: color 0.3s;"
                                                onmouseover="this.style.color='#059669';"
                                                onmouseout="this.style.color='#10B981';">
                                                <span class="tooltip-text">Ver Datos</span>
                                                <x-tax-icon />
                                            </a>
                                            @php
                                                // Verificar si el usuario tiene permisos para editar
                                                $puedeEditar = false;

                                                // Si el usuario es administrador (tipo_usuario == 1), puede editar cualquier registro
                                                if (Auth::user()->tipo_usuario == 1) {
                                                    $puedeEditar = true;
                                                }
                                                // Si el usuario es tipo 2 o 3, solo puede editar sus propios registros si tiene el permiso activado
                                                else {
                                                    $puedeEditar =
                                                        Auth::user()->puede_editar &&
                                                        (($empresa->registrosSire->isNotEmpty() &&
                                                            $empresa->registrosSire->first()->id_usuario ==
                                                                Auth::id()) ||
                                                            ($empresa->registrosPdt->isNotEmpty() &&
                                                                $empresa->registrosPdt->first()->id_usuario ==
                                                                    Auth::id()) ||
                                                            ($empresa->registrosCompras->isNotEmpty() &&
                                                                $empresa->registrosCompras->first()->id_usuario ==
                                                                    Auth::id()) ||
                                                            ($empresa->registrosVentas->isNotEmpty() &&
                                                                $empresa->registrosVentas->first()->id_usuario ==
                                                                    Auth::id()));
                                                }
                                            @endphp
                                            @if ($puedeEditar)
                                                <div class="relative">
                                                    <x-dropdown align="right" width="48">
                                                        <x-slot name="trigger">
                                                            <button class="flex items-center py-1 rounded-md tooltip"
                                                                style="color: #F59E0B; transition: color 0.3s;"
                                                                onmouseover="this.style.color='#D97706';"
                                                                onmouseout="this.style.color='#F59E0B';">
                                                                <span class="tooltip-text">Editar</span>
                                                                <x-edit-icon />
                                                            </button>
                                                        </x-slot>

                                                        <x-slot name="content">
                                                            <div
                                                                class="absolute right-0 z-50 w-48 bg-white shadow-lg rounded-md border p-2">
                                                                @php
                                                                    $tieneRegistros =
                                                                        $empresa->registrosSire->isNotEmpty() ||
                                                                        $empresa->registrosPdt->isNotEmpty() ||
                                                                        $empresa->registrosCompras->isNotEmpty() ||
                                                                        $empresa->registrosVentas->isNotEmpty();
                                                                @endphp

                                                                @if ($tieneRegistros)
                                                                    <!-- Opción para editar SIRE -->
                                                                    @if (
                                                                        $empresa->registrosSire->isNotEmpty() &&
                                                                            (Auth::user()->tipo_usuario == 1 || $empresa->registrosSire->first()->id_usuario == Auth::id()))
                                                                        <x-dropdown-link
                                                                            href="{{ route('sire_empresas_clientes.edit', $empresa->registrosSire->first()->id_sire) }}">
                                                                            <i class="bi bi-stripe"></i>
                                                                            {{ __('Editar SIRE') }}
                                                                        </x-dropdown-link>
                                                                    @endif

                                                                    <!-- Opción para editar PDT -->
                                                                    @if (
                                                                        $empresa->registrosPdt->isNotEmpty() &&
                                                                            (Auth::user()->tipo_usuario == 1 || $empresa->registrosPdt->first()->id_usuario == Auth::id()))
                                                                        <x-dropdown-link
                                                                            href="{{ route('pdt_empresas_clientes.edit', $empresa->registrosPdt->first()->id_pdt) }}">
                                                                            <i class="bi bi-file-earmark-text-fill"></i>
                                                                            {{ __('Editar PDT') }}
                                                                        </x-dropdown-link>
                                                                    @endif

                                                                    <!-- Opción para editar Compras -->
                                                                    @if (
                                                                        $empresa->registrosCompras->isNotEmpty() &&
                                                                            (Auth::user()->tipo_usuario == 1 || $empresa->registrosCompras->first()->id_usuario == Auth::id()))
                                                                        <x-dropdown-link
                                                                            href="{{ route('compras_empresas_clientes.edit', $empresa->registrosCompras->first()->id_compras) }}">
                                                                            <i class="bi bi-currency-dollar"></i>
                                                                            {{ __('Editar Compras') }}
                                                                        </x-dropdown-link>
                                                                    @endif

                                                                    <!-- Opción para editar Ventas -->
                                                                    @if (
                                                                        $empresa->registrosVentas->isNotEmpty() &&
                                                                            (Auth::user()->tipo_usuario == 1 || $empresa->registrosVentas->first()->id_usuario == Auth::id()))
                                                                        <x-dropdown-link
                                                                            href="{{ route('ventas_empresas_clientes.edit', $empresa->registrosVentas->first()->id_ventas) }}">
                                                                            <i class="bi bi-cart-fill"></i>
                                                                            {{ __('Editar Ventas') }}
                                                                        </x-dropdown-link>
                                                                    @endif
                                                                @else
                                                                    <p class="text-gray-500 text-sm p-2">No hay
                                                                        registros para editar.</p>
                                                                @endif
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
            @else
                <p class="text-gray-600">Selecciona un mes para ver los registros.</p>
            @endif
            @php
                $mesesEs = [
                    'January' => 'Enero',
                    'February' => 'Febrero',
                    'March' => 'Marzo',
                    'April' => 'Abril',
                    'May' => 'Mayo',
                    'June' => 'Junio',
                    'July' => 'Julio',
                    'August' => 'Agosto',
                    'September' => 'Septiembre',
                    'October' => 'Octubre',
                    'November' => 'Noviembre',
                    'December' => 'Diciembre',
                ];
                $nombreMesEsp = $mesesEs[$mes] ?? $mes;
            @endphp

            <!-- Modal de estadísticas -->
            <x-modal-show id="modalGrafico" title="Estadísticas de Registros Mensuales – {{ $nombreMesEsp }}">
                <canvas id="graficoResumen" width="400" height="300"></canvas>
            </x-modal-show>
        </div>
    </div>
</x-app-layout>

<!-- Modal para gestionar permisos -->
<x-modal-show id="modalPermisos" title="Gestionar Permisos de Edición para CONTABILIDAD">
    <div class="space-y-4">
        @foreach ($users as $usuario)
            <div class="flex items-center justify-between">
                <span>{{ $usuario->name }}</span>
                <form action="{{ route('user.togglePermiso', $usuario->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 rounded-md {{ $usuario->puede_editar ? 'bg-green-500 hover:bg-green-600 transition duration-300' : 'bg-red-500 hover:bg-red-600 transition duration-300' }} text-white">
                        {{ $usuario->puede_editar ? 'Desactivar' : 'Activar' }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</x-modal-show>

<script>
    function mostrarDatos(idEmpresa) {
        // Obtener el mes seleccionado
        const mes = document.getElementById('mesActual').value;

        // Redirigir a la ruta con el ID de la empresa y el mes
        window.location.href = `/empresas_clientes/detalles/${idEmpresa}/${mes}`;
    }
</script>
<input type="hidden" name="mesActual" id="mesActual" value="{{ $mes }}">
<script>
    $(document).ready(function() {
        // Obtener el nombre del mes desde el campo oculto
        const mesActual = document.getElementById('mesActual').value;
        const meses = {
            'January': 'ENERO',
            'February': 'FEBRERO',
            'March': 'MARZO',
            'April': 'ABRIL',
            'May': 'MAYO',
            'June': 'JUNIO',
            'July': 'JULIO',
            'August': 'AGOSTO',
            'September': 'SEPTIEMBRE',
            'October': 'OCTUBRE',
            'November': 'NOVIEMBRE',
            'December': 'DICIEMBRE'
        };
        const nombreMes = meses[mesActual] || 'MES_DESCONOCIDO';

        $('#table-filtrar-mes').DataTable({
            responsive: true, // Hacer la tabla responsive
            dom: 'Bfrtip', // Activa el diseño para los botones
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: `REGISTROS_MENSUALES_COMPRAS_VENTAS_SIRE_PDT_DE_LAS_EMPRESAS_${nombreMes}`, // Nombre personalizado
                exportOptions: {
                    columns: ':not(:last)' // Excluir la última columna
                }
            }],
            language: {
                url: "/js/datatables/lang/es-ES.json" // Textos en español
            },
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('graficoResumen').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['SIRE', 'PDT', 'COMPRAS', 'VENTAS'],
                datasets: [{
                        label: 'Sí',
                        backgroundColor: '#10B981',
                        data: [{{ $conteoResumen['sire']['si'] }},
                            {{ $conteoResumen['pdt']['si'] }},
                            {{ $conteoResumen['compras']['si'] }},
                            {{ $conteoResumen['ventas']['si'] }}
                        ]
                    },
                    {
                        label: 'No',
                        backgroundColor: '#EF4444',
                        data: [{{ $conteoResumen['sire']['no'] }},
                            {{ $conteoResumen['pdt']['no'] }},
                            {{ $conteoResumen['compras']['no'] }},
                            {{ $conteoResumen['ventas']['no'] }}
                        ]
                    },
                    {
                        label: 'N/A',
                        backgroundColor: '#FBBF24',
                        data: [{{ $conteoResumen['sire']['na'] }},
                            {{ $conteoResumen['pdt']['na'] }},
                            {{ $conteoResumen['compras']['na'] }},
                            {{ $conteoResumen['ventas']['na'] }}
                        ]
                    },
                    {
                        label: 'Sin registro',
                        backgroundColor: '#9CA3AF',
                        data: [{{ $conteoResumen['sire']['sin_registro_total'] }},
                            {{ $conteoResumen['pdt']['sin_registro_total'] }},
                            {{ $conteoResumen['compras']['sin_registro_total'] }},
                            {{ $conteoResumen['ventas']['sin_registro_total'] }}
                        ]
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribución por Estado'
                    }
                }
            }
        });
    });

    function mostrarDatos(idEmpresa) {
        const mes = document.getElementById('mesActual').value;
        window.location.href = `/empresas_clientes/detalles/${idEmpresa}/${mes}`;
    }
</script>
