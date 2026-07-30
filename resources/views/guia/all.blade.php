<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lista de  TODAS las Guías') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-success-error-bagdes />

            @php
                $meses_es = [
                    '01' => 'Enero',
                    '02' => 'Febrero',
                    '03' => 'Marzo',
                    '04' => 'Abril',
                    '05' => 'Mayo',
                    '06' => 'Junio',
                    '07' => 'Julio',
                    '08' => 'Agosto',
                    '09' => 'Septiembre',
                    '10' => 'Octubre',
                    '11' => 'Noviembre',
                    '12' => 'Diciembre',
                ];
            @endphp

            <div class="flex flex-wrap gap-4 items-center md:flex-row flex-col">

                {{-- Botón Crear Guía (fuera del form) --}}
                <a href="{{ route('guias.index') }}"
                    class="block bg-gray-500 px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300 text-white text-center w-full md:w-auto">
                    <i class="bi bi-arrow-left-circle"></i> {{ __('Volver a guías') }}
                </a>
                <div class="relative w-full md:w-auto">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center justify-center gap-2 bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition w-full md:w-auto">
                                <i class="bi bi-file-earmark-plus-fill"></i> Crear Guía <i
                                    class="bi bi-chevron-down"></i>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="p-2">
                                <x-dropdown-link :href="route('guias.create_empresa')">
                                    <i class="bi bi-building me-2"></i> Empresa
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('guias.create_cliente')">
                                    <i class="bi bi-person me-2"></i> Cliente
                                </x-dropdown-link>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <br />

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto display nowrap responsive nowrap" id="myTable">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">ID</th>
                            <th class="px-4 py-2 border-b">Razón Social</th>
                            <th class="px-4 py-2 border-b">Monto</th>
                            <th class="px-4 py-2 border-b">Recepción</th>
                            <th class="px-4 py-2 border-b">Programado</th>
                            <th class="px-4 py-2 border-b">Emitido</th>
                            {{-- <th class="px-4 py-2 border-b">GRR</th>
                            <th class="px-4 py-2 border-b">GRT</th>
                            <th class="px-4 py-2 border-b">Bancarización</th> --}}
                            {{-- <th class="px-4 py-2 border-b">Entregado</th> --}}
                            {{-- <th class="px-4 py-2 border-b">Observaciones</th> --}}
                            <th class="px-4 py-2 border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($guias as $guia)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $guia->id_guia }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($guia->empresa)
                                        {{ $guia->empresa->razon_social }}
                                    @elseif ($guia->cliente)
                                        {{ $guia->cliente->razon_social }}
                                    @endif
                                </td>
                                {{-- <td class="px-4 py-2 border-b">
                                    @if ($guia->empresa)
                                        {{ $guia->empresa->razon_social }} – {{ $guia->empresa->ruc }}
                                    @elseif ($guia->cliente)
                                        {{ $guia->cliente->razon_social }} – {{ $guia->cliente->ruc }}
                                    @endif
                                </td> --}}
                                <td class="px-4 py-2 border-b">S/ {{ number_format($guia->monto, 2) }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ \Carbon\Carbon::parse($guia->recepcion)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 border-b">{{ ucfirst($guia->programado) }}</td>
                                <td class="px-4 py-2 border-b">{{ ucfirst($guia->emitido) }}</td>
                                {{-- <td class="px-4 py-2 border-b">{{ strtoupper($guia->grr) }}</td>
                                <td class="px-4 py-2 border-b">{{ strtoupper($guia->grt) }}</td>
                                <td class="px-4 py-2 border-b">{{ strtoupper($guia->bancarizacion) }}</td> --}}
                                {{-- <td class="px-4 py-2 border-b">{{ $guia->entregado ?: '-' }}</td> --}}
                                {{-- <td class="px-4 py-2 border-b">{{ $guia->observaciones ?: 'Sin Observaciones' }}</td> --}}
                                <td class="px-4 py-2 border-b">
                                    <div class="flex space-x-2">

                                        <a href="javascript:void(0)"
                                            class="flex items-center py-1 rounded-md tooltip btn-detalles"
                                            style="color: #6B7280; transition: color 0.3s;"
                                            onmouseover="this.style.color='#4B5563';"
                                            onmouseout="this.style.color='#6B7280';" data-id="{{ $guia->id_guia }}"
                                            data-razon="{{ $guia->empresa->razon_social ?? ($guia->cliente->razon_social ?? 'VACÍO') }}"
                                            data-ruc="{{ $guia->empresa->ruc ?? ($guia->cliente->ruc ?? 'VACÍO') }}"
                                            data-contacto="{{ $guia->empresa->contacto ?? ($guia->cliente->contacto ?? 'VACÍO') }}"
                                            data-rubro="{{ $guia->empresa->rubro ?? ($guia->cliente->rubro ?? 'VACÍO') }}"
                                            data-monto="{{ number_format($guia->monto, 2) }}"
                                            data-recepcion="{{ \Carbon\Carbon::parse($guia->recepcion)->format('d/m/Y') }}"
                                            data-programado="{{ ucfirst($guia->programado) }}"
                                            data-emitido="{{ ucfirst($guia->emitido) }}"
                                            data-entregado="{{ $guia->entregado ?? 'VACÍO' }}"
                                            data-grr="{{ strtoupper($guia->grr) }}"
                                            data-grt="{{ strtoupper($guia->grt) }}"
                                            data-bancarizacion="{{ strtoupper($guia->bancarizacion) }}"
                                            data-observaciones="{{ $guia->observaciones ?? 'Sin observaciones' }}"
                                            data-usuario_sol="{{ $guia->empresa->sol_usuario ?? '-' }}"
                                            data-clave_sol="{{ $guia->empresa->sol_clave ?? '-' }}"
                                            data-usuario="{{ $guia->usuario->name ?? '—' }}"
                                            data-tipo="{{ $guia->empresa ? 'empresa' : 'cliente' }}"
                                            data-info_cuentas="{{ $guia->empresa->info_cuentas ?? '' }}"
                                            data-direcciones="{{ $guia->empresa ? $guia->empresa->direcciones->map(fn($d) => $d->tipo_domicilio . ': ' . $d->nombre . ' - ' . $d->distrito . ', ' . $d->provincia . ', ' . $d->region)->implode('|') : '' }}">
                                            <span class="tooltip-text">Detalles</span>
                                            <x-details-icon />
                                        </a>


                                        <a href="{{ route('guias.edit', $guia->id_guia) }}"
                                            class="flex items-center py-1 rounded-md tooltip"
                                            style="color: #F59E0B; transition: color 0.3s;"
                                            onmouseover="this.style.color='#D97706';"
                                            onmouseout="this.style.color='#F59E0B';">
                                            <span class="tooltip-text">Editar</span>
                                            <x-edit-icon />
                                        </a>
                                        @if (Auth::user()->tipo_usuario == 1 || Auth::user()->tipo_usuario == 2)
                                            <form action="{{ route('guias.destroy', $guia->id_guia) }}" method="POST"
                                                style="display:inline;"
                                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta guía?')">
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
<x-modal-show id="guiaModal" title="Detalles de la Guía">
    <div class="space-y-4 text-sm text-gray-800">

        {{-- Info Principal --}}
        <div class="grid grid-cols-2 gap-2">
            <p><strong>ID Guía:</strong> <span id="guiaId"></span></p>
            <p><strong>Recepción:</strong> <span id="guiaRecepcion"></span></p>

            <p><strong>Contacto:</strong> <span id="guiaContacto"></span></p>
            <p><strong>RUC:</strong> <span id="guiaRuc"></span></p>

            <p><strong>Razón Social:</strong> <span id="guiaRazon"></span></p>
            <p><strong>Rubro:</strong> <span id="guiaRubro"></span></p>

            <p><strong>Monto:</strong> <span id="guiaMonto"></span></p>
            <p><strong>Ultima edición:</strong> <span id="guiaUsuario"></span></p>
        </div>

        {{-- Usuario y Clave SOL (solo para empresas) --}}
        {{-- <div id="empresaSol" class="space-y-1 hidden border-t pt-3">
            <h4 class="font-semibold text-gray-700">Datos SUNAT:</h4>
            <p><strong>Usuario SOL:</strong> <span id="guiaUsuarioSol"></span></p>
            <p><strong>Clave SOL:</strong> <span id="guiaClaveSol"></span></p>
        </div> --}}

        {{-- Estados --}}
        <div class="grid grid-cols-2 gap-2 border-t pt-3">
            <p><strong>Programado:</strong> <span id="guiaProgramado"></span></p>
            <p><strong>Emitido:</strong> <span id="guiaEmitido"></span></p>
            <p><strong>Entregado:</strong> <span id="guiaEntregado"></span></p>
            <p><strong>GRR:</strong> <span id="guiaGrr"></span></p>
            <p><strong>GRT:</strong> <span id="guiaGrt"></span></p>
            <p><strong>Bancarización:</strong> <span id="guiaBancarizacion"></span></p>
        </div>

        {{-- Observaciones --}}
        <div class="border-t pt-3">
            <p><strong>Observaciones:</strong> <span id="guiaObs"></span></p>
        </div>

        {{-- <div id="empresaCuentas" class="border-t pt-3 hidden">
            <h4 class="font-semibold text-gray-700">Información de Cuentas:</h4>
            <pre id="guiaCuentas" class="text-sm text-gray-700 whitespace-pre-wrap break-words"></pre>
        </div> --}}


        {{-- Direcciones --}}
        {{-- <div id="empresaDirecciones" class="space-y-1 border-t pt-3 hidden">
            <h4 class="font-semibold text-gray-700">Direcciones:</h4>
            <ul id="guiaDirecciones" class="list-disc list-inside space-y-1 text-gray-700 text-sm"></ul>
        </div> --}}

    </div>
</x-modal-show>

<script>
    document.querySelectorAll('.btn-detalles').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;

            document.getElementById('guiaId').textContent = data.id;
            document.getElementById('guiaRazon').textContent = data.razon;
            document.getElementById('guiaRuc').textContent = data.ruc;
            //document.getElementById('guiaUsuarioSol').textContent = data.usuario_sol;
            //document.getElementById('guiaClaveSol').textContent = data.clave_sol;
            document.getElementById('guiaContacto').textContent = data.contacto;
            document.getElementById('guiaRubro').textContent = data.rubro;
            document.getElementById('guiaMonto').textContent = `S/ ${data.monto}`;
            document.getElementById('guiaRecepcion').textContent = data.recepcion;
            document.getElementById('guiaProgramado').textContent = data.programado;
            document.getElementById('guiaEmitido').textContent = data.emitido;
            document.getElementById('guiaEntregado').textContent = data.entregado;
            document.getElementById('guiaGrr').textContent = data.grr;
            document.getElementById('guiaGrt').textContent = data.grt;
            document.getElementById('guiaBancarizacion').textContent = data.bancarizacion;
            document.getElementById('guiaObs').textContent = data.observaciones;
            document.getElementById('guiaUsuario').textContent = data.usuario;

            /*
            const list = document.getElementById('guiaDirecciones');
            const box = document.getElementById('empresaDirecciones');
            list.innerHTML = '';

            if (data.tipo === 'empresa') {
                if (data.direcciones && data.direcciones !== 'Sin direcciones registradas') {
                    const direcciones = data.direcciones.split('|');
                    direcciones.forEach(dir => {
                        const li = document.createElement('li');
                        li.textContent = dir;
                        list.appendChild(li);
                    });
                    box.classList.remove('hidden');
                } else {
                    box.classList.add('hidden');
                }

                document.getElementById('empresaSol').classList.remove('hidden');
            } else {
                box.classList.add('hidden');
                document.getElementById('empresaSol').classList.add('hidden');
            }
            
            const cuentas = document.getElementById('guiaCuentas');
            const cuentaBox = document.getElementById('empresaCuentas');

            if (data.tipo === 'empresa' && data.info_cuentas && data.info_cuentas.trim() !== '') {
                cuentas.textContent = data.info_cuentas;
                cuentaBox.classList.remove('hidden');
            } else {
                cuentaBox.classList.add('hidden');
            } */


            toggleModal('guiaModal');
        });
    });
</script>
