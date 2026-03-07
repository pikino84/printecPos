<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $partner->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    </style>
</head>
<body>
    <div id="printec-site"></div>

    <script src="{{ url('/js/printec-catalog-widget.js') }}"></script>
    <script src="{{ url('/js/printec-site-widget.js') }}"></script>
    <script>
        PrintecSite.init({
            apiKey: '{{ $partner->api_key }}',
            apiUrl: '{{ url("/api/public/catalog") }}',
            container: '#printec-site'
        });
    </script>
</body>
</html>
