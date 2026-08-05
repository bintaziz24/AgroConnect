<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorieSeeder::class,
            UserSeeder::class,
            AgriculteurSeeder::class,
            ProduitSeeder::class,
        ]);
    }
}