<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            /*AsociadoSeeder::class,*/
            UserSeeder::class,
            RolePermissionSeeder::class,
            ModelHasRolesSeeder::class,
            ProductProviderSeeder::class,
            PrintecCategorySeeder::class,
            /*ProductWarehouseSeeder::class,*/
            ProductWarehouseCitiesSeeder::class,
            ProductWarehouseDobleVelaSeeder::class,
            ProductWarehouseInnovationSeeder::class,
            /*DobleVelaSeeder::class,*/
            /*InnovationSeeder::class,
            FourPromotionalSeeder::class,
            ProductKeywordSeeder::class,*/

        ]);
    }
}