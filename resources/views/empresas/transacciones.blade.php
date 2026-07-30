<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Todas las Transacciones de todas las cuentas de ') . $empresa->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <!-- Volver a cuentas -->
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('empresas.showCuentas', $empresa->id_empresa) }}"
                    class="block bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                    {{ __('Volver a las cuentas') }}
                </a>
            </div>

            <br>

            <!-- Transacciones Table -->
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap display responsive nowrap" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Fecha</th>
                            <th class="px-4 py-2 border-b">Descripción</th>
                            <th class="px-4 py-2 border-b">Monto</th>
                            <th class="px-4 py-2 border-b">Cuenta</th>
                            <th class="px-4 py-2 border-b">Hecho por</th>
                            {{-- <th class="px-4 py-2 border-b">Acciones</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transacciones as $transaccion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $transaccion->fecha }}</td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->descripcion }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($transaccion->tipo == 'ingreso')
                                        + S/. {{ number_format($transaccion->monto, 2, '.', ',') }}
                                    @elseif($transaccion->tipo == 'egreso')
                                        - S/. {{ number_format($transaccion->monto, 2, '.', ',') }}
                                    @else
                                        S/. {{ number_format($transaccion->monto, 2, '.', ',') }}
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->cuenta->nombre }}</td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->user->name }}</td>  
                                {{-- <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('transacciones.create', ['empresa' => $empresa->cuenta->id_cuenta]) }}"
                                            class="btn btn-info btn-sm bg-blue-500 hover:bg-blue-600 py-1 px-3 rounded-md">
                                            Registrar Transacción
                                        </a>
                                        <a href="{{ route('transacciones.edit', $transaccion) }}"
                                            class="btn btn-warning btn-sm bg-yellow-500 hover:bg-yellow-600 py-1 px-3 rounded-md">
                                            Editar
                                        </a>
                                        <form action="{{ route('transacciones.destroy', $transaccion) }}"
                                            method="POST" style="display:inline;"
                                            onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta transacción?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-danger btn-sm bg-red-500 hover:bg-red-600 py-1 px-3 rounded-md">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
