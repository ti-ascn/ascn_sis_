@if (session('success'))
    <div id="success-message"
        class="flash-message mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-300 dark:border-green-800 rounded-md transition-all duration-500 ease-in-out transform hover:shadow-md cursor-pointer relative pr-10">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span>{{ session('success') }}</span>
            </div>
            <button
                onclick="this.parentElement.parentElement.classList.add('opacity-0', 'h-0', 'py-0', 'mb-0', 'border-0'); setTimeout(() => this.parentElement.parentElement.remove(), 500)"
                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 text-lg font-bold">
                ×
            </button>
        </div>
        <div class="flash-progress h-1 bg-green-300 dark:bg-green-800 absolute bottom-0 left-0 w-full">
            <div class="h-full bg-green-500 progress-bar"></div>
        </div>
    </div>
@endif

@if (session('error') || $errors->any())
    <div id="error-message"
        class="flash-message mb-4 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-300 dark:border-red-800 rounded-md transition-all duration-500 ease-in-out transform hover:shadow-md cursor-pointer relative pr-10">
        <div class="flex flex-col space-y-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span>{{ session('error') ?? 'Se encontraron errores de validación:' }}</span>
                </div>
                <button
                    onclick="this.parentElement.parentElement.parentElement.classList.add('opacity-0', 'h-0', 'py-0', 'mb-0', 'border-0'); setTimeout(() => this.parentElement.parentElement.parentElement.remove(), 500)"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 text-lg font-bold">
                    ×
                </button>
            </div>
            @if ($errors->any())
                <ul class="list-disc list-inside text-sm mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="flash-progress h-1 bg-red-300 dark:bg-red-800 absolute bottom-0 left-0 w-full">
            <div class="h-full bg-red-500 progress-bar"></div>
        </div>
    </div>
@endif
