<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cotización {{ $quote->quote_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #1F4C94;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #1F4C94;
            font-size: 24px;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .header p {
            margin: 5px 0;
        }
        .company-info {
            float: left;
            width: 50%;
            background-color: #1F4C94;
            padding: 15px;
            border-radius: 5px;
            color: white;
        }
        .company-info p {
            color: white;
        }
        .quote-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .section-title {
            background-color: #f8f9fa;
            padding: 8px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: bold;
            border-left: 4px solid #1F4C94;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background-color: #1F4C94;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            margin-top: 20px;
            float: right;
            width: 40%;
        }
        .totals table {
            margin-top: 0;
        }
        .totals table td {
            border: none;
            padding: 5px;
        }
        .total-row {
            background-color: #1F4C94 !important;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }
        .notes {
            background-color: #fff3cd;
            padding: 10px;
            margin-top: 20px;
            border-left: 4px solid #ffc107;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-draft { background-color: #6c757d; color: white; }
        .status-sent { background-color: #1F4C94; color: white; }
        .status-accepted { background-color: #28a745; color: white; }
        .status-rejected { background-color: #dc3545; color: white; }
        .status-expired { background-color: #ffc107; color: #000; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header clearfix">
        <div class="company-info">
            <img src="{{ public_path('images/logo_printec_white.png') }}" class="logo" alt="Printec">
            @if($quote->partner->defaultEntity)
                <p><strong>{{ $quote->partner->defaultEntity->razon_social }}</strong></p>
                @if($quote->partner->defaultEntity->rfc)
                    <p>RFC: {{ $quote->partner->defaultEntity->rfc }}</p>
                @endif
                @if($quote->partner->defaultEntity->direccion)
                    <p>{{ $quote->partner->defaultEntity->direccion }}</p>
                @endif
                @if($quote->partner->defaultEntity->telefono)
                    <p>Tel: {{ $quote->partner->defaultEntity->telefono }}</p>
                @endif
                @if($quote->partner->defaultEntity->correo_contacto)
                    <p>Email: {{ $quote->partner->defaultEntity->correo_contacto }}</p>
                @endif
            @else
                <p>{{ $quote->partner->name }}</p>
                @if($quote->partner->contact_email)
                    <p>Email: {{ $quote->partner->contact_email }}</p>
                @endif
                @if($quote->partner->contact_phone)
                    <p>Tel: {{ $quote->partner->contact_phone }}</p>
                @endif
            @endif
        </div>
        <div class="quote-info">
            <h2>COTIZACIÓN</h2>
            <p><strong>{{ $quote->quote_number }}</strong></p>
            <p>Fecha: {{ $quote->created_at->format('d/m/Y') }}</p>
            @if($quote->valid_until)
                <p>Válida hasta: {{ $quote->valid_until->format('d/m/Y') }}</p>
            @endif
            <p>
                @if($quote->status === 'draft')
                    <span class="status-badge status-draft">BORRADOR</span>
                @elseif($quote->status === 'sent')
                    <span class="status-badge status-sent">ENVIADA</span>
                @elseif($quote->status === 'accepted')
                    <span class="status-badge status-accepted">ACEPTADA</span>
                @elseif($quote->status === 'rejected')
                    <span class="status-badge status-rejected">RECHAZADA</span>
                @elseif($quote->status === 'expired')
                    <span class="status-badge status-expired">EXPIRADA</span>
                @endif
            </p>
        </div>
    </div>

    <!-- Customer Notes -->
    @if($quote->customer_notes)
        <div class="notes">
            <strong>Comentarios:</strong><br>
            {{ $quote->customer_notes }}
        </div>
    @endif

    <!-- Items Table -->
    <div class="section-title">PRODUCTOS</div>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Imagen</th>
                <th style="width: 10%;">SKU</th>
                <th style="width: 32%;">Descripción</th>
                <th style="width: 12%;">Almacén</th>
                <th style="width: 10%;" class="text-center">Cantidad</th>
                <th style="width: 13%;" class="text-right">P. Unitario</th>
                <th style="width: 15%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
                <tr>
                    <td class="text-center">
                        @if($item->variant->image_url)
                            <img src="{{ public_path(str_replace('/storage', 'storage', $item->variant->image_url)) }}" class="product-image" alt="">
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $item->variant->sku }}</td>
                    <td>
                        <strong>{{ $item->product->name }}</strong>
                        @if($item->variant->color_name)
                            <br><small>Color: {{ $item->variant->color_name }}</small>
                        @endif
                    </td>
                    <td>
                        @if($item->warehouse)
                            {{ $item->warehouse->nickname ?? $item->warehouse->name }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals clearfix">
        <table>
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-right">${{ number_format($quote->subtotal, 2) }}</td>
            </tr>
            @if($quote->tax > 0)
                <tr>
                    <td>IVA (16%):</td>
                    <td class="text-right">${{ number_format($quote->tax, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td><strong>TOTAL:</strong></td>
                <td class="text-right"><strong>${{ number_format($quote->total, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <!-- Footer -->
    <div class="footer">
        <p>Cotización generada el {{ now()->format('d/m/Y H:i') }}</p>
        <p>Esta cotización es válida únicamente por el período especificado.</p>
        <p><strong>Printec</strong> - Soluciones Promocionales</p>
    </div>
</body>
</html>