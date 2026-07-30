<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de Todas las Cuentas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <!-- Crear Cuenta button -->
            {{-- <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">
                <a href="{{ route('cuentas.create') }}"
                    class="block bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white text-center w-full md:w-auto">
                    {{ __('Crear Cuenta') }}
                </a>
            </div> --}}

            <br>

            <!-- Cuentas Table -->
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap display responsive nowrap" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            {{-- <th class="px-4 py-2 border-b">ID</th> --}}
                            <th class="px-4 py-2 border-b">Empresa</th>
                            <th class="px-4 py-2 border-b">Banco</th>
                            {{-- <th class="px-4 py-2 border-b">Número de Cuenta</th>
                            <th class="px-4 py-2 border-b">Número Interbancario</th> --}}
                            {{-- <th class="px-4 py-2 border-b">Tipo</th> --}}
                            <th class="px-4 py-2 border-b">Saldo Actual</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cuentas as $cuenta)
                            <tr class="hover:bg-gray-50">
                                {{-- <td class="px-4 py-2 border-b">{{ $cuenta->id_cuenta }}</td> --}}
                                <td class="px-4 py-2 border-b">{{ $cuenta->empresa->nombre }}</td>
                                <td class="px-4 py-2 border-b">{{ $cuenta->nombre }} {{ $cuenta->tipo }}</td>
                                {{-- <td class="px-4 py-2 border-b">{{ $cuenta->numero_cuenta }}</td>
                                <td class="px-4 py-2 border-b">{{ $cuenta->numero_interbancario }}</td> --}}
                                {{-- <td class="px-4 py-2 border-b">{{ $cuenta->tipo }}</td> --}}
                                <td class="px-4 py-2 border-b">
                                    @if ($cuenta->tipo == 'Soles')
                                        S/. {{ $cuenta->saldo_actual }}
                                    @elseif($cuenta->tipo == 'Dolares')
                                        $ {{ $cuenta->saldo_actual }}
                                    @else
                                        {{ $cuenta->saldo_actual }}
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('transacciones.create', ['cuenta' => $cuenta->id_cuenta]) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #2563EB; transition: color 0.3s;"
                                            onmouseover="this.style.color='#1D4ED8';"
                                            onmouseout="this.style.color='#2563EB';">
                                            <span class="tooltip-text">+Transacción</span>
                                            <x-register-transaction-icon />
                                        </a>
                                        <a href="{{ route('transacciones.listarPorCuenta', $cuenta->id_cuenta) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #22C55E; transition: color 0.3s;"
                                            onmouseover="this.style.color='#15803D';"
                                            onmouseout="this.style.color='#22C55E';">
                                            <span class="tooltip-text">Transacciones</span>
                                            <x-view-transactions-icon />
                                        </a>
                                        @if (Auth::user()->tipo_usuario != 3)
                                            <a href="{{ route('cuentas.edit', $cuenta) }}"
                                                class="flex items-center py-1 rounded-md tooltip"
                                                style="color: #F59E0B; transition: color 0.3s;"
                                                onmouseover="this.style.color='#D97706';"
                                                onmouseout="this.style.color='#F59E0B';">
                                                <span class="tooltip-text">Editar</span>
                                                <x-edit-icon />
                                            </a>
                                            <form action="{{ route('cuentas.destroy', $cuenta) }}" method="POST"
                                                style="display:inline;"
                                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta empresa?')">
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
