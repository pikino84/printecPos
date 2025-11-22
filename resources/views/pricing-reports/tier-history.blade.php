@extends('layouts.app')
@section('title', 'Historial de Niveles')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5>Historial de Asignaciones de Niveles</h5>
            <p class="text-muted mb-0">Reporte detallado de cambios de nivel por partner</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('pricing-reports.export-tier-history', request()->all()) }}" class="btn btn-success">
                <i class="feather icon-download"></i> Exportar CSV
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card">
    <div class="card-body">
        <form action="{{ route('pricing-reports.tier-history') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="partner_id" class="form-label">Partner</label>
                <select class="form-control" id="partner_id" name="partner_id">
                    <option value="">Todos los partners</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                            {{ $partner->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="tier_id" class="form-label">Nivel</label>
                <select class="form-control" id="tier_id" name="tier_id">
                    <option value="">Todos los niveles</option>
                    @foreach($tiers as $tier)
                        <option value="{{ $tier->id }}" {{ request('tier_id') == $tier->id ? 'selected' : '' }}>
                            {{ $tier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="date_from" class="form-label">Desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            
            <div class="col-md-2">
                <label for="date_to" class="form-label">Hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            
            <div class="col-md-2">
                <label for="type" class="form-label">Tipo</label>
                <select class="form-control" id="type" name="type">
                    <option value="">Todos</option>
                    <option value="automatic" {{ request('type') == 'automatic' ? 'selected' : '' }}>Automático</option>
                    <option value="manual" {{ request('type') == 'manual' ? 'selected' : '' }}>Manual</option>
                </select>
            </div>
            
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="feather icon-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Resultados -->
<div class="card mt-3">
    <div class="card-body">
        @if($history->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha Asignación</th>
                        <th>Partner</th>
                        <th>Período</th>
                        <th>Nivel Asignado</th>
                        <th>Compras</th>
                        <th>Tipo</th>
                        <th>Notas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $record)
                    <tr>
                        <td>
                            <small>{{ $record->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('partners.show', $record->partner) }}">
                                {{ $record->partner->name }}
                            </a>
                        </td>
                        <td>
                            <small>
                                {{ $record->period_start->format('M Y') }}
                                <br>
                                <span class="text-muted">
                                    {{ $record->period_start->format('d/m') }} - {{ $record->period_end->format('d/m') }}
                                </span>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $record->tier->name }}</span>
                            <br>
                            <small class="text-muted">{{ $record->tier->discount_percentage }}% desc.</small>
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
                            <small>{{ Str::limit($record->notes, 30) ?? '-' }}</small>
                        </td>
                        <td>
                            <a href="{{ route('partner-pricing.history', $record->partner) }}" 
                               class="btn btn-sm btn-info"
                               title="Ver historial completo">
                                <i class="feather icon-clock"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $history->appends(request()->all())->links() }}
        </div>
        
        <!-- Resumen -->
        <div class="mt-4 pt-3 border-top">
            <div class="row">
                <div class="col-md-4">
                    <p class="mb-2">
                        <strong>Total Registros:</strong> {{ $history->total() }}
                    </p>
                </div>
                <div class="col-md-4">
                    <p class="mb-2">
                        <strong>Compras Totales:</strong> ${{ number_format($history->sum('purchases_amount'), 2) }}
                    </p>
                </div>
                <div class="col-md-4">
                    <p class="mb-2">
                        <strong>Partners Únicos:</strong> {{ $history->pluck('partner_id')->unique()->count() }}
                    </p>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-5">
            <i class="feather icon-inbox" style="font-size: 48px; color: #ccc;"></i>
            <p class="text-muted mt-3">No se encontraron registros con los filtros aplicados</p>
            <a href="{{ route('pricing-reports.tier-history') }}" class="btn btn-secondary">
                <i class="feather icon-refresh-cw"></i> Limpiar Filtros
            </a>
        </div>
        @endif
    </div>
</div>
@endsection