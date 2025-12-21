@extends('layouts.app')
@section('title', 'Partner - ' . $partner->name)

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5>{{ $partner->name }}</h5>
            <p class="text-muted mb-0">Información del Partner</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('partners.edit', $partner) }}" class="btn btn-warning">
                <i class="feather icon-edit"></i> Editar
            </a>
            <a href="{{ route('partners.index') }}" class="btn btn-secondary">
                <i class="feather icon-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<!-- Información General -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Información General</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Nombre:</th>
                        <td>{{ $partner->name }}</td>
                    </tr>
                    <tr>
                        <th>Tipo:</th>
                        <td><span class="badge bg-secondary">{{ $partner->type }}</span></td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>
                            @if($partner->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Contacto:</th>
                        <td>{{ $partner->contact_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $partner->contact_email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td>{{ $partner->contact_phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Dirección:</th>
                        <td>{{ $partner->direccion ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Estadísticas</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Usuarios:</th>
                        <td><span class="badge bg-info">{{ $stats['users'] }}</span></td>
                    </tr>
                    <tr>
                        <th>Clientes:</th>
                        <td><span class="badge bg-info">{{ $stats['clients'] }}</span></td>
                    </tr>
                    <tr>
                        <th>Productos:</th>
                        <td><span class="badge bg-info">{{ $stats['products'] }}</span></td>
                    </tr>
                    <tr>
                        <th>Entidades:</th>
                        <td><span class="badge bg-info">{{ $stats['entities'] }}</span></td>
                    </tr>
                    <tr>
                        <th>Almacenes:</th>
                        <td><span class="badge bg-info">{{ $stats['warehouses'] }}</span></td>
                    </tr>
                    <tr>
                        <th>Cotizaciones:</th>
                        <td><span class="badge bg-info">{{ $stats['quotes'] }}</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Pricing -->
@if($partner->pricing)
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">Configuración de Pricing</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <p class="mb-2"><strong>Markup:</strong> {{ number_format($partner->pricing->markup_percentage, 2) }}%</p>
            </div>
            <div class="col-md-3">
                <p class="mb-2">
                    <strong>Nivel Actual:</strong> 
                    @if($partner->pricing->currentTier)
                        <span class="badge bg-success">{{ $partner->pricing->currentTier->name }}</span>
                    @else
                        <span class="badge bg-secondary">Sin nivel</span>
                    @endif
                </p>
            </div>
            <div class="col-md-3">
                <p class="mb-2"><strong>Compras Mes Actual:</strong> ${{ number_format($partner->pricing->current_month_purchases, 2) }}</p>
            </div>
            <div class="col-md-3">
                <a href="{{ route('partner-pricing.edit', $partner) }}" class="btn btn-sm btn-primary">
                    <i class="feather icon-settings"></i> Configurar Pricing
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<!-- API del Catálogo -->
@if($partner->isAsociadoOMixto())
<div class="card mt-3" id="api-section">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">API del Catálogo</h6>
        @if($partner->api_key)
            <span class="badge bg-success">Activa</span>
        @else
            <span class="badge bg-secondary">No configurada</span>
        @endif
    </div>
    <div class="card-body">
        @if($partner->api_key)
            <div class="alert alert-info mb-3">
                <strong>API Key:</strong>
                <code id="api-key-display" class="user-select-all">{{ $partner->api_key }}</code>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="copyApiKey()">
                    <i class="feather icon-copy"></i> Copiar
                </button>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <form action="{{ route('partners.api-settings', $partner) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="api_show_prices"
                                   name="api_show_prices" value="1"
                                   {{ $partner->api_show_prices ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <label class="form-check-label" for="api_show_prices">Mostrar precios en el catálogo</label>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mb-3">
                <strong>URL de la API:</strong>
                <code class="user-select-all">{{ url('/api/public/catalog') }}</code>
            </div>

            <div class="mb-3">
                <strong>Endpoints disponibles:</strong>
                <ul class="mb-0 mt-2">
                    <li><code>GET /api/public/catalog/info</code> - Información del partner</li>
                    <li><code>GET /api/public/catalog/categories</code> - Lista de categorías</li>
                    <li><code>GET /api/public/catalog/products</code> - Lista de productos (paginado)</li>
                    <li><code>GET /api/public/catalog/products/{id}</code> - Detalle de producto</li>
                </ul>
            </div>

            <div class="mb-3">
                <button type="button" class="btn btn-outline-primary" id="toggleWidgetCode">
                    <i class="feather icon-code me-2"></i> Ver código del Widget para insertar en su sitio web
                </button>
            </div>

            <div id="widgetCodeContainer" style="display: none;">
                <div class="card bg-light">
                    <div class="card-body">
                        <pre class="bg-dark text-light p-3 rounded mb-2" style="font-size: 12px; overflow-x: auto;"><code>&lt;!-- Contenedor del catálogo --&gt;
&lt;div id="printec-catalog"&gt;&lt;/div&gt;

&lt;!-- Script del widget --&gt;
&lt;script src="{{ url('/js/printec-catalog-widget.js') }}"&gt;&lt;/script&gt;
&lt;script&gt;
  PrintecCatalog.init({
    apiKey: '{{ $partner->api_key }}',
    apiUrl: '{{ url('/api/public/catalog') }}',
    container: '#printec-catalog',
    perPage: 12,
    showSearch: true,
    showCategories: true,
    primaryColor: '#007bff',
    language: 'es'
  });
&lt;/script&gt;</code></pre>
                        <button type="button" class="btn btn-sm btn-primary" onclick="copyWidgetCode()">
                            <i class="feather icon-copy"></i> Copiar código
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary ms-2" id="hideWidgetCode">
                            <i class="feather icon-x"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <form action="{{ route('partners.generate-api-key', $partner) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('¿Regenerar la API key? La anterior dejará de funcionar.')">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="feather icon-refresh-cw"></i> Regenerar API Key
                    </button>
                </form>
                <form action="{{ route('partners.revoke-api-key', $partner) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('¿Revocar la API key? El catálogo dejará de funcionar en el sitio del partner.')">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="feather icon-x-circle"></i> Revocar API Key
                    </button>
                </form>
            </div>
        @else
            <p class="text-muted mb-3">Este partner aún no tiene una API key configurada para mostrar el catálogo en su sitio web.</p>
            <form action="{{ route('partners.generate-api-key', $partner) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="feather icon-key"></i> Generar API Key
                </button>
            </form>
        @endif
    </div>
</div>
@endif

<!-- Información Adicional -->
<div class="card mt-3">
    <div class="card-body">
        <h6 class="mb-3">Información Adicional</h6>
        <div class="row">
            <div class="col-md-6">
                <p class="mb-2"><strong>Fecha de creación:</strong> {{ $partner->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-2"><strong>Última actualización:</strong> {{ $partner->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        @if($partner->commercial_terms)
        <div class="mt-3">
            <p class="mb-1"><strong>Términos Comerciales:</strong></p>
            <p class="text-muted">{{ $partner->commercial_terms }}</p>
        </div>
        @endif
        @if($partner->comments)
        <div class="mt-3">
            <p class="mb-1"><strong>Comentarios:</strong></p>
            <p class="text-muted">{{ $partner->comments }}</p>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleWidgetCode');
    const hideBtn = document.getElementById('hideWidgetCode');
    const container = document.getElementById('widgetCodeContainer');

    if (toggleBtn && container) {
        toggleBtn.addEventListener('click', function() {
            if (container.style.display === 'none') {
                container.style.display = 'block';
                toggleBtn.innerHTML = '<i class="feather icon-code me-2"></i> Ocultar código del Widget';
            } else {
                container.style.display = 'none';
                toggleBtn.innerHTML = '<i class="feather icon-code me-2"></i> Ver código del Widget para insertar en su sitio web';
            }
        });
    }

    if (hideBtn && container) {
        hideBtn.addEventListener('click', function() {
            container.style.display = 'none';
            if (toggleBtn) {
                toggleBtn.innerHTML = '<i class="feather icon-code me-2"></i> Ver código del Widget para insertar en su sitio web';
            }
        });
    }
});

function copyApiKey() {
    const apiKey = document.getElementById('api-key-display').textContent;
    navigator.clipboard.writeText(apiKey).then(() => {
        swal("¡Copiado!", "API Key copiada al portapapeles", "success");
    });
}

function copyWidgetCode() {
    const code = `<!-- Contenedor del catálogo -->
<div id="printec-catalog"></div>

<!-- Script del widget -->
<script src="{{ url('/js/printec-catalog-widget.js') }}"><\/script>
<script>
  PrintecCatalog.init({
    apiKey: '{{ $partner->api_key ?? '' }}',
    apiUrl: '{{ url('/api/public/catalog') }}',
    container: '#printec-catalog',
    perPage: 12,
    showSearch: true,
    showCategories: true,
    primaryColor: '#007bff',
    language: 'es'
  });
<\/script>`;
    navigator.clipboard.writeText(code).then(() => {
        swal("¡Copiado!", "Código del widget copiado al portapapeles", "success");
    });
}
</script>
@endsection