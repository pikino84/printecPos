@extends('layouts.app')

@section('title', 'Asociados')

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col-md-12">
            <h5>Partners</h5>
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
            <a href="{{ route('partners.create') }}" class="btn btn-primary float-right">Agregar Partner</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-block table-border-style">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nombre Comercial</th>
                        <th>Slug</th>
                        <th>Razón Social</th>
                        <th>Tipo</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($partners as $partner)
                        <tr>
                            <td>{{ $partner->nombre_comercial }}</td>
                            <td>{{ $partner->slug }}</td>
                            <td>{{ $partner->razon_social }}</td>
                            <td>{{ ucfirst($partner->tipo) }}</td>
                            <td>{{ $partner->correo_contacto }}</td>
                            <td>{{ $partner->telefono }}</td>
                            <td>{{ $partner->is_active ? 'Sí' : 'No' }}</td>
                            <td class="d-flex gap-1">
                              {{-- Ir al CRUD de razones sociales del partner --}}
                              <a href="{{ route('partners.entities.index', $partner) }}"
                                class="btn btn-sm btn-outline-primary">
                                  Razones sociales
                                  @if(isset($partner->entities_count))
                                      <span class="badge bg-primary">{{ $partner->entities_count }}</span>
                                  @endif
                              </a>
                              
                              <a href="{{ route('partners.edit', $partner) }}" class="btn btn-sm btn-warning">Editar</a>
                              <form action="{{ route('partners.destroy', $partner) }}" method="POST" style="display:inline;" id="delete-form-{{ $partner->id }}">
                                  @csrf
                                  @method('DELETE')
                                  <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $partner->id }})">Eliminar</button>
                              </form>

                              <script>
                                  function confirmDelete(partnerId) {
                                      swal({
                                          title: "¿Estás seguro?",
                                          text: "¡No podrás recuperar este registro!",
                                          icon: "warning",
                                          buttons: true,
                                          dangerMode: true,
                                      }).then((willDelete) => {
                                          if (willDelete) {
                                              document.getElementById('delete-form-' + partnerId).submit();
                                          }
                                      });
                                  }
                              </script>
                          </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay partners registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection