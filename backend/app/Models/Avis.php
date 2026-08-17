<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    use HasFactory;

    protected $table = 'avis';

    protected $fillable = [
        'client_id',
        'produit_id',
        'agriculteur_id',
        'rating',
        'comment',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function agriculteur()
    {
        return $this->belongsTo(Agriculteur::class);
    }
}
