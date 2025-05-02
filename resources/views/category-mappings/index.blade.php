@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Asignación de Categorías Printec</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
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

                            <td>{{ $mapping->productProvider->name ?? '-' }}</td>
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
@endsection
