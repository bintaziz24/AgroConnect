<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('avis', 'client_id')) {
            Schema::table('avis', function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->constrained('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('livraisons', 'adresse_livraison')) {
            Schema::table('livraisons', function (Blueprint $table) {
                $table->string('adresse_livraison')->nullable();
                $table->timestamp('date_livraison')->nullable();
            });
        }

        if (!Schema::hasColumn('paiements', 'mode_paiement_id')) {
            Schema::table('paiements', function (Blueprint $table) {
                $table->foreignId('mode_paiement_id')->nullable()->constrained('mode_paiements')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('avis', 'client_id')) {
            Schema::table('avis', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            });
        }

        if (Schema::hasColumn('livraisons', 'adresse_livraison')) {
            Schema::table('livraisons', function (Blueprint $table) {
                $table->dropColumn(['adresse_livraison', 'date_livraison']);
            });
        }

        if (Schema::hasColumn('paiements', 'mode_paiement_id')) {
            Schema::table('paiements', function (Blueprint $table) {
                $table->dropForeign(['mode_paiement_id']);
                $table->dropColumn('mode_paiement_id');
            });
        }
    }
};
