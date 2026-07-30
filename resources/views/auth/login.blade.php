<x-guest-layout>
    <div class="flex h-screen overflow-hidden" x-data="{
        showPassword: false,
        loading: false,
        email: '{{ old('email') }}',
        password: '',
        emailBlurred: false,
        passwordBlurred: false,
        get isEmailValid() {
            return /^\S+@\S+\.\S+$/.test(this.email);
        },
        get canSubmit() {
            return this.email && this.isEmailValid && this.password;
        }
    }">
        <!-- Left Side: Brand Visual (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 h-full relative bg-[#111] items-center justify-center overflow-hidden">
            <!-- Animated Background Mesh -->
            <div class="absolute inset-0 z-0">
                <div
                    class="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] bg-[#c9a227]/20 blur-[120px] rounded-full animate-pulse">
                </div>
                <div
                    class="absolute bottom-[-10%] left-[-10%] w-[50%] h-[50%] bg-[#c9a227]/10 blur-[120px] rounded-full">
                </div>
            </div>

            <div class="relative z-10 text-center px-12">
                <img src="{{ asset('img/asecon-logo-png-transparente-grande.png') }}" alt="ASECON"
                    class="w-64 h-auto mx-auto mb-8 drop-shadow-[0_0_30px_rgba(201,162,39,0.3)] opacity-90">
                <h2 class="text-4xl font-light text-white tracking-[0.2em] uppercase mb-4">Excelencia</h2>
                <div class="h-[1px] w-24 bg-[#c9a227] mx-auto mb-4"></div>
                <p class="text-amber-100/60 font-medium tracking-widest text-sm uppercase">Asesoría y Consultoría</p>
            </div>

            <!-- Footer for brand side -->
            <div class="absolute bottom-10 left-12 text-white/20 text-xs tracking-widest font-bold uppercase">
                Est. 2023 &copy; ASECON
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full lg:w-1/2 h-full flex items-center justify-center p-8 sm:p-16 bg-white overflow-y-auto lg:overflow-hidden">
            <div class="w-full max-w-md py-8">
                <!-- Simple Logo (No Borders) -->
                <div class="mb-8 text-center lg:text-left">
                    <img src="{{ asset('img/ASECON-HORIZONTAL.png') }}" alt="ASECON"
                        class="h-14 sm:h-12 w-auto object-contain mx-auto lg:mx-0">
                </div>

                <div class="mb-10 text-center lg:text-left">
                    <h1 class="text-4xl font-bold text-gray-900 mb-3 tracking-tight">Iniciar Sesión</h1>
                    {{-- <p class="text-gray-500 text-lg font-medium">Sistema web.</p> --}}
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-8"
                    @submit="if(canSubmit) loading = true">
                    @csrf

                    <!-- Email -->
                    <div class="space-y-3">
                        <label for="email"
                            class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Email</label>
                        <div class="relative">
                            <input id="email" x-model="email" @blur="emailBlurred = true"
                                class="w-full px-0 py-4 bg-transparent border-0 border-b-2 border-gray-100 focus:border-[#c9a227] focus:ring-0 transition-all duration-300 text-lg font-medium placeholder:text-gray-200"
                                type="email" name="email" placeholder="ejemplo@asecon.com" required autofocus />
                            <!-- Real-time validation icon -->
                            <div class="absolute right-0 top-1/2 -translate-y-1/2" x-show="isEmailValid" x-transition>
                                <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
                            </div>
                        </div>

                        <!-- Client-side error -->
                        <div x-show="emailBlurred && !email"
                            class="text-red-400 text-xs font-bold flex items-center gap-1 mt-1 px-1 text-red-500">
                            <i class="bi bi-exclamation-circle-fill"></i> El correo es obligatorio
                        </div>
                        <div x-show="emailBlurred && email && !isEmailValid"
                            class="text-red-400 text-xs font-bold flex items-center gap-1 mt-1 px-1 text-red-500">
                            <i class="bi bi-exclamation-circle-fill"></i> Ingresa un formato de correo válido
                        </div>

                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="space-y-3">
                        <label for="password"
                            class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Contraseña</label>
                        <div class="relative">
                            <input id="password" x-model="password" @blur="passwordBlurred = true"
                                :type="showPassword ? 'text' : 'password'"
                                class="w-full px-0 py-4 bg-transparent border-0 border-b-2 border-gray-100 focus:border-[#c9a227] focus:ring-0 transition-all duration-300 text-lg font-medium placeholder:text-gray-200"
                                name="password" placeholder="••••••••" required autocomplete="current-password" />
                            <!-- Password Toggle -->
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-0 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#c9a227] transition-colors focus:outline-none">
                                <i class="bi" :class="showPassword ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                            </button>
                        </div>

                        <!-- Client-side error -->
                        <div x-show="passwordBlurred && !password"
                            class="text-red-400 text-xs font-bold flex items-center gap-1 mt-1 px-1 text-red-500">
                            <i class="bi bi-exclamation-circle-fill"></i> La contraseña es obligatoria
                        </div>

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Action -->
                    <div class="pt-4">
                        <button type="submit" :disabled="loading"
                            class="w-full bg-[#111] hover:bg-[#c9a227] disabled:bg-gray-400 text-white font-black py-5 rounded-xl transition-all duration-300 transform active:scale-[0.98] shadow-xl hover:shadow-[#c9a227]/20 uppercase tracking-widest text-sm flex items-center justify-center gap-3">
                            <template x-if="!loading">
                                <span>ACCEDER</span>
                            </template>
                            <template x-if="loading">
                                <div class="flex items-center gap-3">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span>Verificando...</span>
                                </div>
                            </template>
                        </button>
                    </div>

                    @if (session('error'))
                        <div
                            class="mt-6 p-5 bg-red-50 border-l-4 border-red-500 text-red-700 flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                            <i class="bi bi-exclamation-triangle-fill text-xl"></i>
                            <div class="text-sm font-bold uppercase tracking-tight">
                                {{ session('error') }}
                            </div>
                        </div>
                    @endif
                </form>

                <div class="mt-10 text-center lg:text-left">
                    <p class="text-gray-300 text-xs font-bold uppercase tracking-widest">
                        &copy; {{ date('Y') }} ASECON &bull; Todos los derechos reservados
                    </p>
                </div>
            </div>
        </div>
    </div>
    <style>
        /* Desactivar el color celeste/azul del autocompletado del navegador */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 40px white inset !important;
            -webkit-text-fill-color: #111 !important;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</x-guest-layout>
