@extends('layouts.app')

@section('title', 'Permisos')

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col-md-12">
            <h5>Permisos</h5>
            <a href="{{ route('permissions.create') }}" class="btn btn-primary float-right mb-3">Nuevo Permiso</a>
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
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->name }}</td>
                            <td>
                                <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('permissions.destroy', $permission) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este permiso?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">No hay permisos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection