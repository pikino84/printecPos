# Configuración de Pricing

La configuración de pricing define los parámetros globales del sistema de precios escalonados. **Solo accesible para Super Admin**.

## Acceder al Módulo

1. En el menú lateral, haz clic en **Administración**
2. Selecciona **Config. de Pricing**

## Parámetros Configurables

### Período de Cálculo
- **Período de evaluación**: Meses considerados para calcular el volumen de compras
- **Fecha de corte**: Cuándo se recalculan los niveles

### Comportamiento del Sistema
- **Cálculo automático**: Activar/desactivar actualización automática de niveles
- **Notificaciones**: Alertas cuando un partner cambia de nivel
- **Período de gracia**: Tiempo antes de bajar de nivel por bajo volumen

### Valores por Defecto
- **Nivel inicial**: Nivel asignado a nuevos partners
- **Markup por defecto**: Margen de ganancia inicial para asociados

## Modificar Configuración

1. Ajusta los valores en el formulario
2. Haz clic en **Guardar Cambios**
3. Los cambios se aplican inmediatamente

> **Precaución**: Los cambios en la configuración afectan a todo el sistema. Procede con cuidado.

## Historial de Cambios

El sistema registra todos los cambios en la configuración:
- Quién hizo el cambio
- Cuándo se realizó
- Valores anteriores y nuevos

## Tips

- Documenta los cambios importantes antes de realizarlos
- Considera el impacto en partners existentes
- Comunica los cambios a los administradores
- Realiza cambios en horarios de baja actividad si es posible

## Seguridad

- Solo usuarios con rol **Super Admin** pueden acceder
- Se requiere permiso `pricing-settings.manage` para modificar
- Todos los cambios quedan registrados en el historial de actividad

---

[← Volver a la guía](./README.md)
