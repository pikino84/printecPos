@extends('layouts.app')
@section('title', 'Nuevo Usuario')

@section('content')
<div class="card">
    {{-- show error messages --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <div class="card-header"><h5>Crear Usuario</h5></div>
    <div class="card-block">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf           
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Contraseña (8 caracteres mínimo)</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            {{-- Solo Printec puede elegir el partner --}}
            @if (auth()->user()->partner_id === 1)
                <div class="form-group">
                    <label for="partner_id">Partner</label>
                    <select name="partner_id" class="form-control">
                        <option value="">-- Selecciona --</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}"
                                {{ old('partner_id', $user->partner_id ?? '') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="partner_id" value="{{ Auth::user()->partner_id }}">
            @endif

            {{-- Fin del select de asociados --}}

            <div class="form-group">
                <label>Roles</label>
                <select name="role" class="form-control" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}">{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Solo Printec puede establecer el estado activo --}}
            @if (auth()->user()->partner_id === 1)
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Usuario Activo</label>
                    </div>
                </div>
            @endif

            <button type="submit" class="btn btn-primary">Crear</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
