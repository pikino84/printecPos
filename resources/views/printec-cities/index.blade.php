@extends('layouts.app')

@section('title', 'Ciudades Printec')

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col-md-12">
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
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Crear nueva ciudad</h5>
        <span>Por favor, completa el siguiente formulario para agregar una nueva ciudad para los almacenes.</span>
    </div>
    <div class="card-body">
        <form action="{{ url('/printec-cities') }}" method="POST" class="form-inline">
            @csrf
            <div class="form-group mr-2 mb-2">
                <input type="text" name="name" class="form-control" placeholder="Nueva ciudad" required>
            </div>
            <button type="submit" class="btn btn-success mb-2">Agregar</button>
        </form>
    </div>
    <div class="card-block table-border-style">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cities as $city)
                        <tr>
                            <td>
                                <form method="POST" action="{{ url('/printec-cities/' . $city->id) }}" class="form-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" class="form-control form-control-sm mr-2" value="{{ $city->name }}" required>
                                    <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
                                </form>
                            </td>
                            <td>{{ $city->slug }}</td>
                            <td>
                                <form action="{{ url('/printec-ciudades/' . $city->id) }}" method="POST" style="display:inline;" id="delete-form-{{ $city->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $city->id }})">Eliminar</button>
                                </form>
                                <script>
                                    function confirmDelete(id) {
                                        swal({
                                            title: "¿Estás seguro?",
                                            text: "Esta acción no se puede deshacer.",
                                            icon: "warning",
                                            buttons: ["Cancelar", "Eliminar"],
                                            dangerMode: true,
                                        }).then((willDelete) => {
                                            if (willDelete) {
                                                document.getElementById(`delete-form-${id}`).submit();
                                            }
                                        });
                                    }
                                </script>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3">No hay ciudades registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
