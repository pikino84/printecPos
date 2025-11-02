@extends('layouts.app')
@section('title', 'Editar Usuario')

@section('content')
<div class="card">
    <div class="card-header"><h5>Editar Usuario</h5></div>
    <div class="card-block">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
            </div>

            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
            </div>

            <div class="form-group">
                <label>Nueva Contraseña (opcional 8 caracteres mínimo )</label>
                <input type="password" name="password" class="form-control">
            </div>

            {{-- Solo Printec puede editar el partner --}}
            @if (auth()->user()->partner_id === 1)
                <div class="form-group">
                    <label>Partner</label>
                    <select name="partner_id" class="form-control">
                        <option value="">Ninguno</option>
                        @foreach ($partners as $partner)
                            <option value="{{ $partner->id }}" {{ $user->partner_id == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="form-group">
                <label>Roles</label>                
                <select name="role" class="form-control" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" {{ $user->roles->pluck('name')->contains($role) ? 'selected' : '' }}>
                        {{ $role }}
                    </option>

                    @endforeach
                </select>
                
            </div>

            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
