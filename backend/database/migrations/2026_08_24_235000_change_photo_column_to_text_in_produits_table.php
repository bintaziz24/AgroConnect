<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE produits ALTER COLUMN photo TYPE TEXT;");
        } catch (\Throwable $e) {
            Schema::table('produits', function (Blueprint $table) {
                $table->text('photo')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE produits ALTER COLUMN photo TYPE VARCHAR(255);");
        } catch (\Throwable $e) {
            Schema::table('produits', function (Blueprint $table) {
                $table->string('photo', 255)->nullable()->change();
            });
        }
    }
};
