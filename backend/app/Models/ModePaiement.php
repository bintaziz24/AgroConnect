<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModePaiement extends Model
{
    use HasFactory;

    protected $table = 'mode_paiements';

    protected $fillable = [
        'libelle',
    ];

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'mode_paiement_id');
    }
}
