# Usuarios

Gestiona todos los usuarios que tienen acceso al sistema PrintecPOS.

## Acceder al Módulo

1. En el menú lateral, haz clic en **Administración**
2. Selecciona **Usuarios**

## Lista de Usuarios

La tabla muestra todos los usuarios registrados:

| Columna | Descripción |
|---------|-------------|
| Nombre | Nombre completo del usuario |
| Email | Correo electrónico (usado para login) |
| Partner | Partner al que pertenece |
| Roles | Roles asignados |
| Estado | Activo/Inactivo |
| Acciones | Editar, Eliminar |

## Crear Nuevo Usuario

1. Haz clic en **Nuevo Usuario**
2. Completa el formulario:
   - **Nombre**: Nombre completo
   - **Email**: Correo electrónico (será su usuario de acceso)
   - **Contraseña**: Contraseña inicial
   - **Confirmar contraseña**: Repite la contraseña
   - **Partner**: Selecciona a qué partner pertenece
   - **Roles**: Asigna uno o más roles
3. Haz clic en **Guardar**

## Editar Usuario

1. Haz clic en **Editar** junto al usuario
2. Modifica los campos necesarios
3. Para cambiar la contraseña, completa los campos de contraseña (dejar vacío para mantener la actual)
4. Haz clic en **Actualizar**

## Activar/Desactivar Usuario

- Los usuarios **inactivos** no pueden iniciar sesión
- Para cambiar el estado, edita el usuario y modifica el campo **Estado**

## Eliminar Usuario

1. Haz clic en el botón **Eliminar** (rojo)
2. Confirma la acción en el diálogo

> **Nota**: Eliminar un usuario es permanente. Considera desactivarlo primero si no estás seguro.

## Roles Disponibles

| Rol | Descripción |
|-----|-------------|
| super admin | Acceso total al sistema |
| admin | Administración sin roles/permisos |
| Asociado Administrador | Gestión de su organización |
| Asociado Vendedor | Funciones de venta |

## Tips

- Cada usuario debe pertenecer a un partner
- Un usuario puede tener múltiples roles
- El email debe ser único en todo el sistema
- Si un usuario olvida su contraseña, puedes restablecerla editando su perfil

---

[← Volver a la guía](./README.md)
