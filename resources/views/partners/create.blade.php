@extends('layouts.app')

@section('title', 'Nuevo Partner')

@section('content')
<div class="card">
    <div class="card-header"><h5>Crear Partner</h5></div>

    <div class="card-body">
        <form action="{{ route('partners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label class="form-label col-md-2">Nombre Comercial</label>
                <input type="text" name="nombre_comercial" class="form-control col-md-4" required>
            </div>

            <div class="form-group">
                <label class="form-label col-md-2">Razón Social</label>
                <input type="text" name="razon_social" class="form-control col-md-4">
            </div>

            <div class="form-group">
                <label class="form-label col-md-2">RFC</label>
                <input type="text" name="rfc" class="form-control col-md-4">
            </div>

            <div class="form-group">
                <label class="form-label col-md-2">Teléfono</label>
                <input type="text" name="telefono" class="form-control col-md-4">
            </div>

            <div class="form-group">
                <label class="form-label col-md-2">Correo de Contacto</label>
                <input type="email" name="correo_contacto" class="form-control col-md-4">
            </div>

            <div class="form-group">
                <label class="form-label col-md-2">Dirección</label>
                <textarea name="direccion" class="form-control col-md-4"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label col-md-2">Tipo</label>
                <select name="tipo" class="form-control col-md-4" required>
                    <option value="asociado">Asociado</option>
                    <option value="proveedor">Proveedor</option>
                    <option value="mixto">Proveedor-Asociado</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label col-md-2">Condiciones Comerciales</label>
                <textarea name="condiciones_comerciales" class="form-control col-md-4"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label col-md-2">Comentarios</label>
                <textarea name="comentarios" class="form-control col-md-4"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label col-md-2">¿Activo?</label>
                <select name="is_active" class="form-control col-md-4">
                    <option value="1" selected>Sí</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label col-md-2">Logo (opcional)</label>
                <input type="file" name="logo" class="form-control col-md-4" accept="image/*">
                @error('logo') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="{{ route('partners.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
