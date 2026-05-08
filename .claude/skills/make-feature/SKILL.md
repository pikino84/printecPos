---
name: make-feature
description: Generar el scaffold completo de un recurso Laravel — Model + Migration + FormRequest + Controller + Resource + Feature Test. Úsalo cuando el usuario invoque /make-feature <Recurso> o pida "scaffold para X". Sigue las convenciones del proyecto (thin controllers, services, multi-tenant).
---

# /make-feature — Scaffold completo de recurso

## Uso

```
/make-feature <Recurso>
```

Ej: `/make-feature Order` genera:
- `app/Models/Order.php`
- `database/migrations/YYYY_MM_DD_HHMMSS_create_orders_table.php`
- `app/Http/Requests/StoreOrderRequest.php` + `UpdateOrderRequest.php`
- `app/Http/Controllers/OrderController.php` (thin)
- `app/Http/Resources/OrderResource.php`
- `app/Policies/OrderPolicy.php`
- `tests/Feature/OrderTest.php`
- `database/factories/OrderFactory.php`
- (opcional) `app/Services/Order/OrderService.php` — preguntar al usuario si la lógica lo amerita

## Comportamiento

1. Validar que `<Recurso>` esté en singular PascalCase (`Order`, no `orders`/`order`).
2. Antes de crear, revisar si ya existe alguno de esos archivos. Si existe, **preguntar** antes de sobrescribir.
3. Usar artisan con sus flags:
   ```bash
   php artisan make:model <Recurso> -mfsc --requests --resource --policy
   php artisan make:test <Recurso>Test
   ```
4. Después de generar, **editar los archivos** para adaptarlos a las convenciones:
   - Migration: agregar `partner_id` (FK a `partners`) si el recurso es por-partner. **Preguntar al usuario** si aplica.
   - Model: agregar `$fillable`, `$casts`, relaciones esperadas, `LogsActivity` trait si aplica.
   - FormRequest: rules iniciales con TODOs.
   - Controller: thin, llamando a Service si la lógica lo amerita.
   - Policy: stubs con verificación de `partner_id`.
   - Test: 3-5 stubs (`it_creates`, `it_lists`, `it_validates`, `it_authorizes`).
5. **No correr la migración**. Mostrar al usuario el comando `php artisan migrate` para que él decida.
6. **Correr Pint** sobre los archivos generados (`vendor/bin/pint <paths>`).

## Reglas

- Si el recurso es claramente por-partner (Order, Quote, OwnProduct), agregar `partner_id` en migration y scope en queries.
- Si NO es por-partner (catalog global, settings), explicitar al usuario.
- Si requiere relación con otros modelos (ej: Order → Quote, Order → OrderItem), preguntar antes de inferir.

## Ejemplo de uso esperado

Usuario: `/make-feature Order`
Tú: 
1. Confirma que quiere `Order` (singular), por-partner.
2. Genera todos los archivos.
3. Edita migration con campos básicos (`status`, `partner_id`, `quote_id`, `total`, `notes`, timestamps).
4. Corre Pint.
5. Resume al usuario qué archivos creó y qué falta hacer (campos específicos, validation rules, factory data).

## Notas

- No crear vistas Blade en este scaffold — eso lo hace el usuario manualmente o pide `/make-feature <Recurso> --views`.
- No agregar rutas a `routes/web.php` automáticamente — sugerirlas al final del scaffold.
