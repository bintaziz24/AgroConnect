<?php
namespace App\Http\Controllers;
use App\Models\Produit;

class AccueilController extends Controller
{
    public function index()
    {
        // Temporaire : tableau vide si pas encore de données
        $produits = collect([]);

        // Décommente quand tu auras ta base de données
        // $produits = Produit::with(['agriculteur', 'categorie'])
        //               ->where('stock', '>', 0)
        //               ->latest()
        //               ->take(8)
        //               ->get();

        return view('pages.accueil', compact('produits'));
    }
}