<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Agriculteur;
use App\Models\Produit;
use App\Models\Commande;

class AdminController extends Controller
{
    /**
     * Get list of all users with agriculteur profiles.
     */
    public function utilisateurs(Request $request)
    {
        $users = User::with('agriculteur')->latest()->get()->unique('id')->values();
        return response()->json($users);
    }

    /**
     * Get global platform statistics.
     */
    public function statistiques(Request $request)
    {
        $totalUsers = User::count();
        $totalClients = User::where('role', 'client')->count();
        $totalAgriculteurs = User::where('role', 'agriculteur')->count();
        $totalLivreurs = User::where('role', 'livreur')->count();
        $totalProduits = Produit::count();
        $totalCommandes = Commande::count();
        $totalRevenus = floatval(Commande::where('statut', '!=', 'annulee')->sum('montant_total'));
        $commissions = floatval(round($totalRevenus * 0.05, 2));

        $pendingAgriCount = Agriculteur::where('statut_validation', 'en_attente')->count();
        $pendingUserCount = User::where('statut_validation', 'en_attente')->count();
        $validationsEnAttente = max($pendingAgriCount, $pendingUserCount);

        return response()->json([
            'utilisateurs'           => $totalUsers,
            'clients'                => $totalClients,
            'agriculteurs'           => $totalAgriculteurs,
            'livreurs'               => $totalLivreurs,
            'produits'               => $totalProduits,
            'commandes'              => $totalCommandes,
            'revenus'                => $totalRevenus,
            'commissions'            => $commissions,
            'validations_en_attente' => $validationsEnAttente,
        ]);
    }

    /**
     * Validate or reject an agricultural producer or delivery driver profile.
     */
    public function validerAgriculteur(Request $request, string $id)
    {
        $statut = $request->input('statut', 'validé');

        $user = User::find($id);
        $agriculteur = Agriculteur::where('id', $id)->orWhere('user_id', $id)->first();

        if (!$user && $agriculteur) {
            $user = $agriculteur->user;
        }

        if ($user) {
            $user->statut_validation = $statut;
            $user->save();
        }

        if ($agriculteur) {
            $agriculteur->statut_validation = $statut;
            $agriculteur->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Statut utilisateur mis à jour avec succès.',
            'statut'  => $statut,
        ]);
    }

    /**
     * Get all products for product moderation.
     */
    public function produits(Request $request)
    {
        $produits = Produit::with(['agriculteur.user', 'agriculteur.fermes', 'categorie', 'ferme'])
            ->latest()
            ->get()
            ->unique(function ($p) {
                return strtolower(trim($p->nom)) . '-' . $p->agriculteur_id;
            })
            ->values();

        return response()->json($produits);
    }

    /**
     * Moderation action: Delete product listing.
     */
    public function supprimerProduit(Request $request, string $id)
    {
        $produit = Produit::find($id);
        if (!$produit) {
            return response()->json(['message' => 'Produit introuvable.'], 404);
        }

        try {
            $produit->panierItems()->delete();
            $produit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produit supprimé avec succès par la modération admin.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer le produit : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent platform orders for dashboard activities.
     */
    public function commandes(Request $request)
    {
        $commandes = Commande::with(['client'])->latest()->take(10)->get()->unique('id')->values();
        return response()->json($commandes);
    }
}
