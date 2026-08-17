<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Livraison;

class LivraisonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $livraisons = Livraison::with('commande.client')->latest()->get();
        } elseif ($user->role === 'agriculteur') {
            $agriculteur = $user->agriculteur;
            $livraisons = Livraison::whereHas('commande.lignesCommande.produit', function ($q) use ($agriculteur) {
                $q->where('agriculteur_id', $agriculteur->id);
            })->with(['commande.lignesCommande' => function ($q) use ($agriculteur) {
                $q->whereHas('produit', function ($qp) use ($agriculteur) {
                    $qp->where('agriculteur_id', $agriculteur->id);
                })->with('produit');
            }, 'commande.client'])->latest()->get();
        } elseif ($user->role === 'livreur') {
            $livraisons = Livraison::where(function ($q) use ($user) {
                $q->where('livreur_id', $user->id)
                  ->orWhereNull('livreur_id');
            })->with(['commande.client', 'commande.lignesCommande.produit.agriculteur.user'])->latest()->get();
        } else {
            // Client
            $livraisons = Livraison::whereHas('commande', function ($q) use ($user) {
                $q->where('client_id', $user->id);
            })->with('commande.lignesCommande.produit')->latest()->get();
        }

        return response()->json($livraisons);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'status'     => 'sometimes|string|in:en_attente,preparation,expediee,en_cours,livree,annulee',
            'livreur_id' => 'nullable|exists:users,id',
        ]);

        $livraison = Livraison::findOrFail($id);
        $user = $request->user();

        if ($request->input('action') === 'refuser' || $request->input('status') === 'refusee') {
            $livraison->livreur_id = null;
            $livraison->status = 'en_attente';
            $livraison->save();

            if ($livraison->commande) {
                $livraison->commande->statut = 'preparation';
                $livraison->commande->save();
            }

            return response()->json($livraison);
        }

        if ($request->has('status')) {
            $livraison->status = $request->status;
        }

        if ($request->has('livreur_id')) {
            $livraison->livreur_id = $request->livreur_id;
        } elseif ($user && $user->role === 'livreur' && !$livraison->livreur_id) {
            $livraison->livreur_id = $user->id;
        }

        $livraison->save();

        // Synchroniser le statut de la commande
        if ($request->has('status')) {
            $commande = $livraison->commande;
            if ($commande) {
                $commande->statut = $request->status;
                $commande->save();
            }
        }

        return response()->json($livraison->load(['commande.client', 'livreur']));
    }

    /**
     * Obtenir le profil livreur avec immatriculation et disponibilité
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $livreur = \App\Models\Livreur::firstOrCreate(
            ['user_id' => $user->id],
            [
                'immatriculation_vehicule' => 'DK-8921-AB',
                'est_valid'                => true,
                'est_dispo'                => true,
            ]
        );

        return response()->json($livreur);
    }

    /**
     * Mettre à jour l immatriculation du véhicule ou la disponibilité
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $livreur = \App\Models\Livreur::firstOrCreate(
            ['user_id' => $user->id],
            [
                'immatriculation_vehicule' => 'DK-8921-AB',
                'est_valid'                => true,
                'est_dispo'                => true,
            ]
        );

        $livreur->update($request->only(['immatriculation_vehicule', 'est_dispo']));

        return response()->json($livreur);
    }
}
