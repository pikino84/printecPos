# 09 — Sitio web público conectado al sistema

**Prioridad:** 🔵 Posterior. Depende de épicas 01 y 02 (modelo de impresión cerrado primero).

## Problema
El sitio web público de Printec hoy no comparte catálogo ni cotizador con el sistema. El cliente final no puede pedir personalización online.

## Objetivo
Que el sitio web público use el mismo catálogo y motor de cotización del sistema, con la misma UX de "sugerir impresión" para el cliente final.

## Subtareas

### 9.1 API pública / endpoints expuestos
- Reusar el "API catálogo para widgets externos" que ya menciona `CLAUDE.md`.
- Endpoints públicos: catálogo paginado, detalle de producto, tipos de impresión sugeridos, cálculo de cotización (sin guardar).
- Rate limit + reCAPTCHA en endpoints sensibles.

### 9.2 Sugerencia de impresión al cliente final
- Misma lógica que la épica 01, pero con copy adaptado al cliente final (no al distribuidor).
- Si el cliente quiere personalización, lo pasa al flujo de cotización (épica 02 simplificado: sin partner_id, captura datos de contacto).

### 9.3 Captura de leads
- Cliente final que cotiza pero no compra → entra como lead a una bandeja (super-admin).
- Notificación a Eduardo / equipo.

### 9.4 Convertir cliente final a distribuidor / cliente directo
- En la bandeja de leads, convertir a partner con un clic.

### 9.5 Pagos en línea (futuro)
- Por ahora solo transferencia (ver épica 02).
- A futuro evaluar Mercado Pago / Stripe / OpenPay.

## Criterios de aceptación
- [ ] El sitio web público lista el mismo catálogo que el sistema (con caché).
- [ ] El cliente final ve sugerencias de impresión iguales a las del distribuidor.
- [ ] Una cotización del sitio público entra al sistema como lead identificable.
- [ ] El equipo de Printec puede convertir un lead en partner desde super-admin.

## Notas
- Eduardo: *"el sitio web también ya le va a poder sugerir al cliente final cómo se va a tratar eso"* — está condicionado a que la épica 01 entregue una sugerencia robusta y bien probada en el cotizador del distribuidor primero.
