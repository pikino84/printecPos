---
name: roadmap
description: Carga el roadmap activo del proyecto PrintecPOS antes de implementar trabajo de épicas. Úsalo cuando el usuario mencione "épica X", "sprint A/B/C/D", "trabajemos en impresión/lonas/comisiones/perfil/DMC/CFDI/Innovation/sitio web", o cualquier feature listada en el roadmap. NO inventes tareas — siempre lee primero el archivo de la épica correspondiente.
---

# Roadmap PrintecPOS

Antes de implementar trabajo de cualquier épica, **lee el archivo correspondiente** en `.claude/roadmap/`. Cada uno tiene problema, objetivo, subtareas, criterios de aceptación, riesgos.

## Índice rápido

| Mención del usuario | Archivo a leer |
|---|---|
| "impresión", "grabado", "sugerir tipo", "MALVA", "DENCEL", "épica 01" | `.claude/roadmap/01-impresion-grabado.md` |
| "pedido", "fincar pedido", "Solicitar a Printec", "estados", "fecha entrega", "épica 02" | `.claude/roadmap/02-flujo-pedidos.md` |
| "lona", "vinil", "decimales", "metro cuadrado", "épica 03" | `.claude/roadmap/03-lonas-decimales.md` |
| "comisiones", "dispersión", "factura única", "cierre mensual", "épica 04" | `.claude/roadmap/04-comisiones-desglose.md` |
| "barra de progreso", "perfil 100%", "deadline 15 días", "veto", "épica 05" | `.claude/roadmap/05-perfil-progreso.md` |
| "Innovation", "For Promotion", "integración proveedor", "épica 06" | `.claude/roadmap/06-integraciones-proveedores.md` |
| "DMC", "B2B", "escalas descuento volumen", "épica 07" | `.claude/roadmap/07-dmc-b2b.md` |
| "facturación", "CFDI", "Prodigia", "timbrado", "épica 08" | `.claude/roadmap/08-facturacion-cfdi.md` |
| "sitio web público", "cliente final cotiza", "widget", "épica 09" | `.claude/roadmap/09-sitio-web-publico.md` |
| "sprint A" | épicas 03 + 05 (quick wins) |
| "sprint B" | épicas 01 + 02 (foco mayo) |
| "sprint C" | épicas 04 + 07 + 08 |
| "sprint D" | épicas 06 + 09 |
| "qué falta", "qué sigue", "índice", "general" | `.claude/roadmap/README.md` |

## Reglas al trabajar una épica

1. **Lee la épica completa antes de cualquier cambio de código** — no implementes desde memoria de la junta.
2. **Verifica estado del codebase**: la épica fue escrita 2026-05-04; algunos modelos pueden ya existir (ej: `ProductImpressionTechnique` ya existe en `app/Models/`). Reusar antes de crear.
3. **No inventes scope**: si una sub-tarea no está en el archivo, pregúntale al usuario antes de hacerla.
4. **Marca progreso** con TaskCreate/TaskUpdate cuando pegues más de 2 subtareas en una sesión.
5. **Decisiones nuevas** que afecten la épica → actualiza el archivo de la épica y haz commit.

## Foco actual (mayo 2026)

Según la junta del 2026-05-04 (transcripción local en `temp/transcript.txt`, no commiteada):
- Impresión sugerida (épica 01) y flujo de pedido (épica 02) son el foco del mes.
- Lonas decimales (03) y barra de progreso (05) son quick wins permitidos en paralelo.
- Sitio web público (09) **depende** de que 01 y 02 estén estables; no adelantar.
