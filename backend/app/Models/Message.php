<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'discussion_id',
        'expediteur_id',
        'contenu',
        'type_message',
        'fichier_url',
        'est_lu',
    ];

    protected $casts = [
        'est_lu' => 'boolean',
    ];

    public function discussion()
    {
        return $this->belongsTo(Discussion::class, 'discussion_id');
    }

    public function expediteur()
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }
}
