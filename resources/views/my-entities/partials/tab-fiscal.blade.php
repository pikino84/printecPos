<div class="tab-pane fade" id="fiscal" role="tabpanel">
  <div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    Estos datos son necesarios para poder emitir facturas electronicas (CFDI).
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="form-group mb-3">
        <label class="form-label">Regimen Fiscal <span class="text-danger">*</span></label>
        <select name="fiscal_regime" class="form-select @error('fiscal_regime') is-invalid @enderror">
          <option value="">-- Seleccionar --</option>
          @php
          $regimes = [
            '601' => '601 - General de Ley Personas Morales',
            '603' => '603 - Personas Morales con Fines no Lucrativos',
            '605' => '605 - Sueldos y Salarios',
            '606' => '606 - Arrendamiento',
            '612' => '612 - Personas Fisicas con Actividades Empresariales y Profesionales',
            '616' => '616 - Sin obligaciones fiscales',
            '621' => '621 - Incorporacion Fiscal',
            '625' => '625 - Regimen de Plataformas Tecnologicas',
            '626' => '626 - Regimen Simplificado de Confianza (RESICO)',
          ];
          @endphp
          @foreach($regimes as $code => $label)
            <option value="{{ $code }}" {{ old('fiscal_regime', $entity->fiscal_regime) == $code ? 'selected' : '' }}>
              {{ $label }}
            </option>
          @endforeach
        </select>
        @error('fiscal_regime')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group mb-3">
        <label class="form-label">Codigo Postal Fiscal <span class="text-danger">*</span></label>
        <input name="zip_code" class="form-control @error('zip_code') is-invalid @enderror"
               value="{{ old('zip_code', $entity->zip_code) }}" maxlength="5" placeholder="Ej: 03100">
        @error('zip_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="text-muted">Codigo postal del domicilio fiscal registrado ante el SAT</small>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-4">
      <div class="form-group mb-3">
        <label class="form-label">Serie de Factura</label>
        <input name="invoice_series" class="form-control @error('invoice_series') is-invalid @enderror"
               value="{{ old('invoice_series', $entity->invoice_series ?? 'A') }}" maxlength="10" placeholder="Ej: A, B, FAC">
        @error('invoice_series')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-group mb-3">
        <label class="form-label">Siguiente Folio</label>
        <input type="number" name="invoice_next_folio" class="form-control @error('invoice_next_folio') is-invalid @enderror"
               value="{{ old('invoice_next_folio', $entity->invoice_next_folio ?? 1) }}" min="1">
        @error('invoice_next_folio')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-group mb-3">
        <label class="form-label">Proxima factura</label>
        <input type="text" class="form-control" disabled
               value="{{ ($entity->invoice_series ?? 'A') }}-{{ $entity->invoice_next_folio ?? 1 }}">
      </div>
    </div>
  </div>

  <div class="card bg-light mt-3">
    <div class="card-body">
      <h6 class="card-title">Estado de configuracion fiscal</h6>
      <ul class="list-unstyled mb-0">
        <li>
          @if($entity->rfc)
            <i class="fas fa-check-circle text-success me-2"></i> RFC configurado
          @else
            <i class="fas fa-times-circle text-danger me-2"></i> RFC pendiente
          @endif
        </li>
        <li>
          @if($entity->fiscal_regime)
            <i class="fas fa-check-circle text-success me-2"></i> Regimen fiscal configurado
          @else
            <i class="fas fa-times-circle text-danger me-2"></i> Regimen fiscal pendiente
          @endif
        </li>
        <li>
          @if($entity->zip_code)
            <i class="fas fa-check-circle text-success me-2"></i> Codigo postal configurado
          @else
            <i class="fas fa-times-circle text-danger me-2"></i> Codigo postal pendiente
          @endif
        </li>
        <li>
          @if($entity->canIssueInvoices())
            <i class="fas fa-check-circle text-success me-2"></i> <strong>Puede emitir facturas</strong>
          @else
            <i class="fas fa-exclamation-triangle text-warning me-2"></i> <strong>No puede emitir facturas</strong> - Complete los datos faltantes
          @endif
        </li>
      </ul>
    </div>
  </div>
</div>
