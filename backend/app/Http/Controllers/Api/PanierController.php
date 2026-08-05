<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PanierItem;

class PanierController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->panierItems()->with('produit.agriculteur')->get();
        return response()->json($items);
    }

    public function ajouter(Request $request)
    {
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'quantite'   => 'integer|min:1',
        ]);

        $user = $request->user();
        $produitId = $request->produit_id;
        $quantite = $request->input('quantite', 1);

        $item = $user->panierItems()->where('produit_id', $produitId)->first();

        if ($item) {
            $item->quantite += $quantite;
            $item->save();
        } else {
            $item = $user->panierItems()->create([
                'produit_id' => $produitId,
                'quantite'   => $quantite,
            ]);
        }

        return response()->json([
            'success' => true,
            'item'    => $item->load('produit.agriculteur')
        ], 200);
    }

    public function diminuer(Request $request)
    {
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'quantite'   => 'integer|min:1',
        ]);

        $user = $request->user();
        $produitId = $request->produit_id;
        $quantite = $request->input('quantite', 1);

        $item = $user->panierItems()->where('produit_id', $produitId)->first();

        if ($item) {
            $item->quantite -= $quantite;
            if ($item->quantite <= 0) {
                $item->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Produit retiré du panier'
                ], 200);
            }
            $item->save();
        }

        return response()->json([
            'success' => true,
            'item'    => $item ? $item->load('produit.agriculteur') : null
        ], 200);
    }

    public function supprimer(Request $request, $id)
    {
        $request->user()->panierItems()->where('produit_id', $id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Produit supprimé du panier'
        ], 200);
    }
}
