# Historial de Actividad

El historial de actividad registra todas las acciones importantes realizadas en el sistema para auditoría y control.

## Acceder al Módulo

1. En el menú lateral, haz clic en **Administración**
2. Selecciona **Historial de Actividad**

## Vista del Historial

La tabla muestra los eventos registrados:

| Columna | Descripción |
|---------|-------------|
| Fecha | Cuándo ocurrió la acción |
| Usuario | Quién realizó la acción |
| Tipo | Tipo de registro (usuario, producto, etc.) |
| Descripción | Detalle de la acción |
| Cambios | Valores anteriores y nuevos |

## Tipos de Eventos Registrados

### Usuarios
- Creación de usuario
- Edición de perfil
- Cambio de estado (activar/desactivar)
- Cambio de roles

### Partners
- Creación de partner
- Edición de información
- Cambio de estado
- Modificaciones de pricing

### Productos
- Creación de productos propios
- Edición de productos
- Eliminación de productos

### Cotizaciones
- Creación de cotizaciones
- Envío por correo
- Modificaciones

### Sistema
- Cambios en configuración de pricing
- Modificaciones de roles/permisos

## Filtrar el Historial

Puedes filtrar por:
- **Fecha**: Rango de fechas específico
- **Usuario**: Acciones de un usuario particular
- **Tipo**: Tipo de registro (usuario, producto, etc.)
- **Acción**: Crear, editar, eliminar, etc.

## Detalle de Cambios

Cada registro muestra:
- **Valores anteriores**: Estado antes del cambio
- **Valores nuevos**: Estado después del cambio
- **Campos modificados**: Qué campos específicos cambiaron

## Tips

- Revisa el historial regularmente para detectar actividad inusual
- Útil para auditorías y resolver disputas
- Los registros son inmutables (no se pueden modificar)
- Conserva los registros según políticas de retención de datos

## Seguridad

- Solo usuarios con permiso `activity-logs.view` pueden acceder
- Por defecto, solo Super Admin tiene este permiso
- El historial no puede ser modificado ni eliminado por usuarios

---

[← Volver a la guía](./README.md)
