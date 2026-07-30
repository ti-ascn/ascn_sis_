<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registrar detalles de póliza — {{ $poliza->empresa->razon_social }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('polizas.detalle.store') }}" autocomplete="off" class="space-y-6">
                    @csrf
                    <input type="hidden" name="id_poliza" value="{{ $poliza->id_poliza }}">
                    <input type="hidden" name="tipos" value="{{ implode(',', $tipos) }}">

                    @php
                        $aseguradoras = [
                            'SIN ASEGURADORA',
                            'CRECER SEGUROS S.A. COMPAÑIA DE SEGUROS',
                            'PACIFICO COMPAÑIA DE SEGUROS Y REASEGUROS',
                            'LA POSITIVA VIDA SEGUROS Y REASEGUROS',
                            'MAPFRE PERU COMPAÑIA DE SEGUROS Y REASEGUROS',
                            'RIMAC SEGUROS Y REASEGUROS',
                        ];
                    @endphp

                    {{-- Poliza VIDA --}}
                    @if (in_array('vida', $tipos))
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h3 class="font-semibold mb-3 text-blue-600">Póliza Vida Ley</h3>
                            <x-input-label :value="__('Aseguradora')" />
                            <select name="vida_aseguradora"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="" disabled selected>Selecciona aseguradora</option>
                                @foreach ($aseguradoras as $aseg)
                                    <option value="{{ $aseg }}">{{ $aseg }}</option>
                                @endforeach
                            </select>

                            <div>
                                <x-input-label class="mt-3" :value="__('Brokey vida Ley')" />
                                <x-text-input name="vida_broker" class="mt-1 block w-full" />
                            </div>

                            <x-input-label class="mt-3" :value="__('N° Póliza')" />
                            <x-text-input name="vida_nro_poliza" class="mt-1 block w-full" />

                            <div class="grid grid-cols-2 gap-4 mt-3">
                                <div>
                                    <x-input-label :value="__('Fecha inicio')" />
                                    <x-text-input name="vida_fecha_inicio" type="date" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label :value="__('Fecha fin')" />
                                    <x-text-input name="vida_fecha_fin" type="date" class="mt-1 block w-full" />
                                </div>
                            </div>

                            <div class="mt-3">
                                <x-input-label :value="__('Estado')" />
                                <select name="vida_estado" class="border-gray-300 rounded-md shadow-sm w-full">
                                    <option value="COTIZAR" selected>COTIZAR</option>
                                    <option value="ACTIVO">ACTIVO</option>
                                    <option value="INACTIVO">INACTIVO</option>
                                    <option value="COTIZ. ENVIADA">COTIZ. ENVIADA</option>
                                    <option value="COTIZ. RECIBIDA">COTIZ. RECIBIDA</option>
                                </select>
                            </div>

                            <x-input-label class="mt-3" :value="__('Observación')" />
                            <x-text-input name="vida_observacion"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                placeholder="Ej. Pendiente de aprobación" />
                        </div>
                    @endif

                    {{-- Poliza SCTR --}}
                    @if (in_array('sctr', $tipos))
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h3 class="font-semibold mb-3 text-green-600">Póliza SCTR</h3>
                            <x-input-label :value="__('Aseguradora')" />
                            <select name="sctr_aseguradora"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="" disabled selected>Selecciona aseguradora</option>
                                @foreach ($aseguradoras as $aseg)
                                    <option value="{{ $aseg }}">{{ $aseg }}</option>
                                @endforeach
                            </select>

                            <div>
                                <x-input-label class="mt-3" :value="__('Brokey SCTR')" />
                                <x-text-input name="sctr_broker" class="mt-1 block w-full" />
                            </div>

                            <div class="grid grid-cols-2 gap-4 mt-3">
                                <div>
                                    <x-input-label :value="__('Fecha inicio')" />
                                    <x-text-input name="sctr_fecha_inicio" type="date" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label :value="__('Fecha fin')" />
                                    <x-text-input name="sctr_fecha_fin" type="date" class="mt-1 block w-full" />
                                </div>
                            </div>

                            <div class="mt-3">
                                <x-input-label :value="__('Estado')" />
                                <select name="sctr_estado" class="border-gray-300 rounded-md shadow-sm w-full">
                                    <option value="COTIZAR" selected>COTIZAR</option>
                                    <option value="ACTIVO">ACTIVO</option>
                                    <option value="INACTIVO">INACTIVO</option>
                                    <option value="COTIZ. ENVIADA">COTIZ. ENVIADA</option>
                                    <option value="COTIZ. RECIBIDA">COTIZ. RECIBIDA</option>
                                    <option value="BAJO RIESGO">BAJO RIESGO</option>
                                </select>
                            </div>


                            <x-input-label class="mt-3" :value="__('Observación')" />
                            <x-text-input name="sctr_observacion"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                placeholder="Ej. Pendiente de cotización" />
                        </div>
                    @endif

                    <div class="flex justify-end mt-6">
                        <x-primary-button>Guardar póliza(s)</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
