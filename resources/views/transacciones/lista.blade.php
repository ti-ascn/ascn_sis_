<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transacciones de la Cuenta: ') . $cuenta->numero_cuenta  .(' '). $cuenta->tipo.' de '.$cuenta->empresa->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <!-- Crear Transacción -->
            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('transacciones.create', ['cuenta' => $cuenta->id_cuenta]) }}" class="block bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                    {{ __('Registrar Transacción') }}
                </a>
            </div>

            <br>

            <!-- Tabla de Transacciones -->
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive" id="cuentaTransaccionesTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Descripción</th>
                            <th class="px-4 py-2 border-b">Monto</th>
                            <th class="px-4 py-2 border-b">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transacciones as $transaccion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $transaccion->id_transaccion }}</td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->descripcion }}</td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->monto }}</td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->fecha }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>