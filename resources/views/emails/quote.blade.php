<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @php
        $partnerEntity = $quote->partnerEntity ?? $quote->partner->defaultEntity;
        $brandColor = $partnerEntity->brand_color ?? '#1F4C94';
        $companyName = $partnerEntity->razon_social ?? $quote->partner->name;
    @endphp
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: {{ $brandColor }};
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header img {
            max-height: 60px;
            margin-bottom: 10px;
        }
        .content {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .quote-info {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid {{ $brandColor }};
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($partnerEntity && $partnerEntity->logo_path)
                <img src="{{ asset('storage/' . $partnerEntity->logo_path) }}" alt="{{ $companyName }}">
            @endif
            <h1>{{ strtoupper($companyName) }}</h1>
            <p>Cotización de Productos Promocionales</p>
        </div>

        <div class="content">
            <h2>Estimado Cliente,</h2>

            <p>Adjunto encontrará la cotización <strong>{{ $quote->quote_number }}</strong> con los productos solicitados.</p>

            @if($customMessage)
                <div class="quote-info">
                    <p><strong>Mensaje:</strong></p>
                    <p>{{ $customMessage }}</p>
                </div>
            @endif

            <div class="quote-info">
                <p><strong>Número de Cotización:</strong> {{ $quote->quote_number }}</p>
                <p><strong>Fecha:</strong> {{ $quote->created_at->format('d/m/Y') }}</p>
                @if($quote->valid_until)
                    <p><strong>Válida hasta:</strong> {{ $quote->valid_until->format('d/m/Y') }}</p>
                @endif
                <p><strong>Total de Items:</strong> {{ $quote->items->count() }}</p>
                @if($quote->is_urgent && $quote->urgency_fee > 0)
                    <p style="color: #e67e22;"><strong>Cargo por urgencia ({{ number_format($quote->urgency_percentage, 0) }}%):</strong> ${{ number_format($quote->urgency_fee, 2) }}</p>
                @endif
                <p><strong>Total:</strong> <span style="font-size: 18px; color: {{ $brandColor }};">${{ number_format($quote->total, 2) }}</span></p>
            </div>

            <p>El documento PDF adjunto contiene el detalle completo de todos los productos cotizados.</p>

            @if($partnerEntity && $partnerEntity->payment_terms)
            <div class="quote-info" style="margin-top: 20px;">
                <p><strong>CONDICIONES DE PAGO</strong></p>
                {!! nl2br(e($partnerEntity->payment_terms)) !!}
            </div>
            @endif

            @if($partnerEntity && $partnerEntity->bankAccounts->count() > 0)
            @php
                $bankAccount = $partnerEntity->getMainBankAccount();
                $usdBankAccount = $partnerEntity->getUsdBankAccount();
            @endphp
            @if($bankAccount)
            <div class="quote-info" style="margin-top: 20px;">
                <p><strong>DATOS PARA REALIZAR PAGO</strong></p>
                <p><strong>BENEFICIARIO:</strong> {{ $bankAccount->account_holder ?: $partnerEntity->razon_social }}</p>
                @if($partnerEntity->rfc)
                <p><strong>RFC:</strong> {{ $partnerEntity->rfc }}</p>
                @endif
                <p><strong>BANCO:</strong> {{ $bankAccount->bank_name }}</p>
                @if($bankAccount->account_number)
                <p><strong>CUENTA:</strong> {{ $bankAccount->account_number }}</p>
                @endif
                @if($bankAccount->card_number)
                <p><strong>NÚMERO DE TARJETA:</strong> {{ $bankAccount->card_number }}</p>
                @endif
                @if($bankAccount->clabe)
                <p><strong>CLABE:</strong> {{ $bankAccount->clabe }}</p>
                @endif
            </div>

            {{-- Cuenta en Dólares (USD) --}}
            @if($usdBankAccount)
            <div class="quote-info" style="margin-top: 20px; border-left-color: #28a745;">
                <p><strong>CUENTA EN DÓLARES (USD)</strong></p>
                <p><strong>BENEFICIARIO:</strong> {{ $usdBankAccount->account_holder ?: $partnerEntity->razon_social }}</p>
                <p><strong>BANCO:</strong> {{ $usdBankAccount->bank_name }}</p>
                @if($usdBankAccount->account_number)
                <p><strong>CUENTA:</strong> {{ $usdBankAccount->account_number }}</p>
                @endif
                @if($usdBankAccount->clabe)
                <p><strong>CLABE:</strong> {{ $usdBankAccount->clabe }}</p>
                @endif
                @if($usdBankAccount->swift)
                <p><strong>SWIFT:</strong> {{ $usdBankAccount->swift }}</p>
                @endif
                @if($usdBankAccount->iban)
                <p><strong>IBAN:</strong> {{ $usdBankAccount->iban }}</p>
                @endif
            </div>
            @endif
            @endif
            @endif

            <p>Si tiene alguna pregunta o necesita realizar algún cambio, no dude en contactarnos.</p>

            <p>Gracias por su preferencia.</p>
        </div>

        <div class="footer">
            <p><strong>{{ $companyName }}</strong></p>
            @if($partnerEntity && $partnerEntity->correo_contacto)
                <p>Email: {{ $partnerEntity->correo_contacto }}</p>
            @elseif($quote->partner->contact_email)
                <p>Email: {{ $quote->partner->contact_email }}</p>
            @endif
            @if($partnerEntity && $partnerEntity->telefono)
                <p>Teléfono: {{ $partnerEntity->telefono }}</p>
            @elseif($quote->partner->contact_phone)
                <p>Teléfono: {{ $quote->partner->contact_phone }}</p>
            @endif
            @if($partnerEntity && $partnerEntity->direccion)
                <p>{{ $partnerEntity->direccion }}</p>
            @endif
            <p style="font-size: 10px; color: #999; margin-top: 20px;">
                Este es un correo automático, por favor no responda directamente a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>
