<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'agriculteur_id',
        'livreur_id',
        'produit_id',
        'commande_id',
        'statut',
        'dernier_message_at',
    ];

    protected $casts = [
        'dernier_message_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function agriculteur()
    {
        return $this->belongsTo(Agriculteur::class, 'agriculteur_id');
    }

    public function livreur()
    {
        return $this->belongsTo(User::class, 'livreur_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class, 'commande_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'discussion_id')->orderBy('created_at', 'asc');
    }

    public function dernierMessage()
    {
        return $this->hasOne(Message::class, 'discussion_id')->latestOfMany();
    }
}
