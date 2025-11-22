@extends('layouts.app')
@section('title', 'Dashboard de Pricing')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Dashboard de Pricing</h5>
            <p class="text-muted mb-0">Resumen y estadísticas del sistema de precios</p>
        </div>
    </div>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h4 class="text-primary mb-0">{{ $stats['total_partners'] }}</h4>
                        <p class="text-muted mb-0">Total Partners</p>
                    </div>
                    <div class="col-4 text-right">
                        <i class="feather icon-users f-28 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h4 class="text-success mb-0">{{ $stats['partners_with_tier'] }}</h4>
                        <p class="text-muted mb-0">Con Nivel Asignado</p>
                    </div>
                    <div class="col-4 text-right">
                        <i class="feather icon-check-circle f-28 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h4 class="text-info mb-0">${{ number_format($stats['current_month_purchases'], 0) }}</h4>
                        <p class="text-muted mb-0">Compras Mes Actual</p>
                    </div>
                    <div class="col-4 text-right">
                        <i class="feather icon-shopping-cart f-28 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h4 class="mb-0 {{ $stats['growth_percentage'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $stats['growth_percentage'] > 0 ? '+' : '' }}{{ $stats['growth_percentage'] }}%
                        </h4>
                        <p class="text-muted mb-0">Crecimiento</p>
                    </div>
                    <div class="col-4 text-right">
                        <i class="feather icon-trending-{{ $stats['growth_percentage'] >= 0 ? 'up' : 'down' }} f-28 {{ $stats['growth_percentage'] >= 0 ? 'text-success' : 'text-danger' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficas -->
<div class="row">
    <!-- Distribución por Tier -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Distribución de Partners por Nivel</h6>
            </div>
            <div class="card-body">
                <canvas id="tierDistributionChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Evolución de Compras -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Evolución de Compras Mensuales</h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyPurchasesChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Partners y Partners Próximos a Cambiar -->
<div class="row">
    <!-- Top 10 Partners -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Top 10 Partners por Compras del Mes</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Partner</th>
                                <th>Nivel</th>
                                <th>Compras</th>
                                <th>Markup</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPartners as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('partners.show', $item['partner']) }}">
                                        {{ $item['partner']->name }}
                                    </a>
                                </td>
                                <td>
                                    @if($item['tier'])
                                        <span class="badge bg-success">{{ $item['tier']->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">Sin nivel</span>
                                    @endif
                                </td>
                                <td>${{ number_format($item['purchases'], 2) }}</td>
                                <td>{{ number_format($item['markup'], 2) }}%</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No hay datos disponibles</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Partners Próximos a Cambiar de Nivel -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">⚠️ Partners Próximos a Cambiar de Nivel</h6>
            </div>
            <div class="card-body">
                @if($nearLevelChange->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Partner</th>
                                <th>Nivel Actual</th>
                                <th>Compras</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nearLevelChange as $pricing)
                            <tr>
                                <td>
                                    <a href="{{ route('partner-pricing.edit', $pricing->partner) }}">
                                        {{ $pricing->partner->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $pricing->currentTier->name }}</span>
                                </td>
                                <td>${{ number_format($pricing->current_month_purchases, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center mb-0">No hay partners próximos a cambiar de nivel</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Actividad Reciente -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Actividad Reciente - Cambios de Nivel</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Partner</th>
                        <th>Nivel Asignado</th>
                        <th>Compras del Período</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTierChanges as $change)
                    <tr>
                        <td>
                            <small>{{ $change->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('partners.show', $change->partner) }}">
                                {{ $change->partner->name }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $change->tier->name }}</span>
                        </td>
                        <td>${{ number_format($change->purchases_amount, 2) }}</td>
                        <td>
                            @if($change->is_manual)
                                <span class="badge bg-warning">
                                    <i class="feather icon-user"></i> Manual
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="feather icon-zap"></i> Automático
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No hay cambios recientes</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Resumen de Configuración -->
<div class="card mt-3">
    <div class="card-body">
        <h6 class="mb-3">📊 Resumen del Sistema</h6>
        <div class="row">
            <div class="col-md-4">
                <p class="mb-2"><strong>Markup Promedio:</strong> {{ number_format($stats['avg_markup'], 2) }}%</p>
            </div>
            <div class="col-md-4">
                <p class="mb-2"><strong>Compras Mes Anterior:</strong> ${{ number_format($stats['last_month_purchases'], 2) }}</p>
            </div>
            <div class="col-md-4">
                <p class="mb-2"><strong>Partners Sin Nivel:</strong> {{ $stats['partners_without_tier'] }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Distribución por Tier
    const tierCtx = document.getElementById('tierDistributionChart').getContext('2d');
    const tierData = @json($tierDistribution);
    
    new Chart(tierCtx, {
        type: 'doughnut',
        data: {
            labels: tierData.map(t => t.name + ' (' + t.discount + '%)'),
            datasets: [{
                data: tierData.map(t => t.count),
                backgroundColor: [
                    '#4680ff', '#0ac074', '#ffc107', '#ff5252', 
                    '#536dfe', '#00bcd4', '#ff9800', '#e91e63'
                ],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
    
    // Evolución de Compras
    const monthlyCtx = document.getElementById('monthlyPurchasesChart').getContext('2d');
    const monthlyData = @json($monthlyPurchases);
    
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(m => m.month),
            datasets: [{
                label: 'Compras Mensuales',
                data: monthlyData.map(m => m.total),
                borderColor: '#4680ff',
                backgroundColor: 'rgba(70, 128, 255, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(0).replace(/\d(?=(\d{3})+$)/g, '$&,');
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection