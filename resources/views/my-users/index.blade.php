@extends('layouts.app')
@section('title', 'Mis Usuarios')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5>Mis Usuarios</h5>
            <p class="text-muted mb-0">{{ $partner->name }}</p>
        </div>
        <div class="col-md-4 text-right">
            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
            <a href="{{ route('my-users.create') }}" class="btn btn-primary">
                <i class="feather icon-plus"></i> Agregar Usuario
            </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($users->count() > 0)
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <strong>{{ $user->name }}</strong>
                            @if($user->id === auth()->id())
                                <span class="badge bg-info ms-1">Tú</span>
                            @endif
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                @if($role->name === 'Asociado Administrador')
                                    <span class="badge bg-primary">Distribuidor Administrador</span>
                                @elseif($role->name === 'Asociado Vendedor')
                                    <span class="badge bg-info">Distribuidor Vendedor</span>
                                @else
                                    <span class="badge bg-secondary">{{ $role->name }}</span>
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $user->created_at->format('d/m/Y') }}
                            </small>
                        </td>
                        <td>
                            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('my-users.edit', $user->id) }}" 
                                   class="btn btn-warning"
                                   title="Editar">
                                    <i class="feather icon-edit"></i>
                                </a>
                                
                                @if($user->id !== auth()->id())
                                <button type="button"
                                        class="btn btn-danger"
                                        title="Eliminar"
                                        onclick="confirmDelete({{ $user->id }})">
                                    <i class="feather icon-trash-2"></i>
                                </button>
                                
                                <form id="delete-form-{{ $user->id }}" 
                                      action="{{ route('my-users.destroy', $user->id) }}" 
                                      method="POST" 
                                      style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </div>
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
@else
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="feather icon-users" style="font-size: 48px; color: #ccc;"></i>
            <p class="text-muted mt-3">No tienes usuarios registrados.</p>
            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
            <a href="{{ route('my-users.create') }}" class="btn btn-primary">
                <i class="feather icon-plus"></i> Agregar primer usuario
            </a>
            @endif
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
function confirmDelete(userId) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            html: '¿Deseas eliminar este usuario?<br><br>Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + userId).submit();
            }
        });
    } else if (typeof swal !== 'undefined') {
        swal({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar este usuario?\n\nEsta acción no se puede deshacer.',
            icon: 'warning',
            buttons: {
                cancel: {
                    text: 'Cancelar',
                    value: null,
                    visible: true,
                    closeModal: true,
                },
                confirm: {
                    text: 'Sí, eliminar',
                    value: true,
                    visible: true,
                    className: 'btn-danger',
                    closeModal: true
                }
            },
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                document.getElementById('delete-form-' + userId).submit();
            }
        });
    } else {
        if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
            document.getElementById('delete-form-' + userId).submit();
        }
    }
}
</script>
@endsection