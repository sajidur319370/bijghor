<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * No demo users here: the local DB is exported to the live site on Day 5.
     * Create the admin with `php artisan make:filament-user` (Day 4).
     */
    public function run(): void
    {
        $this->call(CatalogSeeder::class);
    }
}
