<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Produit;
use App\Models\Agriculteur;
use App\Models\Categorie;

// 1. Update existing photos
$updates = [
    'Patates douces'    => 'assets/patates_douces.jpg',
    'Gombo frais'       => 'assets/gombo.jpg',
    'Mil local'         => 'assets/mil.jpg',
    'Arachides grillées' => 'assets/arachides.jpg',
    'Piment rouge'      => 'assets/piment.jpg',
    'Ignames'           => 'assets/ignames.jpg',
    'Aubergines'        => 'assets/aubergines.jpg',
    'Poireaux frais'    => 'assets/poireaux.jpg',
    'Maïs local'        => 'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=400',
];

echo "Updating product images in database...\n";
foreach ($updates as $name => $url) {
    $count = Produit::where('nom', $name)->update(['photo' => $url]);
    echo "- {$name}: updated {$count} row(s)\n";
}

// 2. Ensure Sorgho local is created and has correct photo
$sorgho = Produit::where('nom', 'Sorgho local')->first();
if (!$sorgho) {
    $agri = Agriculteur::skip(1)->first();
    $cat = Categorie::where('nom', 'Céréales')->first();
    
    if ($agri && $cat) {
        Produit::create([
            'agriculteur_id' => $agri->id,
            'categorie_id'   => $cat->id,
            'nom'            => 'Sorgho local',
            'prix'           => 280,
            'stock'          => 200,
            'unite'          => 'kg',
            'photo'          => 'assets/sorgho.jpg'
        ]);
        echo "Created Sorgho local successfully!\n";
    } else {
        echo "Error: Could not find farmer or cereal category to create Sorgho local.\n";
    }
} else {
    $sorgho->update(['photo' => 'assets/sorgho.jpg']);
    echo "Updated Sorgho local successfully!\n";
}

// 3. Ensure Chou is created and has correct photo
$chou = Produit::where('nom', 'Chou')->first();
if (!$chou) {
    $agri = Agriculteur::skip(1)->first();
    $cat = Categorie::where('nom', 'Légumes')->first();
    
    if ($agri && $cat) {
        Produit::create([
            'agriculteur_id' => $agri->id,
            'categorie_id'   => $cat->id,
            'nom'            => 'Chou',
            'prix'           => 300,
            'stock'          => 70,
            'unite'          => 'kg',
            'photo'          => 'assets/chou.jpg'
        ]);
        echo "Created Chou successfully!\n";
    } else {
        echo "Error: Could not find farmer or legumes category to create Chou.\n";
    }
} else {
    $chou->update([
        'photo' => 'assets/chou.jpg',
        'categorie_id' => Categorie::where('nom', 'Légumes')->first()->id
    ]);
    echo "Updated Chou successfully!\n";
}

echo "Done!\n";
