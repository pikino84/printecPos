@extends('layouts.app')

@section('title', 'Editar Almacén')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h5 class="m-b-10">Editar Almacén</h5>
                    <p class="m-b-0">Actualiza la información del almacén</p>
                </div>
            </div>
            <div class="col-md-4">
                <ul class="breadcrumb-title">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}"><i class="fa fa-home"></i></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('warehouses.index') }}">Almacenes</a>
                    </li>
                    <li class="breadcrumb-item"><a href="#!">Editar</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="pcoded-inner-content">
    <div class="main-body">
        <div class="page-wrapper">
            <div class="page-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Información del Almacén</h5>
                                <span class="text-muted">
                                    <strong>Partner:</strong> {{ $warehouse->partner->name ?? 'Sin asignar' }}
                                </span>
                            </div>
                            <div class="card-block">
                                <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    {{-- Partner (solo lectura) --}}
                                    <div class="form-group">
                                        <label class="form-label">
                                            Partner/Proveedor
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               value="{{ $warehouse->partner->name ?? 'Sin asignar' }} ({{ $warehouse->partner->type ?? 'N/A' }})"
                                               disabled>
                                        <small class="form-text text-muted">
                                            <i class="feather icon-lock"></i> El partner no se puede cambiar una vez creado el almacén
                                        </small>
                                    </div>

                                    {{-- Código (solo lectura) --}}
                                    <div class="form-group">
                                        <label class="form-label">
                                            Código del Almacén
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               value="{{ $warehouse->codigo }}"
                                               disabled>
                                        <small class="form-text text-muted">
                                            <i class="feather icon-lock"></i> El código no se puede modificar
                                        </small>
                                    </div>

                                    {{-- Nombre --}}
                                    <div class="form-group">
                                        <label class="form-label">
                                            Nombre del Almacén <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               name="name" 
                                               value="{{ old('name', $warehouse->name) }}"
                                               placeholder="Ej: Almacén Central CDMX"
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="feather icon-info"></i> Nombre completo del almacén
                                        </small>
                                    </div>

                                    {{-- Nickname --}}
                                    <div class="form-group">
                                        <label class="form-label">
                                            Apodo/Alias (Opcional)
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('nickname') is-invalid @enderror" 
                                               name="nickname" 
                                               value="{{ old('nickname', $warehouse->nickname) }}"
                                               placeholder="Ej: Centro, Norte, Sur">
                                        @error('nickname')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="feather icon-info"></i> Nombre corto para mostrar públicamente
                                        </small>
                                    </div>

                                    {{-- Ciudad --}}
                                    <div class="form-group">
                                        <label class="form-label">
                                            Ciudad
                                        </label>
                                        <select class="form-control @error('city_id') is-invalid @enderror" 
                                                name="city_id">
                                            <option value="">Seleccionar ciudad</option>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->id }}" 
                                                        {{ old('city_id', $warehouse->city_id) == $city->id ? 'selected' : '' }}>
                                                    {{ $city->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('city_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="feather icon-info"></i> Ciudad donde se ubica el almacén
                                        </small>
                                    </div>

                                    {{-- Estado Activo --}}
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   name="is_active" 
                                                   id="is_active" 
                                                   value="1"
                                                   {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Almacén activo
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="feather icon-info"></i> Solo los almacenes activos aparecerán en el sistema
                                        </small>
                                    </div>

                                    {{-- Información adicional --}}
                                    <div class="alert alert-info">
                                        <strong><i class="feather icon-info"></i> Información:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Creado el: {{ $warehouse->created_at->format('d/m/Y H:i') }}</li>
                                            <li>Última actualización: {{ $warehouse->updated_at->format('d/m/Y H:i') }}</li>
                                        </ul>
                                    </div>

                                    {{-- Botones --}}
                                    <div class="form-group mb-0">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather icon-save"></i> Actualizar Almacén
                                        </button>
                                        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">
                                            <i class="feather icon-x"></i> Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Card de estadísticas --}}
                        <div class="card">
                            <div class="card-header">
                                <h5>Estadísticas del Almacén</h5>
                            </div>
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card bg-c-blue text-white">
                                            <div class="card-block">
                                                <h6 class="text-white">Productos en Stock</h6>
                                                <h2 class="text-white">{{ $warehouse->stocks()->count() }}</h2>
                                                <p class="mb-0">Variantes con inventario</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-c-green text-white">
                                            <div class="card-block">
                                                <h6 class="text-white">Stock Total</h6>
                                                <h2 class="text-white">{{ $warehouse->stocks()->sum('stock') }}</h2>
                                                <p class="mb-0">Unidades totales</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection