<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Calendario de Vencimientos') }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <x-success-error-bagdes />

        <a href="{{ route('calendario.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Nuevo Calendario
        </a>

        <div class="mt-6 bg-white shadow-lg rounded-lg p-4">
            <table class="min-w-full table-auto" id="myTable">
                <thead>
                    <tr class="bg-gray-100">
                        {{-- <th class="px-4 py-2">ID</th> --}}
                        <th class="px-4 py-2">Tipo</th>
                        <th class="px-4 py-2">Período</th>
                        <th class="px-4 py-2">Fechas</th>
                        {{-- <th class="px-4 py-2">Estado</th> --}}
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($calendarios as $calendario)
                        <tr>
                            {{-- <td class="border px-4 py-2">{{ $calendario->id_calendario }}</td> --}}
                            <td class="border px-4 py-2">{{ $calendario->tipo }}</td>
                            <td class="border px-4 py-2">
                                @php
                                    // Creamos la fecha base a partir de lo que viene en BD (ej: "2025-01")
                                    $baseDate = \Carbon\Carbon::createFromFormat('Y-m', $calendario->mes);
                                @endphp
                                {{ strtoupper($baseDate->translatedFormat('F-Y')) }}
                            </td>

                            <td class="border px-4 py-2">
                                @php
                                    $fechas = json_decode($calendario->fechas, true);
                                    $nextMonth = $baseDate->copy()->addMonth();
                                @endphp
                                {{ strtoupper($nextMonth->translatedFormat('F-Y')) }} → {{ implode(', ', $fechas) }}
                            </td>

                            {{-- <td class="border px-4 py-2">
                                {{ $calendario->estado ? 'Activo' : 'Inactivo' }}
                            </td> --}}
                            <td class="border px-4 py-2">
                                <a href="{{ route('calendario.edit', $calendario->id_calendario) }}"
                                    class="text-yellow-500">Editar</a>
                                <form action="{{ route('calendario.destroy', $calendario->id_calendario) }}"
                                    method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 ml-2"
                                        onclick="return confirm('¿Eliminar este calendario?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
