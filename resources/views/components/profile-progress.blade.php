@props(['partner'])

@php
    $percentage = $partner->profileCompletionPercentage();
    $missing = $partner->missingProfileFields();
    $isComplete = $percentage === 100;

    $barColor = match (true) {
        $percentage === 100 => 'bg-green-500',
        $percentage >= 60 => 'bg-blue-500',
        $percentage >= 30 => 'bg-yellow-500',
        default => 'bg-red-500',
    };

    $textColor = match (true) {
        $percentage === 100 => 'text-green-700',
        $percentage >= 60 => 'text-blue-700',
        $percentage >= 30 => 'text-yellow-700',
        default => 'text-red-700',
    };

    $blockLabels = [
        'fiscal' => 'Datos fiscales (RFC, razón social, dirección, teléfono)',
        'bank' => 'Datos bancarios (banco, titular, CLABE)',
        'contact' => 'Datos de contacto (nombre, teléfono, correo, dirección)',
        'logo' => 'Logo de la empresa',
    ];

    $blockUrls = [
        'fiscal' => route('my-entities.index'),
        'bank' => route('my-bank-accounts.index'),
        'logo' => route('my-website.edit'),
    ];
@endphp

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Completitud de tu perfil
        </h3>
        <span class="text-2xl font-bold {{ $textColor }}">{{ $percentage }}%</span>
    </div>

    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-4">
        <div class="{{ $barColor }} h-3 rounded-full transition-all duration-500"
             style="width: {{ $percentage }}%"></div>
    </div>

    @if ($isComplete)
        <p class="text-sm text-green-700 dark:text-green-400">
            ¡Perfil completo! Estás recibiendo el descuento de distribuidor y acumulando comisión.
        </p>
    @else
        <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded p-3 mb-3">
            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                ⚠️ Mientras tu perfil esté incompleto cotizas a precio público y no acumulas comisión.
            </p>
        </div>

        <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">Te falta:</p>
        <ul class="space-y-2">
            @foreach ($missing as $block => $fields)
                @if (! empty($fields))
                    <li class="flex items-center justify-between text-sm">
                        <span class="text-gray-700 dark:text-gray-300">
                            • {{ $blockLabels[$block] }}
                        </span>
                        @if (isset($blockUrls[$block]))
                            <a href="{{ $blockUrls[$block] }}"
                               class="ml-3 inline-flex items-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded transition">
                                Completar
                            </a>
                        @else
                            <span class="ml-3 text-xs text-gray-500 dark:text-gray-400">
                                (Pide a tu administrador completar el alta)
                            </span>
                        @endif
                    </li>
                @endif
            @endforeach
        </ul>
    @endif
</div>
