@props(['partner'])

@php
    $percentage = $partner->profileCompletionPercentage();
    $missing = $partner->missingProfileFields();
    $isComplete = $percentage === 100;

    // Solo Asociado puro está sujeto a deadline/veto/pérdida de descuento.
    // Mixto y Proveedor son socios estratégicos: la barra es solo informativa.
    $isAsociado = $partner->isAsociado();
    $daysRemaining = $isAsociado ? $partner->daysUntilDeadline() : null;

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

    $typeBadgeClass = match ($partner->type) {
        'Asociado' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
        'Mixto' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-200',
        'Proveedor' => 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        default => 'bg-gray-200 text-gray-800',
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
        <div class="flex items-center gap-2">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Completitud de tu perfil
            </h3>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $typeBadgeClass }}">
                {{ $partner->type }}
            </span>
        </div>
        <span class="text-2xl font-bold {{ $textColor }}">{{ $percentage }}%</span>
    </div>

    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-4">
        <div class="{{ $barColor }} h-3 rounded-full transition-all duration-500"
             style="width: {{ $percentage }}%"></div>
    </div>

    @if ($isComplete)
        <p class="text-sm text-green-700 dark:text-green-400">
            @if ($isAsociado)
                ¡Perfil completo! Estás recibiendo el descuento de distribuidor y acumulando comisión.
            @else
                ¡Perfil completo! Tu información de empresa está al día.
            @endif
        </p>
    @else
        @if ($isAsociado)
            <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded p-3 mb-3">
                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                    ⚠️ Mientras tu perfil esté incompleto cotizas a precio público y no acumulas comisión.
                </p>
                @if ($daysRemaining !== null)
                    <p class="mt-1 text-xs font-semibold text-yellow-900 dark:text-yellow-100">
                        @if ($daysRemaining > 0)
                            ⏱ Te quedan {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'día' : 'días' }} para completar tu perfil.
                        @else
                            ⏱ El plazo venció hace {{ abs($daysRemaining) }} {{ abs($daysRemaining) === 1 ? 'día' : 'días' }}.
                        @endif
                    </p>
                @endif
            </div>
        @else
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                Mantén tu información de empresa actualizada para tus clientes.
            </p>
        @endif

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
