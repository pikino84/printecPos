@extends('layouts.app')
@section('title', 'Evolución de Partner')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Evolución de Partner</h5>
            <p class="text-muted mb-0">Análisis histórico de compras y niveles por partner</p>
        </div>
    </div>
</div>

<!-- Selector de Partner -->
<div class="card">
    <div class="card-body">
        <form action="{{ route('pricing-reports.partner-evolution') }}" method="GET" class="row g-3">
            <div class="col-md-10">
                <label for="partner_id" class="form-label">Seleccionar Partner</label>
                <select class="form-control" id="partner_id" name="partner_id" required>
                    <option value="">-- Selecciona un partner --</option>
                    @foreach($partners as $p)
                        <option value="{{ $p->id }}" {{ isset($partner) && $partner->id == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->type }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="feather icon-search"></i> Consultar
                </button>
            </div>
        </form>
    </div>
</div>

@if(isset($partner))
<!-- Información del Partner -->
<div class="row mt-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">{{ $partner->name }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Tipo:</strong> <span class="badge bg-secondary">{{ $partner->type }}</span></p>
                        <p class="mb-2"><strong>Estado:</strong> 
                            @if($partner->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Nivel Actual:</strong> 
                            @if($partner->pricing && $partner->pricing->currentTier)
                                <span class="badge bg-success">{{ $partner->pricing->currentTier->name }}</span>
                            @else
                                <span class="badge bg-secondary">Sin nivel</span>
                            @endif
                        </p>
                        <p class="mb-2"><strong>Markup:</strong> {{ $partner->pricing ? number_format($partner->pricing->markup_percentage, 2) : 0 }}%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Estadísticas</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Total Períodos:</strong> {{ $stats['total_periods'] }}</p>
                <p class="mb-2"><strong>Compras Totales:</strong> ${{ number_format($stats['total_purchases'], 2) }}</p>
                <p class="mb-2"><strong>Promedio/Mes:</strong> ${{ number_format($stats['avg_purchases'], 2) }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Gráfica de Evolución -->
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">📈 Evolución de Compras y Niveles</h6>
    </div>
    <div class="card-body">
        <canvas id="evolutionChart" height="100"></canvas>
    </div>
</div>

<!-- Historial Detallado -->
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">Historial de Niveles</h6>
    </div>
    <div class="card-body">
        @if($history->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Período</th>
                        <th>Nivel</th>
                        <th>Descuento</th>
                        <th>Compras</th>
                        <th>Tipo</th>
                        <th>Fecha Asignación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $record)
                    <tr>
                        <td>
                            <strong>{{ $record->period_start->format('M Y') }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $record->period_start->format('d/m') }} - {{ $record->period_end->format('d/m') }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $record->tier->name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $record->tier->discount_percentage }}%</span>
                        </td>
                        <td>
                            <strong>${{ number_format($record->purchases_amount, 2) }}</strong>
                        </td>
                        <td>
                            @if($record->is_manual)
                                <span class="badge bg-warning">
                                    <i class="feather icon-user"></i> Manual
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="feather icon-zap"></i> Automático
                                </span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $record->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center mb-0">No hay historial disponible para este partner</p>
        @endif
    </div>
</div>

<!-- Niveles Alcanzados -->
@if($stats['highest_tier'] || $stats['lowest_tier'])
<div class="card mt-3">
    <div class="card-body">
        <h6 class="mb-3">🏆 Niveles Alcanzados</h6>
        <div class="row">
            @if($stats['highest_tier'])
            <div class="col-md-6">
                <p class="mb-2">
                    <strong>Nivel Más Alto:</strong> 
                    <span class="badge bg-success">{{ $stats['highest_tier']->name }}</span>
                    ({{ $stats['highest_tier']->discount_percentage }}% descuento)
                </p>
            </div>
            @endif
            @if($stats['lowest_tier'])
            <div class="col-md-6">
                <p class="mb-2">
                    <strong>Nivel Más Bajo:</strong> 
                    <span class="badge bg-secondary">{{ $stats['lowest_tier']->name }}</span>
                    ({{ $stats['lowest_tier']->discount_percentage }}% descuento)
                </p>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('evolutionChart').getContext('2d');
    const history = @json($history);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: history.map(h => h.period_start.substring(0, 7)), // YYYY-MM
            datasets: [
                {
                    label: 'Compras',
                    data: history.map(h => h.purchases_amount),
                    borderColor: '#4680ff',
                    backgroundColor: 'rgba(70, 128, 255, 0.1)',
                    yAxisID: 'y',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Descuento del Nivel (%)',
                    data: history.map(h => h.tier.discount_percentage),
                    borderColor: '#0ac074',
                    backgroundColor: 'rgba(10, 192, 116, 0.1)',
                    yAxisID: 'y1',
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Compras ($)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(0).replace(/\d(?=(\d{3})+$)/g, '$&,');
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Descuento (%)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
});
</script>
@endsection
@endif
@endsection