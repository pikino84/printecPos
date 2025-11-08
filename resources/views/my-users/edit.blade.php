@extends('layouts.app')
@section('title', 'Editar Usuario')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Editar Usuario</h5>
            <p class="text-muted mb-0">{{ $partner->name }}</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('my-users.update', $userToEdit->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Nombre -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $userToEdit->name) }}" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $userToEdit->email) }}" 
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Rol -->
                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label">Rol <span class="text-danger">*</span></label>
                    <select class="form-control @error('role') is-invalid @enderror" 
                            id="role" 
                            name="role" 
                            required>
                        <option value="">-- Seleccionar Rol --</option>
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" 
                                {{ old('role', $userToEdit->roles->first()?->name) == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Estado -->
                <div class="col-md-6 mb-3">
                    <label for="is_active" class="form-label">Estado</label>
                    <select class="form-control" id="is_active" name="is_active">
                        <option value="1" {{ old('is_active', $userToEdit->is_active) == 1 ? 'selected' : '' }}>
                            Activo
                        </option>
                        <option value="0" {{ old('is_active', $userToEdit->is_active) == 0 ? 'selected' : '' }}>
                            Inactivo
                        </option>
                    </select>
                    <small class="form-text text-muted">Los usuarios inactivos no podrán iniciar sesión</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <hr>
                    <h6 class="mb-3">Cambiar Contraseña (Opcional)</h6>
                    <p class="text-muted small">Deja estos campos vacíos si no deseas cambiar la contraseña</p>
                </div>
            </div>

            <div class="row">
                <!-- Nueva Contraseña -->
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Nueva Contraseña</label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password"
                           minlength="8">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Mínimo 8 caracteres</small>
                </div>

                <!-- Confirmar Nueva Contraseña -->
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                    <input type="password" 
                           class="form-control" 
                           id="password_confirmation" 
                           name="password_confirmation"
                           minlength="8">
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <hr class="my-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="feather icon-save"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('my-users.index') }}" class="btn btn-secondary">
                        <i class="feather icon-x"></i> Cancelar
                    </a>
                    
                    @if($userToEdit->id !== auth()->id())
                    <button type="button" 
                            class="btn btn-danger float-right" 
                            onclick="confirmDelete()">
                        <i class="feather icon-trash-2"></i> Eliminar Usuario
                    </button>
                    @endif
                </div>
            </div>
        </form>
        
        @if($userToEdit->id !== auth()->id())
        <form id="delete-form" 
              action="{{ route('my-users.destroy', $userToEdit->id) }}" 
              method="POST" 
              style="display: none;">
            @csrf
            @method('DELETE')
        </form>
        @endif
    </div>
</div>

<!-- Información adicional -->
<div class="card mt-3">
    <div class="card-body">
        <h6 class="mb-3">Información del Usuario</h6>
        <div class="row">
            <div class="col-md-6">
                <p class="mb-2">
                    <strong>Fecha de registro:</strong> 
                    {{ $userToEdit->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-2">
                    <strong>Última actualización:</strong> 
                    {{ $userToEdit->updated_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
        @if($userToEdit->must_change_password)
        <div class="alert alert-warning mt-3 mb-0">
            <i class="feather icon-alert-circle"></i>
            Este usuario debe cambiar su contraseña en el próximo inicio de sesión.
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
// Validación en tiempo real para contraseñas
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    
    function validatePasswords() {
        if (password.value && passwordConfirmation.value) {
            if (password.value !== passwordConfirmation.value) {
                passwordConfirmation.setCustomValidity('Las contraseñas no coinciden');
            } else {
                passwordConfirmation.setCustomValidity('');
            }
        } else {
            passwordConfirmation.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', validatePasswords);
    passwordConfirmation.addEventListener('input', validatePasswords);
});

// Confirmación de eliminación
function confirmDelete() {
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
                document.getElementById('delete-form').submit();
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
                document.getElementById('delete-form').submit();
            }
        });
    } else {
        if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
            document.getElementById('delete-form').submit();
        }
    }
}
</script>
@endsection