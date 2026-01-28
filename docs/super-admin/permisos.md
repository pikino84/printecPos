# Permisos

Los permisos son las acciones específicas que pueden realizarse en el sistema. Se asignan a los roles.

## Acceder al Módulo

1. En el menú lateral, haz clic en **Administración**
2. Selecciona **Permisos**

## Lista de Permisos

La vista muestra todos los permisos disponibles en el sistema, organizados por categoría.

## Crear Nuevo Permiso

1. Haz clic en **Nuevo Permiso**
2. Ingresa el **Nombre** del permiso (formato recomendado: `modulo.accion`)
3. Haz clic en **Guardar**

### Convención de Nombres

Se recomienda usar el formato `modulo.accion`:

```
partners.view        → Ver partners
partners.manage      → Gestionar partners
users.view           → Ver usuarios
users.manage         → Gestionar usuarios
```

## Editar Permiso

1. Haz clic en **Editar** junto al permiso
2. Modifica el nombre
3. Haz clic en **Actualizar**

> **Precaución**: Cambiar el nombre de un permiso puede afectar el funcionamiento si el código depende de ese nombre exacto.

## Eliminar Permiso

1. Haz clic en **Eliminar**
2. Confirma la acción

> **Advertencia**: Solo elimina permisos que no estén en uso. Eliminar un permiso activo puede causar errores en el sistema.

## Categorías de Permisos

### Dashboard
- `dashboard.view` - Acceso al dashboard principal

### Catálogo
- `catalog.view` - Ver catálogo de productos

### Partners y Usuarios
- `partners.view` / `partners.manage`
- `associates.view` / `associates.manage`
- `users.view` / `users.manage`

### Roles y Permisos
- `roles.view` / `roles.manage`
- `permissions.view` / `permissions.manage`

### Productos
- `products.view` / `products.manage`
- `product_categories.view` / `product_categories.manage`
- `view-own-products` / `manage-own-products`

### Almacenes y Ciudades
- `warehouses.view` / `warehouses.manage`
- `cities.view` / `cities.manage`

### Pricing
- `pricing-dashboard.view`
- `pricing-tiers.view` / `pricing-tiers.manage`
- `partner-pricing.view` / `partner-pricing.manage`
- `pricing-reports.view`
- `pricing-settings.view` / `pricing-settings.manage`

### Clientes y Cotizaciones
- `clients.view` / `clients.manage`
- `quotes.view` / `quotes.manage`

### Distribuidor
- `razones-sociales.view` / `razones-sociales.manage`
- `cuentas-bancarias.view` / `cuentas-bancarias.manage`

### Otros
- `activity-logs.view` - Ver historial de actividad
- `printec_categories.view` / `printec_categories.manage`

## Tips

- Los permisos `.view` permiten solo lectura
- Los permisos `.manage` incluyen crear, editar y eliminar
- Algunos módulos verifican permisos legacy (ej: `manage users`, `almacenes`)
- Crea permisos nuevos solo cuando sea necesario para funcionalidad específica

---

[← Volver a la guía](./README.md)
