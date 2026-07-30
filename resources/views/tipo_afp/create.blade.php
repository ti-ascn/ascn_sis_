<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Nuevo Tipo AFP</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <form method="POST" action="{{ route('tipo-afp.store') }}" class="space-y-6">
                    @csrf
                    <div>
                        <x-input-label value="Nombre" />
                        <x-text-input name="nombre" class="w-full" required />
                    </div>

                    <div>
                        <x-input-label value="Estado" />
                        <select name="estado" class="border-gray-300 rounded-md shadow-sm w-full">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
