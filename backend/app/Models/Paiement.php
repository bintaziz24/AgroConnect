<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
        'commande_id',
        'mode_paiement_id',
        'amount',
        'payment_method',
        'transaction_id',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class, 'mode_paiement_id');
    }

    public function facture()
    {
        return $this->hasOne(Facture::class);
    }
}
