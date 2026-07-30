<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Calendario') }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <form method="POST" action="{{ route('calendario.update', $calendario->id_calendario) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Tipo --}}
                <div>
                    <x-input-label for="tipo" :value="__('Tipo')" />
                    <select name="tipo" id="tipo" class="w-full border rounded">
                        <option value="PDT" {{ $calendario->tipo == 'PDT' ? 'selected' : '' }}>PDT</option>
                        <option value="SIRE" {{ $calendario->tipo == 'SIRE' ? 'selected' : '' }}>SIRE</option>
                    </select>
                </div>

                {{-- Mes --}}
                <div class="mt-4">
                    <x-input-label for="mes" :value="__('Período')" />
                    <x-text-input id="mes" type="month" name="mes" class="w-full"
                        value="{{ $calendario->mes }}" required />
                </div>

                {{-- Fechas --}}
                <div class="mt-4">
                    {{-- Aquí se actualizará dinámicamente --}}
                    <x-input-label id="fechas-label" for="fechas" :value="__('Fechas de Vencimiento (0-9)')" />

                    <div class="grid grid-cols-5 gap-2 mt-2">
                        @php
                            $fechas = json_decode($calendario->fechas, true) ?? [];
                        @endphp
                        @for ($i = 0; $i < 10; $i++)
                            <input type="number" name="fechas[]" class="border rounded p-1 fechas-input"
                                placeholder="RUC {{ $i }}" value="{{ $fechas[$i] ?? '' }}" required>
                        @endfor
                    </div>
                </div>

                {{-- Estado --}}
                <div class="mt-4 hidden">
                    <x-input-label for="estado" :value="__('Estado')" />
                    <select name="estado" id="estado" class="w-full border rounded">
                        <option value="1" {{ $calendario->estado ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ !$calendario->estado ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>

                <div class="mt-6 flex justify-end">
                    <x-primary-button>Actualizar</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

{{-- Script para actualizar dinámicamente el label y placeholders --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const mesInput = document.getElementById("mes");
        const fechaInputs = document.querySelectorAll(".fechas-input");
        const fechasLabel = document.getElementById("fechas-label");

        const monthNames = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];

        function actualizarVista(mesValue) {
            if (!mesValue) return;

            let [year, month] = mesValue.split("-").map(Number);

            // Calcular siguiente mes
            month += 1;
            if (month > 12) {
                month = 1;
                year++;
            }

            const nombreMes = monthNames[month - 1];

            // Actualizar el label dinámicamente
            fechasLabel.textContent = `Fechas de Vencimiento (${nombreMes} ${year})`;

            // Actualizar placeholders
            fechaInputs.forEach((input, index) => {
                input.placeholder = `RUC ${index} - dd/${month.toString().padStart(2, "0")}/${year}`;
            });
        }

        // Al cargar la página (para mostrarlo con el valor guardado)
        actualizarVista(mesInput.value);

        // Al cambiar el input mes
        mesInput.addEventListener("change", function() {
            actualizarVista(this.value);
        });
    });
</script>
