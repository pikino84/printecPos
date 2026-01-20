@extends('layouts.app')

@section('title', 'Importar Carrito')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Carrito</a></li>
                    <li class="breadcrumb-item active">Importar Pedido</li>
                </ol>
            </nav>
            <h4>
                <i class="feather icon-upload"></i> Importar Pedido desde JSON
            </h4>
            <p class="text-muted">Importa un pedido exportado desde el widget del catálogo externo</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="feather icon-alert-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="feather icon-file-text"></i> Archivo de Pedido</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('cart.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf

                        <div class="form-group">
                            <label for="json_file">Selecciona el archivo JSON del pedido</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="json_file" name="json_file" accept=".json,.txt">
                                <label class="custom-file-label" for="json_file">Seleccionar archivo...</label>
                            </div>
                            <small class="form-text text-muted">
                                Acepta archivos .json o .txt exportados desde el widget del catálogo
                            </small>
                        </div>

                        <hr>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="clear_existing" name="clear_existing" value="1" checked>
                                <label class="custom-control-label" for="clear_existing">
                                    Vaciar carrito actual antes de importar
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Si desmarcas esta opción, los productos se agregarán al carrito existente
                            </small>
                        </div>

                        <!-- Preview del JSON -->
                        <div id="jsonPreview" class="d-none">
                            <hr>
                            <h6><i class="feather icon-eye"></i> Vista previa del pedido</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th>Color/SKU</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-right">Precio Unit.</th>
                                            <th class="text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewBody"></tbody>
                                    <tfoot>
                                        <tr class="table-primary">
                                            <th colspan="4" class="text-right">Total:</th>
                                            <th class="text-right" id="previewTotal">$0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div id="previewMeta" class="small text-muted"></div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                <i class="feather icon-download"></i> Importar al Carrito
                            </button>
                            <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary">
                                <i class="feather icon-x"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Instrucciones -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="feather icon-info"></i> Instrucciones</h5>
                </div>
                <div class="card-body">
                    <ol class="pl-3 mb-0">
                        <li class="mb-2">
                            El cliente agrega productos al carrito desde tu sitio web usando el widget
                        </li>
                        <li class="mb-2">
                            El cliente descarga el archivo JSON con su pedido
                        </li>
                        <li class="mb-2">
                            El cliente te envía el archivo JSON por correo
                        </li>
                        <li class="mb-2">
                            Tu subes el archivo aquí para crear el carrito
                        </li>
                        <li class="mb-2">
                            Generas la cotización desde el carrito
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Formato esperado -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="feather icon-code"></i> Formato del JSON</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-2 rounded" style="font-size: 11px; max-height: 300px; overflow: auto;">{
  "version": "1.0",
  "partner_api_key": "tu_api_key",
  "items": [
    {
      "variant_id": 123,
      "name": "Producto XYZ",
      "quantity": 5,
      "unit_price": 150.00
    }
  ],
  "totals": {
    "items_count": 5,
    "subtotal": 750.00
  }
}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('json_file');
    const submitBtn = document.getElementById('submitBtn');
    const previewDiv = document.getElementById('jsonPreview');
    const previewBody = document.getElementById('previewBody');
    const previewTotal = document.getElementById('previewTotal');
    const previewMeta = document.getElementById('previewMeta');

    fileInput.addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'Seleccionar archivo...';
        e.target.nextElementSibling.textContent = fileName;

        if (e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(event) {
                parseAndPreview(event.target.result);
            };
            reader.readAsText(e.target.files[0]);
        } else {
            hidePreview();
        }
    });

    function parseAndPreview(jsonString) {
        try {
            const data = JSON.parse(jsonString);

            if (!data.version || !data.items || !Array.isArray(data.items) || data.items.length === 0) {
                showError('El formato del JSON no es válido');
                return;
            }

            let html = '';
            let total = 0;

            data.items.forEach(function(item) {
                const subtotal = (item.unit_price || 0) * (item.quantity || 0);
                total += subtotal;

                html += `
                    <tr>
                        <td>${escapeHtml(item.name || 'Sin nombre')}</td>
                        <td>${escapeHtml(item.color || item.sku || '-')}</td>
                        <td class="text-center">${item.quantity || 0}</td>
                        <td class="text-right">$${formatNumber(item.unit_price || 0)}</td>
                        <td class="text-right">$${formatNumber(subtotal)}</td>
                    </tr>
                `;
            });

            previewBody.innerHTML = html;
            previewTotal.textContent = '$' + formatNumber(total);

            let metaHtml = `<strong>Items:</strong> ${data.items.length}`;
            if (data.partner_name) {
                metaHtml += ` | <strong>Partner:</strong> ${escapeHtml(data.partner_name)}`;
            }
            if (data.created_at) {
                const date = new Date(data.created_at);
                metaHtml += ` | <strong>Fecha:</strong> ${date.toLocaleDateString('es-MX')}`;
            }
            previewMeta.innerHTML = metaHtml;

            previewDiv.classList.remove('d-none');
            submitBtn.disabled = false;

        } catch (e) {
            showError('Error al leer el JSON: ' + e.message);
        }
    }

    function showError(message) {
        previewBody.innerHTML = `<tr><td colspan="5" class="text-danger text-center">${escapeHtml(message)}</td></tr>`;
        previewTotal.textContent = '$0.00';
        previewMeta.innerHTML = '';
        previewDiv.classList.remove('d-none');
        submitBtn.disabled = true;
    }

    function hidePreview() {
        previewDiv.classList.add('d-none');
        submitBtn.disabled = true;
    }

    function formatNumber(num) {
        return parseFloat(num).toLocaleString('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@endsection
