<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Discussion;
use App\Models\Produit;

echo "=== CHECKING EXISTING DISCUSSIONS ===\n";
$discussions = Discussion::with(['agriculteur.user', 'produit.agriculteur.user'])->get();

foreach ($discussions as $d) {
    echo "Discussion #{$d->id}:\n";
    $currAgriName = $d->agriculteur && $d->agriculteur->user ? $d->agriculteur->user->name : 'N/A';
    echo "  Current agriculteur_id: {$d->agriculteur_id} ({$currAgriName})\n";

    if ($d->produit) {
        $realAgriId = $d->produit->agriculteur_id;
        $realAgriName = $d->produit->agriculteur && $d->produit->agriculteur->user ? $d->produit->agriculteur->user->name : 'N/A';
        echo "  Produit #{$d->produit_id}: {$d->produit->nom} -> Real agriculteur_id: {$realAgriId} ({$realAgriName})\n";

        if ($d->agriculteur_id != $realAgriId && $realAgriId) {
            echo "  --> FIXING Discussion #{$d->id}: changing agriculteur_id from {$d->agriculteur_id} to {$realAgriId}\n";
            $d->agriculteur_id = $realAgriId;
            $d->save();
        }
    }
    echo "----------------------------------------\n";
}
