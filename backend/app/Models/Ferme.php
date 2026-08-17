<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ferme extends Model
{
    use HasFactory;

    protected $fillable = [
        'agriculteur_id',
        'nom_ferme',
        'adresse_ferme',
        'description_ferme',
    ];

    public function agriculteur()
    {
        return $this->belongsTo(Agriculteur::class);
    }

    public function produits()
    {
        return $this->hasMany(Produit::class);
    }
}
