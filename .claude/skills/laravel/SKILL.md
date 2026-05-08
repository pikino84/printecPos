---
name: laravel
description: Convenciones Laravel del proyecto PrintecPOS. Úsalo al crear/editar controllers, services, models, FormRequests, migrations, policies o jobs. Incluye reglas de thin controllers, scoping multi-tenant por partner_id, y patrones específicos del codebase.
---

# Convenciones Laravel — PrintecPOS

## Arquitectura por capas

| Capa | Dónde vive | Responsabilidad |
|---|---|---|
| Controllers | `app/Http/Controllers/` | HTTP-only: recibir Request, delegar a Service, devolver Response/View |
| FormRequests | `app/Http/Requests/` | Validación + autorización inicial |
| Policies | `app/Policies/` | Autorización fina por modelo (`@can`, `Gate::authorize`) |
| Services | `app/Services/<Modulo>/` | Lógica de dominio. Ej: `Services/DobleVela/`, `Services/Innovation/` |
| Models | `app/Models/` | Relaciones, casts, scopes. Sin lógica de negocio |
| Jobs | `app/Jobs/` | Trabajo en background (queue driver = `database`) |

**Thin controllers regla de oro:** si un método de controller pasa de 20 líneas, sale a Service.

## Multi-tenant por `partner_id`

- No hay un `BelongsToTenant` global trait. El scoping es manual.
- Al crear queries nuevas que toquen entidades por-partner (`PartnerPricing`, `Quote`, `OwnProduct`, etc.):
  - Filtrar por `partner_id` en el controller/service.
  - Validar pertenencia en la Policy del modelo (`return $user->partner_id === $model->partner_id`).
- `super_admin` puede pasar de la regla anterior; usar `Gate::before` o checar rol explícitamente.

## FormRequests

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:120'],
        'partner_id' => ['required', 'exists:partners,id'],
    ];
}

public function authorize(): bool
{
    return $this->user()->can('quotes.create');
}
```

## Migraciones seguras

- Defaults explícitos (`->nullable()` o `->default(0)`) en columnas nuevas para no romper filas existentes.
- `Schema::hasColumn()` antes de leer columnas recién agregadas en queries de reporting (evita 500s mientras prod no haya migrado).
- Una migración por cambio lógico. No mezclar drop + create + alter en una sola.

## Eloquent

- Relaciones definidas explícitamente en el modelo. No hacer queries crudas salvo en reportes complejos.
- Scopes para filtros recurrentes: `scopeActive`, `scopeForPartner($query, $partnerId)`.
- `$casts` para boolean/datetime/json — no leer flags como strings.

## Activity log

- Modelos sensibles (Quote, Order, PartnerPricing) usan `Spatie\Activitylog\Traits\LogsActivity`.
- Configurar `getActivitylogOptions()` con los atributos que sí queremos auditar (no todos).

## Testing

- `RefreshDatabase` o `DatabaseTransactions` en Feature tests.
- **No mockear DB**. Si la API externa es DobleVela/Innovation, mockear la response del Service o Guzzle, pero no la base.
- Factory por cada modelo nuevo bajo `database/factories/`.

## Anti-patrones a evitar

- ❌ `if ($user->role === 'super_admin')` directo en controllers — usar Policies/Gates.
- ❌ Validación dentro del controller — siempre FormRequest.
- ❌ Lógica de negocio en Blade — pasar variables ya resueltas desde el controller.
- ❌ Botones UI que disparan sincronización con APIs externas — todo por cron/job.
- ❌ `User::all()` o `Quote::all()` sin paginar — usar `paginate()`.

## Referencias en el repo

- Ejemplos de Service real: `app/Services/DobleVela/`, `app/Services/Innovation/`.
- Helpers globales: `app/Helpers/helpers.php`.
- Modelos como ejemplo: `app/Models/Partner.php`, `app/Models/Quote.php`.
