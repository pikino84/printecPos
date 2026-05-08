@php
    $blockLabels = [
        'fiscal' => 'Datos fiscales (RFC, razón social, dirección, teléfono)',
        'bank' => 'Datos bancarios (banco, titular, CLABE)',
        'contact' => 'Datos de contacto (nombre, teléfono, correo, dirección)',
        'logo' => 'Logo de la empresa',
    ];
@endphp
<x-mail::message>
# Hola {{ $partner->contact_name ?? $partner->name }}

Tu perfil en Printec todavía no está completo (**{{ $percentage }}%**) y queda
**{{ $daysRemaining }}** {{ $daysRemaining === 1 ? 'día' : 'días' }} para que cumplas el plazo de 15 días.

## Mientras esté incompleto:
- Cotizas a precio público (sin descuento de distribuidor).
- No acumulas comisión por tus ventas.
- Si llegas a la fecha límite sin completarlo, tu cuenta queda suspendida por 1 año.

## Te falta:
@foreach ($missing as $block => $fields)
@if (! empty($fields))
- {{ $blockLabels[$block] }}
@endif
@endforeach

<x-mail::button :url="url('/dashboard')">
Completar mi perfil
</x-mail::button>

Si tienes dudas, responde a este correo.

Gracias,
{{ config('app.name') }}
</x-mail::message>
