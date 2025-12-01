@extends('layouts.app')
@section('title', 'Crear Usuario')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Crear Nuevo Usuario</h5>
            <p class="text-muted mb-0">{{ $partner->name }}</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('my-users.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Nombre -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required 
                           autofocus>
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
                           value="{{ old('email') }}" 
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Rol -->
                <div class="col-md-12 mb-3">
                    <label for="role" class="form-label">Rol <span class="text-danger">*</span></label>
                    <select class="form-control @error('role') is-invalid @enderror" 
                            id="role" 
                            name="role" 
                            required>
                        <option value="">-- Seleccionar Rol --</option>
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        <strong>Asociado Administrador:</strong> Puede gestionar usuarios, razones sociales y cuentas bancarias.<br>
                        <strong>Asociado Vendedor:</strong> Puede ver información y generar cotizaciones.
                    </small>
                </div>
            </div>

            <div class="row">
                <!-- Contraseña -->
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           required
                           minlength="8">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Mínimo 8 caracteres</small>
                </div>

                <!-- Confirmar Contraseña -->
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                    <input type="password" 
                           class="form-control" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required
                           minlength="8">
                </div>
            </div>

            <div class="row">
                <!-- Debe cambiar contraseña -->
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="must_change_password" 
                               name="must_change_password" 
                               value="1"
                               {{ old('must_change_password', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="must_change_password">
                            Obligar al usuario a cambiar la contraseña en el primer inicio de sesión
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Estado Activo -->
                <div class="col-md-6 mb-3">
                    <label for="is_active" class="form-label">Estado</label>
                    <select class="form-control" id="is_active" name="is_active">
                        <option value="1" selected>Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <hr class="my-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="feather icon-save"></i> Crear Usuario
                    </button>
                    <a href="{{ route('my-users.index') }}" class="btn btn-secondary">
                        <i class="feather icon-x"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Validación en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    
    passwordConfirmation.addEventListener('input', function() {
        if (password.value !== passwordConfirmation.value) {
            passwordConfirmation.setCustomValidity('Las contraseñas no coinciden');
        } else {
            passwordConfirmation.setCustomValidity('');
        }
    });
    
    password.addEventListener('input', function() {
        if (password.value !== passwordConfirmation.value) {
            passwordConfirmation.setCustomValidity('Las contraseñas no coinciden');
        } else {
            passwordConfirmation.setCustomValidity('');
        }
    });
});
</script>
@endsection