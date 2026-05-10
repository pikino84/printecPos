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
            <a href="{{ route('partners.profile-progress.export', request()->only(['profile_min', 'profile_max'])) }}"
               class="btn btn-outline-success float-right me-2"
               title="Descargar CSV de partners con perfil incompleto">
                <i class="feather icon-download"></i> Exportar CSV
            </a>
        </div>
    </div>
</div>

{{-- Filtros por completitud de perfil --}}
<div class="card mb-3">
    <div class="card-block">
        <form method="GET" action="{{ route('partners.index') }}" class="row align-items-end g-2">
            <div class="col-md-3">
                <label class="form-label small mb-1">% perfil mínimo</label>
                <input type="number" name="profile_min" min="0" max="100" value="{{ $profileMin }}" class="form-control form-control-sm" placeholder="0">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">% perfil máximo</label>
                <input type="number" name="profile_max" min="0" max="100" value="{{ $profileMax }}" class="form-control form-control-sm" placeholder="100">
            </div>
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                <a href="{{ route('partners.index') }}" class="btn btn-sm btn-link">Limpiar</a>
            </div>
        </form>
    </div>
</div>

@php
    // Helper para alternar dirección al hacer click en una columna ordenable.
    $sortLink = function (string $column, string $label) use ($sort, $direction, $profileMin, $profileMax) {
        $newDir = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
        $arrow = $sort === $column ? ($direction === 'asc' ? ' ↑' : ' ↓') : '';
        $url = route('partners.index', array_filter([
            'sort' => $column,
            'direction' => $newDir,
            'profile_min' => $profileMin,
            'profile_max' => $profileMax,
        ]));
        return '<a href="'.$url.'" class="text-dark text-decoration-none">'.$label.$arrow.'</a>';
    };
@endphp

<div class="card">
    <div class="card-block table-border-style">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>{!! $sortLink('name', 'Nombre') !!}</th>
                        <th>Contacto</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Tipo</th>
                        <th>Activo</th>
                        <th style="min-width:200px;">{!! $sortLink('profile', '% Perfil') !!}</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($partners as $partner)
                        @php
                            $pct = $partner->profile_completion;
                            $pctClass = match (true) {
                                $pct === 100 => 'bg-success',
                                $pct >= 60 => 'bg-info',
                                $pct >= 30 => 'bg-warning',
                                default => 'bg-danger',
                            };
                            // Solo Asociado puro está sujeto a deadline/veto. Mixto y Proveedor
                            // muestran solo la barra (informativa) sin countdown.
                            $appliesDeadline = $partner->type === 'Asociado';
                            $days = $appliesDeadline ? $partner->daysUntilDeadline() : null;
                            $countdownClass = match (true) {
                                $days === null => '',
                                $days <= 0 => 'text-danger fw-semibold',
                                $days <= 3 => 'text-danger',
                                $days <= 7 => 'text-warning',
                                default => 'text-muted',
                            };
                        @endphp
                        <tr>
                            <td>{{ $partner->name }}</td>
                            <td>{{ $partner->contact_name }}</td>
                            <td>{{ $partner->contact_email }}</td>
                            <td>{{ $partner->contact_phone }}</td>
                            <td>{!! $partner->type_badge !!}</td>
                            <td>{{ $partner->is_active ? 'Sí' : 'No' }}</td>
                            <td>
                                <div class="progress" style="height:18px;" title="{{ $partner->type }} — {{ $pct }}% completo">
                                    <div class="progress-bar {{ $pctClass }}" role="progressbar"
                                         style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}"
                                         aria-valuemin="0" aria-valuemax="100">
                                        <small>{{ $pct }}%</small>
                                    </div>
                                </div>
                                @if ($partner->isVetoed())
                                    <small class="d-block mt-1 text-danger fw-semibold">
                                        🚫 Vetado hasta {{ $partner->vetoed_until->format('Y-m-d') }}
                                    </small>
                                @elseif ($appliesDeadline && $days !== null && $pct < 100)
                                    <small class="d-block mt-1 {{ $countdownClass }}">
                                        @if ($days > 0)
                                            ⏱ {{ $days }} {{ $days === 1 ? 'día restante' : 'días restantes' }}
                                        @else
                                            ⚠ Vencido hace {{ abs($days) }} {{ abs($days) === 1 ? 'día' : 'días' }}
                                        @endif
                                    </small>
                                @elseif (! $appliesDeadline)
                                    <small class="d-block mt-1 text-muted">— exento de deadline</small>
                                @endif
                            </td>
                            <td class="d-flex gap-1">
                              {{-- Ver detalle del partner --}}
                              <a href="{{ route('partners.show', $partner) }}" class="btn btn-sm btn-info" title="Ver detalle">
                                  <i class="feather icon-eye"></i>
                              </a>

                              {{-- API del catálogo (solo para Asociados y Mixtos) --}}
                              @if($partner->isAsociadoOMixto())
                              <a href="{{ route('partners.show', $partner) }}#api-section" class="btn btn-sm {{ $partner->api_key ? 'btn-success' : 'btn-outline-secondary' }}" title="{{ $partner->api_key ? 'API Activa' : 'Configurar API' }}">
                                  <i class="feather icon-link"></i>
                              </a>
                              @endif

                              {{-- Configuracion del sitio web (solo para Asociados y Mixtos) --}}
                              @if($partner->isAsociadoOMixto())
                              <a href="{{ route('partners.website.edit', $partner) }}" class="btn btn-sm btn-outline-info" title="Configurar sitio web">
                                  <i class="feather icon-globe"></i>
                              </a>
                              @endif

                              {{-- Ir al CRUD de razones sociales del partner --}}
                              <a href="{{ route('partners.entities.index', $partner) }}"
                                class="btn btn-sm btn-outline-primary" title="Razones sociales">
                                  <i class="feather icon-briefcase"></i>
                                  @if(isset($partner->entities_count))
                                      <span class="badge bg-primary">{{ $partner->entities_count }}</span>
                                  @endif
                              </a>

                              <a href="{{ route('partners.edit', $partner) }}" class="btn btn-sm btn-warning" title="Editar">
                                  <i class="feather icon-edit"></i>
                              </a>
                              <form action="{{ route('partners.destroy', $partner) }}" method="POST" style="display:inline;" id="delete-form-{{ $partner->id }}">
                                  @csrf
                                  @method('DELETE')
                                  <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $partner->id }})" title="Eliminar">
                                      <i class="feather icon-trash-2"></i>
                                  </button>
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
                            <td colspan="8" class="text-center">No hay partners registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection