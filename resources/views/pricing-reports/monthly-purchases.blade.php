@extends('layouts.app')
@section('title', 'Reporte de Compras Mensuales')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Reporte de Compras Mensuales</h5>
            <p class="text-muted mb-0">Análisis de compras y distribución por nivel</p>
        </div>
    </div>
</div>

<!-- Selector de Período -->
<div class="card">
    <div class="card-body">
        <form action="{{ route('pricing-reports.monthly-purchases') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="months" class="form-label">Período a Mostrar</label>
                <select class="form-control" id="months" name="months" onchange="this.form.submit()">
                    <option value="6" {{ $monthsBack == 6 ? 'selected' : '' }}>Últimos 6 meses</option>
                    <option value="12" {{ $monthsBack == 12 ? 'selected' : '' }}>Últimos 12 meses</option>
                    <option value="24" {{ $monthsBack == 24 ? 'selected' : '' }}>Últimos 24 meses</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Gráfica de Evolución -->
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">📈 Evolución de Compras Totales</h6>
    </div>
    <div class="card-body">
        <canvas id="purchasesChart" height="100"></canvas>
    </div>
</div>

<!-- Tabla Detallada -->
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">Desglose Mensual</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Total Compras</th>
                        <th>Partners Activos</th>
                        <th>Distribución por Nivel</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $month)
                    <tr>
                        <td>
                            <strong>{{ $month['month'] }}</strong>
                        </td>
                        <td>
                            <strong class="text-primary">${{ number_format($month['total_purchases'], 2) }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $month['partners_count'] }}</span>
                        </td>
                        <td>
                            @if($month['tier_breakdown']->count() > 0)
                                <div class="d-flex gap-1 flex-wrap">
                                    @foreach($month['tier_breakdown'] as $tierData)
                                        <span class="badge bg-secondary" style="font-size: 11px;">
                                            {{ $tierData['tier']->name }}: {{ $tierData['count'] }} 
                                            (${{ number_format($tierData['total_purchases'], 0) }})
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">Sin datos</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th>TOTAL</th>
                        <th>${{ number_format($data->sum('total_purchases'), 2) }}</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Análisis por Nivel -->
<div class="row mt-3">
    @php
        $allTiers = $data->pluck('tier_breakdown')->collapse()->groupBy('tier.id');
    @endphp
    
    @foreach($allTiers as $tierGroup)
        @php
            $tier = $tierGroup->first()['tier'];
            $totalPurchases = $tierGroup->sum('total_purchases');
            $avgPartners = $tierGroup->avg('count');
        @endphp
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">{{ $tier->name }}</h6>
                    <p class="mb-2">
                        <strong>Total Compras:</strong> ${{ number_format($totalPurchases, 2) }}
                    </p>
                    <p class="mb-2">
                        <strong>Promedio Partners/Mes:</strong> {{ round($avgPartners, 1) }}
                    </p>
                    <p class="mb-0">
                        <strong>Descuento:</strong> 
                        <span class="badge bg-success">{{ $tier->discount_percentage }}%</span>
                    </p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Estadísticas Generales -->
<div class="card mt-3">
    <div class="card-body">
        <h6 class="mb-3">📊 Estadísticas del Período</h6>
        <div class="row">
            <div class="col-md-3">
                <p class="mb-2">
                    <strong>Compras Totales:</strong><br>
                    <span class="text-primary h5">${{ number_format($data->sum('total_purchases'), 2) }}</span>
                </p>
            </div>
            <div class="col-md-3">
                <p class="mb-2">
                    <strong>Promedio Mensual:</strong><br>
                    <span class="text-info h5">${{ number_format($data->avg('total_purchases'), 2) }}</span>
                </p>
            </div>
            <div class="col-md-3">
                <p class="mb-2">
                    <strong>Mes Más Alto:</strong><br>
                    <span class="text-success h5">{{ $data->sortByDesc('total_purchases')->first()['month'] }}</span>
                </p>
            </div>
            <div class="col-md-3">
                <p class="mb-2">
                    <strong>Mes Más Bajo:</strong><br>
                    <span class="text-warning h5">{{ $data->sortBy('total_purchases')->first()['month'] }}</span>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}?v={{ filemtime(public_path('vendor/chartjs/chart.umd.min.js')) }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('purchasesChart').getContext('2d');
    const data = @json($data);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.month),
            datasets: [{
                label: 'Compras Mensuales',
                data: data.map(d => d.total_purchases),
                backgroundColor: 'rgba(70, 128, 255, 0.8)',
                borderColor: '#4680ff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Compras: $' + context.parsed.y.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                        }
                    }
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