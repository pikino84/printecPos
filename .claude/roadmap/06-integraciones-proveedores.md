# 06 — Reactivar integración Innovation / For Promotion (y siguientes)

**Prioridad:** 🟡 Media. No es crítico para mayo (foco es impresión), pero sí para el modelo B2B que Eduardo quiere empujar.

## Problema
Hoy solo está integrado DobleVela. Eduardo: *"el deal está en conectar negocios con negocios"*. El crecimiento depende de tener catálogo amplio en el cotizador.

## Objetivo
Tener al menos **2-3 proveedores integrados** en el cotizador, mostrándose juntos para el distribuidor (si Eduardo decide que se vea el origen) o mezclados.

## Subtareas

### 6.1 Innovation / For Promotion — retomar
- Buscar la documentación que ya se había levantado (Eduardo confirma que existía).
- Revisar contratos/credenciales con el proveedor — pueden estar caducas si pasaron meses.
- Implementar la sincronización siguiendo el patrón de DobleVela:
  - Importador / cron de catálogo.
  - Cron de stock por almacén.
  - Mapeo de imágenes.
  - Mapeo de tipos de impresión (épica 01).

### 6.2 Decisión: mostrar u ocultar el proveedor en el cotizador
Eduardo dejó la decisión abierta: *"podemos mostrar o no mostrar qué proveedor es, como tú lo decidas"*.
- Por defecto, **ocultar el proveedor** al distribuidor (Printec aparece como el proveedor único frente al cliente del distribuidor).
- Mostrar el proveedor solo en vistas de super-admin (para back-office y reportes).

### 6.3 Mecanismo de toggle por integración
- Tabla `suppliers` (ya debe existir o equivalente). Agregar columna `enabled` y `visible_to_partner`.
- Permite encender/apagar Innovation sin tirar todo si hay incidencias en su API.

### 6.4 Manejo de fallos de proveedor
Eduardo dejó claro su miedo: *"otro proveedor a lo mejor no te está cumpliendo y te atrasa lo de DobleVela"*.
- Stock con timestamp `synced_at` por SKU.
- Alerta en super-admin si la sincronización de un proveedor lleva > X horas sin éxito.
- Posibilidad de bloquear cotizar productos de un proveedor "caído" sin afectar otros.

### 6.5 Otros proveedores (futuro)
Eduardo mencionó la estrategia "uno por giro" — fabricantes especializados:
- Lanyards (Juan Pablo).
- Aplaudidores.
- Pelotas.
- Etc.

No tocar todavía hasta tener Innovation funcionando. Eduardo va a Expubicidad (feria) a sondear proveedores nuevos.

## Criterios de aceptación
- [ ] Innovation sincroniza catálogo y stock como DobleVela.
- [ ] Cotizador muestra productos de Innovation en línea con DobleVela.
- [ ] Super-admin puede pausar Innovation con un toggle si la API falla.
- [ ] Decisión documentada sobre visibilidad del proveedor al distribuidor.

## Notas
- Reusar memoria `reference_doblevela_api.md` como plantilla para documentar la API de Innovation con el mismo formato.
- Recordar feedback `no_ui_buttons_sync`: la sincronización debe ser transparente, sin botones para el usuario.
