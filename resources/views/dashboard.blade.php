<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php
                $user = auth()->user();
                $partner = $user?->partner;
                // Mostrar barra a todos los partners (Asociado/Mixto/Proveedor) excepto Printec.
                // El componente decide internamente si muestra deadline/veto (solo Asociado puro)
                // o si es informativa (Mixto/Proveedor).
                $shouldShowProfileProgress = $partner && ! $user->isPrintec();
            @endphp

            @if ($shouldShowProfileProgress)
                <x-profile-progress :partner="$partner" />
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
