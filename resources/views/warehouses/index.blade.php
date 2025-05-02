@extends('layouts.app')

@section('title', 'Almacenes')

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col-md-12">
            <h5>Almacenes</h5>
            @if (session('success'))
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    swal("¡Éxito!", "{{ session('success') }}", "success");
                });
            </script>
            @endif
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>                    
                    <th>Proveedor</th>
                    <th>Nombre Real</th>
                    <th>Nickname</th>
                    <th>Actualizar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($warehouses as $warehouse)
                    <tr>                        
                        <td>{{ $warehouse->provider->name ?? 'N/A' }}</td>                        
                        <td>{{ $warehouse->name }}</td>
                        <td>
                            <form method="POST" action="{{ route('warehouses.update', $warehouse->id) }}">
                                @csrf
                                @method('PUT')
                                <input type="text" name="nickname" value="{{ $warehouse->nickname }}" class="form-control form-control-sm" required>
                        </td>
                        <td>
                                <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
