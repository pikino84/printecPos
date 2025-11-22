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