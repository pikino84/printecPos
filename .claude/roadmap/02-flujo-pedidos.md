# 02 — Flujo cotización → pedido → producción → entrega

**Prioridad:** 🔥 Alta. Es el siguiente paso natural después de que la cotización ya incluye impresión (épica 01).

## Problema
Hoy el distribuidor genera y envía cotización, pero no hay un camino dentro del sistema para convertirla en pedido, manejar pagos, mandarla a producción y cerrarla con entrega. Todo sale del sistema y vuelve manual.

## Objetivo
Que la cotización pueda escalar a pedido confirmado, con todos sus estados y reglas de fechas, sin salir del sistema.

## Subtareas

### 2.1 Botón "Solicitar a Printec" / "Fincar pedido"
- En la vista de cotización (después de "Enviar cotización") agregar el botón.
- Detrás: crear un registro `order` (o promover la `quote` a `order`) con su propio ciclo de estados.

### 2.2 Checkbox "con / sin impresión"
- Al fincar el pedido, el distribuidor decide si Printec debe entregar el producto pelado o personalizarlo.
- Si marca "con impresión":
  - Mostrar desglose del costo de la personalización y el tipo (alimentado por la épica 01).
  - Forzar subir un archivo vector (pdf, ai, eps, svg) — Eduardo: *"Si le podemos poner de una vez que suba su archivo con vector, está todo mal"* (sic — quiere que sea obligatorio).
  - Validar tipo de archivo y peso máximo en server-side.

### 2.3 Pago
- Mostrar formas de pago. Por ahora **solo transferencia** según Eduardo.
- Generar un comprobante / instrucciones de pago al confirmar.
- Estado del pedido cambia a `pendiente_de_pago`.
- Cuando alguien de Printec confirma manualmente que llegó el pago, pasa a `pagado` → `en_produccion`.

### 2.4 Catálogo de estados del pedido
Definir y persistir esta máquina de estados:

| Estado | Quién dispara |
|---|---|
| `borrador` | Distribuidor (creación) |
| `enviada` | Distribuidor (envía cotización) |
| `solicitada` | Distribuidor (clic "Solicitar a Printec") |
| `confirmada` | Printec (revisó stock y disponibilidad) |
| `pendiente_pago` | Sistema, automático tras confirmar |
| `pagada` | Printec, manual (confirmación de transferencia) |
| `en_produccion` | Sistema, automático tras pago + horarios |
| `entregada` | Printec, manual |

Usar tabla `order_status_history` para auditar transiciones (quién, cuándo, comentario).

### 2.5 Cálculo de fecha de entrega (días hábiles)
Reglas confirmadas por Eduardo:
- Catálogo de horario laboral del taller (lunes–viernes o lunes–sábado, hora apertura/cierre).
- Catálogo de días feriados configurable.
- Si el pago entra fuera de horario o en feriado, "cuenta" desde el siguiente día hábil.
- A partir de ahí sumar el lead time del producto/servicio.

Implementación sugerida:
- Tabla `business_hours` (día_semana, hora_inicio, hora_fin).
- Tabla `holidays` (fecha, descripción).
- Helper `OrderScheduler::nextProductionStart(Carbon $paidAt)` que respete ambas tablas.

### 2.6 Vista de Printec — bandeja de pedidos
- Lista filtrable por estado.
- Acciones rápidas: confirmar, marcar pagado, marcar en producción, marcar entregado.
- Visible solo para super-admin / staff Printec.

### 2.7 Notificaciones por correo
- Al distribuidor: confirmación del pedido, instrucciones de pago, paso a producción, entrega.
- A Printec: nuevo pedido solicitado, pendiente de confirmar.

## Criterios de aceptación
- [ ] El distribuidor convierte una cotización en pedido sin salir del sistema.
- [ ] El pedido obliga a subir vector si lleva impresión.
- [ ] Los estados se transitan según la tabla y queda historial.
- [ ] La fecha de entrega calculada respeta horarios y feriados configurables.
- [ ] Printec ve la bandeja con un solo filtro por estado.

## Notas / abierto
- Dispersar pago automático vs manual: Eduardo quiere revisar si "ya dispersamos automáticamente" — confirmar antes de implementar la rama de pagos automáticos.
- Integración con SAT/CFDI ocurre por separado en la épica 08.
