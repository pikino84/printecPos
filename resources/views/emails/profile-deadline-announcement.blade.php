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

Te escribimos para informarte de un cambio importante en Printec.

A partir de ahora, los Asociados tienen un plazo de **15 días** para completar su perfil.
Tu fecha límite es el **{{ $deadlineFormatted }}** (en {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'día' : 'días' }}).

Hoy tu perfil está al **{{ $percentage }}%**.

## ¿Qué pasa si no lo completas a tiempo?
- Cotizarás a precio público (sin descuento de distribuidor).
- No acumularás comisión por tus ventas.
- Si llegas a la fecha límite con perfil incompleto, tu cuenta queda suspendida por un año.

## Te falta:
@foreach ($missing as $block => $fields)
@if (! empty($fields))
- {{ $blockLabels[$block] }}
@endif
@endforeach

<x-mail::button :url="url('/dashboard')">
Completar mi perfil
</x-mail::button>

Si tienes dudas, escríbenos a [vendors@printec.mx](mailto:vendors@printec.mx).

Gracias,
{{ config('app.name') }}
</x-mail::message>
