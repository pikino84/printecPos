@extends('layouts.app')
@section('title', 'Agregar Cuenta Bancaria')

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col-md-12">
            <h5>Agregar Cuenta Bancaria</h5>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6>Nueva Cuenta Bancaria</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('my-bank-accounts.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                    <select name="partner_entity_id" class="form-control @error('partner_entity_id') is-invalid @enderror" required>
                        <option value="">Seleccionar razón social</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}" {{ old('partner_entity_id') == $entity->id ? 'selected' : '' }}>
                                {{ $entity->razon_social }}
                                @if($entity->rfc)
                                    - RFC: {{ $entity->rfc }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('partner_entity_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Selecciona la razón social a la que pertenece esta cuenta</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Banco <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="bank_name" 
                               class="form-control @error('bank_name') is-invalid @enderror" 
                               value="{{ old('bank_name') }}" 
                               required
                               placeholder="Ej: BBVA, Santander, Banamex">
                        @error('bank_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Alias</label>
                        <input type="text" 
                               name="alias" 
                               class="form-control @error('alias') is-invalid @enderror" 
                               value="{{ old('alias') }}"
                               placeholder="Ej: Cuenta principal, Cuenta nómina">
                        @error('alias')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Nombre descriptivo para identificar la cuenta</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Titular</label>
                        <input type="text" 
                               name="account_holder" 
                               class="form-control @error('account_holder') is-invalid @enderror" 
                               value="{{ old('account_holder') }}"
                               placeholder="Nombre del titular de la cuenta">
                        @error('account_holder')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Número de Cuenta</label>
                        <input type="text"
                               name="account_number"
                               class="form-control @error('account_number') is-invalid @enderror"
                               value="{{ old('account_number') }}"
                               maxlength="40"
                               placeholder="Número de cuenta bancaria">
                        @error('account_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Número de Tarjeta</label>
                        <input type="text"
                               name="card_number"
                               class="form-control @error('card_number') is-invalid @enderror"
                               value="{{ old('card_number') }}"
                               maxlength="20"
                               placeholder="16 dígitos de la tarjeta">
                        @error('card_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">CLABE Interbancaria</label>
                        <input type="text" 
                               name="clabe" 
                               class="form-control @error('clabe') is-invalid @enderror" 
                               value="{{ old('clabe') }}"
                               maxlength="18"
                               placeholder="18 dígitos">
                        @error('clabe')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">18 dígitos para transferencias SPEI</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Código SWIFT/BIC</label>
                        <input type="text" 
                               name="swift" 
                               class="form-control @error('swift') is-invalid @enderror" 
                               value="{{ old('swift') }}"
                               maxlength="20"
                               placeholder="Para transferencias internacionales">
                        @error('swift')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">IBAN</label>
                        <input type="text" 
                               name="iban" 
                               class="form-control @error('iban') is-invalid @enderror" 
                               value="{{ old('iban') }}"
                               maxlength="34"
                               placeholder="Número de cuenta internacional">
                        @error('iban')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Moneda</label>
                        <select name="currency" class="form-control @error('currency') is-invalid @enderror">
                            <option value="MXN" {{ old('currency', 'MXN') == 'MXN' ? 'selected' : '' }}>MXN - Peso Mexicano</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - Dólar Americano</option>
                            <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_default" 
                               name="is_default" 
                               value="1" 
                               {{ old('is_default') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">
                            <strong>Marcar como cuenta principal</strong>
                            <br><small class="text-muted">La cuenta principal se usará por defecto para esta razón social</small>
                        </label>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="feather icon-save"></i> Guardar Cuenta
                </button>
                <a href="{{ route('my-bank-accounts.index') }}" class="btn btn-secondary">
                    <i class="feather icon-x"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection