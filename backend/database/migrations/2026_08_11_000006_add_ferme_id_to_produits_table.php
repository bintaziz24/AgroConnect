<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('produits', 'ferme_id')) {
            Schema::table('produits', function (Blueprint $table) {
                $table->foreignId('ferme_id')->nullable()->constrained('fermes')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('produits', 'ferme_id')) {
            Schema::table('produits', function (Blueprint $table) {
                $table->dropForeign(['ferme_id']);
                $table->dropColumn('ferme_id');
            });
        }
    }
};
