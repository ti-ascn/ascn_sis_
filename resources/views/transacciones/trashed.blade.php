{{-- @foreach ($transacciones as $transaccion)
    <tr>
        <td>{{ $transaccion->descripcion }}</td>
        <td>{{ $transaccion->monto }}</td>
        <td>
            <a href="{{ route('transacciones.restore', $transaccion->id) }}">Restaurar</a>
            <form action="{{ route('transacciones.forceDelete', $transaccion->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit">Eliminar Permanentemente</button>
            </form>
        </td>
    </tr>
@endforeach --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('RECUPERAR TRANSACCIONES BORRADAS O ELIMINARLAS TOTALMENTE') }}
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <br>

            <!-- Transacciones Table -->
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap display responsive nowrap" id="myTable">
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
                                    <div class="flex space-x-2">
                                        <!-- Restaurar transacción -->
                                        <form
                                            action="{{ route('transacciones.restore', $transaccion->id_transaccion) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="bg-green-500 hover:bg-green-600 py-1 px-3 rounded-md">
                                                Restaurar
                                            </button>
                                        </form>

                                        <!-- Eliminar permanentemente -->
                                        <form
                                            action="{{ route('transacciones.forceDelete', $transaccion->id_transaccion) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-500 hover:bg-red-600 py-1 px-3 rounded-md"
                                                onclick="return confirm('¿Estás seguro de que deseas eliminar esta transacción de forma permanente?')">
                                                Eliminar Permanentemente
                                            </button>
                                        </form>
                                    </div>
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
