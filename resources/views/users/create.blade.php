@extends('layouts.app')
@section('title', 'Nuevo Usuario')

@section('content')
<div class="card">
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

            {{-- Solo Printec puede elegir el asociado --}}
            @if (auth()->user()->asociado_id === null)
                <div class="form-group">
                    <label>Asociado</label>
                    <select name="asociado_id" class="form-control">
                        <option value="">Ninguno</option>
                        @foreach ($asociados as $asociado)
                            <option value="{{ $asociado->id }}">{{ $asociado->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="form-group">
                <label>Roles</label>
                <select name="role" class="form-control" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}">{{ $role }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Crear</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
