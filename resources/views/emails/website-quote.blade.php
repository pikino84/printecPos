<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #1F4C94;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .badge-website {
            display: inline-block;
            background-color: #e67e22;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .content {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #1F4C94;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .product-table th {
            background-color: #1F4C94;
            color: white;
            padding: 8px 12px;
            text-align: left;
            font-size: 13px;
        }
        .product-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 13px;
        }
        .btn-link {
            display: inline-block;
            background-color: #1F4C94;
            color: white;
            padding: 10px 24px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
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
            <span class="badge-website">DESDE SITIO WEB</span>
            <h1 style="margin: 10px 0 5px;">Nueva Solicitud de Cotización</h1>
            <p style="margin: 0;">{{ $quote->quote_number }}</p>
        </div>

        <div class="content">
            <div class="info-box">
                <h3 style="margin-top: 0;">Datos del Cliente</h3>
                <p><strong>Nombre:</strong> {{ $quote->client->nombre_completo }}</p>
                <p><strong>Email:</strong> {{ $quote->client->email }}</p>
                @if($quote->client->telefono)
                    <p><strong>Teléfono:</strong> {{ $quote->client->telefono }}</p>
                @endif
            </div>

            @if($quote->customer_notes)
                <div class="info-box" style="border-left-color: #e67e22;">
                    <h3 style="margin-top: 0;">Comentarios del Cliente</h3>
                    <p>{{ $quote->customer_notes }}</p>
                </div>
            @endif

            <h3>Productos Solicitados</h3>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quote->items as $item)
                        <tr>
                            <td>
                                {{ $item->variant->product->name ?? 'Producto' }}
                                @if($item->variant->color_name)
                                    <br><small style="color: #6c757d;">{{ $item->variant->color_name }}</small>
                                @endif
                            </td>
                            <td>{{ $item->variant->sku ?? '-' }}</td>
                            <td>{{ $item->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p><strong>Total de productos:</strong> {{ $quote->items->sum('quantity') }} unidades</p>

            <div style="text-align: center;">
                <a href="{{ url('/quotes/' . $quote->id) }}" class="btn-link">
                    Ver Cotización en el Sistema
                </a>
            </div>
        </div>

        <div class="footer">
            <p>Este correo fue generado automáticamente desde el sitio web printec.mx</p>
            <p>{{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
