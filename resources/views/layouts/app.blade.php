<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="description" content="" />
        <meta name="keywords" content="">
        <meta name="author" content="colorlib" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Admindek')</title>

        <!-- Captura de errores JS + watchdog del pre-loader (debug página en blanco) -->
        <script>
        (function () {
            var sent = 0;
            function report(type, payload) {
                if (sent >= 5) return; // no inundar el log desde una misma página
                sent++;
                payload = payload || {};
                payload.type = type;
                payload.url = location.pathname + location.search;
                try {
                    fetch('/frontend-log', {
                        method: 'POST',
                        keepalive: true,
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                } catch (e) { /* sin red o browser viejo: no hay nada que hacer */ }
            }

            // Errores de ejecución JS y fallas de carga de recursos (capture: true
            // es necesario para atrapar los error de <script>/<link> externos)
            window.addEventListener('error', function (e) {
                if (e.target && (e.target.tagName === 'SCRIPT' || e.target.tagName === 'LINK')) {
                    report('resource-error', {
                        message: 'No cargó: ' + (e.target.src || e.target.href || '?'),
                        source: e.target.src || e.target.href || null
                    });
                } else if (e.message) {
                    report('js-error', {
                        message: String(e.message).slice(0, 2000),
                        source: e.filename || null,
                        line: e.lineno || null,
                        column: e.colno || null,
                        stack: e.error && e.error.stack ? String(e.error.stack).slice(0, 4000) : null
                    });
                }
            }, true);

            window.addEventListener('unhandledrejection', function (e) {
                var r = e.reason || {};
                report('unhandled-rejection', {
                    message: String(r.message || r).slice(0, 2000),
                    stack: r.stack ? String(r.stack).slice(0, 4000) : null
                });
            });

            // Diagnóstico del estado de la página cuando el loader se queda pegado
            function diagnostics() {
                var pending = [];
                try {
                    document.querySelectorAll('script[src]').forEach(function (s) {
                        var loaded = performance.getEntriesByName(s.src).some(function (en) {
                            return en.responseEnd > 0;
                        });
                        if (!loaded) pending.push(s.src);
                    });
                } catch (e) { /* performance API no disponible */ }
                return {
                    readyState: document.readyState,
                    jquery: typeof window.jQuery !== 'undefined',
                    scriptsPendientes: pending.slice(0, 10)
                };
            }

            function hideLoaderIfStuck(context) {
                var bg = document.querySelector('.loader-bg');
                var stuck = bg && getComputedStyle(bg).display !== 'none';
                if (stuck || document.readyState === 'loading') {
                    if (bg) bg.style.display = 'none';
                    report('loader-stuck', { message: context, diagnostics: diagnostics() });
                }
            }

            // Failsafe 1: si jQuery/script.min.js fallaron, el fadeOut() del loader
            // nunca corre — lo quitamos nosotros 3s después de que el DOM esté listo
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () { hideLoaderIfStuck('DOM listo pero loader visible (script.min.js no corrió)'); }, 3000);
            });

            // Failsafe 2: si un script externo se atoró descargando, el DOM nunca
            // termina de parsear — este timer corre igual y destapa la página
            // (5s: el incidente del 2026-06-12 mostró que los usuarios refrescan antes de 10s)
            setTimeout(function () { hideLoaderIfStuck('5s sin terminar de cargar la página'); }, 5000);
        })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <!-- Favicon icon -->
        <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
        <!-- Google font-->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Quicksand:500,700" rel="stylesheet">
        <!-- Required Fremwork -->
        <link rel="stylesheet" type="text/css" href="{{ asset('bower_components/bootstrap/css/bootstrap.min.css') }}">
        <!-- waves.css -->
        <link rel="stylesheet" href="{{ asset('pages/waves/css/waves.min.css') }}" type="text/css" media="all">
        <!-- feather icon -->
        <link rel="stylesheet" type="text/css" href="{{ asset('icon/feather/css/feather.css') }}">
        <!-- font-awesome-n -->
        <link rel="stylesheet" type="text/css" href="{{ asset('css/font-awesome-n.min.css') }}">
        <!-- Chartlist chart css -->
        <link rel="stylesheet" href="{{ asset('bower_components/chartist/css/chartist.css') }}" type="text/css" media="all">
        <!-- Style.css -->
        <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/widget.css' )}}">
        <!-- Select2 CSS -->
        <link href="{{ asset('vendor/select2/select2.min.css') }}?v={{ filemtime(public_path('vendor/select2/select2.min.css')) }}" rel="stylesheet" />
        <!-- Custom css -->
        <link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}">
        <!-- Intro.js CSS -->
        <link rel="stylesheet" href="{{ asset('vendor/introjs/introjs.min.css') }}?v={{ filemtime(public_path('vendor/introjs/introjs.min.css')) }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/tour.css') }}">
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <!-- [ Pre-loader ] start -->
        <div class="loader-bg">
            <div class="loader-bar"></div>
        </div>
        <!-- [ Pre-loader ] end -->
        <div id="pcoded" class="pcoded">
            <div class="pcoded-overlay-box"></div>
            <div class="pcoded-container navbar-wrapper">
                @include('partials.header')
                <div class="pcoded-main-container">
                    <div class="pcoded-wrapper">
                        <!-- [ navigation menu ] start -->
                        @include('layouts.navigation')
                        <!-- [ navigation menu ] end -->
                        <div class="pcoded-content px-4 py-4">
                            @yield('content')
                        </div>
                    </div>
                </div>                
            </div>
        </div>
        <script type="text/javascript" src="{{ asset('bower_components/jquery/js/jquery.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('bower_components/jquery-ui/js/jquery-ui.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('bower_components/popper.js/js/popper.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('bower_components/bootstrap/js/bootstrap.min.js') }}"></script>
        <!-- waves js -->
        <script src="{{ asset('pages/waves/js/waves.min.js') }}"></script>
        <!-- jquery slimscroll js -->
        <script type="text/javascript" src="{{ asset('bower_components/jquery-slimscroll/js/jquery.slimscroll.js') }}"></script>
        <!-- Float Chart js -->
        <script src="{{ asset('pages/chart/float/jquery.flot.js') }}"></script>
        <script src="{{ asset('pages/chart/float/jquery.flot.categories.js') }}"></script>
        <script src="{{ asset('pages/chart/float/curvedLines.js') }}"></script>
        <script src="{{ asset('pages/chart/float/jquery.flot.tooltip.min.js') }}"></script>
        <!-- Chartlist charts -->
        <script src="{{ asset('bower_components/chartist/js/chartist.js') }}"></script>
        <!-- amchart js -->
        <script src="{{ asset('pages/widget/amchart/amcharts.js') }}"></script>
        <script src="{{ asset('pages/widget/amchart/serial.js') }}"></script>
        <script src="{{ asset('pages/widget/amchart/light.js') }}"></script>
        <!-- Custom js -->
        <script src="{{ asset('js/pcoded.min.js') }}"></script>
        <script src="{{ asset('js/vertical/vertical-layout.min.js') }}"></script>
        <!--script type="text/javascript" src="{{ asset('pages/dashboard/custom-dashboard.min.js') }}"></script-->
        <script src="{{ asset('vendor/sweetalert/sweetalert.min.js') }}?v={{ filemtime(public_path('vendor/sweetalert/sweetalert.min.js')) }}"></script>
        <!-- Select2 JS -->
        <script src="{{ asset('vendor/select2/select2.min.js') }}?v={{ filemtime(public_path('vendor/select2/select2.min.js')) }}"></script>
        <script type="text/javascript" src="{{ asset('js/script.min.js') }}?v={{ filemtime(public_path('js/script.min.js')) }}"></script>
        <!-- Intro.js -->
        <script src="{{ asset('vendor/introjs/intro.min.js') }}?v={{ filemtime(public_path('vendor/introjs/intro.min.js')) }}"></script>
        <script src="{{ asset('js/tour.js') }}"></script>
        @yield('scripts')
        <script>
            // Función global para actualizar el contador del carrito
            function updateCartBadge() {
                $.ajax({
                    url: '{{ route("cart.count") }}',
                    type: 'GET',
                    success: function(response) {
                        const badge = $('#cart-count-badge');
                        if (response.count > 0) {
                            badge.text(response.count).show();
                        } else {
                            badge.hide();
                        }
                    }
                });
            }

            // Actualizar badge al cargar la página
            $(document).ready(function() {
                updateCartBadge();
            });

            // Función global para agregar al carrito desde cualquier página
            window.addToCart = function(variantId, quantity, warehouseId = null, unitPrice = null) {
                $.ajax({
                    url: '{{ route("cart.add") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        variant_id: variantId,
                        quantity: quantity,
                        warehouse_id: warehouseId,
                        unit_price: unitPrice
                    },
                    success: function(response) {
                        // Actualizar badge con animación
                        const badge = $('#cart-count-badge');
                        badge.addClass('cart-pulse');
                        setTimeout(() => badge.removeClass('cart-pulse'), 300);
                        
                        if (response.cart_count > 0) {
                            badge.text(response.cart_count).show();
                        }
                        
                        // Mostrar notificación
                        swal({
                            title: "¡Agregado!",
                            text: response.message,
                            icon: "success",
                            timer: 2000,
                            buttons: false
                        });
                    },
                    error: function(xhr) {
                        let message = 'Error al agregar al carrito';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        swal("Error", message, "error");
                    }
                });
            };
        </script>
    </body>
</html>