<?php

namespace Database\Seeders;

use Encore\Admin\Auth\Database\AdminTablesSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminTablesSeeder::class);
    }
}
