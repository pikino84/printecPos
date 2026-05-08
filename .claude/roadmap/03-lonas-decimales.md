# 03 — Lonas y viniles con cantidad decimal

**Prioridad:** 🟢 Quick win. Cambio acotado, alta utilidad inmediata.

## Problema
Hoy el cotizador redondea la cantidad a entero. Cuando un cliente pide 1.8 m² de lona o vinil, el sistema lo trata como 2 m², y el costo no cuadra.

## Objetivo
Permitir cantidad decimal **solo** en productos vendidos por metro lineal/cuadrado (lonas y viniles), sin habilitar decimales para productos contables (tazas, plumas, termos).

## Subtareas

### 3.1 Categoría especial "por metro"
- Crear/usar una categoría o flag en producto: `unit_type = 'metro_cuadrado'` (o `metro_lineal`).
- Lonas y viniles llevan ese flag.

### 3.2 Validación condicional de cantidad
- En el formulario de cotización: si el producto tiene `unit_type` lineal/m², el input acepta decimales (`step="0.01"`).
- Si no, sigue siendo entero (regla previa).
- Backend: validar lo mismo en el `StoreQuoteItemRequest` o equivalente.

### 3.3 Reglas de ancho de lona
Confirmadas por Eduardo:
- Lona ≤ 1 m de alto → toma rollo de 1 m de ancho.
- Lona > 1 m hasta 1.5 m → toma rollo de 1.5 m de ancho.
- Vinil siempre 1.5 m por defecto (revisar con Eduardo si hay otro caso).

Cálculo:
```
ancho_rollo = (alto <= 1.0) ? 1.0 : 1.5
costo = largo * ancho_rollo * costo_metro_cuadrado
```

Verificar dónde vive hoy el cálculo y modificar para respetar decimales en `largo`. Eduardo notó que el costo redondea — el bug está ahí.

### 3.4 Mostrar el desglose
En la línea del cotizador, además del subtotal mostrar:
- `1.80 m × 1.50 m = 2.70 m²` → para que el distribuidor vea por qué le da el costo que da.

## Criterios de aceptación
- [ ] Ingresar `1.8` como cantidad de lona NO redondea.
- [ ] Productos no por-metro siguen sin aceptar decimales.
- [ ] El cálculo aplica el ancho correcto (1.0 vs 1.5) según la regla.
- [ ] El desglose visible en la línea del cotizador.

## Notas
Eduardo aceptó relajar la validación para todos los productos *"al final son usuarios serios, no creo que alguien te pida 1.8 termos"* — pero igual conviene mantenerlo restringido por `unit_type` para evitar errores de captura accidental que metan ruido a producción.
