@extends('layouts.app')
@section('title', 'Agregar Partner')

@section('content')
{{-- show error --}}
<div class="mb-4">
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
</div>
<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('partners.store') }}">
      @csrf

      <div class="form-group mb-3">
        <label>Nombre del Partner</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="form-group">
          <label>Nombre de la persona de contacto</label>
          <input type="text" name="contact_name" value="{{ old('contact_name') }}" class="form-control">
        </div>
        <div class="form-group">
          <label>Celular de la persona de contacto</label>
          <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="form-control">
        </div>
        <div class="form-group">
          <label>Correo de la persona de contacto</label>
          <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="form-control">
        </div>
      </div>

      {{-- Dirección --}}
      <div class="form-group mt-3">
        <label>Dirección</label>
        <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
        <div class="form-group">
          <label>Tipo</label>
          <select name="type" class="form-control" required>
            @foreach (['Proveedor','Asociado','Mixto'] as $opt)
              <option value="{{ $opt }}" @selected(old('type')===$opt)>{{ $opt }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label>¿Activo?</label>
          <select name="is_active" class="form-control" required>
            <option value="1" @selected(old('is_active',1)==1)>Sí</option>
            <option value="0" @selected(old('is_active')==0)>No</option>
          </select>
        </div>
      </div>

      <div class="form-group mt-3">
        <label>Condiciones comerciales</label>
        <textarea name="commercial_terms" class="form-control" rows="3">{{ old('commercial_terms') }}</textarea>
      </div>

      <div class="form-group mt-3">
        <label>Comentarios</label>
        <textarea name="comments" class="form-control" rows="3">{{ old('comments') }}</textarea>
      </div>

      <div class="mt-4 flex gap-2">
        <button class="btn btn-primary">Guardar</button>
        <a href="{{ route('partners.index') }}" class="btn btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection
