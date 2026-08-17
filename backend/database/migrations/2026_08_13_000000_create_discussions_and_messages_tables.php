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
        Schema::create('discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('agriculteur_id')->constrained('agriculteurs')->onDelete('cascade');
            $table->foreignId('livreur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('produit_id')->nullable()->constrained('produits')->onDelete('set null');
            $table->foreignId('commande_id')->nullable()->constrained('commandes')->onDelete('set null');
            $table->enum('statut', ['active', 'fermee'])->default('active');
            $table->timestamp('dernier_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discussion_id')->constrained('discussions')->onDelete('cascade');
            $table->foreignId('expediteur_id')->constrained('users')->onDelete('cascade');
            $table->text('contenu');
            $table->enum('type_message', ['texte', 'image', 'fichier', 'systeme'])->default('texte');
            $table->string('fichier_url')->nullable();
            $table->boolean('est_lu')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('discussions');
    }
};
