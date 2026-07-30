<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gerente y Socios de <span class="text-blue-500">{{ $empresa->razon_social }} </span> RUC: <span
                class="text-blue-500">{{ $empresa->ruc }} </span>
        </h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <!-- Botón Atrás -->
            <div class="flex gap-3 mb-4">
                <a href="{{ url()->previous() }}"
                    class="inline-block bg-gray-300 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-300 text-gray-700">
                    ← Atrás
                </a>
                @if (Auth::user()->area->nombre == 'LEGAL')
                    <div x-data="{ modalGerente: false, modalSocio: false }">
                        <!-- Botón Nuevo Gerente -->
                        <button type="button" onclick="toggleModal('cuentaModal')"
                            class="inline-block bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                            Historial Gerente
                        </button>
                        <button type="button" onclick="toggleModal('cuentaModa2')"
                            class="inline-block bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 text-white">
                            Historial Socios
                        </button>
                    </div>
                @endif
            </div>
            <div class="bg-white shadow rounded-lg p-6 overflow-x-auto">
                <a href="{{ route('gerente_socios.listar', ['id' => $empresa->id_empresas_clientes]) }}"></a>

                {{-- Sección Gerente Actual --}}
                <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    @if ($gerenteActual)
                        <p class="text-gray-700">
                            El gerente actual es <span class="font-bold">{{ $gerenteActual->nombre }}</span>
                            desde la fecha
                            <span class="font-bold">
                                {{ $gerenteActual->fecha_sunat ? \Carbon\Carbon::parse($gerenteActual->fecha_sunat)->format('d/m/Y') : '—' }}
                            </span>
                            @if ($sociosActivos->count() > 0)
                                — Los socios actuales son:
                                @foreach ($sociosActivos as $index => $socio)
                                    <span class="font-bold">{{ $socio->nombre }}</span>
                                    <span class="text-gray-500">({{ $socio->porcentaje }}%)</span>
                                    @if ($socio->fecha_cambio && \Carbon\Carbon::parse($socio->fecha_cambio)->year > 1)
                                        <span class="text-gray-500 text-xs">desde
                                            {{ \Carbon\Carbon::parse($socio->fecha_cambio)->format('d/m/Y') }}</span>
                                    @endif
                                    @if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            @endif
                        </p>
                    @else
                        <p class="text-gray-500 italic">No hay un gerente activo registrado actualmenente.</p>
                    @endif
                </div>

                <div x-data="{ tab: 'gerentes' }" class="space-y-6">

                    {{-- Pestañas --}}
                    <div class="flex border-b border-gray-200 mb-4">
                        <button class="px-4 py-2 font-medium text-gray-700 focus:outline-none"
                            :class="tab === 'gerentes' ? 'border-b-2 border-blue-500 text-blue-500' : ''"
                            @click="tab = 'gerentes'">
                            Gerentes Inactivos
                        </button>
                        <button class="px-4 py-2 font-medium text-gray-700 focus:outline-none"
                            :class="tab === 'socios' ? 'border-b-2 border-blue-500 text-blue-500' : ''"
                            @click="tab = 'socios'">
                            Socios Inactivos
                        </button>
                    </div>

                    {{-- Contenido Gerentes --}}
                    <div x-show="tab === 'gerentes'" class="transition duration-300 ease-in-out">
                        <table id="myTable-gerentes" class="min-w-full table-auto border border-gray-200 mb-8">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2">Nombre Gerente</th>
                                    <th class="px-4 py-2">Fecha Acta</th>
                                    <th class="px-4 py-2">Fecha SUNAT</th>
                                    <th class="px-4 py-2">Fecha Cambio</th>
                                    <th class="px-4 py-2">Observación</th>
                                    <th class="px-4 py-2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($gerentesInactivos->count())

                                    @php
                                        $ultimoGrupo = null;
                                        $color = false;
                                    @endphp

                                    @foreach ($gerentesInactivos as $gerente)
                                        @php
                                            $grupoActual = $gerente->created_at->format('Y-m-d H:i');
                                            if ($ultimoGrupo !== $grupoActual) {
                                                $color = !$color;
                                            }
                                        @endphp

                                        <tr class="{{ $color ? 'bg-white' : 'bg-gray-100' }}">
                                            <td class="px-4 py-2">{{ $gerente->nombre }}</td>
                                            <td class="px-4 py-2 text-center">
                                                {{ $gerente->fecha_acta && \Carbon\Carbon::parse($gerente->fecha_acta)->year > 1 ? \Carbon\Carbon::parse($gerente->fecha_acta)->format('d/m/Y') : '—' }}
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                {{ $gerente->fecha_sunat && \Carbon\Carbon::parse($gerente->fecha_sunat)->year > 1 ? \Carbon\Carbon::parse($gerente->fecha_sunat)->format('d/m/Y') : '—' }}
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                {{ $gerente->fecha_cambio && \Carbon\Carbon::parse($gerente->fecha_cambio)->year > 1 ? \Carbon\Carbon::parse($gerente->fecha_cambio)->format('d/m/Y') : '—' }}
                                            </td>
                                            <td class="px-4 py-2">{{ $gerente->observacion_gerente ?? '—' }}</td>
                                            <td class="px-4 py-2 text-center">
                                                <form
                                                    action="{{ route('cambiar.estado.gerente', ['id' => $gerente->id_gerente]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('POST') <!-- Método POST -->
                                                    <input type="hidden" name="estado" value="ACTIVO">
                                                    <input type="hidden" name="gerenteActual"
                                                        value="{{ $gerenteActual->id_gerente ?? NULL }}">
                                                    <button type="submit"
                                                        class="flex items-center p-2 rounded-md tooltip text-green-500"
                                                        style="transition: color 0.3s;"
                                                        onmouseover="this.style.color='#059669';"
                                                        onmouseout="this.style.color='#10b981';">
                                                        <x-add-people-icon />
                                                        <span class="tooltip-text">Activar</span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        @php $ultimoGrupo = $grupoActual; @endphp
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="px-4 py-2 text-center text-gray-500">
                                            No hay gerentes inactivos.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- Contenido Socios --}}
                    <div x-show="tab === 'socios'" class="transition duration-300 ease-in-out">
                        <form action="{{ route('activar.socios') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_empresas_clientes"
                                value="{{ $empresa->id_empresas_clientes }}">
                            <table class="min-w-full table-auto border border-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2">Nombre Socio</th>
                                        <th class="px-4 py-2">Porcentaje</th>
                                        <th class="px-4 py-2">Fecha Cambio</th>
                                        <th class="px-4 py-2">Observación</th>
                                        <th class="px-4 py-2">Activar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($sociosInactivos->count())
                                        @php
                                            $ultimoGrupo = null;
                                            $color = false;
                                        @endphp
                                        @foreach ($sociosInactivos as $socio)
                                            @php
                                                $grupoActual = $socio->created_at->format('Y-m-d H:i');
                                                if ($ultimoGrupo !== $grupoActual) {
                                                    $color = !$color;
                                                }
                                            @endphp
                                            <tr class="{{ $color ? 'bg-white' : 'bg-gray-100' }}">
                                                <td class="px-4 py-2">{{ $socio->nombre }}</td>
                                                {{-- <td class="px-4 py-2 text-center">{{ $socio->porcentaje }}%</td> --}}
                                                <td class="px-4 py-2 text-center">
                                                    <input type="number" name="porcentaje[{{ $socio->id_socios }}]"
                                                        value="{{ $socio->porcentaje }}"
                                                        class="w-full text-center border px-2 py-1 {{ $socio->estado === 'INACTIVO' ? 'bg-gray-300' : '' }}"
                                                        min="0" max="100"
                                                        id="porcentaje_{{ $socio->id_socios }}" disabled>
                                                </td>
                                                <td class="px-4 py-2 text-center">
                                                    {{ $socio->fecha_cambio && \Carbon\Carbon::parse($socio->fecha_cambio)->year > 1 ? \Carbon\Carbon::parse($socio->fecha_cambio)->format('d/m/Y') : '—' }}
                                                </td>
                                                <td class="px-4 py-2">{{ $socio->observacion ?? '—' }}</td>
                                                <td class="px-4 py-2 text-center">
                                                    <label for="activo_{{ $socio->id_socios }}"
                                                        class="flex items-center">
                                                        <input type="checkbox" id="activo_{{ $socio->id_socios }}"
                                                            name="socios_seleccionados[]"
                                                            value="{{ $socio->id_socios }}" class="mr-2"
                                                            onclick="togglePorcentajeInput({{ $socio->id_socios }})">
                                                    </label>
                                                </td>
                                                @php $ultimoGrupo = $grupoActual; @endphp
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="px-4 py-2 text-center text-gray-500">
                                                No hay socios inactivos.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            <!-- Agregar este estilo en el contenedor del botón -->
                            <div class="flex justify-end mt-4">
                                <button type="submit"
                                    class="bg-green-500 px-4 py-2 rounded-md hover:bg-green-600 transition duration-300 text-white">
                                    ACTIVAR
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- Modal Gerente-->
                <x-modal-show id="cuentaModal" title="Registro Historial de Gerente" class="max-w-5xl">
                    <div class="max-h-[85vh] px-1">
                        {{-- Contexto: Gerente Actual --}}
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Estado Actual:</h4>
                            @if ($gerenteActual)
                                <p class="text-sm text-gray-700">
                                    Gerente activo: <span class="font-bold">{{ $gerenteActual->nombre }}</span>,
                                    <span class="text-xs text-gray-500">
                                        (Desde:
                                        {{ $gerenteActual->created_at ? \Carbon\Carbon::parse($gerenteActual->created_at)->format('d/m/Y') : '—' }})
                                    </span>
                                </p>
                            @else
                                <p class="text-sm text-gray-500 italic">No hay gerente activo actualmente.</p>
                            @endif
                        </div>

                        <form action="{{ route('gerente_socios.store') }}" method="POST" class="space-y-6">
                            @csrf
                            <input type="hidden" name="id_empresas_clientes"
                                value="{{ $empresa->id_empresas_clientes }}">
                            <input type="hidden" name="estado" value="INACTIVO">

                            {{-- Nombre Gerente --}}
                            <div>
                                <x-input-label for="nombre_gerente_new" value="Nombre Nuevo Gerente" />
                                <x-text-input id="nombre_gerente_new" name="nombre" type="text"
                                    class="mt-1 block w-full" oninput="this.value = this.value.toUpperCase()" />
                            </div>

                            {{-- Fechas --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="fecha_acta_new" value="Fecha Acta" />
                                    <x-text-input id="fecha_acta_new" type="date" name="fecha_acta"
                                        value="{{ now()->format('Y-m-d') }}" />
                                </div>
                                <div>
                                    <x-input-label for="fecha_sunat_new" value="Fecha Sunat" />
                                    <x-text-input id="fecha_sunat_new" type="date" name="fecha_sunat"
                                        value="{{ now()->format('Y-m-d') }}" />
                                </div>
                                <div>
                                    <x-input-label for="fecha_cambio_new" value="Fecha Cambio" />
                                    <x-text-input id="fecha_cambio_new" type="date" name="fecha_cambio"
                                        value="{{ now()->format('Y-m-d') }}" />
                                </div>
                                {{-- Observaciones --}}
                                <div class="md:col-span-3">
                                    <x-input-label for="observacion_gerente_new" value="Observaciones" />
                                    <x-text-input id="observacion_gerente_new" name="observacion_gerente"
                                        rows="2"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></x-text-input>
                                </div>
                            </div>

                            {{-- Botones --}}
                            <div class="flex justify-end gap-2 pt-4 border-t">
                                <button type="button" onclick="toggleModal('cuentaModal')"
                                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition text-gray-700">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                    Guardar Registro
                                </button>
                            </div>
                        </form>
                    </div>
                </x-modal-show>

                {{-- SOCIOS --}}
                <x-modal-show id="cuentaModa2" title="Registro Historial de Socios" class="max-w-5xl">
                    <!-- Formulario para enviar los datos -->
                    <form action="{{ route('gerente_socios.store') }}" method="POST" class="space-y-6">
                        @csrf <!-- Asegurarnos de incluir el token CSRF -->
                        <input type="hidden" name="id_empresas_clientes"
                            value="{{ $empresa->id_empresas_clientes }}">
                        <input type="hidden" name="estado" value="INACTIVO">
                        <input type="hidden" name="validar_porcentaje" value="false">
                        <div x-data="{
                            socios: [{ nombre: '', fecha_cambio: '{{ now()->format('Y-m-d') }}' }]
                        }" class="space-y-4 border-t pt-4">

                            <!-- Estado Actual -->
                            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Estado Actual:</h4>
                                @if ($gerenteActual)
                                    <p class="text-sm text-gray-700">
                                        Socios activos:
                                        @foreach ($sociosActivos as $socio)
                                            <span class="font-bold">{{ $socio->nombre }}</span>
                                            <span class="text-gray-500">({{ $socio->porcentaje }}%)</span>
                                            @if ($socio->created_at)
                                                <span class="text-gray-500 text-xs">desde
                                                    {{ \Carbon\Carbon::parse($socio->created_at)->format('d/m/Y') }}</span>
                                            @endif
                                            @if (!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    </p>
                                @else
                                    <p class="text-sm text-gray-500 italic">No hay gerente activo actualmente.</p>
                                @endif
                            </div>

                            <!-- Título -->
                            <h4 class="font-medium text-gray-700">Socios</h4>

                            <!-- Empezamos con un socio por defecto -->
                            <div x-data="{
                                socios: [{ nombre: '', fecha_cambio: '{{ now()->format('Y-m-d') }}' }]
                            }" class="space-y-4 border-t pt-4">

                                <div class="mb-4">
                                    <!-- Campo para el primer socio -->
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-6 gap-2 items-end socio-row bg-gray-50 p-2 rounded">
                                        <div class="md:col-span-3">
                                            <label class="block text-sm font-medium text-gray-700">Nombre Socio
                                                1</label>
                                            <input type="text" name="socios[0][nombre]"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                                x-model="socios[0].nombre"
                                                @input="socios[0].nombre = socios[0].nombre.toUpperCase()" />
                                        </div>
                                        <div class="md:col-span-1">
                                            <label class="block text-sm font-medium text-gray-700">%</label>
                                            <input type="number" name="socios[0][porcentaje]"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm text-center"
                                                x-model="socios[0].porcentaje" />
                                        </div>
                                        <div class="md:col-span-1">
                                            <label class="block text-sm font-medium text-gray-700">Fecha Cambio</label>
                                            <input type="date" name="socios[0][fecha_cambio]"
                                                class="mt-1 block w-full min-w-[140px] border-gray-300 rounded-md shadow-sm text-sm text-center"
                                                x-model="socios[0].fecha_cambio" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Agregar socios adicionales -->
                                <template x-for="(socio, i) in socios.slice(1)" :key="i">
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-6 gap-2 items-end socio-row bg-gray-50 p-2 rounded">
                                        <div class="md:col-span-3">
                                            <label class="block text-sm font-medium text-gray-700"
                                                x-text="'Nombre Socio ' + (i + 2)"></label>
                                            <input type="text" :name="'socios[' + (i + 1) + '][nombre]'"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                                x-model="socio.nombre"
                                                @input="socio.nombre = socio.nombre.toUpperCase()" />
                                        </div>
                                        <div class="md:col-span-1">
                                            <label class="block text-sm font-medium text-gray-700">%</label>
                                            <input type="number" :name="'socios[' + (i + 1) + '][porcentaje]'"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm text-center"
                                                x-model="socio.porcentaje" />
                                        </div>
                                        <div class="md:col-span-1">
                                            <label class="block text-sm font-medium text-gray-700">Fecha Cambio</label>
                                            <input type="date" :name="'socios[' + (i + 1) + '][fecha_cambio]'"
                                                class="mt-1 block w-full min-w-[140px] border-gray-300 rounded-md shadow-sm text-sm text-center"
                                                x-model="socio.fecha_cambio" />
                                        </div>
                                        <div class="flex items-end justify-center pb-1 ml-3">
                                            <button type="button" @click="socios.splice(i,1);"
                                                class="text-red-500 text-lg">✕</button>
                                        </div>
                                    </div>
                                </template>
                                <!-- Botón para agregar más socios -->
                                <button type="button"
                                    class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition"
                                    @click="socios.push({ nombre: '', porcentaje: 0, fecha_cambio: '{{ now()->format('Y-m-d') }}' });">
                                    + Agregar socio
                                </button>
                            </div>
                            <!-- Campo de observaciones -->
                            <div>
                                <x-input-label for="observaciones_new" value="Observaciones" />
                                <x-text-input id="observaciones_new" name="observaciones" rows="2"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></x-text-input>
                            </div>
                            <!-- Botones de acción -->
                            <div class="flex justify-end gap-2 pt-4 border-t">
                                <button type="button" onclick="toggleModal('cuentaModa2')"
                                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition text-gray-700">Cancelar</button>
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Guardar
                                    Registro</button>
                            </div>
                        </div>
                    </form>
                </x-modal-show>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    // Abre el modal cambiando el tipo de modal (gerente o socio)
    function openModal(type) {
        document.querySelector('[x-data]').__x.$data.modalType = type;
        document.getElementById('cuentaModal').classList.remove('hidden');
        document.getElementById('cuentaModa2').classList.remove('hidden');
    }

    // Cierra el modal
    function closeModal() {
        document.getElementById('cuentaModal').classList.add('hidden');
        document.getElementById('cuentaModa2').classList.add('hidden');
    }

    // Cierra el modal si se hace clic fuera del modal
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('cuentaModal');
        const modal = document.getElementById('cuentaModa2');
        if (event.target === modal) {
            closeModal();
        }
    });
