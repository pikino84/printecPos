@extends('layouts.app')
@section('title', 'Mis Cuentas Bancarias')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5>Mis Cuentas Bancarias</h5>
            <p class="text-muted mb-0">{{ $partner->name }}</p>
        </div>
        <div class="col-md-4 text-right">
            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
            <a href="{{ route('my-bank-accounts.create') }}" class="btn btn-primary">
                <i class="feather icon-plus"></i> Agregar Cuenta
            </a>
            @endif
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

@if($entities->sum(function($e) { return $e->bankAccounts->count(); }) > 0)
    @foreach($entities as $entity)
        @if($entity->bankAccounts->count() > 0)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="feather icon-file-text"></i> 
                    {{ $entity->razon_social }}
                    @if($entity->is_default)
                        <span class="badge bg-primary ms-2">Principal</span>
                    @endif
                </h6>
                <small class="text-muted">RFC: {{ $entity->rfc ?? 'N/A' }}</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Alias</th>
                                <th>Banco</th>
                                <th>Titular</th>
                                <th>Cuenta</th>
                                <th>CLABE</th>
                                <th>Moneda</th>
                                <th>Principal</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entity->bankAccounts as $account)
                            <tr>
                                <td>{{ $account->alias ?: '-' }}</td>
                                <td>{{ $account->bank_name }}</td>
                                <td>{{ $account->account_holder ?: '-' }}</td>
                                <td>
                                    @if($account->account_number)
                                        {{ substr($account->account_number, 0, 4) }}****{{ substr($account->account_number, -4) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($account->clabe)
                                        {{ substr($account->clabe, 0, 4) }}**********{{ substr($account->clabe, -4) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $account->currency }}</td>
                                <td>
                                    @if($account->is_default)
                                        <span class="badge bg-primary">Sí</span>
                                    @else
                                        <span class="text-muted">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($account->is_active)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td>
                                    @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('my-bank-accounts.edit', $account->id) }}" 
                                           class="btn btn-warning"
                                           title="Editar">
                                            <i class="feather icon-edit"></i>
                                        </a>
                                        
                                        <button type="button"
                                                class="btn btn-danger"
                                                title="Eliminar"
                                                onclick="confirmDelete({{ $account->id }})">
                                            <i class="feather icon-trash-2"></i>
                                        </button>
                                        
                                        <form id="delete-form-{{ $account->id }}" 
                                              action="{{ route('my-bank-accounts.destroy', $account->id) }}" 
                                              method="POST" 
                                              style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    @endforeach
@else
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="feather icon-credit-card" style="font-size: 48px; color: #ccc;"></i>
            <p class="text-muted mt-3">No tienes cuentas bancarias registradas.</p>
            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
            <a href="{{ route('my-bank-accounts.create') }}" class="btn btn-primary">
                <i class="feather icon-plus"></i> Agregar primera cuenta
            </a>
            @endif
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
function confirmDelete(accountId) {
    console.log('confirmDelete called:', accountId);
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            html: '¿Deseas eliminar esta cuenta bancaria?<br><br>Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + accountId).submit();
            }
        });
    } else if (typeof swal !== 'undefined') {
        swal({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar esta cuenta bancaria?\n\nEsta acción no se puede deshacer.',
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
                document.getElementById('delete-form-' + accountId).submit();
            }
        });
    } else {
        if (confirm('¿Estás seguro de que deseas eliminar esta cuenta bancaria?')) {
            document.getElementById('delete-form-' + accountId).submit();
        }
    }
}
</script>
@endsection