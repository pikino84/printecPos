<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
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
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .quote-info {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #007bff;
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
            <h1>PRINTEC</h1>
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
                <p><strong>Total:</strong> <span style="font-size: 18px; color: #007bff;">${{ number_format($quote->total, 2) }}</span></p>
            </div>

            <p>El documento PDF adjunto contiene el detalle completo de todos los productos cotizados.</p>

            <p>Si tiene alguna pregunta o necesita realizar algún cambio, no dude en contactarnos.</p>

            <p>Gracias por su preferencia.</p>
        </div>

        <div class="footer">
            <p><strong>Printec</strong></p>
            @if($quote->partner->contact_email)
                <p>Email: {{ $quote->partner->contact_email }}</p>
            @endif
            @if($quote->partner->contact_phone)
                <p>Teléfono: {{ $quote->partner->contact_phone }}</p>
            @endif
            <p style="font-size: 10px; color: #999; margin-top: 20px;">
                Este es un correo automático, por favor no responda directamente a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>