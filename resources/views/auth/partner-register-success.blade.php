<x-guest-layout>
    <div class="text-center">
        <div class="mb-4">
            <span class="text-5xl">✅</span>
        </div>
        
        <h2 class="text-xl font-bold text-gray-700 dark:text-gray-200 mb-2">
            ¡Registro Exitoso!
        </h2>
        
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Gracias por registrarte como partner de Printec.
        </p>

        <div class="bg-blue-50 dark:bg-gray-700 rounded-lg p-4 mb-4 text-left">
            <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">📧 Revisa tu correo</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Te hemos enviado un email de confirmación con los detalles de tu registro.
            </p>
        </div>

        <div class="bg-yellow-50 dark:bg-gray-700 rounded-lg p-4 mb-6 text-left">
            <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">⏳ ¿Qué sigue?</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Nuestro equipo revisará tu solicitud y activará tu cuenta. Te notificaremos por email cuando puedas iniciar sesión.
            </p>
        </div>

        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Este proceso normalmente toma de 24 a 48 horas hábiles.
        </p>

        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            Ir a Iniciar Sesión
        </a>
    </div>
</x-guest-layout>