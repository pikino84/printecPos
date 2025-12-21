<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Factura {{ $invoice->full_folio }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
        }
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #e74c3c;
        }
        .invoice-number {
            font-size: 14px;
            color: #7f8c8d;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        .two-columns {
            display: table;
            width: 100%;
        }
        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }
        .column:last-child {
            padding-right: 0;
            padding-left: 15px;
        }
        .data-row {
            margin-bottom: 3px;
        }
        .data-label {
            color: #7f8c8d;
            display: inline-block;
            width: 80px;
        }
        .data-value {
            font-weight: bold;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.items th {
            background-color: #2c3e50;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
        }
        table.items th.right {
            text-align: right;
        }
        table.items th.center {
            text-align: center;
        }
        table.items td {
            padding: 6px 5px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 9px;
        }
        table.items td.right {
            text-align: right;
        }
        table.items td.center {
            text-align: center;
        }
        .totals {
            margin-top: 20px;
            float: right;
            width: 250px;
        }
        .totals table {
            width: 100%;
        }
        .totals td {
            padding: 5px;
        }
        .totals .total-row {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }
        .uuid-section {
            clear: both;
            margin-top: 30px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .uuid-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .uuid-value {
            font-family: monospace;
            font-size: 11px;
            word-break: break-all;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #7f8c8d;
            border-top: 1px solid #bdc3c7;
            padding-top: 10px;
        }
        .stamp-warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
        }
        .stamp-warning strong {
            color: #856404;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        @if($invoice->isDraft())
        <div class="stamp-warning">
            <strong>DOCUMENTO NO FISCAL - PENDIENTE DE TIMBRADO</strong>
        </div>
        @endif

        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ $invoice->partnerEntity->razon_social }}</div>
                <div>RFC: {{ $invoice->partnerEntity->rfc }}</div>
                <div>Régimen Fiscal: {{ $invoice->partnerEntity->fiscal_regime_label }}</div>
                <div>C.P.: {{ $invoice->partnerEntity->zip_code }}</div>
                @if($invoice->partnerEntity->direccion)
                    <div style="margin-top: 5px; font-size: 9px;">{{ $invoice->partnerEntity->direccion }}</div>
                @endif
            </div>
            <div class="header-right">
                <div class="invoice-title">FACTURA</div>
                <div class="invoice-number">{{ $invoice->full_folio }}</div>
                <div style="margin-top: 10px;">
                    @if($invoice->isStamped())
                        <span class="badge badge-success">TIMBRADA</span>
                    @elseif($invoice->isCancelled())
                        <span class="badge badge-danger">CANCELADA</span>
                    @else
                        <span class="badge badge-warning">BORRADOR</span>
                    @endif
                </div>
                <div style="margin-top: 10px; font-size: 9px;">
                    Fecha: {{ $invoice->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <div class="two-columns section">
            <div class="column">
                <div class="section-title">DATOS DEL RECEPTOR</div>
                <div class="data-row">
                    <span class="data-label">Nombre:</span>
                    <span class="data-value">{{ $invoice->receptor_name }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">RFC:</span>
                    <span class="data-value">{{ $invoice->receptor_rfc }}</span>
                </div>
                @if($invoice->receptor_email)
                <div class="data-row">
                    <span class="data-label">Email:</span>
                    <span class="data-value">{{ $invoice->receptor_email }}</span>
                </div>
                @endif
            </div>
            <div class="column">
                <div class="section-title">DATOS DEL CFDI</div>
                <div class="data-row">
                    <span class="data-label">Tipo:</span>
                    <span class="data-value">{{ $invoice->cfdi_type_label }} ({{ $invoice->cfdi_type }})</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Uso CFDI:</span>
                    <span class="data-value">{{ $invoice->cfdi_use }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Forma Pago:</span>
                    <span class="data-value">{{ $invoice->payment_form }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Método Pago:</span>
                    <span class="data-value">{{ $invoice->payment_method }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">CONCEPTOS</div>
            <table class="items">
                <thead>
                    <tr>
                        <th style="width: 60px;">Clave</th>
                        <th>Descripción</th>
                        <th class="center" style="width: 60px;">Cantidad</th>
                        <th class="center" style="width: 50px;">Unidad</th>
                        <th class="right" style="width: 80px;">P. Unit.</th>
                        <th class="right" style="width: 80px;">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->product_key }}</td>
                        <td>
                            {{ $item->description }}
                            @if($item->sku)
                                <br><small style="color: #7f8c8d;">SKU: {{ $item->sku }}</small>
                            @endif
                        </td>
                        <td class="center">{{ number_format($item->quantity, 2) }}</td>
                        <td class="center">{{ $item->unit_key }}</td>
                        <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="right">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td style="text-align: right;">${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>IVA ({{ $invoice->tax_rate }}%):</td>
                    <td style="text-align: right;">${{ number_format($invoice->tax, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td style="padding: 8px;">TOTAL {{ $invoice->currency }}:</td>
                    <td style="text-align: right; padding: 8px;">${{ number_format($invoice->total, 2) }}</td>
                </tr>
            </table>
        </div>

        @if($invoice->uuid)
        <div class="uuid-section">
            <div class="uuid-label">UUID (Folio Fiscal):</div>
            <div class="uuid-value">{{ $invoice->uuid }}</div>
            @if($invoice->stamped_at)
                <div style="margin-top: 5px; font-size: 9px;">
                    Fecha de Timbrado: {{ $invoice->stamped_at->format('d/m/Y H:i:s') }}
                </div>
            @endif
        </div>
        @endif

        <div class="footer">
            Este documento es una representación impresa de un CFDI<br>
            {{ $invoice->partnerEntity->razon_social }} - RFC: {{ $invoice->partnerEntity->rfc }}
        </div>
    </div>
</body>
</html>
