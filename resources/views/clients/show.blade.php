@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2>{{ $client->nombre_completo }}</h2>
            @if($client->is_active)
                <span class="badge bg-success">Activo</span>
            @else
                <span class="badge bg-danger">Inactivo</span>
            @endif
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Mensajes --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Información del cliente --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Información Personal</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Nombre:</th>
                            <td>{{ $client->nombre }}</td>
                        </tr>
                        <tr>
                            <th>Apellido:</th>
                            <td>{{ $client->apellido }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>
                                @if($client->email)
                                    <a href="mailto:{{ $client->email }}">{{ $client->email }}</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Teléfono:</th>
                            <td>
                                @if($client->telefono)
                                    <a href="tel:{{ $client->telefono }}">{{ $client->telefono }}</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Información Fiscal</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">RFC:</th>
                            <td>{{ $client->rfc ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Razón Social:</th>
                            <td>{{ $client->razon_social ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Dirección:</th>
                            <td>{{ $client->direccion ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($client->notas)
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Notas Internas</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $client->notas }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Partners y cotizaciones --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Partners Asociados</h5>
                </div>
                <div class="card-body">
                    @if($client->partners->count() > 0)
                        <div class="list-group">
                            @foreach($client->partners as $partner)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $partner->name }}</strong>
                                            <span class="badge bg-secondary ms-2">{{ $partner->type }}</span>
                                            @if($partner->pivot->first_contact_at)
                                                <br>
                                                <small class="text-muted">
                                                    Primer contacto: {{ \Carbon\Carbon::parse($partner->pivot->first_contact_at)->format('d/m/Y') }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                    @if($partner->pivot->notes)
                                        <p class="mb-0 mt-2 small text-muted">
                                            <strong>Notas:</strong> {{ $partner->pivot->notes }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Sin partners asociados</p>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Cotizaciones</h5>
                    <span class="badge bg-primary">{{ $client->quotes->count() }}</span>
                </div>
                <div class="card-body">
                    @if($client->quotes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($client->quotes()->latest()->take(10)->get() as $quote)
                                        <tr>
                                            <td>
                                                <strong>{{ $quote->quote_number }}</strong>
                                            </td>
                                            <td>
                                                {{ $quote->created_at->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                ${{ number_format($quote->total, 2) }}
                                            </td>
                                            <td>
                                                @switch($quote->status)
                                                    @case('draft')
                                                        <span class="badge bg-secondary">Borrador</span>
                                                        @break
                                                    @case('pending')
                                                        <span class="badge bg-warning">Pendiente</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge bg-success">Aprobada</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge bg-danger">Rechazada</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $quote->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <a 
                                                    href="{{ route('quotes.show', $quote) }}" 
                                                    class="btn btn-sm btn-info"
                                                    title="Ver cotización"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($client->quotes->count() > 10)
                            <div class="text-center mt-2">
                                <a href="{{ route('quotes.index', ['client_id' => $client->id]) }}" class="btn btn-sm btn-outline-primary">
                                    Ver todas las cotizaciones
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle"></i> 
                            Sin cotizaciones registradas
                        </p>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Información del Sistema</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Registrado:</th>
                            <td>{{ $client->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Última actualización:</th>
                            <td>{{ $client->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection