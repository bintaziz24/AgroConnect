<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('adresse_livraison');
            $table->string('telephone');
            $table->string('statut')->default('en_attente'); // en_attente, preparation, expediee, en_cours, livree, annulee
            $table->decimal('montant_total', 10, 2);
            $table->string('mode_paiement'); // wave, orange_money, cash, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
