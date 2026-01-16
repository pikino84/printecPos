@extends('layouts.app')
@section('title', 'Niveles de Precio')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5>Niveles de Precio (Pricing Tiers)</h5>
            <p class="text-muted mb-0">Administra los niveles de descuento por volumen de compras</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('pricing-tiers.create') }}" class="btn btn-primary">
                <i class="feather icon-plus"></i> Crear Nivel
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Nivel</th>
                        <th>Rango de Compras Mensuales</th>
                        <th>Markup</th>
                        <th>Partners</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tiers as $tier)
                    <tr>
                        <td>{{ $tier->order }}</td>
                        <td>
                            <strong>{{ $tier->name }}</strong>
                            @if($tier->description)
                                <br><small class="text-muted">{{ $tier->description }}</small>
                            @endif
                        </td>
                        <td>
                            ${{ number_format($tier->min_monthly_purchases, 2) }}
                            @if($tier->max_monthly_purchases)
                                - ${{ number_format($tier->max_monthly_purchases, 2) }}
                            @else
                                <span class="text-muted">en adelante</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">
                                {{ number_format($tier->markup_percentage, 2) }}%
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                {{ $tier->partners_count }} partners
                            </span>
                        </td>
                        <td>
                            @if($tier->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('pricing-tiers.show', $tier) }}" 
                                   class="btn btn-info"
                                   title="Ver detalle">
                                    <i class="feather icon-eye"></i>
                                </a>
                                <a href="{{ route('pricing-tiers.edit', $tier) }}" 
                                   class="btn btn-warning"
                                   title="Editar">
                                    <i class="feather icon-edit"></i>
                                </a>
                                
                                @if($tier->partners_count == 0)
                                <button type="button"
                                        class="btn btn-danger"
                                        title="Eliminar"
                                        onclick="confirmDelete({{ $tier->id }})">
                                    <i class="feather icon-trash-2"></i>
                                </button>
                                
                                <form id="delete-form-{{ $tier->id }}" 
                                      action="{{ route('pricing-tiers.destroy', $tier) }}" 
                                      method="POST" 
                                      style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="feather icon-inbox" style="font-size: 48px; color: #ccc;"></i>
                            <p class="text-muted mt-3">No hay niveles de precio configurados.</p>
                            <a href="{{ route('pricing-tiers.create') }}" class="btn btn-primary">
                                <i class="feather icon-plus"></i> Crear primer nivel
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <h6 class="mb-3">Información sobre Niveles de Precio</h6>
        <ul class="mb-0">
            <li>Los niveles se asignan automáticamente cada inicio de mes basándose en las compras del mes anterior</li>
            <li>El descuento se aplica sobre el precio con markup de Printec antes de aplicar el markup del partner</li>
            <li>Los rangos no deben solaparse entre sí</li>
            <li>El comando <code>php artisan pricing:evaluate-tiers</code> evalúa y asigna niveles</li>
        </ul>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(tierId) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            html: '¿Deseas eliminar este nivel de precio?<br><br>Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + tierId).submit();
            }
        });
    } else if (typeof swal !== 'undefined') {
        swal({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar este nivel de precio?\n\nEsta acción no se puede deshacer.',
            icon: 'warning',
            buttons: {
                cancel: {
                    text: 'Cancelar',
                    value: null,
                    visible: true,
                    closeModal: true,
                },
                confirm: {
                    text: 'Sí, eliminar',
                    value: true,
                    visible: true,
                    className: 'btn-danger',
                    closeModal: true
                }
            },
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                document.getElementById('delete-form-' + tierId).submit();
            }
        });
    } else {
        if (confirm('¿Estás seguro de que deseas eliminar este nivel de precio?')) {
            document.getElementById('delete-form-' + tierId).submit();
        }
    }
}
</script>
@endsection