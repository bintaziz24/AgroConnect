<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agriculteur extends Model
{
    protected $fillable = [
        'user_id',
        'localisation',
        'latitude',
        'longitude',
        'description',
        'statut_validation',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fermes()
    {
        return $this->hasMany(Ferme::class);
    }

    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    /**
     * Méthode métier UML : Ajouter une récolte / produit
     */
    public function ajouterRecolte(array $data): Produit
    {
        return $this->produits()->create($data);
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class, 'agriculteur_id');
    }
}
