<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registros Mensuales de AFP y PDT PLAME de las Empresas') }}
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
                <form action="{{ route('empresas_clientes.filtrar_por_mes_afp_pdtplame') }}" method="GET"
                    class="flex flex-wrap gap-4 items-center">
                    <!-- Selector de mes -->
                    <div class="flex flex-col w-full md:w-auto">
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
                    <div class="flex flex-col w-full md:w-auto">
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
                    <div class="flex items-end">
                        <button type="submit"
                            class="bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="bi bi-search"></i> {{ __('Buscar') }}
                        </button>
                    </div>
                    @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                        <!-- Botón para abrir el modal -->
                        <button onclick="event.preventDefault(); toggleModal('modalPermisos')"
                            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300">
                            <i class="bi bi-sliders2"></i> Permisos de Edición
                        </button>
                    @endif
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
                                <th class="px-4 py-2 border-b">DATOS AFP</th>
                                <th class="px-4 py-2 border-b">ESTADO</th>
                                <th class="px-4 py-2 border-b">AFP</th>
                                <th class="px-4 py-2 border-b">PLAZO AFP</th>
                                <th class="px-4 py-2 border-b">PLAME</th>
                                <th class="px-4 py-2 border-b">PLAZO PLAME</th>
                                <th class="px-4 py-2 border-b">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($empresas as $empresa)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">{{ substr($empresa->ruc, -1) }}</td>
                                    <td class="px-4 py-2 border-b">{{ $empresa->razon_social }}</td>
                                    <td class="px-4 py-2 border-b"><strong>{{ $empresa->afp_usuario ?: '-' }}</strong>
                                        |
                                        <strong>{{ $empresa->afp_clave ?: '-' }}</strong>
                                    </td>
                                    <td class="px-4 py-2 border-b">{{ $empresa->estado }}</td>
                                    <td class="px-4 py-2 border-b">
                                        @if ($empresa->registrosAfp->isNotEmpty())
                                            @if ($empresa->registrosAfp->isNotEmpty())
                                                @php
                                                    $registroAfp = $empresa->registrosAfp->first()->opcion_si_no;
                                                    $autorafp = explode(
                                                        ' ',
                                                        $empresa->registrosAfp->first()->usuario->name,
                                                    )[0];
                                                @endphp
                                                @if ($registroAfp === 'si')
                                                    Sí | {{ $autorafp }}
                                                @elseif ($registroAfp === 'no')
                                                    No | {{ $autorafp }}
                                                @elseif ($registroAfp === 'na')
                                                    N/A | {{ $autorafp }}
                                                @else
                                                    Sin registro
                                                @endif
                                            @else
                                                Sin registro
                                            @endif
                                        @else
                                            Sin registro
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @if ($empresa->registrosAfp->isNotEmpty())
                                            @if ($empresa->registrosAfp->first()->estado == 'dentro')
                                                <span
                                                    class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                                    Dentro
                                                </span>
                                            @else
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
                                        @if ($empresa->registrosPdtplame->isNotEmpty())
                                            @php
                                                $registroPdtplame = $empresa->registrosPdtplame->first()->opcion_si_no;
                                                $autorpdtplame = explode(
                                                    ' ',
                                                    $empresa->registrosPdtplame->first()->usuario->name,
                                                )[0];
                                            @endphp
                                            @if ($registroPdtplame === 'si')
                                                Sí | {{ $autorpdtplame }}
                                            @elseif ($registroPdtplame === 'no')
                                                No | {{ $autorpdtplame }}
                                            @elseif ($registroPdtplame === 'na')
                                                N/A | {{ $autorpdtplame }}
                                            @else
                                                Sin registro
                                            @endif
                                        @else
                                            Sin registro
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @if ($empresa->registrosPdtplame->isNotEmpty())
                                            <!-- Mostrar estado "dentro" o "fuera" -->
                                            @if ($empresa->registrosPdtplame->first()->estado == 'dentro')
                                                <span
                                                    class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                                    Dentro
                                                </span>
                                            @else
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

                                                // Si el usuario es administrador (tipo_usuario == 1) o tipo_usuario == 2, puede editar cualquier registro
                                                if (
                                                    Auth::user()->tipo_usuario == 1 ||
                                                    Auth::user()->tipo_usuario == 2
                                                ) {
                                                    $puedeEditar = true;
                                                }
                                                // Si el usuario es tipo 3, solo puede editar sus propios registros si tiene el permiso activado
                                                else {
                                                    $puedeEditar =
                                                        Auth::user()->puede_editar &&
                                                        (($empresa->registrosAfp->isNotEmpty() &&
                                                            $empresa->registrosAfp->first()->id_usuario ==
                                                                Auth::id()) ||
                                                            ($empresa->registrosPdtplame->isNotEmpty() &&
                                                                $empresa->registrosPdtplame->first()->id_usuario ==
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
                                                                        $empresa->registrosAfp->isNotEmpty() ||
                                                                        $empresa->registrosPdtplame->isNotEmpty();
                                                                @endphp

                                                                @if ($tieneRegistros)
                                                                    <!-- Opción para editar AFP -->
                                                                    @if (
                                                                        $empresa->registrosAfp->isNotEmpty() &&
                                                                            (in_array(Auth::user()->tipo_usuario, [1, 2]) || $empresa->registrosAfp->first()->id_usuario == Auth::id()))
                                                                        <x-dropdown-link
                                                                            href="{{ route('afp_empresas_clientes.edit', $empresa->registrosAfp->first()->id_afp) }}">
                                                                            <i class="bi bi-piggy-bank"></i>
                                                                            {{ __('Editar AFP') }}
                                                                        </x-dropdown-link>
                                                                    @endif

                                                                    <!-- Opción para editar PDT PLAME -->
                                                                    @if (
                                                                        $empresa->registrosPdtplame->isNotEmpty() &&
                                                                            (in_array(Auth::user()->tipo_usuario, [1, 2]) ||
                                                                                $empresa->registrosPdtplame->first()->id_usuario == Auth::id()))
                                                                        <x-dropdown-link
                                                                            href="{{ route('pdtplame_empresas_clientes.edit', $empresa->registrosPdtplame->first()->id_pdtplame) }}">
                                                                            <i class="bi bi-file-earmark-text"></i>
                                                                            {{ __('Editar PDT PLAME') }}
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
        </div>
    </div>
</x-app-layout>

<!-- Modal para gestionar permisos -->
<x-modal-show id="modalPermisos" title="Gestionar Permisos de Edición para RR.HH">
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
        window.location.href = `/empresas_clientes/detalles_afp_pdtplame/${idEmpresa}/${mes}`;
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
                title: `REGISTROS_MENSUALES_AFP_PDT-PLAME_DE_LAS_EMPRESAS_${nombreMes}`, // Nombre personalizado
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
