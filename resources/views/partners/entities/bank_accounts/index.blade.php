@extends('layouts.app')
@section('title', 'Cuentas bancarias')

@section('content')
<div class="page-header">
  <div class="row">
    <div class="col-md-12">
      <h5>
        Cuentas bancarias · <strong>{{ $entity->razon_social }}</strong>
        <small class="text-muted">({{ $entity->partner->name }})</small>
      </h5>

      @if (session('success'))
        <script>
          document.addEventListener("DOMContentLoaded", function () {
            swal("¡Éxito!", "{{ session('success') }}", "success");
          });
        </script>
      @elseif (session('error'))
        <script>
          document.addEventListener("DOMContentLoaded", function () {
            swal("¡Error!", "{{ session('error') }}", "error");
          });
        </script>
      @endif

      <div class="d-flex gap-2">
        <a href="{{ route('partner-entities.bank-accounts.create', $entity) }}" class="btn btn-primary">Agregar cuenta</a>
        <a href="{{ route('partners.entities.index', $entity->partner) }}" class="btn btn-secondary">Volver a razones sociales</a>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-block table-border-style">
    <div class="table-responsive">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Alias</th>
            <th>Banco</th>
            <th>Titular</th>
            <th>Cuenta</th>
            <th>CLABE</th>
            <th>Moneda</th>
            <th>Principal</th>
            <th>Activo</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        @forelse($accounts as $acc)
          <tr>
            <td>{{ $acc->alias ?: '—' }}</td>
            <td>{{ $acc->bank_name }}</td>
            <td>{{ $acc->account_holder ?: '—' }}</td>
            <td>{{ $acc->account_number ?: '—' }}</td>
            <td>{{ $acc->clabe ?: '—' }}</td>
            <td>{{ $acc->currency }}</td>
            <td>{!! $acc->is_default ? '<span class="badge bg-primary">Sí</span>' : '<span class="text-muted">No</span>' !!}</td>
            <td>{!! $acc->is_active ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
            <td class="d-flex gap-2">
              <a href="{{ route('bank-accounts.edit', $acc) }}" class="btn btn-sm btn-warning">Editar</a>

              <form method="POST" action="{{ route('bank-accounts.destroy', $acc) }}" id="del-acc-{{ $acc->id }}">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $acc->id }})">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="text-center text-muted">Sin cuentas registradas.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function confirmDelete(id){
  swal({
    title: "¿Eliminar cuenta?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  }).then((ok) => { if(ok) document.getElementById('del-acc-' + id).submit(); });
}
</script>
@endsection
