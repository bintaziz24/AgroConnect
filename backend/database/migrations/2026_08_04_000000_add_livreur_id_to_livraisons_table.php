<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('livraisons', 'livreur_id')) {
            Schema::table('livraisons', function (Blueprint $table) {
                $table->foreignId('livreur_id')->nullable()->constrained('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('livraisons', 'livreur_id')) {
            Schema::table('livraisons', function (Blueprint $table) {
                $table->dropForeign(['livreur_id']);
                $table->dropColumn('livreur_id');
            });
        }
    }
};
