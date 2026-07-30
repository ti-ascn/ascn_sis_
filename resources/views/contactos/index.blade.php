<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Contactos Web') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">
                        Contactos recibidos desde la web
                    </h1>

                    <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                        <table class="min-w-[1200px] table-auto display nowrap responsive nowrap" id="myTable-contactos">
                            <thead>
                                <tr class="bg-gray-100 text-left">
                                    <th class="border px-4 py-2 border-b">Nombre</th>
                                    <th class="border px-4 py-2 border-b">Teléfono</th>
                                    <th class="border px-4 py-2 border-b">Tipo</th>
                                    <th class="border px-4 py-2 border-b">Correo</th>
                                    <th class="border px-4 py-2 border-b">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($contactos as $contacto)
                                    <tr class="hover:bg-gray-50">
                                        <td class="border px-4 py-2 border-b">{{ $contacto->nombre }}</td>
                                        <td class="border px-4 py-2 border-b">
                                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $contacto->telefono) }}?text={{ urlencode(
                                                'Hola ' .
                                                    ucwords($contacto->nombre) .
                                                    ', le escribo desde Asecon para brindarle información sobre nuestros servicios de ' .
                                                    $contacto->tipo .
                                                    '. Quedamos atentos a su respuesta. Gracias.',
                                            ) }}"
                                                target="_blank" class="cursor-pointer transition hover:text-green-600"
                                                title="Abrir chat en WhatsApp">
                                                {{ $contacto->telefono }}
                                            </a>
                                        </td>
                                        <td class="border px-4 py-2 border-b">{{ $contacto->tipo }}</td>
                                        <td class="border px-4 py-2 border-b">
                                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ trim($contacto->correo) }}"
                                                target="_blank" rel="noopener"
                                                class="cursor-pointer hover:text-blue-700"
                                                title="Enviar correo con Gmail">
                                                {{ $contacto->correo }}
                                            </a>
                                        </td>
                                        <td class="border px-4 py-2 border-b">
                                            {{ $contacto->created_at ? \Carbon\Carbon::parse($contacto->created_at)->format('d/m/Y') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    $(document).ready(function() {
        $('#myTable-contactos').DataTable({
            responsive: false,
            paging: true,
            ordering: true,
            searching: true,
            info: true,
            language: {
                url: "/js/datatables/lang/es-ES.json"
            },
        });
    });
</script>
