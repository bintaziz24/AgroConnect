<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livreur extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'immatriculation_vehicule',
        'est_valid',
        'est_dispo',
    ];

    protected $casts = [
        'est_valid' => 'boolean',
        'est_dispo' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function livraisons()
    {
        return $this->hasMany(Livraison::class, 'livreur_id');
    }
}
