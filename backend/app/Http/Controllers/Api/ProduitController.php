<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use App\Models\Categorie;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    public function index(Request $request)
    {
        $query = Produit::with(['agriculteur.user', 'agriculteur.fermes', 'categorie', 'ferme'])
                        ->where('stock', '>', 0);

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('categorie')) {
            $query->where('categorie_id', $request->categorie);
        }

        if ($request->filled('prix_max')) {
            $query->where('prix', '<=', $request->prix_max);
        }

        $produits = $query->latest()->paginate(12);

        return response()->json($produits);
    }

    public function show($id)
    {
        $produit = Produit::with(['agriculteur.user', 'agriculteur.fermes', 'categorie', 'ferme'])->findOrFail($id);
        return response()->json($produit);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $statut = $user ? ($user->statut_validation ?? ($user->agriculteur->statut_validation ?? 'en_attente')) : 'en_attente';
        if (in_array($statut, ['rejeté', 'refusé', 'suspendu', 'en_attente'])) {
            $msg = $statut === 'en_attente'
                ? 'Votre compte agriculteur est en cours de vérification par l\'administration AgroConnect. Vous pourrez ajouter des produits dès que votre compte sera validé.'
                : 'Votre compte agriculteur a été rejeté ou suspendu par l\'administration.';
            return response()->json(['message' => $msg], 403);
        }

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE produits ALTER COLUMN photo TYPE TEXT;");
        } catch (\Throwable $e) {}

        $request->validate([
            'nom'          => 'required|string',
            'prix'         => 'required|numeric',
            'stock'        => 'required|integer',
            'categorie_id' => 'required|exists:categories,id',
            'unite'        => 'required|string',
        ]);

        $agriculteur = auth()->user()->agriculteur;
        if (!$agriculteur) {
            $agriculteur = \App\Models\Agriculteur::create([
                'user_id'           => auth()->id(),
                'localisation'      => 'Sénégal',
                'statut_validation' => auth()->user()->statut_validation ?? 'en_attente',
            ]);
        }

        $photo = $request->photo;
        if (!$photo) {
            $photo = 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&h=200&fit=crop';
        } elseif (str_contains(strtolower($request->nom), 'bissap') && str_starts_with($photo, 'data:image')) {
            $photo = '/assets/illustrations/bissap.png';
        } elseif (str_contains(strtolower($request->nom), 'arachide') && str_starts_with($photo, 'data:image')) {
            $photo = '/assets/illustrations/arachides.svg';
        }

        $produit = Produit::create([
            'agriculteur_id' => $agriculteur->id,
            'categorie_id'   => $request->categorie_id,
            'nom'            => $request->nom,
            'description'    => $request->description,
            'prix'           => $request->prix,
            'stock'          => $request->stock,
            'unite'          => $request->unite,
            'photo'          => $photo,
        ]);

        return response()->json($produit->load(['categorie', 'agriculteur.user']), 201);
    }

    public function update(Request $request, $id)
    {
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE produits ALTER COLUMN photo TYPE TEXT;");
        } catch (\Throwable $e) {}

        $produit = Produit::findOrFail($id);
        $data = $request->all();
        if (isset($data['photo']) && str_starts_with($data['photo'], 'data:image')) {
            if (str_contains(strtolower($produit->nom), 'bissap')) {
                $data['photo'] = '/assets/illustrations/bissap.png';
            } elseif (str_contains(strtolower($produit->nom), 'arachide')) {
                $data['photo'] = '/assets/illustrations/arachides.svg';
            }
        }
        $produit->update($data);
        return response()->json($produit);
    }

    public function destroy($id)
    {
        Produit::findOrFail($id)->delete();
        return response()->json(['message' => 'Produit supprimé']);
    }

    public function dashboard()
    {
        $user = auth()->user();
        $agriculteur = $user ? $user->agriculteur : null;
        
        if (!$agriculteur && $user && $user->role === 'agriculteur') {
            $agriculteur = \App\Models\Agriculteur::create([
                'user_id'           => $user->id,
                'localisation'      => 'Sénégal',
                'statut_validation' => $user->statut_validation ?? 'en_attente',
            ]);
        }

        if (!$agriculteur) {
            return response()->json([
                'commandes'           => 0,
                'revenus'             => 0,
                'produits'            => 0,
                'dernieres_commandes' => [],
                'mes_produits'        => [],
                'statut_validation'   => $user->statut_validation ?? 'en_attente',
            ]);
        }

        // Nombre total de commandes contenant ses produits
        $totalCommandes = \App\Models\Commande::whereHas('lignesCommande.produit', function($q) use ($agriculteur) {
            $q->where('agriculteur_id', $agriculteur->id);
        })->count();

        // Revenu exact (somme de quantite * prix_unitaire de ses produits uniquement)
        $revenus = \App\Models\LigneCommande::whereHas('produit', function ($q) use ($agriculteur) {
            $q->where('agriculteur_id', $agriculteur->id);
        })->whereHas('commande', function ($q) {
            $q->where('statut', '!=', 'annulee');
        })->selectRaw('SUM(quantite * prix_unitaire) as total')->value('total') ?? 0;

        // Les 5 dernières commandes contenant ses produits
        $dernieresCommandes = \App\Models\Commande::whereHas('lignesCommande.produit', function($q) use ($agriculteur) {
            $q->where('agriculteur_id', $agriculteur->id);
        })->with(['lignesCommande' => function ($q) use ($agriculteur) {
            $q->whereHas('produit', function ($qp) use ($agriculteur) {
                $qp->where('agriculteur_id', $agriculteur->id);
            })->with('produit');
        }, 'client', 'livraison'])->latest()->take(5)->get();

        $mesProduits = Produit::where('agriculteur_id', $agriculteur->id)
            ->with('categorie')
            ->latest()
            ->get()
            ->unique(function ($p) {
                return strtolower(trim($p->nom));
            })
            ->values();

        return response()->json([
            'commandes'           => $totalCommandes,
            'revenus'             => floatval($revenus),
            'produits'            => count($mesProduits),
            'dernieres_commandes' => $dernieresCommandes,
            'mes_produits'        => $mesProduits,
            'statut_validation'   => $user->statut_validation ?? ($agriculteur->statut_validation ?? 'en_attente'),
        ]);
    }

    public function agriculteurs()
    {
        $agriculteurs = \App\Models\Agriculteur::with('user')
                        ->where('statut_validation', 'validé')
                        ->get();
        return response()->json($agriculteurs);
    }
}