@extends('layouts.app')

@section('title', 'Asociados')

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col-md-12">
            <h5>Asociados</h5>
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
            <a href="{{ route('asociados.create') }}" class="btn btn-primary float-right mb-3">Nuevo Asociado</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-block table-border-style">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>RFC</th>
                        <th>Email</th> 
                        <th>Teléfono</th>                      
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asociados as $asociado)
                        <tr>
                            <td>{{ $asociado->nombre_comercial }}</td>
                            <td>{{ $asociado->rfc }}</td>
                            <td>{{ $asociado->email }}</td>
                            <td>{{ $asociado->telefono }}</td>
                            <td>
                                <a href="{{ route('asociados.edit', $asociado) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('asociados.destroy', $asociado) }}" method="POST" style="display:inline;" id="delete-form-{{ $asociado->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $asociado->id }})">Eliminar</button>
                                </form>

                                <script>
                                    function confirmDelete(asociadoId) {
                                        swal({
                                            title: "¿Estás seguro?",
                                            text: "¡No podrás recuperar este registro!",
                                            icon: "warning",
                                            buttons: true,
                                            dangerMode: true,
                                        }).then((willDelete) => {
                                            if (willDelete) {
                                                document.getElementById('delete-form-' + asociadoId).submit();
                                            }
                                        });
                                    }
                                </script>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay asociados registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection