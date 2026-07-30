<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Área') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('areas.store') }}" autocomplete="off" class="space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="nombre" :value="__('Nombre')" />
                        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" required
                            autofocus />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="id_user" :value="__('Usuario Responsable')" />
                        <select id="id_user" name="id_user"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            {{ $users->isEmpty() ? 'disabled' : '' }}>
                            @if ($users->isNotEmpty())
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            @else
                                <option value="">{{ __('No hay usuarios disponibles') }}</option>
                            @endif
                        </select>
                        <x-input-error :messages="$errors->get('id_user')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="estado" :value="__('Estado')" />
                        <select id="estado" name="estado"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                        <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Guardar') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
