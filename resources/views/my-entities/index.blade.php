@extends('layouts.app')
@section('title', 'Mis Razones Sociales')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5>Mis Razones Sociales</h5>
            <p class="text-muted mb-0">{{ $partner->name }}</p>
        </div>
        <div class="col-md-4 text-right">
            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
            <a href="{{ route('my-entities.create') }}" class="btn btn-primary">
                <i class="feather icon-plus"></i> Agregar Razón Social
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

<div class="card">
    <div class="card-body">
        @if($entities->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Razón Social</th>
                        <th>RFC</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Principal</th>
                        <th>Estado</th>
                        <th>Correo SMTP</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entities as $entity)
                    <tr>
                        <td>
                            @if($entity->logo_path)
                                <img src="{{ asset('storage/' . $entity->logo_path) }}" 
                                     alt="Logo" 
                                     class="img-thumbnail" 
                                     style="max-height: 50px; max-width: 80px;">
                            @else
                                <span class="text-muted">Sin logo</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $entity->razon_social }}</strong>
                        </td>
                        <td>{{ $entity->rfc ?? '-' }}</td>
                        <td>{{ $entity->correo_contacto ?? '-' }}</td>
                        <td>{{ $entity->telefono ?? '-' }}</td>
                        <td>
                            @if($entity->is_default)
                                <span class="badge bg-primary">Sí</span>
                            @else
                                <span class="text-muted">No</span>
                            @endif
                        </td>
                        <td>
                            @if($entity->is_active)
                                <span class="badge bg-success">Activa</span>
                            @else
                                <span class="badge bg-secondary">Inactiva</span>
                            @endif
                        </td>
                        <td>
                            {!! $entity->mail_status_badge !!}
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
                                <a href="{{ route('my-entities.edit', $entity->id) }}"
                                   class="btn btn-warning"
                                   title="Editar">
                                    <i class="feather icon-edit"></i>
                                </a>

                                <a href="{{ route('my-entities.mail-config', $entity->id) }}"
                                   class="btn btn-info"
                                   title="Configurar Correo">
                                    <i class="feather icon-mail"></i>
                                </a>

                                <button type="button"
                                        class="btn btn-danger"
                                        title="Eliminar"
                                        onclick="confirmDelete({{ $entity->id }})">
                                    <i class="feather icon-trash-2"></i>
                                </button>

                                <form id="delete-form-{{ $entity->id }}"
                                      action="{{ route('my-entities.destroy', $entity->id) }}"
                                      method="POST"
                                      style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="feather icon-file-text" style="font-size: 48px; color: #ccc;"></i>
            <p class="text-muted mt-3">No tienes razones sociales registradas.</p>
            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
            <a href="{{ route('my-entities.create') }}" class="btn btn-primary">
                <i class="feather icon-plus"></i> Crear primera razón social
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(entityId) {
    console.log('confirmDelete called:', entityId);
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            html: '¿Deseas eliminar esta razón social?<br><br>Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + entityId).submit();
            }
        });
    } else if (typeof swal !== 'undefined') {
        swal({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar esta razón social?\n\nEsta acción no se puede deshacer.',
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
                document.getElementById('delete-form-' + entityId).submit();
            }
        });
    } else {
        if (confirm('¿Estás seguro de que deseas eliminar esta razón social?')) {
            document.getElementById('delete-form-' + entityId).submit();
        }
    }
}
</script>
@endsection