<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Produit;
use App\Models\Agriculteur;
use App\Models\Categorie;

class ProduitSeeder extends Seeder
{
    public function run(): void
    {
        $legumes   = Categorie::where('nom', 'Légumes')->first()->id;
        $fruits    = Categorie::where('nom', 'Fruits')->first()->id;
        $cereales  = Categorie::where('nom', 'Céréales')->first()->id;
        $tubercules= Categorie::where('nom', 'Tubercules')->first()->id;
        $laitiers  = Categorie::where('nom', 'Produits laitiers')->first()->id;

        $agri1 = Agriculteur::first()->id;
        $agri2 = Agriculteur::skip(1)->first()->id;
        $agri3 = Agriculteur::skip(2)->first()->id;
        $agri4 = Agriculteur::skip(3)->first()->id;
        $agri5 = Agriculteur::skip(4)->first()->id;

        $produits = [
            ['agriculteur_id'=>$agri1,'categorie_id'=>$legumes,  'nom'=>'Carottes fraîches', 'prix'=>500,  'stock'=>45,  'unite'=>'kg',    'photo'=>'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=400'],
            ['agriculteur_id'=>$agri2,'categorie_id'=>$legumes,  'nom'=>'Oignons violets',   'prix'=>350,  'stock'=>12,  'unite'=>'kg',    'photo'=>'https://images.unsplash.com/photo-1618512496248-a07fe83aa8cb?w=400'],
            ['agriculteur_id'=>$agri3,'categorie_id'=>$fruits,   'nom'=>'Tomates cerises',   'prix'=>800,  'stock'=>20,  'unite'=>'kg',    'photo'=>'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=400'],
            ['agriculteur_id'=>$agri4,'categorie_id'=>$legumes,  'nom'=>'Laitue verte',      'prix'=>250,  'stock'=>30,  'unite'=>'pièce', 'photo'=>'https://images.unsplash.com/photo-1622206151226-18ca2c9ab4a1?w=400'],
            ['agriculteur_id'=>$agri5,'categorie_id'=>$fruits,   'nom'=>'Mangues Kent',      'prix'=>1200, 'stock'=>60,  'unite'=>'kg',    'photo'=>'https://images.unsplash.com/photo-1553279768-865429fa0078?w=400'],
            ['agriculteur_id'=>$agri1,'categorie_id'=>$cereales, 'nom'=>'Maïs local',        'prix'=>300,  'stock'=>100, 'unite'=>'kg',    'photo'=>'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=400'],
            ['agriculteur_id'=>$agri2,'categorie_id'=>$legumes,  'nom'=>'Poivrons verts',    'prix'=>600,  'stock'=>25,  'unite'=>'kg',    'photo'=>'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?w=400'],
            ['agriculteur_id'=>$agri3,'categorie_id'=>$tubercules,'nom'=>'Patates douces',   'prix'=>400,  'stock'=>80,  'unite'=>'kg',    'photo'=>'assets/patates_douces.jpg'],
            ['agriculteur_id'=>$agri4,'categorie_id'=>$legumes,  'nom'=>'Aubergines',        'prix'=>450,  'stock'=>35,  'unite'=>'kg',    'photo'=>'assets/aubergines.jpg'],
            ['agriculteur_id'=>$agri5,'categorie_id'=>$fruits,   'nom'=>'Pastèques',         'prix'=>800,  'stock'=>15,  'unite'=>'pièce', 'photo'=>'https://images.unsplash.com/photo-1563114773-84221bd62daa?w=400'],
            ['agriculteur_id'=>$agri1,'categorie_id'=>$legumes,  'nom'=>'Gombo frais',       'prix'=>300,  'stock'=>40,  'unite'=>'kg',    'photo'=>'assets/gombo.jpg'],
            ['agriculteur_id'=>$agri2,'categorie_id'=>$cereales, 'nom'=>'Mil local',         'prix'=>250,  'stock'=>200, 'unite'=>'kg',    'photo'=>'assets/mil.jpg'],
            ['agriculteur_id'=>$agri3,'categorie_id'=>$legumes,  'nom'=>'Poireaux frais',    'prix'=>400,  'stock'=>25,  'unite'=>'botte', 'photo'=>'assets/poireaux.jpg'],
            ['agriculteur_id'=>$agri4,'categorie_id'=>$fruits,   'nom'=>'Bananes plantains', 'prix'=>600,  'stock'=>18,  'unite'=>'régime','photo'=>'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=400'],
            ['agriculteur_id'=>$agri5,'categorie_id'=>$cereales, 'nom'=>'Arachides grillées','prix'=>700,  'stock'=>150, 'unite'=>'kg',    'photo'=>'assets/arachides.jpg'],
            ['agriculteur_id'=>$agri1,'categorie_id'=>$legumes,  'nom'=>'Piment rouge',      'prix'=>500,  'stock'=>30,  'unite'=>'kg',    'photo'=>'assets/piment.jpg'],
            ['agriculteur_id'=>$agri2,'categorie_id'=>$tubercules,'nom'=>'Ignames',          'prix'=>550,  'stock'=>60,  'unite'=>'kg',    'photo'=>'assets/ignames.jpg'],
            ['agriculteur_id'=>$agri3,'categorie_id'=>$legumes,  'nom'=>'Concombres',        'prix'=>300,  'stock'=>40,  'unite'=>'kg',    'photo'=>'https://images.unsplash.com/photo-1449300079323-02e209d9d3a6?w=400'],
            ['agriculteur_id'=>$agri4,'categorie_id'=>$laitiers, 'nom'=>'Lait frais local',  'prix'=>800,  'stock'=>20,  'unite'=>'litre', 'photo'=>'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=400'],
            ['agriculteur_id'=>$agri5,'categorie_id'=>$laitiers, 'nom'=>'Yaourt naturel',    'prix'=>500,  'stock'=>15,  'unite'=>'pot',   'photo'=>'https://images.unsplash.com/photo-1571212515416-fef01fc43637?w=400'],
            ['agriculteur_id'=>$agri2,'categorie_id'=>$cereales, 'nom'=>'Sorgho local',      'prix'=>280,  'stock'=>200, 'unite'=>'kg',    'photo'=>'assets/sorgho.jpg'],
            ['agriculteur_id'=>$agri2,'categorie_id'=>$legumes,  'nom'=>'Chou',              'prix'=>300,  'stock'=>70,  'unite'=>'kg',    'photo'=>'assets/chou.jpg'],
        ];

        foreach ($produits as $produit) {
            Produit::create($produit);
        }
    }
}