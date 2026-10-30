<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Store defaults only — safe on a live database. For a sample
     * catalogue run: php artisan db:seed --class=DemoCatalogSeeder
     * Create the first admin with: php artisan store:create-admin
     */
    public function run(): void
    {
        $this->call(StoreSetupSeeder::class);
    }
}
