---
name: test-filter
description: Correr un subset de tests PHPUnit con --filter. Úsalo cuando el usuario invoca /test-filter <patrón>. El argumento puede ser nombre de método, clase, o regex. Si no se pasa argumento, listar tests disponibles.
---

# /test-filter — Correr tests filtrados

## Uso

```
/test-filter <patrón>
```

## Comportamiento

1. Si no hay argumento → correr `php artisan test --list-tests` y devolver al usuario la lista para que elija.
2. Si hay argumento → correr `php artisan test --filter=<argumento>`.
3. Mostrar resultado: tests pasados, fallados, tiempo total. Si hay fallos, mostrar el output del primer fallo en detalle.

## Ejemplos

- `/test-filter QuoteTest` → corre todos los métodos de `QuoteTest`.
- `/test-filter test_partner_can_create_quote` → corre ese método específico.
- `/test-filter Partner` → corre cualquier test que matchee "Partner".

## Comando

```bash
php artisan test --filter=$ARGUMENTS
```

## Si fallan tests

- Mostrar el primer fallo con stack trace recortado.
- Sugerir `/test-filter <método_específico>` para iterar en uno solo.
- No correr `--stop-on-failure` por default — el usuario suele querer ver el panorama completo.
