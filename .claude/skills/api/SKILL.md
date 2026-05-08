---
name: api
description: Convenciones para endpoints REST y APIs en PrintecPOS. Úsalo al crear/editar rutas en routes/api.php, controllers de api/, autenticación con Sanctum, o cuando se exponga catálogo/cotización a widgets externos del partner.
---

# API REST — PrintecPOS

## Autenticación

Dos modelos coexisten:

1. **Sanctum SPA** (sesiones web del panel admin). `auth:sanctum` middleware.
2. **API key por partner** (catálogo/widget público). Cada `Partner` tiene `api_key` y `api_show_prices`. Validar en middleware custom.

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('quotes', QuoteController::class);
});

Route::middleware('partner.api')->prefix('partners/{partner:slug}')->group(function () {
    Route::get('catalog', [Api\CatalogController::class, 'index']);
});
```

## Estructura de rutas

- Rutas API en `routes/api.php`. Versionar con prefijo si el contrato cambia (`/v1/`, `/v2/`).
- Controllers de API bajo `app/Http/Controllers/Api/` (ya existe).
- Resources (`JsonResource`) para serializar — no devolver modelos crudos.

## Respuestas JSON consistentes

Estructura recomendada:

```json
{
  "data": [...],
  "meta": { "page": 1, "per_page": 20, "total": 145 }
}
```

Errores:

```json
{
  "message": "Resource not found",
  "errors": { "field": ["mensaje"] }
}
```

Laravel ya hace esto con `422` para FormRequest validation. Para errores de negocio, lanzar excepción custom y mappearla en `app/Exceptions/Handler.php`.

## Paginación

- Usar `paginate()` o `cursorPaginate()` (preferir cursor para feeds grandes/streamings de catálogo).
- Default `per_page=20`, máximo `100`.

## API Resources

```php
class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->when(
                $request->user()?->partner?->api_show_prices ?? false,
                fn() => $this->price
            ),
        ];
    }
}
```

Ocultar precios cuando el partner tiene `api_show_prices = false`.

## Rate limiting

- Sanctum endpoints: default `throttle:api` (60/min).
- Endpoints públicos por partner: throttle más estricto (10/min) + reCAPTCHA en formularios públicos.

## CORS

- Configurado en `config/cors.php`. Habilitar dominios de partner que consumen el catálogo.
- No usar `allowed_origins => ['*']` en producción.

## Documentación

- Cuando se expone un endpoint nuevo público, documentar en `docs/api/<modulo>.md` con: ruta, método, params, respuesta de ejemplo, errores.
- Ejemplos `curl` para cada endpoint.

## Anti-patrones

- ❌ Devolver modelos Eloquent crudos sin Resource.
- ❌ Lógica de negocio en el controller de API (mismo principio que web).
- ❌ Endpoints sin throttle.
- ❌ Mostrar precios sin verificar `api_show_prices` del partner.
