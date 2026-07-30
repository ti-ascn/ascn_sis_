<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuevo Calendario') }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <x-success-error-bagdes />
        <div class="bg-white shadow-lg rounded-lg p-6">
            <form method="POST" action="{{ route('calendario.store') }}">
                @csrf

                <div>
                    <x-input-label for="tipo" :value="__('Tipo')" />
                    <select name="tipo" id="tipo" class="w-full border rounded">
                        <option value="PDT">PDT</option>
                        <option value="SIRE">SIRE</option>
                    </select>
                </div>

                <div class="mt-4">
                    <x-input-label for="mes" :value="__('Período')" />
                    <x-text-input id="mes" type="month" name="mes" class="w-full" required />
                </div>

                <div class="mt-4">
                    {{-- Aquí se mostrará dinámicamente el nombre del siguiente mes --}}
                    <x-input-label id="fechas-label" for="fechas" :value="__('Fechas de Vencimiento (0-9)')" />

                    <div class="grid grid-cols-5 gap-2 mt-2">
                        @for ($i = 0; $i < 10; $i++)
                            <input type="number" name="fechas[]" class="border rounded p-1 fechas-input"
                                placeholder="RUC {{ $i }}" required>
                        @endfor
                    </div>
                </div>

                <div class="mt-4 hidden">
                    <x-input-label for="estado" :value="__('Estado')" />
                    <select name="estado" id="estado" class="w-full border rounded">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>

                <div class="mt-6 flex justify-end">
                    <x-primary-button>Guardar</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
{{-- Script para actualizar los placeholders con mes seleccionado --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const mesInput = document.getElementById("mes");
        const fechaInputs = document.querySelectorAll(".fechas-input");
        const fechasLabel = document.getElementById("fechas-label");

        const monthNames = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];

        mesInput.addEventListener("change", function() {
            if (!this.value) return;

            let [year, month] = this.value.split("-").map(Number);

            // Calcular siguiente mes
            month += 1;
            if (month > 12) {
                month = 1;
                year++;
            }

            const nombreMes = monthNames[month - 1];

            // Actualizar el label dinámicamente
            fechasLabel.textContent = `Fechas de Vencimiento (${nombreMes} ${year})`;

            // Actualizar placeholders de inputs
            fechaInputs.forEach((input, index) => {
                input.placeholder =
                    `RUC ${index} - dd/${month.toString().padStart(2, "0")}/${year}`;
            });
        });
    });
</script>
