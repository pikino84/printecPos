---
name: pint
description: Aplicar Laravel Pint al codebase para enforcer el estilo de código PSR-12. Úsalo cuando el usuario invoca /pint o pide formatear PHP. Si Pint no está instalado, sugerir instalación.
---

# /pint — Formatear código PHP

## Comportamiento

1. Verificar que `vendor/bin/pint` existe.
2. Si no existe, decir: "Pint no está instalado. Ejecuta: `composer require laravel/pint --dev`".
3. Si existe, correr `vendor/bin/pint` (sin `--test` por defecto, aplica los cambios).
4. Mostrar el resumen al usuario: archivos formateados, archivos OK, total de cambios.

## Variantes

- Si el usuario dice "/pint dry" o "/pint check" → correr `vendor/bin/pint --test` (no modifica, solo reporta).
- Si el usuario pasa una ruta `/pint app/Models/Partner.php` → correr `vendor/bin/pint app/Models/Partner.php`.

## Comando default

```bash
vendor/bin/pint
```

## Notas

- El hook `PostToolUse` ya aplica Pint al archivo recién editado. `/pint` se usa para formatear el repo completo o áreas específicas.
- Si Pint detecta cambios extensos, comentar al usuario antes de aplicar (puede haber conflictos pendientes en su working tree).
