<x-app-layout>
    <x-slot name="header">
        @php
            $estado;
            if ($registro->estado == 'recepcion') {
                $estado = 'Recepción';
            } elseif ($registro->estado == 'solicitud') {
                $estado = 'Solicitud';
            } elseif ($registro->estado == 'entrega') {
                $estado = 'Entrega';
            }
        @endphp
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar ') }}{{ $estado }} - <span class="text-blue-500">{{ $empresa->razon_social }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex flex-wrap gap-4 items-center">
                <a href="{{ route('recepcion.documentos.registros_mes_actual', $empresa->id_empresas_clientes) }}"
                    class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                    {{ __('Volver') }}
                </a>
            </div>
            <div class="bg-white shadow-lg rounded-lg p-6">
                <form action="{{ route('recepcion.documentos.update', $registro->id_recepcion) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Campo para el tipo de contacto -->
                    @if ($registro->estado == 'solicitud')
                        <div class="mb-4">
                            <label for="tipo_contacto" class="block text-sm font-medium text-gray-700">Tipo de
                                Contacto</label>
                            <select name="tipo_contacto" id="tipo_contacto"
                                class="block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="WHATSAPP"
                                    {{ $registro->tipo_contacto === 'WHATSAPP' ? 'selected' : '' }}>Whatsapp</option>
                                <option value="LLAMADA" {{ $registro->tipo_contacto === 'LLAMADA' ? 'selected' : '' }}>
                                    Llamada</option>
                            </select>
                        </div>
                    @endif

                    <!-- Campo para el tipo de documento -->
                    @if ($registro->estado == 'recepcion')
                        <div class="mb-4">
                            <label for="tipo_documento" class="block text-sm font-medium text-gray-700">Tipo de
                                Documento</label>
                            <select name="tipo_documento" id="tipo_documento"
                                class="block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="DIGITAL" {{ $registro->tipo_documento === 'DIGITAL' ? 'selected' : '' }}>
                                    Digital</option>
                                <option value="FISICO" {{ $registro->tipo_documento === 'FISICO' ? 'selected' : '' }}>
                                    Físico</option>
                            </select>
                        </div>
                    @endif

                    <!-- Campo para el periodo -->
                    <div class="mb-4">
                        <label for="fecha" class="block text-sm font-medium text-gray-700">Periodo</label>
                        <select name="fecha" id="fecha" class="block w-full border-gray-300 rounded-md shadow-sm">
                            @php
                                // Generar los últimos 12 meses
                                $meses = [];
                                $base = \Carbon\Carbon::now()->startOfMonth(); // anclar al 1er día
                                for ($i = 0; $i < 12; $i++) {
                                    $meses[] = $base->copy()->subMonths($i)->format('Y-m');
                                }
                                // Obtener el mes anterior al actual
                                $mesAnterior = $base->copy()->subMonths(1)->format('Y-m');
                            @endphp
                            @foreach ($meses as $mes)
                                <option value="{{ $mes }}" {{ $mes == $mesAnterior ? 'selected' : '' }}>
                                    {{ ucfirst(\Carbon\Carbon::parse($mes)->isoFormat('MMMM YYYY')) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Campo para la opción (Si, No, N/A) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Opción</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="opcion" value="Si" class="form-radio text-blue-500"
                                    {{ $registro->opcion === 'Si' ? 'checked' : '' }}>
                                <span class="ml-2">Si</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="opcion" value="No" class="form-radio text-blue-500"
                                    {{ $registro->opcion === 'No' ? 'checked' : '' }}>
                                <span class="ml-2">No</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="opcion" value="N/A" class="form-radio text-blue-500"
                                    {{ $registro->opcion === 'N/A' ? 'checked' : '' }}>
                                <span class="ml-2">N/A</span>
                            </label>
                        </div>
                    </div>

                    <!-- Campo para la observación -->
                    <div class="mb-4">
                        <label for="observacion" class="block text-sm font-medium text-gray-700">Observación</label>
                        <textarea name="observacion" id="observacion" rows="3" class="block w-full border-gray-300 rounded-md shadow-sm">{{ $registro->observacion }}</textarea>
                    </div>

                    <!-- Botón para guardar cambios -->
                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
