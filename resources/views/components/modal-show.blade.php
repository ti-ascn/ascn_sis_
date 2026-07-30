@props([
    'id',
    'title',
    'size' => 'default',
    'closeOnOutsideClick' => false, // Por defecto está deshabilitado
])
{{-- Solo añade "close-on-outside-click" o :close-on-outside-click="true" para que se pueda cerrar haciendo click afuera --}}

@php
    $widthClass = match ($size) {
        'small' => 'w-11/12 md:w-1/2 lg:w-1/3 max-w-lg',
        'large' => 'w-11/12 md:w-5/6 lg:w-4/5 max-w-6xl',
        'full' => 'w-11/12 md:w-[96%] max-w-[96vw]',
        default => 'w-11/12 md:w-2/3 lg:w-1/2 max-w-2xl',
    };
@endphp

<div id="{{ $id }}" data-close-outside="{{ $closeOnOutsideClick ? 'true' : 'false' }}"
    class="fixed inset-0 z-50 hidden overflow-y-auto flex items-center justify-center transition-opacity duration-300 opacity-0 backdrop-blur-sm"
    style="background-color: rgba(0, 0, 0, 0.5);" onclick="closeModal(event, '{{ $id }}')">

    <div class="bg-white dark:bg-[#2a2d35] dark:text-gray-100 rounded-lg shadow-lg {{ $widthClass }} transform transition-all duration-300 scale-95 max-h-[90vh] flex flex-col overflow-hidden border dark:border-gray-700"
        onclick="event.stopPropagation();">

        <div class="px-4 py-2 border-b dark:border-gray-700 flex justify-between items-center shrink-0">
            <h2 class="text-xl font-semibold dark:text-white">{{ $title }}</h2>
            <button onclick="toggleModal('{{ $id }}')"
                class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white focus:outline-none flex items-center justify-center w-8 h-8 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-150"
                aria-label="Cerrar modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="p-4 overflow-y-auto flex-1">
            {{ $slot }}
        </div>
    </div>
</div>

<script>
    // Usamos condiciones seguras para evitar duplicar las funciones si usas múltiples modales
    if (typeof toggleModal !== 'function') {
        window.toggleModal = function(id) {
            const modal = document.getElementById(id);
            if (!modal) return;

            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    modal.querySelector('div').classList.remove('scale-95');
                }, 10);
                document.body.classList.add('overflow-hidden');
            } else {
                modal.classList.add('opacity-0');
                modal.querySelector('div').classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }, 300);
            }
        }
    }

    if (typeof closeModal !== 'function') {
        window.closeModal = function(event, id) {
            const modal = document.getElementById(id);
            if (!modal) return;

            // Evaluamos el atributo data-close-outside que configuramos en Blade
            const allowClose = modal.getAttribute('data-close-outside') === 'true';

            if (event.target === modal && allowClose) {
                toggleModal(id);
            }
        }
    }
</script>
