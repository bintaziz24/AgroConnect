<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
    protected $fillable = [
        'commande_id',
        'livreur_id',
        'status',
        'adresse_livraison',
        'date_livraison',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function livreur()
    {
        return $this->belongsTo(User::class, 'livreur_id');
    }

    public function livreurProfile()
    {
        return $this->belongsTo(Livreur::class, 'livreur_id', 'user_id');
    }

    /**
     * Méthode métier UML : Mettre à jour le statut de la livraison
     */
    public function mettreAJourStatut(string $statut): void
    {
        $this->status = $statut;
        $this->save();
    }
}
