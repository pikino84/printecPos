# 05 — Barra de progreso de perfil + deadline de 15 días

**Prioridad:** 🟠 Alta operativa. Bloquea ingresos: distribuidores que no terminan registro no pueden facturar/comisionar, y son varios.

## Problema
Distribuidores se registran al primer paso y abandonan antes de completar fiscal/datos bancarios. Eduardo: *"se me está atorando en el primer paso de que sí se registran y después ya les vale madres"*.

## Objetivo
Que el distribuidor vea siempre cuánto le falta, recibirá recordatorios, y **si no completa en 15 días pierde acceso al descuento** (y se vetea por al menos un año).

## Subtareas

### 5.1 Definir los "campos requeridos" para considerar el perfil 100%
Lista probable (validar con Eduardo):
- Datos fiscales (RFC, razón social, régimen, CSF).
- Datos de contacto (teléfono validado, email validado).
- Domicilio fiscal completo.
- Datos bancarios (CLABE, banco, cuenta).
- Logo (opcional pero suma %).
- Aceptación de T&C / contrato firmado.

Modelar como un mapa peso-por-campo: e.g., fiscales 40%, bancarios 30%, contacto 20%, logo 10%. Cualquier ajuste de pesos es un solo cambio en el helper.

### 5.2 Helper de cálculo
- `Partner::profileCompletionPercentage()` que recorra los campos y devuelva entero 0–100.
- `Partner::missingProfileFields()` que devuelva los faltantes — usado en el correo y en la UI.

### 5.3 Barra de progreso visible
- En la vista del distribuidor (su dashboard) y en el listado super-admin de partners.
- En partners super-admin: filtro/orden por % para identificar a los que están en 0% o estancados.

### 5.4 Bloqueo por % < 100
- Mientras `% < 100`, el distribuidor **no puede** acceder al descuento de distribuidor (cotiza al precio público).
- Mientras `% < 100`, no se le acumula comisión (los pedidos se marcan `commissionable=false` por esta razón).
- UI: banner permanente en su dashboard explicando qué le falta y CTA al perfil.

### 5.5 Deadline de 15 días + correo recordatorio
- Calcular `deadline = created_at + 15 días`.
- Job diario que:
  - Manda correo recordatorio a `created_at + 7` y `deadline - 3`.
  - Al `deadline`, si sigue < 100%, marca `partners.status = 'rejected'` y bloquea el login con `vetado_hasta = deadline + 1 año`.
- El correo lleva nombre del distribuidor + lista de campos que le faltan + link directo al perfil.

### 5.6 Filtros para el equipo de ventas
- Lista exportable de distribuidores en 0% / parcial — para que Eduardo o su equipo los visiten o llamen.
- Eduardo: *"a lo mejor empiezas por los que están en cero"*.

## Criterios de aceptación
- [ ] Cualquier distribuidor ve su % en su dashboard.
- [ ] Super-admin ve el % en el listado de partners y puede filtrar/ordenar.
- [ ] Distribuidor con < 100% no obtiene descuento de distribuidor en el cotizador.
- [ ] Job diario manda recordatorios y vetea al cumplirse 15 días.
- [ ] Existe export CSV de partners pendientes para campañas de ventas.

## Notas
- "Vetar 1 año" según Eduardo es poner una bandera dura, no eliminar — preservar histórico.
- Si el mismo correo intenta registrarse de nuevo durante el veto, mostrar mensaje explicativo y CTA a contactar Printec.
