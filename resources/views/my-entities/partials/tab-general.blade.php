<div class="tab-pane fade show active" id="general" role="tabpanel">
  <div class="row">
    <div class="col-md-6">
      <div class="form-group mb-3">
        <label class="form-label">Razon Social <span class="text-danger">*</span></label>
        <input name="razon_social" class="form-control @error('razon_social') is-invalid @enderror"
               required value="{{ old('razon_social', $entity->razon_social) }}">
        @error('razon_social')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group mb-3">
        <label class="form-label">RFC <span class="text-danger">*</span></label>
        <input name="rfc" class="form-control @error('rfc') is-invalid @enderror"
               value="{{ old('rfc', $entity->rfc) }}" maxlength="13"
               style="text-transform: uppercase;" placeholder="XAXX010101000">
        @error('rfc')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="form-group mb-3">
        <label class="form-label">Telefono</label>
        <input name="telefono" class="form-control" value="{{ old('telefono', $entity->telefono) }}">
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group mb-3">
        <label class="form-label">Correo de contacto</label>
        <input type="email" name="correo_contacto" class="form-control"
               value="{{ old('correo_contacto', $entity->correo_contacto) }}">
      </div>
    </div>
  </div>

  <div class="form-group mb-3">
    <label class="form-label">Direccion fiscal</label>
    <textarea name="direccion" class="form-control" rows="2">{{ old('direccion', $entity->direccion) }}</textarea>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="form-group mb-3">
        <label class="form-label">Logo (JPG/PNG/WEBP max 2MB)</label>
        @if($entity->logo_path)
          <div class="mb-2">
            <img src="{{ Storage::url($entity->logo_path) }}" alt="Logo" style="height:48px" class="rounded shadow-sm">
            <div class="form-check mt-1">
              <input type="checkbox" class="form-check-input" id="remove_logo" name="remove_logo" value="1">
              <label class="form-check-label text-danger small" for="remove_logo">Eliminar logo</label>
            </div>
          </div>
        @endif
        <input type="file" class="form-control @error('logo') is-invalid @enderror"
              name="logo" accept=".jpg,.jpeg,.png,.webp">
        @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group mb-3">
        <label class="form-label">Opciones</label>
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1"
                 {{ old('is_default', $entity->is_default) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_default">Marcar como razon social principal</label>
        </div>
      </div>
    </div>
  </div>
</div>
