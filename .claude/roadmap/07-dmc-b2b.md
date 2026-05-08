# 07 — Categoría DMC + escalas de descuento por volumen

**Prioridad:** 🟡 Media. Eduardo quiere empezar a meter DMCs (Destination Management Companies y similares) ya — los va a visitar él.

## Problema
Hoy todos los partners se tratan como "distribuidor", con el mismo descuento de entrada. Los DMCs son perfil distinto: no se les debe soltar el descuento de entrada, sino premiarlos por volumen.

## Objetivo
Tener una categoría/etiqueta para clientes y aplicar reglas de descuento condicionadas al volumen mensual.

## Subtareas

### 7.1 Etiqueta / categoría de partner
- Agregar `partner_type` enum: `distribuidor`, `dmc`, `cliente_final`, `cliente_directo`.
- Etiqueta visible en super-admin con filtros y búsqueda.
- No romper la lógica actual del distribuidor (default sigue siendo `distribuidor`).

### 7.2 Reglas de descuento escalonado por volumen mensual
Eduardo: *"si tú me compras 150 al mes, te voy reduciendo tanto"*.
- Tabla `volume_discount_tiers`:
  - `partner_id` o `partner_type`
  - `min_monthly_volume` (cantidad o monto, definir con Eduardo)
  - `discount_percentage`
- Job nocturno que recalcule el "tier vigente" para cada DMC con base en el volumen del mes en curso.
- El cotizador aplica el tier vigente al cotizar.

### 7.3 Histórico de tier
- Tabla `partner_tier_history` para ver cómo subió/bajó cada DMC.
- Útil para auditorías y conversaciones comerciales ("subiste 5% por volumen").

### 7.4 Vista comercial DMC
- Lista filtrable de DMCs con: volumen mes actual, tier actual, próximo tier (cuánto falta).
- Eduardo: hubo varios pedidos directos por correo el fin de semana (~70 mil) — herramienta para identificar nuevos DMCs candidatos.

### 7.5 Identificar/etiquetar clientes existentes
Eduardo: *"hay que identificar bien a los que ya son clientes también para que no tope, por los nuevos"* — los clientes históricos no deben perder su trato.
- Migración / vista que liste clientes que recurren para que Eduardo los etiquete manualmente como `cliente_directo` o `dmc`.

## Criterios de aceptación
- [ ] Existe `partner_type` y se puede filtrar/asignar en super-admin.
- [ ] DMCs no obtienen descuento de entrada como distribuidores.
- [ ] El descuento de DMC se calcula a partir del volumen del mes y se aplica en el cotizador.
- [ ] Hay vista que muestra el tier actual y el siguiente para cada DMC.

## Notas
- Eduardo mencionó tener una BD de ~80 DMCs candidatos — pedirla cuando se llegue a esta épica para semilla / outreach.
- La conversación con Eduardo es: él prefiere "hablarles él mismo" antes que onboarding masivo automático para DMCs. El sistema sirve de soporte, no de ventas frías.
