<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-xl font-bold text-gray-700 dark:text-gray-200">Registro de Partner</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Únete a nuestra red de distribuidores</p>
    </div>

    <form method="POST" action="{{ route('partner.registration.submit') }}">
        @csrf

        <!-- Nombre del Negocio -->
        <div>
            <x-input-label for="business_name" :value="__('Nombre del Negocio / Empresa')" />
            <x-text-input id="business_name" 
                          class="block mt-1 w-full" 
                          type="text" 
                          name="business_name" 
                          :value="old('business_name')" 
                          placeholder="Ej: Grupo Mera"
                          required 
                          autofocus />
            <x-input-error :messages="$errors->get('business_name')" class="mt-2" />
        </div>

        <!-- Nombre de Contacto -->
        <div class="mt-4">
            <x-input-label for="contact_name" :value="__('Tu Nombre Completo')" />
            <x-text-input id="contact_name" 
                          class="block mt-1 w-full" 
                          type="text" 
                          name="contact_name" 
                          :value="old('contact_name')" 
                          placeholder="Ej: Juan Pérez García"
                          required />
            <x-input-error :messages="$errors->get('contact_name')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Correo Electrónico')" />
            <x-text-input id="email" 
                          class="block mt-1 w-full" 
                          type="email" 
                          name="email" 
                          :value="old('email')" 
                          placeholder="tu@email.com"
                          required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input id="password" 
                          class="block mt-1 w-full" 
                          type="password" 
                          name="password"
                          required />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Mínimo 8 caracteres, una mayúscula, una minúscula y un número.
            </p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
            <x-text-input id="password_confirmation" 
                          class="block mt-1 w-full" 
                          type="password" 
                          name="password_confirmation"
                          required />
        </div>

        <!-- reCAPTCHA -->
        <div class="mt-4 flex justify-center">
            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
        </div>
        @error('recaptcha')
            <p class="mt-2 text-sm text-red-600 text-center">{{ $message }}</p>
        @enderror

        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                {{ __('Registrarme como Partner') }}
            </x-primary-button>
        </div>

        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                ¿Ya tienes cuenta? 
                <a href="{{ route('login') }}" class="underline text-indigo-600 hover:text-indigo-900">
                    Inicia sesión aquí
                </a>
            </p>
        </div>
    </form>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</x-guest-layout>