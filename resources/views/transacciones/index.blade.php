<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Todas las Transacciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Mensaje de éxito -->
            <x-success-error-bagdes />

            <!-- Tabla de Transacciones -->
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Empresa</th>
                            <th class="px-4 py-2 border-b">Cuenta</th>
                            <th class="px-4 py-2 border-b">Descripción</th>
                            <th class="px-4 py-2 border-b">Monto</th>
                            <th class="px-4 py-2 border-b">Fecha</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transacciones as $transaccion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $transaccion->id_transaccion }}</td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->cuenta->empresa->nombre }}</td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->cuenta->nombre }}
                                    {{ $transaccion->cuenta->tipo }}</td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->descripcion }}</td>
                                <td class="px-4 py-2 border-b">
                                    <span>
                                        {{ $transaccion->cuenta->tipo == 'Soles' ? 'S/. ' : '$ ' }}
                                    </span>
                                    @if ($transaccion->tipo == 'ingreso')
                                        +{{ number_format($transaccion->monto, 2) }}
                                    @elseif ($transaccion->tipo == 'egreso')
                                        -{{ number_format($transaccion->monto, 2) }}
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">{{ $transaccion->fecha }}</td>
                                <td class="flex space-x-2">
                                    <a href="{{ route('transacciones.create', ['cuenta' => $transaccion->id_cuenta]) }}"
                                        class="flex items-center py-1 rounded-md tooltip"
                                        style="color: #2563EB; transition: color 0.3s;"
                                        onmouseover="this.style.color='#1D4ED8';"
                                        onmouseout="this.style.color='#2563EB';">

                                        <span class="tooltip-text">+Transacción</span>
                                        <x-register-transaction-icon />
                                    </a>
                                    <a href="{{ route('transacciones.listarPorCuenta', $transaccion->id_cuenta) }}"
                                        class="flex items-center py-1 rounded-md tooltip"
                                        style="color: #22C55E; transition: color 0.3s;"
                                        onmouseover="this.style.color='#15803D';"
                                        onmouseout="this.style.color='#22C55E';">

                                        <span class="tooltip-text">Transacciones</span>
                                        <x-view-transactions-icon />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
