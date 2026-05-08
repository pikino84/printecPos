# 04 — Desglose y dispersión mensual de comisiones

**Prioridad:** 🟡 Media. Ya existe el panel de stats super-admin (commit `890f433`), aquí toca cerrar el ciclo: cómo se cobra y se factura cada mes.

## Problema
Existen las stats de comisiones pero no el flujo operativo: cómo se acumulan por distribuidor, cómo se confirma cada pedido como "comisionable", y cómo se materializa la dispersión y la factura mensual.

## Objetivo
Generar al cierre de mes un resumen por distribuidor con todos sus pedidos del periodo, los costos asociados, lo comisionable y lo no-comisionable, listo para emitir **una sola factura** y dispersar.

## Subtareas

### 4.1 Marcar pedidos como "comisionable" / "no comisionable"
Eduardo: las comisiones quedan "entre comillas" — si Printec pierde plata en un pedido (errores, retrabajos, descuentos especiales), ese pedido no comisiona.
- Campo `commissionable` (bool, default `true`) y `commission_override` (decimal nullable) en `orders` o `quotes`.
- UI: super-admin puede marcar un pedido como no-comisionable con razón obligatoria (`commission_note`).
- Si `commissionable = false`, el pedido sigue apareciendo en el desglose pero con $0 de comisión.

### 4.2 Periodo de cierre
- Cron mensual el día 1 del siguiente mes (o primer día hábil) que congele el periodo anterior.
- Generar registro en una tabla `commission_runs` con `period_start`, `period_end`, `status` (`open`, `closed`, `paid`).
- Mientras el periodo está `open`, super-admin puede cambiar flags. Una vez `closed`, se bloquea.

### 4.3 Resumen del periodo (vista super-admin)
Por distribuidor en el periodo:
- Total vendido (suma `total_price`).
- Costo proveedor (DobleVela / Innovation / Printec propio).
- Costo personalización (cuando aplica).
- Comisión calculada.
- Pedidos no-comisionables (con razones).
- Acumulado a dispersar.

Eduardo: *"de tal día tal día vendieron tantos cabrones y cada cabrón vendió esto, esto, esto"* — vista tipo resumen agregada + drill-down por pedido.

### 4.4 Generación de factura única
- Botón "Cerrar periodo y generar factura" → genera **un solo CFDI** por distribuidor con concepto agrupado por pedido.
- Depende de la épica 08 (CFDI Prodigia). Mientras tanto, exportar PDF/Excel con el desglose para emitir la factura a mano.

### 4.5 Dispersión
- Después de cerrar el periodo, marcar `commission_runs.status = 'paid'` cuando Eduardo confirme la transferencia.
- Histórico consultable por distribuidor (sus periodos pasados, montos, fechas).

## Criterios de aceptación
- [ ] Cada pedido tiene flag `commissionable` editable hasta el cierre del periodo.
- [ ] El día 1 del mes (o hábil siguiente) se cierra automáticamente el mes anterior.
- [ ] El resumen muestra por distribuidor: vendido, costo, comisión, no-comisionable.
- [ ] Se puede exportar / generar un único CFDI por distribuidor por periodo.
- [ ] Dispersión registrada con fecha y referencia.

## Notas
- Costo de personalización propia (tinta, luz, nómina) **no se modela todavía** — Eduardo dijo "es prueba y error". Por ahora se captura un costo manual por pedido si aplica.
- Reusar lo que ya hay en `project_quote_stats_comisiones` (memoria del proyecto).
