<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Transacciones de la Cuenta: ') . $cuenta->nombre . ' ' . $cuenta->tipo .' de '.$cuenta->empresa->nombre }}
            </h2>
            <div class="mt-2 md:mt-0 text-gray-600">
                <span class="font-semibold">{{ __('Saldo Actual: ') }}</span>
                <span class="text-lg font-bold {{ $cuenta->saldo_actual >= 0 ? 'text-green-500' : 'text-red-500' }}">
                    {{ $cuenta->tipo == 'Soles' ? 'S/' : '$' }}
                    {{ number_format($cuenta->saldo_actual, 2) }}
                </span>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <!-- Volver a cuentas y registrar transacción -->
            {{-- uno a un lado y otro a otro lado "flex flex-wrap gap-4 items-center justify-between mb-6" --}}
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('empresas.showCuentas', $cuenta->id_empresa) }}"
                    class="block bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                    {{ __('Volver a las cuentas') }}
                </a>
                <a href="{{ route('transacciones.create', ['cuenta' => $cuenta->id_cuenta]) }}"
                    class="block bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                    {{ __('Registrar Transacción') }}
                </a>
            </div>

            <!-- Formulario para filtrar -->
            <div class="bg-gray-100 p-4 rounded-lg shadow-md mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    {{ __('Filtrar transacciones por mes y año') }}
                </h3>
                <form action="{{ route('transacciones.filtrar', ['cuenta' => $cuenta->id_cuenta]) }}" method="GET"
                    class="flex flex-wrap gap-4 items-center">
                    <!-- Selección de Mes -->
                    <div class="flex flex-col">
                        {{-- <label for="mes" class="text-sm font-medium text-gray-600 mb-1">{{ __('Mes') }}</label> --}}
                        <select name="mes" id="mes" class="border-gray-300 rounded-md shadow-sm w-full">
                            @foreach (range(1, 12) as $mes)
                                <option value="{{ $mes }}" {{ request('mes') == $mes ? 'selected' : '' }}>
                                    {{ ucfirst(\Carbon\Carbon::create()->locale('es')->month($mes)->translatedFormat('F')) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Selección de Año -->
                    <div class="flex flex-col">
                        {{-- <label for="anio"
                            class="text-sm font-medium text-gray-600 mb-1">{{ __('Año') }}</label> --}}
                        <select name="anio" id="anio" class="border-gray-300 rounded-md shadow-sm w-full">
                            @foreach (range(date('Y') - 5, date('Y')) as $anio)
                                <option value="{{ $anio }}"
                                    {{ (request('anio') ? request('anio') : date('Y')) == $anio ? 'selected' : '' }}>
                                    {{ $anio }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                    <!-- Botón de Buscar -->
                    <div class="flex items-end">
                        <button type="submit"
                            class="bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white shadow-md">
                            <i class="bi bi-search"></i> {{ __('Buscar') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Título con Mes y Año -->
            @if (isset($mesNombre) && isset($anio))
                <h4 class="font-bold text-lg text-gray-700 mb-4">
                    {{ __('Transacciones para el mes: ') . ucfirst($mesNombre) . ' ' . $anio }}
                </h4>
            @endif

            <br>

            <!-- Transacciones Table -->
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap display responsive nowrap"
                    id="myTable-listarPorCuenta">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Fecha</th>
                            {{-- <th class="px-4 py-2 border-b">Tipo</th> --}}
                            <th class="px-4 py-2 border-b">Monto</th>
                            <th class="px-4 py-2 border-b">Descripción</th>
                            <th class="px-4 py-2 border-b">Hecho por</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transacciones as $transaccion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $transaccion->id_transaccion }}</td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->fecha }}</td>
                                {{-- <td class="px-4 py-2 border-b">{{ ucfirst($transaccion->tipo) }}</td> --}}
                                <td class="px-4 py-2 border-b">
                                    @if ($transaccion->tipo == 'ingreso')
                                        +{{ number_format($transaccion->monto, 2) }}
                                    @elseif ($transaccion->tipo == 'egreso')
                                        -{{ number_format($transaccion->monto, 2) }}
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->descripcion ?: 'Vacío' }}</td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->user->name }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($transaccion->id_transaccion === $transacciones->last()->id_transaccion)
                                        <form
                                            action="{{ route('transacciones.destroy', $transaccion->id_transaccion) }}"
                                            method="POST"
                                            onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta transacción?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:underline">
                                                Eliminar
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400">No eliminable</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


            {{-- <!-- Paginación -->
            <div class="mt-4">
                {{ $transacciones->links() }}
            </div> --}}
        </div>
    </div>
</x-app-layout>
<script>
    $(document).ready(function() {
        $('#myTable-listarPorCuenta').DataTable({
            paging: true, // Habilitar paginación
            searching: true, // Habilitar barra de búsqueda
            ordering: true, // Habilitar ordenamiento
            responsive: true, // Hacer la tabla responsive
            language: {
                url: "/js/datatables/lang/es-ES.json" // Textos en español
            },
            order: [
                [0, 'desc']
            ]
        });
    });
</script>
