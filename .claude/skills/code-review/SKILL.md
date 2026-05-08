---
name: code-review
description: Auto-revisar el diff actual del working tree contra los criterios de PrintecPOS — validación, autorización, queries, secretos, multi-tenant, cache busting. Úsalo al invocar /code-review o cuando el usuario pida revisar un cambio antes de commitear.
---

# /code-review — Revisar diff del working tree

## Comportamiento

1. Correr `git diff HEAD` para obtener cambios sin commitear (incluir staged y unstaged).
2. Si no hay diff, también revisar último commit con `git show HEAD`.
3. Aplicar la lista de criterios siguiente a cada archivo modificado.
4. Reportar: ✅ pasa, ⚠️ duda, ❌ problema. Para cada problema, dar línea exacta y sugerencia.

## Criterios

### Validación
- [ ] Toda entrada de usuario pasa por FormRequest, no validación inline.
- [ ] Inputs externos (API DobleVela/Innovation) sanitizados antes de persistir.
- [ ] Tipos correctos (no `int` recibiendo `string` desde request).

### Autorización
- [ ] Endpoints sensibles tienen `auth:sanctum` o middleware equivalente.
- [ ] Acceso a entidades por-partner verifica `partner_id` (Policy o check explícito).
- [ ] No hay `if ($user->role === 'X')` directo — debe ser via Gate/Policy/Permission.
- [ ] Endpoints de partner API verifican `api_key` y `api_show_prices`.

### Queries y performance
- [ ] No hay N+1: relaciones cargadas con `with()`.
- [ ] Listados grandes paginados (`paginate`/`cursorPaginate`).
- [ ] Queries en reportes usan `Schema::hasColumn` si la columna es reciente.
- [ ] No hay `Model::all()` sin paginar.

### Seguridad
- [ ] Cero secretos en código (`.env` access ok, hardcoded NO).
- [ ] CSRF token en forms.
- [ ] No SQL crudo con interpolación (usar bindings).
- [ ] No `eval()`, no `unserialize()` de data externa.
- [ ] Subida de archivos: validar extensión + MIME + tamaño.

### Multi-tenant
- [ ] Queries que tocan `Quote`, `OwnProduct`, `PartnerPricing` etc. filtran por `partner_id`.
- [ ] Super admin puede saltar la regla con check explícito.

### UI / cache busting
- [ ] Assets estáticos con `?v={{ filemtime(...) }}` o via `@vite`.
- [ ] No CSS inline (excepción: branding del partner).
- [ ] No jQuery introducido.

### Tests
- [ ] Cambios no-triviales tienen test Feature/Unit asociado.
- [ ] Tests no mockean DB en integraciones.

### Convenciones del repo
- [ ] Pint pasa (`vendor/bin/pint --test` sobre los archivos modificados).
- [ ] No archivos `.md` de planning huérfanos.
- [ ] No botones UI que disparan sincronización con APIs externas.

## Output esperado

```
## Code Review — <branch> @ <hash>

### app/Http/Controllers/OrderController.php
✅ Validación con StoreOrderRequest
⚠️  Línea 45 — falta verificar partner_id del usuario contra el de la quote
❌ Línea 67 — Order::all() sin paginar; usar paginate(20)

### app/Models/Order.php
✅ Trait LogsActivity presente
✅ $fillable correcto

### resources/views/orders/show.blade.php
⚠️  Línea 12 — asset/js sin cache busting
```

## Si encadena con agente

Si existe el agent `qa` y los cambios son no-triviales, sugerir al usuario lanzarlo en background para tests + revisión más profunda.
