<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Categorie;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nom' => 'Légumes',           'emoji' => '🥕'],
            ['nom' => 'Fruits',            'emoji' => '🍅'],
            ['nom' => 'Céréales',          'emoji' => '🌾'],
            ['nom' => 'Tubercules',        'emoji' => '🍠'],
            ['nom' => 'Produits laitiers', 'emoji' => '🥛'],
        ];
        foreach ($categories as $cat) {
            Categorie::create($cat);
        }
    }
}