@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Clientes Finales</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('clients.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Cliente
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

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('clients.index') }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input 
                            type="text" 
                            name="search" 
                            class="form-control" 
                            placeholder="Buscar por nombre, email, RFC, razón social..."
                            value="{{ request('search') }}"
                        >
                    </div>

                    @if(auth()->user()->hasRole('super admin'))
                    <div class="col-md-4">
                        <select name="partner_id" class="form-control">
                            <option value="">Todos los partners</option>
                            @foreach($partners as $partner)
                                <option 
                                    value="{{ $partner->id }}" 
                                    {{ request('partner_id') == $partner->id ? 'selected' : '' }}
                                >
                                    {{ $partner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de clientes --}}
    <div class="card">
        <div class="card-body">
            @if($clients->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>RFC</th>
                                <th>Razón Social</th>
                                <th>Partners</th>
                                <th>Cotizaciones</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clients as $client)
                                <tr>
                                    <td>
                                        <strong>{{ $client->nombre_completo }}</strong>
                                        @if($client->telefono)
                                            <br><small class="text-muted">{{ $client->telefono }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $client->email ?? '-' }}</td>
                                    <td>{{ $client->rfc ?? '-' }}</td>
                                    <td>{{ $client->razon_social ?? '-' }}</td>
                                    <td>
                                        @foreach($client->partners as $partner)
                                            <span class="badge bg-secondary">{{ $partner->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $client->quotes->count() }} cotizaciones
                                        </span>
                                    </td>
                                    <td>
                                        @if($client->is_active)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a 
                                                href="{{ route('clients.show', $client) }}" 
                                                class="btn btn-info"
                                                title="Ver"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a 
                                                href="{{ route('clients.edit', $client) }}" 
                                                class="btn btn-warning"
                                                title="Editar"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(auth()->user()->hasRole('super admin'))
                                                <button 
                                                    type="button"
                                                    class="btn btn-danger"
                                                    title="Desactivar"
                                                    onclick="confirmDelete({{ $client->id }}, '{{ str_replace("'", "\\'", $client->nombre_completo) }}')"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                        
                                        {{-- Formulario oculto para eliminar --}}
                                        @if(auth()->user()->hasRole('super admin'))
                                            <form 
                                                id="delete-form-{{ $client->id }}" 
                                                action="{{ route('clients.destroy', $client) }}" 
                                                method="POST" 
                                                style="display: none;"
                                            >
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="mt-3">
                    {{ $clients->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No se encontraron clientes.</p>
                    <a href="{{ route('clients.create') }}" class="btn btn-primary">
                        Crear primer cliente
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(clientId, clientName) {
    console.log('confirmDelete called with:', clientId, clientName); // Debug
    
    swal({
        title: '¿Estás seguro?',
        text: '¿Deseas desactivar al cliente "' + clientName + '"?\n\nEsta acción no eliminará el cliente, solo lo marcará como inactivo.',
        icon: 'warning',
        buttons: {
            cancel: {
                text: 'Cancelar',
                value: null,
                visible: true,
                className: '',
                closeModal: true,
            },
            confirm: {
                text: 'Sí, desactivar',
                value: true,
                visible: true,
                className: 'btn-danger',
                closeModal: true
            }
        },
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            document.getElementById('delete-form-' + clientId).submit();
        }
    });
}
</script>
@endsection