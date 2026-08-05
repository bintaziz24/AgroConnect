<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Produit;

$manioc = Produit::where('nom', 'like', '%Manioc%')->first();
if ($manioc) {
    echo "Found Manioc in DB: ID {$manioc->id}, Name: {$manioc->nom}, Category: {$manioc->categorie->nom}\n";
} else {
    echo "Manioc NOT found in database.\n";
    echo "Current products in DB:\n";
    foreach (Produit::all() as $p) {
        echo "- ID {$p->id}: {$p->nom} ({$p->photo})\n";
    }
}
