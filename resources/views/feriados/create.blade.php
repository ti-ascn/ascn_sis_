<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Nuevo Feriado</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <form method="POST" action="{{ route('feriados.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label value="Fecha del feriado" />
                        <x-text-input type="date" name="fecha_feriado" class="w-full" required />
                    </div>

                    <div>
                        <x-input-label value="Nombre del feriado" />
                        <x-text-input name="nombre_feriado" class="w-full" required />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
