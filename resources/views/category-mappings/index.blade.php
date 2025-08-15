@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                </div>
            @elseif (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Asignación de Categorías Printec</h5>
        <span>Por favor, asigna las categorías internas a las categorías externas de los proveedores.</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-sm table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Proveedor</th>
                        <th>Categoría Externa</th>
                        <th>Categorías Internas Asignadas</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $mapping)
                        <tr>
                            <form method="POST" action="{{ url('/category-mappings/' . $mapping->id) }}">
                                @csrf

                                <td>{{ $mapping->partner->nombre_comercial ?? 'N/A' }}</td>
                                <td>{{ $mapping->name }}</td>
                                <td>
                                    <select name="category_ids[]" class="form-control" multiple size="5">
                                        @foreach($printecCategories as $cat)
                                            <option value="{{ $cat->id }}"
                                                {{ $mapping->printecCategories && $mapping->printecCategories->contains('id', $cat->id) ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                                </td>
                            </form>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
