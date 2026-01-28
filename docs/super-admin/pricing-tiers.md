# Niveles de Precio (Pricing Tiers)

Los niveles de precio definen los descuentos que reciben los partners según su volumen de compras acumulado.

## Acceder al Módulo

1. En el menú lateral, haz clic en **Administración**
2. Selecciona **Niveles de Precio**

## Lista de Niveles

La tabla muestra los niveles configurados:

| Columna | Descripción |
|---------|-------------|
| Nombre | Identificador del nivel (ej: Bronce, Plata, Oro) |
| Descuento | Porcentaje de descuento aplicado |
| Mínimo de Compras | Volumen mínimo para alcanzar este nivel |
| Partners | Cantidad de partners en este nivel |
| Acciones | Ver, Editar, Eliminar |

## Crear Nuevo Nivel

1. Haz clic en **Nuevo Nivel**
2. Completa el formulario:
   - **Nombre**: Identificador del nivel
   - **Descuento (%)**: Porcentaje de descuento sobre precio base
   - **Compras Mínimas**: Monto mínimo acumulado para alcanzar el nivel
   - **Descripción**: Descripción opcional del nivel
3. Haz clic en **Guardar**

## Editar Nivel

1. Haz clic en **Editar**
2. Modifica los parámetros
3. Haz clic en **Actualizar**

> **Importante**: Cambiar los montos mínimos puede afectar la asignación automática de niveles a partners.

## Eliminar Nivel

1. Haz clic en **Eliminar**
2. Confirma la acción

> **Advertencia**: No elimines niveles que tengan partners asignados. Primero reasígnalos.

## Ver Detalle del Nivel

1. Haz clic en **Ver**
2. Visualiza:
   - Configuración del nivel
   - Partners asignados actualmente
   - Historial de cambios

## Ejemplo de Configuración

| Nivel | Descuento | Compras Mínimas |
|-------|-----------|-----------------|
| Inicial | 0% | $0 |
| Bronce | 5% | $10,000 |
| Plata | 10% | $50,000 |
| Oro | 15% | $100,000 |
| Platino | 20% | $500,000 |

## Funcionamiento

1. El sistema calcula el **volumen acumulado** de compras del partner
2. Compara con los **mínimos** de cada nivel
3. Asigna automáticamente el nivel correspondiente
4. El descuento se aplica a todos los precios del catálogo

## Tips

- Mantén una progresión lógica en los montos mínimos
- Los descuentos deben ser incrementales
- Considera el margen de ganancia al definir descuentos
- Revisa periódicamente si los niveles siguen siendo competitivos

---

[← Volver a la guía](./README.md)
