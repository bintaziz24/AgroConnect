<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'paiement_id',
        'numero_facture',
        'montant_facture',
        'date_facture',
    ];

    protected $casts = [
        'date_facture' => 'datetime',
        'montant_facture' => 'double',
    ];

    public function paiement()
    {
        return $this->belongsTo(Paiement::class);
    }
}
