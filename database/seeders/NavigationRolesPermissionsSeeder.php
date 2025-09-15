<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class NavigationRolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // -----------------------------
        // 1) Permisos usados en tu menú
        // -----------------------------

        // a) Permisos "limpios" por módulo (recomendados a futuro)
        $modern = [
            'dashboard.view',

            'catalog.view',

            'partners.view', 'partners.manage',
            'associates.view', 'associates.manage',

            'users.view', 'users.manage',
            'roles.view', 'roles.manage',
            'permissions.view', 'permissions.manage',

            'products.view', 'products.manage',
            'product_categories.view', 'product_categories.manage',

            'warehouses.view', 'warehouses.manage',
            'cities.view', 'cities.manage',

            'printec_categories.view', 'printec_categories.manage',
            'view-own-products', 
            'manage-own-products',
        ];

        // b) Permisos "legado" (tal cual los espera tu menú actual)
        $legacy = [
            'manage users',          // legado
            'edit profile',          // legado
            'partners_index',        // listado partners
            'permisos',              // pantalla permisos
            'roles',                 // pantalla roles
            'actividad',             // activity log
            'ciudades',              // vista ciudades
            'almacenes',             // vista almacenes
            'categorias internas',   // vista categorías internas
            'asignar categoria',     // asignación categoría interna
        ];

        // Crea todos los permisos (idempotente)
        foreach (array_merge($modern, $legacy) as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // -----------------------------
        // 2) Roles
        // -----------------------------
        $roles = [
            'super admin',
            'admin',
            'user',
            'Proveedor Administrador',
            'Asociado Administrador',
            'Mixto Administrador',
            'Asociado Vendedor',
            'Mixto Vendedor',
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        // -----------------------------
        // 3) Asignación de permisos por rol
        //    (pensado para que el menú actual funcione ya)
        // -----------------------------

        // super admin: TODO (modern + legacy)
        Role::where('name', 'super admin')->first()
            ?->syncPermissions(array_merge($modern, $legacy));

        // admin: casi todo menos gestión de roles/permisos
        $adminPerms = array_merge(
            [
                'dashboard.view',
                'catalog.view',
                'partners.view','partners.manage',
                'associates.view','associates.manage',
                'users.view','users.manage',
                'products.view','products.manage',
                'product_categories.view','product_categories.manage',
                'warehouses.view','warehouses.manage',
                'cities.view','cities.manage',
                'printec_categories.view','printec_categories.manage',
                'view-own-products','manage-own-products',
            ],
            // legado necesario para que el menú actual no cambie
            [
                'manage users', 'edit profile', 'partners_index',
                'ciudades', 'almacenes',
                'categorias internas', 'asignar categoria',
                // OJO: no incluimos 'permisos', 'roles', 'actividad' en admin
            ]
        );
        Role::where('name', 'admin')->first()?->syncPermissions($adminPerms);

        // Proveedor Administrador
        $provAdmin = array_merge(
            [
                'dashboard.view',
                'catalog.view',
                'products.view','products.manage',
                'product_categories.view','product_categories.manage',
                'warehouses.view',   // ver almacenes
                'cities.view',       // ver ciudades
            ],
            // legado para menú
            [
                'edit profile',
                'almacenes', 'ciudades',
                // si usas categorías internas de Printec para mostrar algo:
                'categorias internas',
            ]
        );
        Role::where('name', 'Proveedor Administrador')->first()?->syncPermissions($provAdmin);

        // Asociado Administrador
        $asocAdmin = array_merge(
            [
                'dashboard.view',
                'catalog.view',
                'products.view','products.manage',
                'product_categories.view', // ver categorías proveedor
                'view-own-products','manage-own-products', // AGREGAR ESTA LÍNEA
            ],
            ['edit profile']
        );
        Role::where('name', 'Asociado Administrador')->first()?->syncPermissions($asocAdmin);

        // Mixto Administrador (proveedor + asociado)
        $mixtoAdmin = array_unique(array_merge($provAdmin, $asocAdmin));
        Role::where('name', 'Mixto Administrador')->first()?->syncPermissions($mixtoAdmin);

        // Asociado Vendedor (solo navegación básica)
        $asocVend = [
            'dashboard.view',
            'catalog.view',
            'products.view',
            'view-own-products',
            'edit profile',
        ];
        Role::where('name', 'Asociado Vendedor')->first()?->syncPermissions($asocVend);

        // Mixto Vendedor (igual que asociado vendedor, puedes ampliar si quieres)
        Role::where('name', 'Mixto Vendedor')->first()?->syncPermissions($asocVend);

        // user (básico)
        Role::where('name', 'user')->first()?->syncPermissions([
            'dashboard.view',
            'catalog.view',
            'products.view',
            'edit profile',
        ]);
    }
}
