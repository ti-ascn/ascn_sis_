<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Planilla') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col mb-6">
                <!-- Botón para abrir el modal de filtrar por mes -->
                <a href="{{ route('empresas_clientes.afp_plame') }}"
                    class="block bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white text-center w-full md:w-auto">
                    Volver
                </a>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg mt-4">
                <table class="min-w-full table-auto" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">UD</th>
                            <th class="px-4 py-2 border-b">Razón Social</th>
                            <th class="px-4 py-2 border-b">RUC</th>
                            <th class="px-4 py-2 border-b">Estado</th>
                            <th class="px-4 py-2 border-b">Planilla</th>
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas_clientes as $empresa)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ substr($empresa->ruc, -1) }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->razon_social }}</td>
                                <td class="px-4 py-2 border-b">{{ $empresa->ruc }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($empresa->estado_actual == 1)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-24">
                                            Activo
                                        </span>
                                    @elseif($empresa->estado_actual == 2)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-yellow-500 rounded-full w-24">
                                            Suspendido
                                        </span>
                                    @else
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-24">
                                            Retirado
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @if ($empresa->planilla)
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-green-500 rounded-full w-16">
                                            Sí
                                        </span>
                                    @else
                                        <span
                                            class="flex justify-center items-center px-4 py-1 text-white bg-red-500 rounded-full w-16">
                                            No
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">
                                        @if ($empresa->planilla)
                                            <form
                                                action="{{ route('empresas_clientes.desactivar_planilla', $empresa->id_empresas_clientes) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-900 hover:underline">
                                                    Desactivar
                                                </button>
                                            </form>
                                        @else
                                            <form
                                                action="{{ route('empresas_clientes.activar_planilla', $empresa->id_empresas_clientes) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="text-green-600 hover:text-green-900 hover:underline">
                                                    Activar
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
