# Roles

Los roles definen el conjunto de permisos que tiene cada tipo de usuario en el sistema.

## Acceder al Módulo

1. En el menú lateral, haz clic en **Administración**
2. Selecciona **Roles**

## Lista de Roles

La tabla muestra todos los roles configurados:

| Columna | Descripción |
|---------|-------------|
| Nombre | Identificador del rol |
| Permisos | Lista de permisos asignados (máx. 5 visibles) |
| Acciones | Editar, Eliminar |

## Roles Predefinidos

| Rol | Propósito |
|-----|-----------|
| super admin | Control total del sistema |
| admin | Administración general |
| Proveedor Administrador | Gestión de productos como proveedor |
| Asociado Administrador | Gestión de organización distribuidora |
| Mixto Administrador | Combina proveedor + asociado |
| Asociado Vendedor | Funciones de venta |
| Mixto Vendedor | Vendedor con acceso mixto |
| user | Usuario básico |

## Crear Nuevo Rol

1. Haz clic en **Nuevo Rol**
2. Ingresa el **Nombre** del rol
3. Selecciona los **Permisos** que tendrá
4. Haz clic en **Guardar**

## Editar Rol

1. Haz clic en **Editar** junto al rol
2. Modifica el nombre o los permisos
3. Haz clic en **Actualizar**

## Eliminar Rol

1. Haz clic en **Eliminar**
2. Confirma la acción

> **Advertencia**: No elimines roles que estén asignados a usuarios activos. Primero reasígnalos a otro rol.

## Permisos Comunes

### Administración
- `partners.view` / `partners.manage` - Ver/gestionar partners
- `users.view` / `users.manage` - Ver/gestionar usuarios
- `roles.view` / `roles.manage` - Ver/gestionar roles
- `activity-logs.view` - Ver historial de actividad

### Pricing
- `pricing-dashboard.view` - Ver dashboard de pricing
- `pricing-tiers.view` / `pricing-tiers.manage` - Niveles de precio
- `pricing-settings.view` / `pricing-settings.manage` - Configuración (solo super admin)

### Productos
- `catalog.view` - Ver catálogo
- `products.view` / `products.manage` - Gestionar productos
- `quotes.view` / `quotes.manage` - Cotizaciones

### Distribuidor
- `razones-sociales.view` / `razones-sociales.manage` - Razones sociales
- `cuentas-bancarias.view` / `cuentas-bancarias.manage` - Cuentas bancarias

## Tips

- Sigue el principio de **mínimo privilegio**: asigna solo los permisos necesarios
- Revisa periódicamente los permisos asignados a cada rol
- Los cambios en roles afectan inmediatamente a todos los usuarios con ese rol

---

[← Volver a la guía](./README.md)
