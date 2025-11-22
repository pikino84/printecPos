<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PartnerSeeder::class,
            RolePermissionSeeder::class,
            NavigationRolesPermissionsSeeder::class,
            AcquisitionChannelSeeder::class,
            UserSeeder::class,
            AssignRolesToExistingUsersSeeder::class,
            PrintecCategorySeeder::class,
            ProductWarehouseCitiesSeeder::class,
            ProductWarehouseSeeder::class,
            ProductWarehouseDobleVelaSeeder::class,
            ProductWarehouseFourPromotionalSeeder::class,
            ProductWarehouseCitySeeder::class,
            DobleVelaSeeder::class,
            OwnProductsExampleSeeder::class,
            /* FourPromotionalSeeder::class,
            ProductWarehouseInnovationSeeder::class,*/
            /*InnovationSeeder::class,*/
        ]);
    }
}