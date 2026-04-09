@extends('layouts.app')
@section('title', 'Cotizaciones Sospechosas')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Monitor de Cotizaciones</h5>
            <p class="text-muted mb-0">Cotizaciones enviadas sin cierre &mdash; posibles ventas no registradas</p>
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
                        <h4 class="text-primary mb-0">{{ $stats['total'] }}</h4>
                        <p class="text-muted mb-0">Total Cotizaciones</p>
                    </div>
                    <div class="col-4 text-right">
                        <i class="feather icon-file-text f-28 text-primary"></i>
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
                        <h4 class="text-warning mb-0">{{ $stats['stale'] }}</h4>
                        <p class="text-muted mb-0">Sin Cierre</p>
                        <small class="text-muted">{{ $stats['sent'] }} enviadas + {{ $stats['expired'] }} expiradas</small>
                    </div>
                    <div class="col-4 text-right">
                        <i class="feather icon-alert-triangle f-28 text-warning"></i>
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
                        <h4 class="text-danger mb-0">${{ number_format($stats['staleAmount'], 2) }}</h4>
                        <p class="text-muted mb-0">Monto en Riesgo</p>
                    </div>
                    <div class="col-4 text-right">
                        <i class="feather icon-dollar-sign f-28 text-danger"></i>
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
                        <h4 class="text-success mb-0">{{ $stats['conversionRate'] }}%</h4>
                        <p class="text-muted mb-0">Tasa de Conversion</p>
                    </div>
                    <div class="col-4 text-right">
                        <i class="feather icon-trending-up f-28 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Partners con posibles fugas -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5>Partners con posibles fugas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Partner</th>
                                <th class="text-center">Sin cierre</th>
                                <th class="text-center">Cerradas</th>
                                <th class="text-center">Conversion</th>
                                <th class="text-right">Monto en riesgo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($conversionByPartner as $partner)
                            <tr>
                                <td>{{ $partner['name'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">{{ $partner['stale_quotes'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $partner['converted_quotes'] }}</span>
                                </td>
                                <td class="text-center">
                                    @if($partner['conversion_rate'] < 20)
                                        <span class="text-danger font-weight-bold">{{ $partner['conversion_rate'] }}%</span>
                                    @elseif($partner['conversion_rate'] < 50)
                                        <span class="text-warning">{{ $partner['conversion_rate'] }}%</span>
                                    @else
                                        <span class="text-success">{{ $partner['conversion_rate'] }}%</span>
                                    @endif
                                </td>
                                <td class="text-right">${{ number_format($partner['stale_amount'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Sin datos</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Fuga mensual estimada -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5>Fuga mensual estimada (cotizaciones enviadas sin cierre)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th class="text-center">Cotizaciones</th>
                                <th class="text-right">Monto Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthlyLeakage as $month)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($month['month'] . '-01')->translatedFormat('F Y') }}</td>
                                <td class="text-center">{{ $month['count'] }}</td>
                                <td class="text-right">${{ number_format($month['total'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Sin datos</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Listado de cotizaciones sospechosas -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cotizaciones sin cierre</h5>
                <form method="GET" class="d-flex align-items-center gap-2">
                    <select name="filter_status" class="form-control form-control-sm" style="width: 130px;" onchange="this.form.submit()">
                        <option value="">Todas</option>
                        <option value="sent" {{ request('filter_status') == 'sent' ? 'selected' : '' }}>Enviadas</option>
                        <option value="expired" {{ request('filter_status') == 'expired' ? 'selected' : '' }}>Expiradas</option>
                    </select>
                    <select name="days" class="form-control form-control-sm" style="width: 140px;" onchange="this.form.submit()">
                        <option value="0" {{ request('days', 0) == 0 ? 'selected' : '' }}>Cualquier fecha</option>
                        <option value="7" {{ request('days') == 7 ? 'selected' : '' }}>+7 dias</option>
                        <option value="15" {{ request('days') == 15 ? 'selected' : '' }}>+15 dias</option>
                        <option value="30" {{ request('days') == 30 ? 'selected' : '' }}>+30 dias</option>
                        <option value="60" {{ request('days') == 60 ? 'selected' : '' }}>+60 dias</option>
                    </select>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Cotizacion</th>
                                <th>Partner</th>
                                <th>Vendedor</th>
                                <th>Cliente</th>
                                <th class="text-right">Total</th>
                                <th>Status</th>
                                <th>Fecha</th>
                                <th>Antiguedad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staleQuotes as $quote)
                            @php
                                $referenceDate = $quote->sent_at ?? $quote->created_at;
                                $daysSince = $referenceDate ? (int) Carbon\Carbon::parse($referenceDate)->diffInDays(now()) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('quotes.show', $quote->id) }}">
                                        {{ $quote->quote_number }}
                                    </a>
                                </td>
                                <td>{{ $quote->partner->name ?? 'N/A' }}</td>
                                <td>{{ $quote->user->name ?? 'N/A' }}</td>
                                <td>{{ $quote->client->nombre ?? $quote->client_name ?? 'N/A' }}</td>
                                <td class="text-right">${{ number_format($quote->total, 2) }}</td>
                                <td>
                                    @if($quote->status === 'expired')
                                        <span class="badge bg-secondary">Expirada</span>
                                    @else
                                        <span class="badge bg-info">Enviada</span>
                                    @endif
                                </td>
                                <td>{{ $referenceDate ? Carbon\Carbon::parse($referenceDate)->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">
                                    @if($daysSince > 30)
                                        <span class="badge bg-danger">{{ $daysSince }} dias</span>
                                    @elseif($daysSince > 14)
                                        <span class="badge bg-warning text-dark">{{ $daysSince }} dias</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $daysSince }} dias</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('quotes.show', $quote->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="feather icon-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No hay cotizaciones sospechosas en este periodo
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($staleQuotes->hasPages())
                <div class="card-footer">
                    {{ $staleQuotes->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
