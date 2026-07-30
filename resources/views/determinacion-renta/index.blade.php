<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Listado de Determinaciones de Renta') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4">
            <x-success-error-bagdes />
            <!-- Formulario para filtrar por año -->
            @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                <div class="bg-gray-100 p-4 rounded-lg shadow-md mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">
                        {{ __('Filtrar empresas sin pre-liquidación y determinación por año') }}
                    </h3>
                    <form action="{{ route('empresas_clientes.filtrar_no_preliquidacion') }}" method="GET"
                        class="flex flex-wrap gap-4 items-center">

                        <div class="flex flex-col">
                            <select name="anio" id="anio" onchange="this.form.submit()"
                                class="block w-full border-gray-300 rounded-md shadow-sm">
                                @for ($i = date('Y'); $i >= date('Y') - 10; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit"
                                class="bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white shadow-md">
                                <i class="bi bi-search"></i> {{ __('Buscar') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-[1200px] table-auto display nowrap responsive nowrap" id="myTable-predeter">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">UD</th>
                            <th class="px-4 py-2 border-b">EMPRESA Y AÑO</th>
                            <th class="px-4 py-2 border-b">VENTAS NETAS</th>
                            <th class="px-4 py-2 border-b">UTILIDAD ANTES DE IMPUESTOS</th>
                            <th class="px-4 py-2 border-b">UTILIDAD DEL EJERCICIO</th>
                            <th class="px-4 py-2 border-b">PAGOS A CUENTA</th>
                            <th class="px-4 py-2 border-b">TOTAL POR PAGAR/ A FAVOR(-)</th>
                            <th class="px-4 py-2 border-b">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($determinaciones as $determinacion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">
                                    {{ substr($determinacion->preLiquidacion->empresaCliente->ruc, -1) }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ $determinacion->preLiquidacion->empresaCliente->razon_social }} : <span
                                        class="text-blue-500 font-bold">{{ $determinacion->preLiquidacion->anio }}</span>
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacion->preLiquidacion->ventas_netas, 2) }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacion->preLiquidacion->total, 2) }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($determinacion->utilidad_tributaria, 2) }}</td>
                                <td class="px-4 py-2 border-b">{{ number_format($determinacion->pagos_cuenta, 2) }}
                                </td>
                                @if ($determinacion->utilidad_antes > 0)
                                    <td class="px-4 py-2 border-b">
                                        {{ number_format(round($determinacion->total_por_pagar, 2), 0, '.', ',') . '.00' }}
                                    </td>
                                @else
                                    <td class="px-4 py-2 border-b">
                                        {{ number_format(round($determinacion->total_a_favor, 2), 0, '.', ',') . '.00' }}
                                    </td>
                                @endif
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
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
                                                        <x-dropdown-link
                                                            href="{{ route('pre-liquidacion.edit', ['id' => $determinacion->preLiquidacion->id_pre_liquidacion]) }}">
                                                            <i class="bi bi-cash-coin"></i>
                                                            {{ __('Editar Pre Liquidación') }}
                                                        </x-dropdown-link>
                                                        <x-dropdown-link
                                                            href="{{ route('determinacion-renta.edit', $determinacion->id_determinacion) }}">
                                                            <i class="bi bi-file-earmark-spreadsheet"></i>
                                                            {{ __('Editar Determinación') }}
                                                        </x-dropdown-link>
                                                    </div>
                                                </x-slot>
                                            </x-dropdown>
                                        </div>
                                        <!-- Enlace a la vista de detalles -->
                                        <a href="{{ route('determinacion-renta.show', $determinacion->id_determinacion) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #10B981; transition: color 0.3s;"
                                            onmouseover="this.style.color='#059669';"
                                            onmouseout="this.style.color='#10B981';">
                                            <span class="tooltip-text">Ver P-L y DTER.</span>
                                            <x-tax-icon />
                                        </a>
                                        @if (Auth::user()->tipo_usuario == 1)
                                            <form
                                                action="{{ route('determinacion-renta.destroy', $determinacion->preLiquidacion->id_pre_liquidacion) }}"
                                                method="POST" style="display:inline;"
                                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta determinación de renta?')">
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
    $(document).ready(function() {
        $('#myTable-predeter').DataTable({
            responsive: false, // Hacer la tabla responsive
            dom: 'Bfrtip', // Activa el diseño para los botones
            buttons: [{
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                title: function() {
                    const hoy = new Date();
                    const dia = hoy.getDate().toString().padStart(2, '0');
                    const mes = (hoy.getMonth() + 1).toString().padStart(2, '0');
                    const anio = hoy.getFullYear();
                    return `REGISTROS_DE_PRELIQUIDACIONES_Y_DETERMINACIONES_ANUALES_ASECON_DESCARGADO_${dia}_${mes}_${anio}`;
                },
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
