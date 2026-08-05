<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'prix',
        'stock',
        'unite',
        'photo',
        'categorie_id',
        'agriculteur_id',
    ];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function agriculteur()
    {
        return $this->belongsTo(Agriculteur::class);
    }

    public function panierItems()
    {
        return $this->hasMany(PanierItem::class);
    }

    public function lignesCommande()
    {
        return $this->hasMany(LigneCommande::class);
    }
}
