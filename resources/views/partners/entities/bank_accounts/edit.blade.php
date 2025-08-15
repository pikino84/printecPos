@extends('layouts.app')
@section('title', 'Editar cuenta bancaria')

@section('content')
<div class="card">
  <div class="card-header">
    <h5>Editar cuenta · {{ $entity->razon_social }}</h5>
  </div>

  <div class="card-body">
    <form method="POST" action="{{ route('bank-accounts.update', $bankAccount) }}">
      @csrf @method('PUT')

      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label>Banco *</label>
            <input name="bank_name" class="form-control" value="{{ old('bank_name',$bankAccount->bank_name) }}" required>
            @error('bank_name')<small class="text-danger">{{ $message }}</small>@enderror
          </div>

          <div class="form-group mb-3">
            <label>Alias</label>
            <input name="alias" class="form-control" value="{{ old('alias',$bankAccount->alias) }}">
          </div>

          <div class="form-group mb-3">
            <label>Titular</label>
            <input name="account_holder" class="form-control" value="{{ old('account_holder',$bankAccount->account_holder) }}">
          </div>

          <div class="form-group mb-3">
            <label>Número de cuenta</label>
            <input name="account_number" class="form-control" value="{{ old('account_number',$bankAccount->account_number) }}">
            @error('account_number')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group mb-3">
            <label>CLABE (18 dígitos)</label>
            <input name="clabe" class="form-control" maxlength="18" value="{{ old('clabe',$bankAccount->clabe) }}">
            @error('clabe')<small class="text-danger">{{ $message }}</small>@enderror
          </div>

          <div class="form-group mb-3">
            <label>SWIFT</label>
            <input name="swift" class="form-control" value="{{ old('swift',$bankAccount->swift) }}">
          </div>

          <div class="form-group mb-3">
            <label>IBAN</label>
            <input name="iban" class="form-control" value="{{ old('iban',$bankAccount->iban) }}">
          </div>

          <div class="form-group mb-3">
            <label>Moneda</label>
            <input name="currency" class="form-control col-md-4" value="{{ old('currency',$bankAccount->currency) }}">
          </div>

          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default',$bankAccount->is_default) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_default">Marcar como principal</label>
          </div>

          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active',$bankAccount->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Activa</label>
          </div>
        </div>
      </div>

      <button class="btn btn-primary">Actualizar</button>
      <a href="{{ route('partner-entities.bank-accounts.index', $entity) }}" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</div>
@endsection
