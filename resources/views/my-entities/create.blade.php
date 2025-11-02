@extends('layouts.app')
@section('title','Agregar razón social')

@section('content')
<div class="card">
  <div class="card-header">
    <h5>Agregar razón social a: {{ $partner->nombre_comercial }}</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('partners.entities.store', $partner) }}" enctype="multipart/form-data">
      @csrf

      <div class="form-group mb-3">
        <label>Razón Social</label>
        <input name="razon_social" class="form-control col-md-6" required value="{{ old('razon_social') }}">
        @error('razon_social')<small class="text-danger">{{ $message }}</small>@enderror
      </div>

      <div class="form-group mb-3">
        <label>RFC</label>
        <input name="rfc" class="form-control col-md-6" value="{{ old('rfc') }}">
      </div>

      <div class="form-group mb-3">
        <label>Teléfono</label>
        <input name="telefono" class="form-control col-md-6" value="{{ old('telefono') }}">
      </div>

      <div class="form-group mb-3">
        <label>Correo de contacto</label>
        <input type="email" name="correo_contacto" class="form-control col-md-6" value="{{ old('correo_contacto') }}">
      </div>

      <div class="form-group mb-3">
        <label>Dirección fiscal</label>
        <textarea name="direccion" class="form-control col-md-6">{{ old('direccion') }}</textarea>
      </div>
      <div class="form-group">
        <label>Logo (JPG/PNG/WEBP máx 2MB)</label>
        <input type="file" class="form-control @error('logo_path') is-invalid @enderror"
              name="logo" accept=".jpg,.jpeg,.png,.webp">
        @error('logo_path')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>
      <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1">
        <label class="form-check-label" for="is_default">Marcar como principal</label>
      </div>
      

      <button class="btn btn-success">Guardar</button>
      <a href="{{ route('my-entities.index', $partner) }}" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</div>
@endsection