</script>

<script>
    // Función para cambiar el estado del gerente a "activo"
    function cambiarActivo(gerenteId) {
        if (!gerenteId) {
            console.error('El ID del gerente no es válido');
            return;
        }

        console.log("ID del gerente: ", gerenteId); // Verifica en la consola si el ID se está pasando correctamente

        // Hacer la solicitud AJAX
        fetch(`/cambiar-estado-gerente/${gerenteId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    estado: 'activo'
                })
            })
            .then(response => response.json()) // Asegúrate de que la respuesta sea JSON
            .then(data => {
                if (data.success) {
                    // Si la respuesta es exitosa, actualiza el botón
                    const button = document.querySelector(`button[onclick="cambiarActivo(${gerenteId})"]`);
                    button.textContent = "Activo"; // Cambia el texto del botón
                    button.classList.remove("bg-blue-500"); // Elimina la clase del color anterior
                    button.classList.add("bg-green-500"); // Agrega la clase para el color de "activo"
                } else {
                    // Si hay un error, muestra un mensaje
                    alert("Hubo un problema al cambiar el estado del gerente.");
                }
            })
            .catch(error => {
                console.error('Error:', error); // Si ocurre un error en la solicitud
            });
    }
</script>

<script>
    function togglePorcentajeInput(socioId) {
        const checkbox = document.getElementById('activo_' + socioId);
        const porcentajeInput = document.getElementById('porcentaje_' + socioId);

        if (checkbox.checked) {
            porcentajeInput.disabled = false; // Desbloquear el campo
            porcentajeInput.classList.remove('bg-gray-300'); // Eliminar color gris
        } else {
            porcentajeInput.disabled = true; // Bloquear el campo
            porcentajeInput.classList.add('bg-gray-300'); // Poner gris cuando está bloqueado
        }
    }
</script>
