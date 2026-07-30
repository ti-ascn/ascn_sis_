<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gerente y Socios de <span class="text-blue-500">{{ $empresa->razon_social }} </span> RUC: <span
                class="text-blue-500">{{ $empresa->ruc }} </span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST"
                    action="{{ $modo === 'edit'
                        ? route('gerente_socios.update', $empresa->id_empresas_clientes)
                        : route('gerente_socios.store') }}">
                    @csrf
                    @if ($modo === 'edit')
                        @method('PUT')
                    @endif
                    {{-- Empresa --}}
                    <div>
                        <input type="hidden" name="id_empresas_clientes" value="{{ $empresa->id_empresas_clientes }}">
                    </div>

                    {{-- Sección Estado Actual --}}
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Estado Actual:</h4>
                        @if ($gerenteActual)
                            <p class="text-sm text-gray-700">
                                El gerente actual es <span class="font-bold">{{ $gerenteActual->nombre }}</span>
                                desde la fecha
                                <span class="font-bold">
                                    {{ $gerenteActual->created_at ? \Carbon\Carbon::parse($gerenteActual->created_at)->format('d/m/Y') : '—' }}
                                </span>
                                @if ($sociosActivos->count() > 0)
                                    — Los socios actuales son:
                                    @foreach ($sociosActivos as $index => $socio)
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
                                @endif
                            </p>
                        @else
                            <p class="text-sm text-gray-500 italic">No hay un gerente activo registrado actualmente.</p>
                        @endif
                    </div>

                    {{-- Campos --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nombre Gerente --}}
                        <div class="md:col-span-2">
                            <x-input-label for="nombre" :value="__('Nombre Gerente')" />
                            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                                value="{{ old('nombre', $gerente->nombre ?? '') }}"
                                oninput="this.value = this.value.toUpperCase()" />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Fecha Acta --}}
                        <div>
                            <x-input-label for="fecha_acta" :value="__('Fecha Acta')" />
                            <x-text-input type="date" name="fecha_acta" class="mt-1 block w-full"
                                value="{{ old('fecha_acta', $gerente->fecha_acta ?? now()->format('Y-m-d')) }}" />
                            <x-input-error :messages="$errors->get('fecha_acta')" class="mt-2" />
                        </div>

                        {{-- Fecha Sunat --}}
                        <div>
                            <x-input-label for="fecha_sunat" :value="__('Fecha Sunat')" />
                            <x-text-input type="date" name="fecha_sunat" class="mt-1 block w-full"
                                value="{{ old('fecha_sunat', $gerente->fecha_sunat ?? now()->format('Y-m-d')) }}" />
                            <x-input-error :messages="$errors->get('fecha_sunat')" class="mt-2" />
                        </div>

                        {{-- Fecha Cambio --}}
                        <div>
                            <x-input-label for="fecha_cambio" :value="__('Fecha Cambio')" />
                            <x-text-input type="date" name="fecha_cambio" class="mt-1 block w-full"
                                value="{{ old('fecha_cambio', $gerente->fecha_cambio ?? now()->format('Y-m-d')) }}" />
                            <x-input-error :messages="$errors->get('fecha_cambio')" class="mt-2" />
                        </div>

                        {{-- Observaciones Gerente --}}
                        <div class="md:col-span-3">
                            <x-input-label for="observacion_gerente" :value="__('Observaciones Gerente')" />
                            <x-text-input id="observacion_gerente" name="observacion_gerente" type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                placeholder="Ingrese observaciones" :value="old('observacion_gerente', $modo === 'edit' ? ($gerente->observacion_gerente ?? '') : '')" />
                            <x-input-error :messages="$errors->get('observacion_gerente')" class="mt-2" />
                        </div>

                    </div>

                    {{-- Nombre Socio --}}
                    <div x-data="{
                        socios: {{ Js::from(old('socios', $socios ?? [])) }},
                    
                        recalcular() {
                            let total = 0;
                            this.socios.slice(0, -1).forEach(s => {
                                total += parseFloat(s.porcentaje) || 0;
                            });
                            let restante = 100 - total;
                            if (restante < 0) restante = 0;
                            if (this.socios.length) {
                                this.socios[this.socios.length - 1].porcentaje = restante.toFixed(2);
                            }
                        }
                    }" class="space-y-6">

                        <template x-for="(socio, i) in socios" :key="i">
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-2 items-end socio-row">

                                <!-- ID oculto -->
                                <input type="hidden" :name="'socios[' + i + '][id_socios]'"
                                    :value="socio.id_socios ?? ''">

                                <!-- Nombre -->
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700"
                                        x-text="'Nombre Socio ' + (i + 1)"></label>
                                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        :name="'socios[' + i + '][nombre]'" x-model="socio.nombre"
                                        @input="socio.nombre = socio.nombre.toUpperCase()" />
                                </div>

                                <!-- Porcentaje -->
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700">Porcentaje (%)</label>
                                    <input type="text" inputmode="decimal" pattern="^\d+(\.\d{0,2})?$"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-center"
                                        :name="'socios[' + i + '][porcentaje]'" x-model="socio.porcentaje"
                                        @input="
                                        let val = $event.target.value.replace(',', '.');
                                        // Permitir solo números y punto
                                        if (!/^\d*\.?\d*$/.test(val)) return;
                                        // Limitar a 2 decimales
                                        let parts = val.split('.');
                                        if (parts[1]?.length > 2) {
                                            parts[1] = parts[1].slice(0, 2);
                                            val = parts.join('.');
                                        }

                                        socio.porcentaje = val;
                                        recalcular();" />
                                </div>

                                <!-- Fecha Cambio -->
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700">Fecha Cambio</label>
                                    <input type="date"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-center"
                                        :name="'socios[' + i + '][fecha_cambio]'" x-model="socio.fecha_cambio" />
                                </div>

                                <!-- Eliminar -->
                                <div class="md:col-span-1 flex items-center justify-center pb-1">
                                    <button type="button"
                                        @click="socios.splice(i, 1); recalcular();"class="text-red-500 text-lg leading-none">
                                        ✕
                                    </button>
                                </div>
                        </template>

                        <!-- Botón agregar -->
                        <button type="button"
                            class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition"
                            @click="socios.push({ nombre: '', porcentaje: 0, fecha_cambio: '{{ now()->format('Y-m-d') }}' }); recalcular();">
                            Agregar socio
                        </button>
                        {{-- <!-- Total (visual) -->
                    <div class="text-sm text-gray-600">
                        Total: <strong
                            x-text="socios.reduce((s, v) => s + Number(v.porcentaje || 0), 0).toFixed(2)"></strong> %
                    </div> --}}
                    </div>

                    {{-- Observaciones Socios --}}
                    <div class="md:col-span-2">
                        <x-input-label for="observaciones" :value="__('Observaciones Socios')" />
                        <x-text-input id="observaciones" name="observaciones" type="text"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            placeholder="Ingrese observaciones" :value="old('observaciones', $modo === 'edit' ? ($sociosActivos->first()->observacion ?? '') : '')" />
                        <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                    </div>

                    <div class="flex justify-end mt-8">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            {{ $modo === 'edit' ? 'Actualizar' : 'Registrar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function eliminarSocio(btn) {
        btn.closest('.socio-row').remove();
    }
</script>
