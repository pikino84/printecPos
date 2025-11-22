@extends('layouts.app')
@section('title', 'Historial de Niveles - ' . $partner->name)

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5>Historial de Niveles de Precio</h5>
            <p class="text-muted mb-0">{{ $partner->name }}</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('partner-pricing.edit', $partner) }}" class="btn btn-warning">
                <i class="feather icon-settings"></i> Configurar
            </a>
            <a href="{{ route('partner-pricing.index') }}" class="btn btn-secondary">
                <i class="feather icon-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<!-- Resumen Actual -->
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Nivel Actual</h6>
                @if($pricing->currentTier)
                    <h4 class="mb-0">{{ $pricing->currentTier->name }}</h4>
                    <small class="text-muted">{{ number_format($pricing->currentTier->discount_percentage, 2) }}% descuento</small>
                @else
                    <h4 class="mb-0 text-muted">Sin nivel</h4>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Markup</h6>
                <h4 class="mb-0">{{ number_format($pricing->markup_percentage, 2) }}%</h4>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Compras Mes Actual</h6>
                <h4 class="mb-0">${{ number_format($pricing->current_month_purchases, 2) }}</h4>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Compras Mes Anterior</h6>
                <h4 class="mb-0">${{ number_format($pricing->last_month_purchases, 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Historial -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Historial de Asignaciones</h6>
    </div>
    <div class="card-body">
        @if($history->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Período</th>
                        <th>Nivel Asignado</th>
                        <th>Descuento</th>
                        <th>Compras del Período</th>
                        <th>Tipo de Asignación</th>
                        <th>Notas</th>
                        <th>Fecha de Asignación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $record)
                    <tr>
                        <td>
                            <strong>{{ $record->period_start->format('M Y') }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $record->period_start->format('d/m/Y') }} - 
                                {{ $record->period_end->format('d/m/Y') }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $record->tier->name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                {{ number_format($record->tier->discount_percentage, 2) }}%
                            </span>
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
                            <small>{{ $record->notes ?? '-' }}</small>
                        </td>
                        <td>
                            <small>{{ $record->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($history->hasPages())
        <div class="mt-3">
            {{ $history->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <i class="feather icon-clock" style="font-size: 48px; color: #ccc;"></i>
            <p class="text-muted mt-3">No hay historial de niveles para este partner.</p>
        </div>
        @endif
    </div>
</div>

<!-- Gráfica de Evolución (Opcional) -->
@if($history->count() > 0)
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">📈 Evolución de Compras</h6>
    </div>
    <div class="card-body">
        <canvas id="purchasesChart" height="80"></canvas>
    </div>
</div>
@endif

<!-- Información -->
<div class="card mt-3">
    <div class="card-body">
        <h6 class="mb-3">💡 Sobre el Historial de Niveles</h6>
        <ul class="mb-0">
            <li><strong>Asignación Automática:</strong> Se realiza cada día 1 del mes basándose en las compras del mes anterior</li>
            <li><strong>Asignación Manual:</strong> Realizada por un administrador, puede incluir notas explicativas</li>
            <li><strong>Override Manual:</strong> Cuando está activo, previene cambios automáticos de nivel</li>
            <li>El historial se guarda permanentemente para auditoría y análisis de tendencias</li>
        </ul>
    </div>
</div>
@endsection

@section('scripts')
@if($history->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('purchasesChart').getContext('2d');
    
    const data = @json($history->reverse()->values()->map(function($record) {
        return [
            'period' => $record->period_start->format('M Y'),
            'purchases' => $record->purchases_amount,
            'tier' => $record->tier->name
        ];
    }));
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.period),
            datasets: [{
                label: 'Compras Mensuales',
                data: data.map(d => d.purchases),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Compras: $' + context.parsed.y.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                        },
                        afterLabel: function(context) {
                            return 'Nivel: ' + data[context.dataIndex].tier;
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
@endif
@endsection