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
        Schema::create('agenda_medecins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medecin_id')->constrained()->onDelete('cascade');
            $table->date('jour'); // Ex: lundi, mardi ou une date au format YYYY-MM-DD
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->boolean('bloque')->default(false); // Pour indiquer les jours fermés si besoin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_medecins');
    }
};
