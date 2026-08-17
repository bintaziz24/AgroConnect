<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class CommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user->role === 'admin') {
            $commandes = Commande::with(['client', 'lignesCommande.produit.agriculteur.user', 'lignesCommande.produit.ferme', 'livraison.livreur', 'paiement.facture'])
                ->latest()->get();
        } else {
            $commandes = $user->commandes()
                ->with(['lignesCommande.produit.agriculteur.user', 'lignesCommande.produit.ferme', 'livraison.livreur', 'paiement.facture'])
                ->latest()->get();
        }

        return response()->json($commandes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rawMode = $request->input('mode_paiement', 'cash');
        $inputModePaiement = str_replace(['-', ' '], '_', strtolower(trim($rawMode)));
        $request->merge(['mode_paiement' => $inputModePaiement]);

        $request->validate([
            'adresse_livraison'   => 'required|string|max:255',
            'telephone'           => 'required|string',
            'mode_paiement'       => 'required|string',
            'lignes'              => 'required|array|min:1',
            'lignes.*.produit_id' => 'required|exists:produits,id',
            'lignes.*.quantite'   => 'required|integer|min:1',
            'lignes.*.prix_unitaire' => 'required|numeric',
        ]);

        return DB::transaction(function () use ($request) {
            $montant_total = 0;
            $lignesData = [];

            // 1. Valider le stock de chaque produit
            foreach ($request->lignes as $ligne) {
                $produit = Produit::lockForUpdate()->find($ligne['produit_id']);

                if (!$produit) {
                    throw new HttpResponseException(
                        response()->json(['message' => 'Un des produits de la commande n\'existe plus.'], 422)
                    );
                }

                if ($produit->stock < $ligne['quantite']) {
                    throw new HttpResponseException(
                        response()->json([
                            'message' => "Le stock pour le produit '{$produit->nom}' est insuffisant (Stock disponible : {$produit->stock})."
                        ], 422)
                    );
                }

                // Décrémenter le stock
                $produit->stock -= $ligne['quantite'];
                $produit->save();

                $montant_total += $ligne['prix_unitaire'] * $ligne['quantite'];

                $lignesData[] = [
                    'produit_id'    => $ligne['produit_id'],
                    'quantite'      => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                ];
            }

            // Frais de livraison (1000 FCFA)
            $frais_livraison = 1000;
            $total_facture = $montant_total + $frais_livraison;

            // 2. Créer la commande
            $commande = Commande::create([
                'client_id'         => $request->user()->id,
                'adresse_livraison' => $request->adresse_livraison,
                'telephone'         => $request->telephone,
                'statut'            => 'en_attente',
                'montant_total'     => $total_facture,
                'mode_paiement'     => $request->mode_paiement,
            ]);

            // 3. Créer les lignes de commande
            foreach ($lignesData as $data) {
                $commande->lignesCommande()->create($data);
            }

            // 4. Traiter le paiement via la passerelle
            try {
                $gateway = \App\Services\Payment\PaymentGatewayFactory::make($request->mode_paiement);
                $paymentResult = $gateway->process($commande, [
                    'telephone' => $request->telephone,
                    'email'     => $request->user()->email,
                ]);
            } catch (\Exception $e) {
                $paymentResult = [
                    'success'        => true,
                    'transaction_id' => 'PAY-' . strtoupper(Str::random(10)),
                    'status'         => 'pending',
                    'message'        => 'Commande enregistrée.',
                    'metadata'       => ['provider' => $request->mode_paiement]
                ];
            }

            $paiement = $commande->paiement()->create([
                'amount'         => $total_facture,
                'payment_method' => $request->mode_paiement,
                'transaction_id' => $paymentResult['transaction_id'] ?? ('TX-' . strtoupper(Str::random(8))),
                'status'         => $paymentResult['status'] ?? 'pending',
                'metadata'       => $paymentResult['metadata'] ?? [],
            ]);

            // Générer la Facture UML
            $paiement->facture()->create([
                'numero_facture'  => 'FAC-AGC-' . str_pad($commande->id, 5, '0', STR_PAD_LEFT),
                'montant_facture' => $total_facture,
                'date_facture'    => now(),
            ]);

            // 5. Ajuster le statut de la commande si le paiement est complété
            if (($paymentResult['status'] ?? '') === 'completed') {
                $commande->statut = 'preparation';
                $commande->save();
            }

            // 6. Créer la livraison
            $commande->livraison()->create([
                'status' => ($paymentResult['status'] ?? '') === 'completed' ? 'preparation' : 'en_attente',
            ]);

            // 7. Vider le panier de l'utilisateur uniquement si le paiement est validé immédiatement
            if (($paymentResult['status'] ?? '') === 'completed' || $request->mode_paiement === 'cash') {
                $request->user()->panierItems()->delete();
            }

            $resData = $commande->load(['lignesCommande.produit.agriculteur.user', 'livraison', 'paiement.facture'])->toArray();
            if (!empty($paymentResult['redirect_url'])) {
                $resData['redirect_url'] = $paymentResult['redirect_url'];
            }

            return response()->json($resData, 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $commande = Commande::with(['client', 'lignesCommande.produit.agriculteur', 'livraison', 'paiement.facture'])
            ->findOrFail($id);

        $user = $request->user();

        if ($user->role === 'client' && $commande->client_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($user->role === 'agriculteur') {
            $agriculteur = $user->agriculteur;
            if (!$agriculteur) {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }
            $aDesProduits = $commande->lignesCommande()->whereHas('produit', function ($q) use ($agriculteur) {
                $q->where('agriculteur_id', $agriculteur->id);
            })->exists();

            if (!$aDesProduits) {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }
        }

        return response()->json($commande);
    }

    /**
     * Get orders for the authenticated farmer.
     */
    public function agriculteurCommandes(Request $request)
    {
        $agriculteur = $request->user()->agriculteur;

        if (!$agriculteur) {
            return response()->json([], 200);
        }

        $commandes = Commande::whereHas('lignesCommande.produit', function ($query) use ($agriculteur) {
            $query->where('agriculteur_id', $agriculteur->id);
        })->with(['lignesCommande' => function ($query) use ($agriculteur) {
            $query->whereHas('produit', function ($q) use ($agriculteur) {
                $q->where('agriculteur_id', $agriculteur->id);
            })->with('produit');
        }, 'client', 'livraison'])->latest()->get();

        return response()->json($commandes);
    }
}
