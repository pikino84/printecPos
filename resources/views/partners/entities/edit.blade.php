@extends('layouts.app')
@section('title','Editar razón social')

@section('content')
<div class="card">
  <div class="card-header">
    <h5>Editar razón social de: {{ $partner->nombre_comercial }}</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('entities.update', $entity) }}" enctype="multipart/form-data">
      @csrf @method('PUT')

      <div class="form-group mb-3">
        <label>Razón Social</label>
        <input name="razon_social" class="form-control col-md-6" required value="{{ old('razon_social',$entity->razon_social) }}">
        @error('razon_social')<small class="text-danger">{{ $message }}</small>@enderror
      </div>

      <div class="form-group mb-3">
        <label>RFC</label>
        <input name="rfc" class="form-control col-md-6" value="{{ old('rfc',$entity->rfc) }}">
      </div>

      <div class="form-group mb-3">
        <label>Teléfono</label>
        <input name="telefono" class="form-control col-md-6" value="{{ old('telefono',$entity->telefono) }}">
      </div>

      <div class="form-group mb-3">
        <label>Correo de contacto</label>
        <input type="email" name="correo_contacto" class="form-control col-md-6" value="{{ old('correo_contacto',$entity->correo_contacto) }}">
      </div>

      <div class="form-group mb-3">
        <label>Dirección fiscal</label>
        <textarea name="direccion" class="form-control col-md-6">{{ old('direccion',$entity->direccion) }}</textarea>
      </div>

      <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1" {{ $entity->is_default ? 'checked' : '' }}>
        <label class="form-check-label" for="is_default">Marcar como principal</label>
      </div>
      <div class="form-group">
        <label>Logo (JPG/PNG/WEBP máx 2MB)</label>
        @if($entity->logo_url)
          <div class="mb-2">
            <img src="{{ $entity->logo_url }}" alt="Logo" style="height:48px" class="rounded shadow-sm">
          </div>
        @endif
        <input type="file" class="form-control @error('logo') is-invalid @enderror"
              name="logo" accept=".jpg,.jpeg,.png,.webp">
        @error('logo')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>
      <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ $entity->is_active ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Activo</label>
      </div>

      <button class="btn btn-primary">Actualizar</button>
      <a href="{{ route('partners.entities.index', $partner) }}" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</div>
@endsection
