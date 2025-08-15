@extends('layouts.app')

@section('title', 'Editar Partner')

@section('content')
<div class="card">
    <div class="card-header"><h5>Editar Partner</h5></div>

    <div class="card-body">
        <form action="{{ route('partners.update', $partner) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Nombre Comercial</label>
                <input type="text" name="nombre_comercial" class="form-control" value="{{ $partner->nombre_comercial }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Razón Social</label>
                <input type="text" name="razon_social" class="form-control" value="{{ $partner->razon_social }}">
            </div>

            <div class="form-group">
                <label class="form-label">RFC</label>
                <input type="text" name="rfc" class="form-control" value="{{ $partner->rfc }}">
            </div>

            <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="{{ $partner->telefono }}">
            </div>

            <div class="form-group">
                <label class="form-label">Correo de Contacto</label>
                <input type="email" name="correo_contacto" class="form-control" value="{{ $partner->correo_contacto }}">
            </div>

            <div class="form-group">
                <label class="form-label">Dirección</label>
                <textarea name="direccion" class="form-control">{{ $partner->direccion }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-control" required>
                    <option value="asociado" {{ $partner->tipo === 'asociado' ? 'selected' : '' }}>Asociado</option>
                    <option value="proveedor" {{ $partner->tipo === 'proveedor' ? 'selected' : '' }}>Proveedor</option>
                    <option value="mixto" {{ $partner->tipo === 'mixto' ? 'selected' : '' }}>Proveedor-Asociado</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Condiciones Comerciales</label>
                <textarea name="condiciones_comerciales" class="form-control">{{ $partner->condiciones_comerciales }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Comentarios</label>
                <textarea name="comentarios" class="form-control">{{ $partner->comentarios }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">¿Activo?</label>
                <select name="is_active" class="form-control">
                    <option value="1" {{ $partner->is_active ? 'selected' : '' }}>Sí</option>
                    <option value="0" {{ !$partner->is_active ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label col-md-2 d-block">Logo</label>

                @if($partner->logo_url)
                    <div class="mb-2">
                        <img src="{{ $partner->logo_url }}" alt="Logo" style="max-height: 80px">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo" value="1">
                        <label class="form-check-label" for="remove_logo">Quitar logo</label>
                    </div>
                @endif

                <input type="file" name="logo" class="form-control col-md-4" accept="image/*">
                <small class="text-muted">JPG/PNG/WEBP hasta 2MB.</small>
                @error('logo') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('partners.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
