<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // -----------------------------
        // Permisos modernos por módulo
        // -----------------------------
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
        ];

        // -----------------------------
        // Permisos "legado" (tal cual están en tus blades/menú)
        // -----------------------------
        $legacy = [
            'manage users',
            'edit profile',
            'partners_index',
            'permisos',
            'roles',
            'actividad',
            'ciudades',
            'almacenes',
            'categorias internas',
            'asignar categoria',
        ];

        foreach (array_merge($modern, $legacy) as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // -----------------------------
        // Roles
        // -----------------------------
        $roleNames = [
            'super admin',
            'admin',
            'user',
            'Proveedor Administrador',
            'Asociado Administrador',
            'Mixto Administrador',
            'Asociado Vendedor',
            'Mixto Vendedor',
        ];
        foreach ($roleNames as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        // -----------------------------
        // Permisos por rol (idempotente)
        // -----------------------------

        // super admin: TODO (modern + legacy)
        Role::where('name', 'super admin')->first()
            ?->syncPermissions(array_merge($modern, $legacy));

        // admin: casi todo menos gestión de roles/permisos/actividad
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
            ],
            // legado necesario para que el menú actual no cambie
            [
                'manage users',
                'edit profile',
                'partners_index',
                'ciudades',
                'almacenes',
                'categorias internas',
                'asignar categoria',
                // no añadimos: 'permisos', 'roles', 'actividad'
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
                'warehouses.view',   // ver/gestionar si lo deseas
                'cities.view',
            ],
            [
                'edit profile',
                'almacenes','ciudades','categorias internas',
            ]
        );
        Role::where('name', 'Proveedor Administrador')->first()?->syncPermissions($provAdmin);

        // Asociado Administrador
        $asocAdmin = array_merge(
            [
                'dashboard.view',
                'catalog.view',
                'products.view','products.manage',
                'product_categories.view',
            ],
            ['edit profile']
        );
        Role::where('name', 'Asociado Administrador')->first()?->syncPermissions($asocAdmin);

        // Mixto Administrador = proveedor + asociado
        $mixtoAdmin = array_unique(array_merge($provAdmin, $asocAdmin));
        Role::where('name', 'Mixto Administrador')->first()?->syncPermissions($mixtoAdmin);

        // Asociado Vendedor (básico para navegar catálogo)
        $asocVend = [
            'dashboard.view',
            'catalog.view',
            'products.view',
            'edit profile',
        ];
        Role::where('name', 'Asociado Vendedor')->first()?->syncPermissions($asocVend);

        // Mixto Vendedor (igual que asociado vendedor)
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
