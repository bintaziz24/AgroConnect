<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fermes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agriculteur_id')->constrained('agriculteurs')->onDelete('cascade');
            $table->string('nom_ferme');
            $table->string('adresse_ferme')->nullable();
            $table->text('description_ferme')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fermes');
    }
};
