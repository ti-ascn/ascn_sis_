<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Relaciones de Facturación') }}
        </h2>
    </x-slot>

    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-success-error-bagdes />

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">

                {{-- Botón Nueva Relación --}}
                <a href="{{ route('relaciones_facturacion.create') }}"
                    class="flex items-center justify-center gap-2 bg-blue-500 px-4 py-2 rounded-md 
                    hover:bg-blue-600 transition duration-300 text-white w-full md:w-auto">
                    <i class="bi bi-link-45deg"></i> Nueva Relación
                </a>

            </div>

            <br>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg p-4">

                <table class="min-w-full table-auto display nowrap responsive" id="tabla-relaciones">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Empresa Origen</th>
                            <th class="px-4 py-2 border-b">Empresas Destino</th>
                            <th class="px-4 py-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($agrupado as $origenId => $grupo)
                            <tr class="hover:bg-gray-50">

                                {{-- ORIGEN --}}
                                <td class="px-4 py-2 border-b w-1/4">
                                    <strong>{{ $grupo['origen']->razon_social }}</strong>
                                    <br>
                                    <span class="text-gray-500 text-sm">{{ $grupo['origen']->ruc }}</span>
                                </td>

                                {{-- DESTINOS --}}
                                <td class="px-4 py-2 border-b">
                                    <ul class="list-disc ml-6 text-gray-700">
                                        @foreach ($grupo['destinos'] as $dest)
                                            <li>
                                                {{ $dest->razon_social }} –
                                                <span class="text-gray-500">{{ $dest->ruc }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>

                                <td class="px-4 py-2 border-b">
                                    <div class="flex items-center space-x-3">

                                        {{-- Editar --}}
                                        <a href="{{ route('relaciones_facturacion.edit', $origenId) }}"
                                            class="flex items-center rounded-md tooltip" style="color: #F59E0B;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';">
                                            <x-edit-icon />
                                            <span class="tooltip-text">Editar</span>
                                        </a>

                                        {{-- Eliminar --}}
                                        <form action="{{ route('relaciones_facturacion.destroyGrupo', $origenId) }}"
                                            method="POST" class="inline-block"
                                            onsubmit="return confirm('¿Eliminar TODAS las relaciones de esta empresa origen?')">

                                            @csrf
                                            @method('DELETE')

                                            <button class="flex items-center rounded-md tooltip" style="color:#dc2626;"
                                                onmouseover="this.style.color='#b91c1c';"
                                                onmouseout="this.style.color='#dc2626';">
                                                <x-delete-icon />
                                                <span class="tooltip-text">Eliminar</span>
                                            </button>
                                        </form>

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
        $('#tabla-relaciones').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            language: {
                url: "{{ asset('/js/datatables/lang/es-ES.json') }}"
            }
        });
    });
</script>
