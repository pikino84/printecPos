<x-mail::message>
# Tu cuenta ha sido suspendida

Hola {{ $partner->contact_name ?? $partner->name }},

No completaste tu perfil dentro del plazo de 15 días desde el alta. Tu cuenta queda
suspendida hasta el **{{ $vetoedUntil?->format('d/m/Y') }}**.

Durante este periodo no podrás iniciar sesión ni cotizar a través de Printec.

## ¿Qué hago si fue un error?
Responde a este correo o contacta a tu ejecutivo de cuenta para revisar tu caso.

Gracias,
{{ config('app.name') }}
</x-mail::message>
