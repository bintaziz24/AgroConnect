<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $fillable = [
        'client_id',
        'adresse_livraison',
        'telephone',
        'statut',
        'montant_total',
        'mode_paiement',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function lignesCommande()
    {
        return $this->hasMany(LigneCommande::class, 'commande_id');
    }

    public function livraison()
    {
        return $this->hasOne(Livraison::class, 'commande_id');
    }

    public function paiement()
    {
        return $this->hasOne(Paiement::class, 'commande_id');
    }

    /**
     * Méthode métier UML : Calculer le montant total de la commande
     */
    public function calculerMontantTotal(): float
    {
        $total = 0;
        foreach ($this->lignesCommande as $ligne) {
            $total += $ligne->calculerSousTotal();
        }
        $this->montant_total = $total;
        $this->save();
        return (float) $total;
    }

    /**
     * Méthode métier UML : Confirmer la commande
     */
    public function confirmerCommande(): void
    {
        $this->statut = 'confirmée';
        $this->save();
    }
}
