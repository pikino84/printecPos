<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RolePermissionSeeder::class,
            ModelHasRolesSeeder::class,
            ProductProviderSeeder::class,
            PrintecCategorySeeder::class,
            ProductWarehouseSeeder::class,
            /*DobleVelaSeeder::class,
            InnovationSeeder::class,*/

        ]);
    }
}