<div class="tab-pane fade" id="csd" role="tabpanel">
  <div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Importante:</strong> Los certificados CSD (Certificado de Sello Digital) son necesarios para firmar y timbrar facturas electronicas.
    Estos archivos son proporcionados por el SAT y son diferentes a la e.firma (FIEL).
  </div>

  @if($entity->hasCsdConfigured())
    <div class="alert alert-success">
      <i class="fas fa-certificate me-2"></i>
      <strong>Certificados configurados</strong>
      @if($entity->csd_valid_until)
        <br>Validos hasta: {{ $entity->csd_valid_until->format('d/m/Y') }}
        @if($entity->csd_valid_until->isPast())
          <span class="badge bg-danger ms-2">EXPIRADO</span>
        @elseif($entity->csd_valid_until->diffInDays(now()) < 30)
          <span class="badge bg-warning ms-2">Por vencer</span>
        @else
          <span class="badge bg-success ms-2">Vigente</span>
        @endif
      @endif
    </div>
  @endif

  <div class="row">
    <div class="col-md-6">
      <div class="form-group mb-3">
        <label class="form-label">
          Certificado (.cer)
          @if($entity->csd_cer_path)
            <span class="badge bg-success ms-2">Cargado</span>
          @endif
        </label>
        <input type="file" class="form-control @error('csd_cer') is-invalid @enderror" name="csd_cer" accept=".cer">
        @error('csd_cer')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="text-muted">Archivo .cer del CSD proporcionado por el SAT</small>
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group mb-3">
        <label class="form-label">
          Llave privada (.key)
          @if($entity->csd_key_path)
            <span class="badge bg-success ms-2">Cargado</span>
          @endif
        </label>
        <input type="file" class="form-control @error('csd_key') is-invalid @enderror" name="csd_key" accept=".key">
        @error('csd_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="text-muted">Archivo .key del CSD proporcionado por el SAT</small>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="form-group mb-3">
        <label class="form-label">Contrasena del CSD</label>
        <input type="password" name="csd_password" class="form-control @error('csd_password') is-invalid @enderror"
               placeholder="{{ $entity->csd_password ? '********' : 'Ingrese la contrasena' }}">
        @error('csd_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="text-muted">Contrasena de la llave privada del CSD</small>
      </div>
    </div>
  </div>

  @if($entity->csd_cer_path || $entity->csd_key_path)
    <div class="form-check mb-3">
      <input type="checkbox" class="form-check-input" id="remove_csd" name="remove_csd" value="1">
      <label class="form-check-label text-danger" for="remove_csd">
        <i class="fas fa-trash me-1"></i> Eliminar certificados actuales
      </label>
    </div>
  @endif

  <div class="card bg-light mt-3">
    <div class="card-body">
      <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Como obtener los certificados CSD?</h6>
      <ol class="mb-0 small">
        <li>Ingresa al portal del SAT: <a href="https://www.sat.gob.mx" target="_blank">www.sat.gob.mx</a></li>
        <li>Accede con tu e.firma (FIEL)</li>
        <li>Ve a "Tramites del RFC" - "Certificados" - "Certificado de Sello Digital"</li>
        <li>Genera un nuevo CSD para facturacion</li>
        <li>Descarga los archivos .cer y .key</li>
        <li>Guarda la contrasena que elegiste</li>
      </ol>
    </div>
  </div>
</div>
