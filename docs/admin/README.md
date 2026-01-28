# Guía del Administrador

Como Administrador, tienes acceso a la mayoría de funciones de PrintecPOS, exceptuando la gestión de roles, permisos y configuración de pricing.

## Módulos Disponibles

### Administración
- [Partners](./partners.md) - Gestión de socios comerciales
- [Usuarios](./usuarios.md) - Administración de usuarios
- [Historial de Actividad](./actividad.md) - Registro de acciones
- [Clientes](./clientes.md) - Gestión de clientes finales
- [Ciudades](./ciudades.md) - Configuración de ciudades
- [Almacenes](./almacenes.md) - Gestión de almacenes

### Distribuidor
- [Razones Sociales](./razones-sociales.md) - Entidades fiscales
- [Cuentas Bancarias](./cuentas-bancarias.md) - Información bancaria

### Productos
- [Categorías Internas](./categorias-internas.md) - Organización de productos
- [Asignar Categorías](./asignar-categorias.md) - Mapeo de categorías
- [Productos Propios](./productos-propios.md) - Productos personalizados
- [Catálogo](./catalogo.md) - Navegación del catálogo
- [Cotizaciones](./cotizaciones.md) - Gestión de cotizaciones

## Accesos Rápidos

| Acción | Ruta |
|--------|------|
| Ver todos los partners | `/partners` |
| Crear nuevo usuario | `/users/create` |
| Ver clientes | `/clients` |
| Ver actividad | `/activity-logs` |

## Limitaciones

Como Admin, **no tienes acceso** a:
- Gestión de Roles (`/roles`)
- Gestión de Permisos (`/permissions`)
- Configuración de Pricing (`/pricing-settings`)

Estas funciones están reservadas para el Super Administrador.

## Tips

- Coordina con el Super Admin para cambios de roles o permisos
- Revisa el historial de actividad regularmente
- Mantén actualizados los datos de partners y usuarios

---

[← Volver al índice](../README.md)
