@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h4><i class="feather icon-edit"></i> Editar Cliente</h4>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Error:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <form method="POST" action="{{ route('clients.update', $client) }}">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h5>Información Personal</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control form-control-sm @error('nombre') is-invalid @enderror" 
                            name="nombre"
                            value="{{ old('nombre', $client->nombre) }}"
                            required
                        >
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Apellido <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control form-control-sm @error('apellido') is-invalid @enderror" 
                            name="apellido"
                            value="{{ old('apellido', $client->apellido) }}"
                            required
                        >
                        @error('apellido')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input 
                            type="email" 
                            class="form-control form-control-sm @error('email') is-invalid @enderror" 
                            name="email"
                            value="{{ old('email', $client->email) }}"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Teléfono</label>
                        <input 
                            type="text" 
                            class="form-control form-control-sm @error('telefono') is-invalid @enderror" 
                            name="telefono"
                            value="{{ old('telefono', $client->telefono) }}"
                        >
                        @error('telefono')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Información Fiscal</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>RFC</label>
                        <input 
                            type="text" 
                            class="form-control form-control-sm @error('rfc') is-invalid @enderror" 
                            name="rfc"
                            value="{{ old('rfc', $client->rfc ?? '') }}"
                            maxlength="13"
                            placeholder="XAXX010101000"
                            style="text-transform: uppercase;"
                        >
                        @error('rfc')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Razón Social</label>
                        <input 
                            type="text" 
                            class="form-control form-control-sm @error('razon_social') is-invalid @enderror" 
                            name="razon_social"
                            value="{{ old('razon_social', $client->razon_social) }}"
                        >
                        @error('razon_social')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label>Dirección</label>
                    <textarea 
                        class="form-control form-control-sm @error('direccion') is-invalid @enderror" 
                        name="direccion"
                        rows="2"
                    >{{ old('direccion', $client->direccion) }}</textarea>
                    @error('direccion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Campo oculto con el partner del usuario actual (si no es super admin) --}}
        @if(!auth()->user()->hasRole('super admin'))
            <input type="hidden" name="partner_ids[]" value="{{ auth()->user()->partner_id }}">
        @endif

        {{-- Solo mostrar la sección si es super admin --}}
        @if(auth()->user()->hasRole('super admin'))
        <div class="card mt-3">
            <div class="card-header">
                <h5>Partners Asociados <span class="text-danger">*</span></h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Seleccione los partners que tienen contacto con este cliente
                </p>

                @foreach($partners as $partner)
                    <div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            name="partner_ids[]" 
                            value="{{ $partner->id }}"
                            id="partner_{{ $partner->id }}"
                            {{ in_array($partner->id, old('partner_ids', $selectedPartnerIds ?? [])) ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="partner_{{ $partner->id }}">
                            {{ $partner->name }}
                            <span class="badge badge-secondary">{{ $partner->type }}</span>
                        </label>
                    </div>
                @endforeach

                @error('partner_ids')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>
        @endif

        <div class="card mt-3">
            <div class="card-header">
                <h5>Notas Adicionales</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label>Notas Internas</label>
                    <textarea 
                        class="form-control form-control-sm @error('notas') is-invalid @enderror" 
                        name="notas"
                        rows="3"
                        placeholder="Notas internas sobre el cliente..."
                    >{{ old('notas', $client->notas) }}</textarea>
                    @error('notas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-3 mb-4">
            <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                <i class="feather icon-arrow-left"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary float-right">
                <i class="feather icon-save"></i> Guardar Cliente
            </button>
        </div>
    </form>
</div>
@endsection