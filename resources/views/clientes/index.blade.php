<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Clientes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">

            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                    <a href="{{ route('clientes.create') }}"
                        class="flex items-center justify-center gap-2 bg-green-500 px-4 py-2 rounded-md text-white hover:bg-green-600 transition duration-300 w-full md:w-auto">
                        <i class="bi bi-person-plus-fill"></i> Crear Cliente
                    </a>
                @endif

                <a href="{{ route('empresas_clientes_auditoria.all') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md text-white hover:bg-blue-600 transition duration-300 w-full md:w-auto">
                    <i class="bi bi-building"></i> Empresas
                </a>

                <a href="{{ route('choferes.index') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md text-white hover:bg-blue-600 transition duration-300 w-full md:w-auto">
                    <i class="bi bi-truck-front-fill"></i> Choferes
                </a>

                <a href="{{ route('direcciones.index') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md text-white hover:bg-blue-600 transition duration-300 w-full md:w-auto">
                    <i class="bi bi-geo-alt-fill"></i> Direcciones
                </a>
            </div>

            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-[1200px] table-auto" id="myTable-clientes">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">Razón Social</th>
                            <th class="px-4 py-2 border-b">Rubro</th>
                            <th class="px-4 py-2 border-b">Contacto</th>
                            <th class="px-4 py-2 border-b">Teléfono</th>
                            <th class="px-4 py-2 border-b">Correo</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clientes as $cliente)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $cliente->ruc }}</td>
                                <td class="px-4 py-2 border-b">{{ $cliente->razon_social }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ empty($cliente->rubro) ? '—' : $cliente->rubro }}
                                </td>

                                <td class="px-4 py-2 border-b">
                                    {{ empty($cliente->contacto) ? '—' : $cliente->contacto }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ empty($cliente->telefono) ? '—' : $cliente->telefono }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ empty($cliente->correo) ? '—' : $cliente->correo }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @if ($cliente->estado_actual == 1)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                            Activo
                                        </span>
                                    @elseif ($cliente->estado_actual == 2)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">
                                            Suspendido
                                        </span>
                                    @elseif ($cliente->estado_actual == 0)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                            Retirado
                                        </span>
                                    @else
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-gray-500 rounded-full w-24">
                                            Sin estado
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2 items-center">
                                            <a href="{{ route('direcciones.create_cliente', $cliente->id_cliente) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #dc2626; transition: color 0.3s;"
                                                onmouseover="this.style.color='#b91c1c';"
                                                onmouseout="this.style.color='#dc2626';">
                                                <span class="tooltip-text">+Dirección</span>
                                                <x-location-icon />
                                            </a>
                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                            {{-- Editar --}}
                                            <a href="{{ route('clientes.edit', $cliente->id_cliente) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #F59E0B; transition: color 0.3s;"
                                                onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </a>
                                        @endif

                                        {{-- Eliminar --}}
                                        {{-- <form action="{{ route('clientes.destroy', $cliente->id_cliente) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Deseas eliminar este cliente?')"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center py-1 rounded-md tooltip"
                                                    style="color: #dc2626; transition: color 0.3s;"
                                                    onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';">
                                                    <span class="tooltip-text">Eliminar</span>
                                                    <x-delete-icon />
                                                </button>
                                            </form> --}}

                                        {{-- Dropdown de estados --}}
                                        <div class="relative">
                                            <x-dropdown align="right" width="48">
                                                <x-slot name="trigger">
                                                    <button class="flex items-center py-1 rounded-md tooltip"
                                                        style="color: #6B7280; transition: color 0.3s;"
                                                        onmouseover="this.style.color='#4B5563';"
                                                        onmouseout="this.style.color='#6B7280';">
                                                        <x-dots-vertical-icon />
                                                    </button>
                                                </x-slot>

                                                <x-slot name="content">
                                                    <x-dropdown-link :href="route('guias.create_cliente', $cliente->id_cliente)">
                                                        <i class="bi bi-file-earmark-plus mr-2"></i> Crear Guía
                                                    </x-dropdown-link>

                                                    <x-dropdown-link :href="route('direcciones.porCliente', $cliente->id_cliente)">
                                                        <i class="bi bi-map mr-2"></i> Ver Direcciones
                                                    </x-dropdown-link>

                                                    <x-dropdown-link :href="route('facturas.por_cliente', $cliente->id_cliente)">
                                                        <i class="bi bi-receipt-cutoff mr-2"></i> Ver Facturas
                                                    </x-dropdown-link>

                                                    @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                                        @if ($cliente->estado_actual == 1)
                                                            <x-dropdown-link href="#"
                                                                onclick="event.preventDefault(); document.getElementById('form-retirar-{{ $cliente->id_cliente }}').submit();">
                                                                <i class="bi bi-trash-fill mr-2"></i> Retirar
                                                            </x-dropdown-link>
                                                            <x-dropdown-link href="#"
                                                                onclick="event.preventDefault(); document.getElementById('form-suspender-{{ $cliente->id_cliente }}').submit();">
                                                                <i class="bi bi-pause-circle-fill mr-2"></i> Suspender
                                                            </x-dropdown-link>
                                                        @elseif($cliente->estado_actual == 2)
                                                            <x-dropdown-link href="#"
                                                                onclick="event.preventDefault(); document.getElementById('form-activar-{{ $cliente->id_cliente }}').submit();">
                                                                <i class="bi bi-play-circle-fill mr-2"></i> Activar
                                                            </x-dropdown-link>
                                                            <x-dropdown-link href="#"
                                                                onclick="event.preventDefault(); document.getElementById('form-retirar-{{ $cliente->id_cliente }}').submit();">
                                                                <i class="bi bi-trash-fill mr-2"></i> Retirar
                                                            </x-dropdown-link>
                                                        @elseif($cliente->estado_actual == 0)
                                                            <x-dropdown-link href="#"
                                                                onclick="event.preventDefault(); document.getElementById('form-activar-{{ $cliente->id_cliente }}').submit();">
                                                                <i class="bi bi-play-circle-fill mr-2"></i> Activar
                                                            </x-dropdown-link>
                                                            <x-dropdown-link href="#"
                                                                onclick="event.preventDefault(); document.getElementById('form-suspender-{{ $cliente->id_cliente }}').submit();">
                                                                <i class="bi bi-pause-circle-fill mr-2"></i> Suspender
                                                            </x-dropdown-link>
                                                        @endif
                                                    @endif
                                                </x-slot>
                                            </x-dropdown>

                                            {{-- Formularios invisibles --}}
                                            <form id="form-retirar-{{ $cliente->id_cliente }}" method="POST"
                                                action="{{ route('clientes.retirar', $cliente->id_cliente) }}"
                                                style="display: none;">
                                                @csrf
                                            </form>
                                            <form id="form-suspender-{{ $cliente->id_cliente }}" method="POST"
                                                action="{{ route('clientes.suspender', $cliente->id_cliente) }}"
                                                style="display: none;">
                                                @csrf
                                            </form>
                                            <form id="form-activar-{{ $cliente->id_cliente }}" method="POST"
                                                action="{{ route('clientes.activar', $cliente->id_cliente) }}"
                                                style="display: none;">
                                                @csrf
                                            </form>
                                        </div>
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

<script>
    const nombreMes = {
        '01': 'ENERO',
        '02': 'FEBRERO',
        '03': 'MARZO',
        '04': 'ABRIL',
        '05': 'MAYO',
        '06': 'JUNIO',
        '07': 'JULIO',
        '08': 'AGOSTO',
        '09': 'SETIEMBRE',
        '10': 'OCTUBRE',
        '11': 'NOVIEMBRE',
        '12': 'DICIEMBRE'
    };

    $(document).ready(function() {
        const today = new Date();
        const dia = String(today.getDate()).padStart(2, '0');
        const mes = String(today.getMonth() + 1).padStart(2, '0');
        const anio = today.getFullYear();

        const fechaExportacion = `EXPORTADO_HOY_${dia}_${nombreMes[mes]}_${anio}`;

        $('#myTable-clientes').DataTable({
            paging: true,
            ordering: true,
            pageLength: 10,
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                    title: `CLIENTES_${fechaExportacion}`,
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                    title: `CLIENTES_${fechaExportacion}`,
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ],
            language: {
                url: "/js/datatables/lang/es-ES.json"
            }
        });
    });
</script>
