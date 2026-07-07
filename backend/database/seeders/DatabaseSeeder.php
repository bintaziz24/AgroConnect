<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Categorie;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nom'         => 'Légumes',
                'emoji'       => '🥕',
                'image'       => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=400',
                'description' => 'Légumes frais cultivés localement',
            ],
            [
                'nom'         => 'Fruits',
                'emoji'       => '🍅',
                'image'       => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=400',
                'description' => 'Fruits frais de saison',
            ],
            [
                'nom'         => 'Céréales',
                'emoji'       => '🌾',
                'image'       => 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=400',
                'description' => 'Céréales et graines locales',
            ],
            [
                'nom'         => 'Tubercules',
                'emoji'       => '🍠',
                'image'       => 'https://images.unspljash.com/photo-1508313880080-c4bef0730395?w=400',
                'description' => 'Tubercules et racines',
            ],
            [
                'nom'         => 'Produits laitiers',
                'emoji'       => '🥛',
                'image'       => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=400',
                'description' => 'Lait, yaourt et fromage locaux',
            ],
        ];

        foreach ($categories as $cat) {
            Categorie::create($cat);
        }
    }
}