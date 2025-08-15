@extends('layouts.app')

@section('title', 'Almacenes')

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
            @endif
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0">Almacenes</h5>
        <span>Por favor, actualiza los apodos de los almacenes según sea necesario.</span>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>                    
                    <th>Proveedor</th>
                    <th>Nombre Real</th>
                    <th>Nickname</th>
                    <th>Ciudad</th>
                    <th>Actualizar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($warehouses as $warehouse)
                    <tr>                        
                        <td>{{ $warehouse->partner->nombre_comercial ?? 'Sin asignar' }}</td>

                        <td>{{ $warehouse->name }}</td>
                        <td>
                            <form method="POST" action="{{ route('warehouses.update', $warehouse->id) }}">
                                @csrf
                                @method('PUT')
                                <input type="text" name="nickname" value="{{ $warehouse->nickname }}" class="form-control form-control-sm" >
                        </td>
                        <td>
                            <select name="city_id" class="form-control">
                                <option value="">Selecciona ciudad</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ $warehouse->city_id == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
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
