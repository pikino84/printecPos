@extends('layouts.app')
@section('title', 'Detalle del Nivel - ' . $pricingTier->name)

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5>{{ $pricingTier->name }}</h5>
            <p class="text-muted mb-0">Detalle del nivel de precio</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('pricing-tiers.edit', $pricingTier) }}" class="btn btn-warning">
                <i class="feather icon-edit"></i> Editar
            </a>
            <a href="{{ route('pricing-tiers.index') }}" class="btn btn-secondary">
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
                        <td>{{ $pricingTier->name }}</td>
                    </tr>
                    <tr>
                        <th>Slug:</th>
                        <td><code>{{ $pricingTier->slug }}</code></td>
                    </tr>
                    <tr>
                        <th>Orden:</th>
                        <td>{{ $pricingTier->order }}</td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>
                            @if($pricingTier->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Descripción:</th>
                        <td>{{ $pricingTier->description ?? 'Sin descripción' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Configuración de Precios</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Compras Mínimas:</th>
                        <td>${{ number_format($pricingTier->min_monthly_purchases, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Compras Máximas:</th>
                        <td>
                            @if($pricingTier->max_monthly_purchases)
                                ${{ number_format($pricingTier->max_monthly_purchases, 2) }}
                            @else
                                <span class="text-muted">Sin límite</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Descuento:</th>
                        <td>
                            <span class="badge bg-success" style="font-size: 14px;">
                                {{ number_format($pricingTier->discount_percentage, 2) }}%
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Partners Asignados:</th>
                        <td>
                            <span class="badge bg-info">
                                {{ $pricingTier->partners->count() }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Ejemplo de Cálculo -->
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">📊 Ejemplo de Cálculo de Precio</h6>
    </div>
    <div class="card-body">
        <p class="mb-3">Simulación del precio final para un producto con precio base de $100.00:</p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-3">Flujo de Precio (sin markup de partner)</h6>
                    @php
                        $basePrice = 100;
                        $printecMarkup = \App\Models\PricingSetting::get('printec_markup', 52);
                        $taxRate = \App\Models\PricingSetting::get('tax_rate', 16);
                        
                        $withPrintecMarkup = $basePrice + ($basePrice * $printecMarkup / 100);
                        $afterDiscount = $pricingTier->applyDiscount($withPrintecMarkup);
                        $withTax = $afterDiscount * (1 + $taxRate / 100);
                    @endphp
                    
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Precio Base:</td>
                            <td class="text-right"><strong>${{ number_format($basePrice, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>+ Markup Printec ({{ $printecMarkup }}%):</td>
                            <td class="text-right">${{ number_format($withPrintecMarkup, 2) }}</td>
                        </tr>
                        <tr class="table-success">
                            <td>- Descuento Tier ({{ $pricingTier->discount_percentage }}%):</td>
                            <td class="text-right"><strong>${{ number_format($afterDiscount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>+ IVA ({{ $taxRate }}%):</td>
                            <td class="text-right">${{ number_format($withTax, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-3">Con Markup de Partner (20%)</h6>
                    @php
                        $partnerMarkup = 20;
                        $withPartnerMarkup = $afterDiscount + ($afterDiscount * $partnerMarkup / 100);
                        $finalWithTax = $withPartnerMarkup * (1 + $taxRate / 100);
                    @endphp
                    
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Precio Base:</td>
                            <td class="text-right"><strong>${{ number_format($basePrice, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>+ Markup Printec ({{ $printecMarkup }}%):</td>
                            <td class="text-right">${{ number_format($withPrintecMarkup, 2) }}</td>
                        </tr>
                        <tr class="table-success">
                            <td>- Descuento Tier ({{ $pricingTier->discount_percentage }}%):</td>
                            <td class="text-right">${{ number_format($afterDiscount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>+ Markup Partner ({{ $partnerMarkup }}%):</td>
                            <td class="text-right"><strong>${{ number_format($withPartnerMarkup, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>+ IVA ({{ $taxRate }}%):</td>
                            <td class="text-right"><strong class="text-success">${{ number_format($finalWithTax, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Partners Asignados -->
@if($pricingTier->partners->count() > 0)
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">Partners con Este Nivel</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>Tipo</th>
                        <th>Markup</th>
                        <th>Compras del Mes Actual</th>
                        <th>Compras del Mes Anterior</th>
                        <th>Asignado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pricingTier->partners as $pricing)
                    <tr>
                        <td>
                            <a href="{{ route('partners.show', $pricing->partner) }}">
                                {{ $pricing->partner->name }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $pricing->partner->type }}</span>
                        </td>
                        <td>{{ number_format($pricing->markup_percentage, 2) }}%</td>
                        <td>${{ number_format($pricing->current_month_purchases, 2) }}</td>
                        <td>${{ number_format($pricing->last_month_purchases, 2) }}</td>
                        <td>
                            @if($pricing->tier_assigned_at)
                                {{ $pricing->tier_assigned_at->format('d/m/Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Historial de Asignaciones -->
@if($pricingTier->history->count() > 0)
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">Historial de Asignaciones (Últimas 20)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>Período</th>
                        <th>Compras</th>
                        <th>Tipo</th>
                        <th>Notas</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pricingTier->history as $history)
                    <tr>
                        <td>
                            <a href="{{ route('partners.show', $history->partner) }}">
                                {{ $history->partner->name }}
                            </a>
                        </td>
                        <td>
                            <small>
                                {{ $history->period_start->format('d/m/Y') }} - 
                                {{ $history->period_end->format('d/m/Y') }}
                            </small>
                        </td>
                        <td>${{ number_format($history->purchases_amount, 2) }}</td>
                        <td>
                            @if($history->is_manual)
                                <span class="badge bg-warning">Manual</span>
                            @else
                                <span class="badge bg-success">Automático</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $history->notes ?? '-' }}</small>
                        </td>
                        <td>
                            <small>{{ $history->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
                <p class="mb-2">
                    <strong>Fecha de creación:</strong> 
                    {{ $pricingTier->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-2">
                    <strong>Última actualización:</strong> 
                    {{ $pricingTier->updated_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection