@extends('layouts.app')

@section('title', 'Nuevo Asociado')

@section('content')
<div class="card">
    <div class="card-header"><h5>Crear Asociado</h5></div>

    <div class="card-body">
        <form action="{{ route('asociados.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Nombre Comercial</label>
                <input type="text" name="nombre_comercial" class="form-control" value="{{ old('nombre_comercial') }}" required>
            </div>

            <div class="form-group">
                <label>Razón Social</label>
                <input type="text" name="razon_social" class="form-control" value="{{ old('razon_social') }}" required>
            </div>

            <div class="form-group">
                <label>RFC</label>
                <input type="text" name="rfc" class="form-control" value="{{ old('rfc') }}">
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
            </div>

            <div class="form-group">
                <label>Correo de Contacto</label>
                <input type="email" name="correo_contacto" class="form-control" value="{{ old('correo_contacto') }}">
            </div>

            <div class="form-group">
                <label>Dirección</label>
                <textarea name="direccion" rows="3" class="form-control">{{ old('direccion') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('asociados.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
