@extends('layouts.app')
@section('title', 'Configuración de Correo - ' . $entity->razon_social)

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Configuración de Correo Electrónico</h5>
            <p class="text-muted mb-0">{{ $entity->razon_social }}</p>
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

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="feather icon-mail"></i> Configuración SMTP</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('my-entities.mail-config.update', $entity->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="alert alert-info">
                        <h6><i class="feather icon-info"></i> Importante</h6>
                        <p class="mb-0">Para poder enviar cotizaciones desde tu propio correo, debes configurar los datos SMTP de tu proveedor de correo.
                        Cada razón social requiere su propia configuración de correo.</p>
                    </div>

                    <h6 class="mb-3 mt-4">Datos del Servidor SMTP</h6>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Servidor SMTP <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="smtp_host"
                                   class="form-control @error('smtp_host') is-invalid @enderror"
                                   value="{{ old('smtp_host', $entity->smtp_host) }}"
                                   placeholder="Ej: smtp.gmail.com, smtp.office365.com">
                            @error('smtp_host')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Puerto <span class="text-danger">*</span></label>
                            <input type="number"
                                   name="smtp_port"
                                   class="form-control @error('smtp_port') is-invalid @enderror"
                                   value="{{ old('smtp_port', $entity->smtp_port ?? 587) }}"
                                   placeholder="587">
                            @error('smtp_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usuario SMTP <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="smtp_username"
                                   class="form-control @error('smtp_username') is-invalid @enderror"
                                   value="{{ old('smtp_username', $entity->smtp_username) }}"
                                   placeholder="tu-correo@dominio.com">
                            @error('smtp_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Contrasena SMTP <span class="text-danger">*</span>
                                @if($entity->smtp_password)
                                    <small class="text-success">(ya configurada)</small>
                                @endif
                            </label>
                            <input type="password"
                                   name="smtp_password"
                                   class="form-control @error('smtp_password') is-invalid @enderror"
                                   placeholder="{{ $entity->smtp_password ? 'Dejar vacio para mantener actual' : 'Contrasena o App Password' }}">
                            @error('smtp_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Para Gmail, usa una "Contrasena de aplicacion"</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Encriptacion</label>
                            <select name="smtp_encryption" class="form-control">
                                <option value="tls" {{ old('smtp_encryption', $entity->smtp_encryption) == 'tls' ? 'selected' : '' }}>TLS (Recomendado)</option>
                                <option value="ssl" {{ old('smtp_encryption', $entity->smtp_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="none" {{ old('smtp_encryption', $entity->smtp_encryption) == 'none' ? 'selected' : '' }}>Ninguna</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">Datos del Remitente</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo del Remitente (From) <span class="text-danger">*</span></label>
                            <input type="email"
                                   name="mail_from_address"
                                   class="form-control @error('mail_from_address') is-invalid @enderror"
                                   value="{{ old('mail_from_address', $entity->mail_from_address) }}"
                                   placeholder="cotizaciones@tuempresa.com">
                            @error('mail_from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Remitente</label>
                            <input type="text"
                                   name="mail_from_name"
                                   class="form-control @error('mail_from_name') is-invalid @enderror"
                                   value="{{ old('mail_from_name', $entity->mail_from_name ?? $entity->razon_social) }}"
                                   placeholder="{{ $entity->razon_social }}">
                            @error('mail_from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Si se deja vacio, se usara la razon social</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">Correos de Copia (CC)</h6>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Correos para recibir copia de cotizaciones</label>
                            <input type="text"
                                   name="mail_cc_addresses"
                                   class="form-control @error('mail_cc_addresses') is-invalid @enderror"
                                   value="{{ old('mail_cc_addresses', $entity->mail_cc_addresses) }}"
                                   placeholder="ventas@empresa.com, gerencia@empresa.com">
                            @error('mail_cc_addresses')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Puedes agregar multiples correos separados por coma. Cada vez que se envie una cotizacion,
                                estos correos recibiran una copia automaticamente.
                            </small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="form-check mb-4">
                        <input type="checkbox"
                               class="form-check-input"
                               id="mail_configured"
                               name="mail_configured"
                               value="1"
                               {{ old('mail_configured', $entity->mail_configured) ? 'checked' : '' }}>
                        <label class="form-check-label" for="mail_configured">
                            <strong>Activar configuracion de correo</strong>
                        </label>
                        <br>
                        <small class="text-muted">
                            Marca esta opcion una vez hayas verificado que la configuracion funciona correctamente.
                            Las cotizaciones solo se enviaran desde tu correo si esta opcion esta activa.
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="feather icon-save"></i> Guardar Configuracion
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="testMailConfig()">
                            <i class="feather icon-send"></i> Enviar Correo de Prueba
                        </button>
                        <a href="{{ route('my-entities.index') }}" class="btn btn-outline-secondary">
                            <i class="feather icon-arrow-left"></i> Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="feather icon-help-circle"></i> Guia de Configuracion</h6>
            </div>
            <div class="card-body">
                <h6>Gmail</h6>
                <ul class="small">
                    <li>Servidor: <code>smtp.gmail.com</code></li>
                    <li>Puerto: <code>587</code></li>
                    <li>Encriptacion: <code>TLS</code></li>
                    <li>Usuario: Tu correo completo</li>
                    <li>Contrasena: Debes crear una <a href="https://myaccount.google.com/apppasswords" target="_blank">"Contrasena de aplicacion"</a></li>
                </ul>

                <hr>

                <h6>Outlook / Office 365</h6>
                <ul class="small">
                    <li>Servidor: <code>smtp.office365.com</code></li>
                    <li>Puerto: <code>587</code></li>
                    <li>Encriptacion: <code>TLS</code></li>
                    <li>Usuario: Tu correo completo</li>
                    <li>Contrasena: Tu contrasena de cuenta</li>
                </ul>

                <hr>

                <h6>Otros proveedores</h6>
                <p class="small text-muted">
                    Consulta la documentacion de tu proveedor de correo para obtener los datos SMTP correctos.
                    Generalmente necesitaras habilitar el acceso SMTP en la configuracion de tu cuenta.
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-warning">
                <h6 class="mb-0"><i class="feather icon-alert-triangle"></i> Notas Importantes</h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li class="mb-2">Si la configuracion no esta activa o es incorrecta, <strong>no se podran enviar cotizaciones</strong> desde esta razon social.</li>
                    <li class="mb-2">Los correos de copia (CC) recibiran una copia de todas las cotizaciones enviadas.</li>
                    <li class="mb-2">Recomendamos usar una cuenta de correo exclusiva para cotizaciones.</li>
                    <li>Nunca compartas tus credenciales SMTP con terceros.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function testMailConfig() {
    const form = document.querySelector('form');
    const formData = new FormData(form);

    // Agregar flag de prueba
    formData.append('test_email', '1');

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Enviando correo de prueba...',
            text: 'Por favor espera mientras verificamos la configuracion',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    fetch('{{ route("my-entities.mail-config.test", $entity->id) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (typeof Swal !== 'undefined') {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Correo enviado!',
                    text: data.message,
                    confirmButtonText: 'Aceptar'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    confirmButtonText: 'Aceptar'
                });
            }
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo enviar el correo de prueba. Verifica tu configuracion.',
                confirmButtonText: 'Aceptar'
            });
        } else {
            alert('Error al enviar correo de prueba');
        }
    });
}
</script>
@endsection
