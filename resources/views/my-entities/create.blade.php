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

      <div class="form-group mb-3">
        <label>Condiciones de pago</label>
        <textarea name="payment_terms" class="form-control" rows="8" placeholder="Ej: Vigencia de cotización, tiempos de entrega, datos bancarios...">{{ old('payment_terms') }}</textarea>
        <small class="text-muted">Estas condiciones se mostrarán en los correos de cotización enviados a los clientes.</small>
      </div>

      <div class="card mb-3">
        <div class="card-header bg-light">
          <h6 class="mb-0"><i class="feather icon-clock"></i> Configuración de trabajo urgente</h6>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label>Porcentaje por trabajo urgente (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="urgent_fee_percentage" class="form-control" value="{{ old('urgent_fee_percentage') }}" placeholder="Ej: 15">
                <small class="text-muted">Porcentaje extra que se aplica sobre el subtotal.</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label>Plazo urgente (días)</label>
                <input type="number" min="1" max="365" name="urgent_days_limit" class="form-control" value="{{ old('urgent_days_limit') }}" placeholder="Ej: 15">
                <small class="text-muted">Días límite para considerar trabajo urgente.</small>
              </div>
            </div>
          </div>
          <small class="text-muted">
            <i class="feather icon-info"></i> Ejemplo: Si configuras 15% y 15 días, los trabajos que se entreguen en menos de 15 días tendrán un cargo extra del 15%.
          </small>
        </div>
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
