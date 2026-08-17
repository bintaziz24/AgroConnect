<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produit;

class LinkFermesSeeder extends Seeder
{
    public function run(): void
    {
        $produits = Produit::with('agriculteur.fermes')->get();

        foreach ($produits as $p) {
            if (!$p->ferme_id && $p->agriculteur && $p->agriculteur->fermes->first()) {
                $p->ferme_id = $p->agriculteur->fermes->first()->id;
                $p->save();
            }
        }
    }
}
