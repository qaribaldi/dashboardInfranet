<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // panggil PermissionsSeeder
        $this->call(PermissionsSeeder::class);

        // kalau ada seeder lain, daftarin juga di sini
        // $this->call(UserSeeder::class);
    }
}
