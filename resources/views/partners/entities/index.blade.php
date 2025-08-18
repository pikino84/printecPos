@extends('layouts.app')
@section('title','Razones sociales')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">
    Razones sociales de: <strong>{{ $partner->nombre_comercial }}</strong>
  </h4>
  <a href="{{ route('partners.entities.create', $partner) }}" class="btn btn-success">Agregar razón social</a>
</div>

<div class="card">
  <div class="card-body table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Logo</th>
          <th>Razón social</th>
          <th>RFC</th>
          <th>Correo</th>
          <th>Teléfono</th>
          <th>Principal</th>
          <th>Activo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($entities as $e)
          <tr>
            <td>
              @if($e->logo_path)
                <img src="/storage/{{ $e->logo_path }}" alt="Logo" style="height:48px" class="rounded shadow-sm">
              @endif
            </td>
            <td>{{ $e->razon_social }}</td>
            <td>{{ $e->rfc }}</td>
            <td>{{ $e->correo_contacto }}</td>
            <td>{{ $e->telefono }}</td>
            <td>
              @if($e->is_default)
                <span class="badge bg-primary">Sí</span>
              @else
                <span class="text-muted">No</span>
              @endif
            </td>
            <td>
              @if($e->is_active)
                <span class="badge bg-success">Sí</span>
              @else
                <span class="badge bg-secondary">No</span>
              @endif
            </td>
            <td class="d-flex gap-2">
              <a href="{{ route('entities.edit', $e) }}" class="btn btn-sm btn-primary">Editar</a>
              <form method="POST" action="{{ route('entities.destroy', $e) }}" onsubmit="return confirm('¿Eliminar?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
              </form>

              <a href="{{ route('partner-entities.bank-accounts.index', $e) }}" class="btn btn-sm btn-outline-secondary">Cuentas</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted">Sin razones sociales aún.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<a href="{{ route('partners.index') }}" class="btn btn-secondary mt-3">Volver a partners</a>
@endsection
