@extends('layouts.app')
@section('title', 'Configuración de Pricing de Partners')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5>Configuración de Pricing de Partners</h5>
            <p class="text-muted mb-0">Administra el markup y nivel de cada partner</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body">
        <form action="{{ route('partner-pricing.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar Partner</label>
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Nombre del partner...">
            </div>
            
            <div class="col-md-4">
                <label for="tier_id" class="form-label">Filtrar por Nivel</label>
                <select class="form-control" id="tier_id" name="tier_id">
                    <option value="">Todos los niveles</option>
                    @foreach($tiers as $tier)
                        <option value="{{ $tier->id }}" {{ request('tier_id') == $tier->id ? 'selected' : '' }}>
                            {{ $tier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="feather icon-search"></i> Buscar
                </button>
                <a href="{{ route('partner-pricing.index') }}" class="btn btn-secondary">
                    <i class="feather icon-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('partner-pricing.index', array_merge(request()->all(), ['sort' => 'partner', 'direction' => request('sort') == 'partner' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                                Partner
                                @if(request('sort') == 'partner')
                                    <i class="feather icon-chevron-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Tipo</th>
                        <th>
                            <a href="{{ route('partner-pricing.index', array_merge(request()->all(), ['sort' => 'markup', 'direction' => request('sort') == 'markup' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                                Markup
                                @if(request('sort') == 'markup')
                                    <i class="feather icon-chevron-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('partner-pricing.index', array_merge(request()->all(), ['sort' => 'tier', 'direction' => request('sort') == 'tier' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                                Nivel Actual
                                @if(request('sort') == 'tier')
                                    <i class="feather icon-chevron-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('partner-pricing.index', array_merge(request()->all(), ['sort' => 'purchases', 'direction' => request('sort') == 'purchases' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                                Compras Mes Actual
                                @if(request('sort') == 'purchases')
                                    <i class="feather icon-chevron-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Compras Mes Anterior</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pricings as $pricing)
                    <tr>
                        <td>
                            <strong>{{ $pricing->partner->name }}</strong>
                            @if($pricing->manual_tier_override)
                                <span class="badge bg-warning ms-1" title="Nivel asignado manualmente">
                                    <i class="feather icon-lock"></i>
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $pricing->partner->type }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                {{ number_format($pricing->markup_percentage, 2) }}%
                            </span>
                        </td>
                        <td>
                            @if($pricing->currentTier)
                                <span class="badge bg-success">
                                    {{ $pricing->currentTier->name }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($pricing->currentTier->discount_percentage, 2) }}% desc.
                                </small>
                            @else
                                <span class="badge bg-secondary">Sin nivel</span>
                            @endif
                        </td>
                        <td>
                            ${{ number_format($pricing->current_month_purchases, 2) }}
                        </td>
                        <td>
                            ${{ number_format($pricing->last_month_purchases, 2) }}
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('partner-pricing.edit', $pricing->partner) }}" 
                                   class="btn btn-warning"
                                   title="Configurar">
                                    <i class="feather icon-settings"></i>
                                </a>
                                <a href="{{ route('partner-pricing.history', $pricing->partner) }}" 
                                   class="btn btn-info"
                                   title="Ver historial">
                                    <i class="feather icon-clock"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="feather icon-inbox" style="font-size: 48px; color: #ccc;"></i>
                            <p class="text-muted mt-3">No hay partners con configuración de pricing.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pricings->hasPages())
        <div class="mt-3">
            {{ $pricings->appends(request()->all())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Información -->
<div class="card mt-3">
    <div class="card-body">
        <h6 class="mb-3">💡 Información sobre Configuración de Pricing</h6>
        <ul class="mb-0">
            <li><strong>Markup:</strong> Porcentaje de ganancia que el partner aplica sobre el precio que recibe de Printec</li>
            <li><strong>Nivel Actual:</strong> Determina el descuento que recibe sobre el precio con markup de Printec</li>
            <li><strong>Override Manual:</strong> Cuando está activo (🔒), el nivel no cambiará automáticamente cada mes</li>
            <li><strong>Compras del Mes:</strong> Se actualizan cuando se envían cotizaciones y determinan el nivel del próximo mes</li>
            <li>Los niveles se evalúan automáticamente el día 1 de cada mes basándose en las compras del mes anterior</li>
        </ul>
    </div>
</div>
@endsection