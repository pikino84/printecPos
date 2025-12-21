@extends('layouts.app')
@section('title','Editar razon social')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Editar razon social: {{ $entity->razon_social }}</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('my-entities.update', $entity->id) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <ul class="nav nav-tabs mb-4" id="entityTabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab">
                  <i class="fas fa-building me-1"></i> Datos Generales
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="fiscal-tab" data-bs-toggle="tab" href="#fiscal" role="tab">
                  <i class="fas fa-file-invoice me-1"></i> Datos Fiscales
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="csd-tab" data-bs-toggle="tab" href="#csd" role="tab">
                  <i class="fas fa-certificate me-1"></i> Certificados CSD
                </a>
              </li>
            </ul>

            <div class="tab-content" id="entityTabsContent">
              @include('my-entities.partials.tab-general')
              @include('my-entities.partials.tab-fiscal')
              @include('my-entities.partials.tab-csd')
            </div>

            <hr>
            <div class="d-flex justify-content-between">
              <a href="{{ route('my-entities.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancelar
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Guardar Cambios
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash) {
      const tab = document.querySelector('a[href="' + hash + '"]');
      if (tab) new bootstrap.Tab(tab).show();
    }
    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(function(tab) {
      tab.addEventListener('shown.bs.tab', function(e) {
        history.replaceState(null, null, e.target.getAttribute('href'));
      });
    });
  });
</script>
@endpush
