# Roadmap PrintecPOS — Mayo 2026

Origen: junta con Eduardo Butrón el **2026-05-04** (transcripción en `temp/transcript.txt`).

## Foco del mes (mayo 2026)
Eduardo lo dejó explícito al final de la junta:
> "Enfocarnos este mes en que reafirmemos bien cómo va a pensar el sistema lo de la impresión."

Todo lo demás se mueve alrededor de esa prioridad. El sitio web público viene después porque depende de que el modelo de impresión esté resuelto.

## Bloques

| # | Épica | Prioridad | Estado |
|---|---|---|---|
| 01 | [Impresión / grabado sugerido en cotizaciones](01-impresion-grabado.md) | 🔥 Alta — foco del mes | Pendiente |
| 02 | [Flujo cotización → pedido → producción → entrega](02-flujo-pedidos.md) | 🔥 Alta | Pendiente |
| 03 | [Lonas y viniles con cantidad decimal](03-lonas-decimales.md) | 🟢 Quick win | Pendiente |
| 04 | [Desglose y dispersión de comisiones mensual](04-comisiones-desglose.md) | 🟡 Media | Parcial (ya hay stats) |
| 05 | [Barra de progreso de perfil + deadline 15 días](05-perfil-progreso.md) | 🟠 Alta operativa | Pendiente |
| 06 | [Reactivar integración Innovation / For Promotion](06-integraciones-proveedores.md) | 🟡 Media | Pendiente |
| 07 | [Categoría DMC + escalas de descuento por volumen](07-dmc-b2b.md) | 🟡 Media | Pendiente |
| 08 | [Facturación CFDI (Prodigia)](08-facturacion-cfdi.md) | 🟡 Media | Pendiente |
| 09 | [Sitio web público conectado al sistema](09-sitio-web-publico.md) | 🔵 Posterior | Pendiente |

## Orden sugerido de ejecución

1. **Sprint A (esta semana)** — quick wins y desbloqueos:
   - 03 Lonas decimales (1 cambio de validación + tests)
   - 05 Barra de progreso de perfil (visible en partners + reminder por correo)

2. **Sprint B (mayo, foco principal)** — impresión:
   - 01 Impresión / grabado sugerido (incluye revisar API DobleVela por tipo de impresión)
   - 02 Flujo cotización → pedido (botón "Solicitar a Printec", checkbox con/sin impresión, upload vector, estados)

3. **Sprint C (cierre de mayo / junio)** — comercial y back-office:
   - 04 Desglose y dispersión mensual de comisiones
   - 07 DMC + escalas de descuento por volumen
   - 08 Facturación CFDI

4. **Sprint D (junio+)** — escalamiento:
   - 06 Reactivar Innovation / For Promotion
   - 09 Sitio web público con impresión sugerida al cliente final

## Decisiones cerradas en la junta

- **Comisión sobre personalización**: queda como "comisionable entre comillas" — no se comisiona si Printec no ganó (errores, retrabajos). A definir reglas finas con prueba y error.
- **Dispersión de comisiones**: acumular y dispersar al primer día hábil del mes siguiente, **una sola factura** con desglose por pedido.
- **Días de entrega**: calcular sobre días hábiles + horarios + catálogo de feriados. Si entra fuera de horario, cuenta el día siguiente.
- **Escalas DMC**: descuento por volumen mensual (ej: 150/mes → tanto %), no descuento de entrada como distribuidores.
- **Deadline de registro**: 15 días para completar perfil, si no se cierra y se vetea por al menos un año (con un correo).
- **Lonas/viniles**: permitir decimales en cantidad. Reglas de ancho: ≤1m usa rollo de 1m, >1m usa rollo de 1.5m.

## Decisiones pendientes (no resueltas en junta)

- ¿Mostrar u ocultar el proveedor (DobleVela / Innovation) al distribuidor en la cotización?
- ¿Cómo modelar el costo de personalización (tinta, luz, nómina) en el cálculo automático? — Eduardo dijo "es prueba y error", no se ataca todavía.
- ¿Comisión sobre personalización: % fijo, escalonado, o se evalúa caso por caso al cierre de mes?
- ¿Qué proveedores adicionales además de Innovation se priorizan? (Eduardo mencionó "uno por giro" pero no listó nombres)

## Referencias

- Transcripción completa: `temp/transcript.txt`
- Memoria de comisiones existente: `~/.claude/projects/.../memory/project_quote_stats_comisiones.md`
- Documentación API DobleVela: `~/.claude/projects/.../memory/reference_doblevela_api.md`
