@extends('layouts.app')
@section('title', isset($isMyView) ? 'Mi Sitio Web' : 'Sitio Web - ' . $partner->name)

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5>{{ isset($isMyView) ? 'Mi Sitio Web' : 'Sitio Web - ' . $partner->name }}</h5>
            <p class="text-muted mb-0">Configura el aspecto del sitio web con tu catalogo de productos</p>
        </div>
        <div class="col-md-4 text-right">
            @if($partner->api_key)
            <a href="{{ isset($isMyView) ? route('my-website.preview') : route('partners.website.preview', $partner) }}"
               class="btn btn-info" target="_blank">
                <i class="feather icon-eye"></i> Ver preview
            </a>
            @endif
            @if(!isset($isMyView))
            <a href="{{ route('partners.show', $partner) }}" class="btn btn-secondary">
                <i class="feather icon-arrow-left"></i> Volver
            </a>
            @endif
        </div>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(!$partner->api_key)
<div class="alert alert-warning">
    <i class="feather icon-alert-triangle"></i>
    Este partner no tiene una API Key configurada. Primero genera una API Key desde la
    <a href="{{ route('partners.show', $partner) }}#api-section">seccion API del partner</a>.
</div>
@endif

<form method="POST"
      action="{{ isset($isMyView) ? route('my-website.update') : route('partners.website.update', $partner) }}"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Logo --}}
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Logo</h6>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Medida sugerida: <strong>300 x 100 px</strong>. Peso maximo: <strong>5 MB</strong>.
                Formatos: JPG, PNG, GIF, WEBP, SVG.
            </p>
            <div class="row align-items-center">
                <div class="col-md-6">
                    <input type="file" name="logo" class="form-control" accept="image/*"
                           onchange="previewImage(this, 'logo-preview')">
                </div>
                <div class="col-md-6">
                    @if($partner->logo)
                    <div id="logo-current" class="d-flex align-items-center gap-3">
                        <img src="{{ Storage::disk('public')->url($partner->logo) }}"
                             alt="Logo actual" style="max-height: 60px;" class="border rounded p-1">
                        <label class="mb-0">
                            <input type="checkbox" name="remove_logo" value="1"> Eliminar logo actual
                        </label>
                    </div>
                    @endif
                    <img id="logo-preview" src="#" alt="Preview" style="max-height: 60px; display: none;" class="border rounded p-1 mt-2">
                </div>
            </div>
        </div>
    </div>

    {{-- Hero Banners --}}
    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0">Hero Banner</h6>
        </div>
        <div class="card-body">
            {{-- Hero Desktop --}}
            <div class="mb-4">
                <label class="font-weight-bold">Hero Escritorio</label>
                <p class="text-muted mb-2">
                    Medida sugerida: <strong>1920 x 600 px</strong>. Peso maximo: <strong>5 MB</strong>.
                    Formatos: JPG, PNG, GIF, WEBP.
                </p>
                <input type="file" name="hero_desktop" class="form-control mb-2" accept="image/*"
                       onchange="previewImage(this, 'hero-desktop-preview')">
                @if($partner->hero_desktop)
                <div class="d-flex align-items-center gap-3 mt-2">
                    <img src="{{ Storage::disk('public')->url($partner->hero_desktop) }}"
                         alt="Hero desktop actual" style="max-height: 100px; max-width: 300px;" class="border rounded">
                    <label class="mb-0">
                        <input type="checkbox" name="remove_hero_desktop" value="1"> Eliminar
                    </label>
                </div>
                @endif
                <img id="hero-desktop-preview" src="#" alt="Preview" style="max-height: 100px; max-width: 300px; display: none;" class="border rounded mt-2">
            </div>

            {{-- Hero Mobile --}}
            <div>
                <label class="font-weight-bold">Hero Celular</label>
                <p class="text-muted mb-2">
                    Medida sugerida: <strong>768 x 400 px</strong>. Peso maximo: <strong>5 MB</strong>.
                    Formatos: JPG, PNG, GIF, WEBP.
                </p>
                <input type="file" name="hero_mobile" class="form-control mb-2" accept="image/*"
                       onchange="previewImage(this, 'hero-mobile-preview')">
                @if($partner->hero_mobile)
                <div class="d-flex align-items-center gap-3 mt-2">
                    <img src="{{ Storage::disk('public')->url($partner->hero_mobile) }}"
                         alt="Hero mobile actual" style="max-height: 100px; max-width: 200px;" class="border rounded">
                    <label class="mb-0">
                        <input type="checkbox" name="remove_hero_mobile" value="1"> Eliminar
                    </label>
                </div>
                @endif
                <img id="hero-mobile-preview" src="#" alt="Preview" style="max-height: 100px; max-width: 200px; display: none;" class="border rounded mt-2">
            </div>
        </div>
    </div>

    {{-- Colores --}}
    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0">Colores del Sitio</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Color Primario</label>
                    <p class="text-muted small mb-1">Botones, enlaces, elementos activos</p>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" name="site_primary_color"
                               value="{{ old('site_primary_color', $partner->site_primary_color ?? '#007bff') }}"
                               class="form-control form-control-color" style="width: 50px; height: 38px;"
                               onchange="updateColorPreview()">
                        <input type="text" class="form-control" style="width: 90px;"
                               value="{{ old('site_primary_color', $partner->site_primary_color ?? '#007bff') }}"
                               onchange="this.previousElementSibling.value = this.value; updateColorPreview()"
                               pattern="^#[0-9A-Fa-f]{6}$">
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Color Secundario</label>
                    <p class="text-muted small mb-1">Acentos, bordes, tags de categoria</p>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" name="site_secondary_color"
                               value="{{ old('site_secondary_color', $partner->site_secondary_color ?? '#6c757d') }}"
                               class="form-control form-control-color" style="width: 50px; height: 38px;"
                               onchange="updateColorPreview()">
                        <input type="text" class="form-control" style="width: 90px;"
                               value="{{ old('site_secondary_color', $partner->site_secondary_color ?? '#6c757d') }}"
                               onchange="this.previousElementSibling.value = this.value; updateColorPreview()"
                               pattern="^#[0-9A-Fa-f]{6}$">
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Color de Acento</label>
                    <p class="text-muted small mb-1">Precios, carrito, notificaciones</p>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" name="site_accent_color"
                               value="{{ old('site_accent_color', $partner->site_accent_color ?? '#28a745') }}"
                               class="form-control form-control-color" style="width: 50px; height: 38px;"
                               onchange="updateColorPreview()">
                        <input type="text" class="form-control" style="width: 90px;"
                               value="{{ old('site_accent_color', $partner->site_accent_color ?? '#28a745') }}"
                               onchange="this.previousElementSibling.value = this.value; updateColorPreview()"
                               pattern="^#[0-9A-Fa-f]{6}$">
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Fondo Header / Footer</label>
                    <p class="text-muted small mb-1">Color de fondo del encabezado y pie de pagina</p>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" name="site_header_footer_bg"
                               value="{{ old('site_header_footer_bg', $partner->site_header_footer_bg ?? '#ffffff') }}"
                               class="form-control form-control-color" style="width: 50px; height: 38px;"
                               onchange="updateColorPreview()">
                        <input type="text" class="form-control" style="width: 90px;"
                               value="{{ old('site_header_footer_bg', $partner->site_header_footer_bg ?? '#ffffff') }}"
                               onchange="this.previousElementSibling.value = this.value; updateColorPreview()"
                               pattern="^#[0-9A-Fa-f]{6}$">
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Fondo del Catalogo</label>
                    <p class="text-muted small mb-1">Color de fondo del area de productos</p>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" name="site_catalog_bg"
                               value="{{ old('site_catalog_bg', $partner->site_catalog_bg ?? '#f8f9fa') }}"
                               class="form-control form-control-color" style="width: 50px; height: 38px;"
                               onchange="updateColorPreview()">
                        <input type="text" class="form-control" style="width: 90px;"
                               value="{{ old('site_catalog_bg', $partner->site_catalog_bg ?? '#f8f9fa') }}"
                               onchange="this.previousElementSibling.value = this.value; updateColorPreview()"
                               pattern="^#[0-9A-Fa-f]{6}$">
                    </div>
                </div>
            </div>

            {{-- Vista previa de colores --}}
            <div class="mt-3">
                <label class="font-weight-bold mb-2">Vista previa de colores</label>
                <div id="color-preview" class="border rounded overflow-hidden" style="max-width: 600px;">
                    <div id="cp-header" style="padding: 12px 20px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-weight: bold; font-size: 14px;">LOGO</span>
                        <div>
                            <span id="cp-btn" style="padding: 4px 12px; border-radius: 4px; font-size: 12px; color: #fff;">Boton</span>
                        </div>
                    </div>
                    <div id="cp-catalog" style="padding: 20px; min-height: 80px;">
                        <div style="display: flex; gap: 10px;">
                            <div style="background: #fff; border-radius: 6px; padding: 10px; width: 120px; box-shadow: 0 1px 3px rgba(0,0,0,.1);">
                                <div style="background: #ddd; height: 50px; border-radius: 4px; margin-bottom: 6px;"></div>
                                <div style="font-size: 11px; color: #333;">Producto</div>
                                <div id="cp-price" style="font-size: 12px; font-weight: bold;">$199.00</div>
                                <span id="cp-tag" style="font-size: 9px; padding: 1px 6px; border-radius: 3px; color: #fff;">Categoria</span>
                            </div>
                            <div style="background: #fff; border-radius: 6px; padding: 10px; width: 120px; box-shadow: 0 1px 3px rgba(0,0,0,.1);">
                                <div style="background: #ddd; height: 50px; border-radius: 4px; margin-bottom: 6px;"></div>
                                <div style="font-size: 11px; color: #333;">Producto</div>
                                <div id="cp-price2" style="font-size: 12px; font-weight: bold;">$299.00</div>
                                <span id="cp-tag2" style="font-size: 9px; padding: 1px 6px; border-radius: 3px; color: #fff;">Categoria</span>
                            </div>
                        </div>
                    </div>
                    <div id="cp-footer" style="padding: 10px 20px; text-align: center; font-size: 11px; color: #666;">
                        Datos de contacto aqui
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Datos de Contacto --}}
    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0">Datos de Contacto (Footer)</h6>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Escribe la informacion de contacto que aparecera en el pie de pagina del sitio web.
                Puedes incluir telefono, correo, direccion, redes sociales, etc.
            </p>
            <textarea name="contact_info" id="contact_info">{{ old('contact_info', $partner->contact_info) }}</textarea>
        </div>
    </div>

    {{-- Boton guardar --}}
    <div class="mt-3 mb-5">
        <button type="submit" class="btn btn-primary">
            <i class="feather icon-save"></i> Guardar configuracion
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script src="{{ asset('pages/ckeditor/ckeditor.js') }}"></script>
<script>
    // Initialize CKEditor
    CKEDITOR.replace('contact_info', {
        height: 200,
        removePlugins: 'elementspath',
        toolbar: [
            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
            { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight'] },
            { name: 'links', items: ['Link', 'Unlink'] },
            { name: 'styles', items: ['FontSize'] },
            { name: 'colors', items: ['TextColor'] },
            { name: 'tools', items: ['Source'] }
        ]
    });

    // Image preview
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            // Check file size (5MB)
            if (input.files[0].size > 5 * 1024 * 1024) {
                swal('Error', 'La imagen no debe pesar mas de 5 MB', 'error');
                input.value = '';
                preview.style.display = 'none';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Color preview sync
    function updateColorPreview() {
        const primary = document.querySelector('input[name="site_primary_color"]').value;
        const secondary = document.querySelector('input[name="site_secondary_color"]').value;
        const accent = document.querySelector('input[name="site_accent_color"]').value;
        const headerFooterBg = document.querySelector('input[name="site_header_footer_bg"]').value;
        const catalogBg = document.querySelector('input[name="site_catalog_bg"]').value;

        document.getElementById('cp-header').style.backgroundColor = headerFooterBg;
        document.getElementById('cp-footer').style.backgroundColor = headerFooterBg;
        document.getElementById('cp-catalog').style.backgroundColor = catalogBg;
        document.getElementById('cp-btn').style.backgroundColor = primary;
        document.getElementById('cp-price').style.color = accent;
        document.getElementById('cp-price2').style.color = accent;
        document.getElementById('cp-tag').style.backgroundColor = secondary;
        document.getElementById('cp-tag2').style.backgroundColor = secondary;

        // Sync text inputs with color pickers
        document.querySelectorAll('input[type="color"]').forEach(function(colorInput) {
            const textInput = colorInput.nextElementSibling;
            if (textInput && textInput.tagName === 'INPUT') {
                textInput.value = colorInput.value;
            }
        });
    }

    // Initialize color preview on page load
    document.addEventListener('DOMContentLoaded', updateColorPreview);
</script>
@endsection
