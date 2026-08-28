<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinistres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrat_id')->constrained('contrats')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('nature', 30);    // degat_des_eaux | vol | incendie | bris_de_glace | collision | rc
            $table->date('survenu_le');
            $table->dateTime('declare_le');
            $table->text('description');
            $table->string('statut', 20);    // declare | en_cours | expertise | clos | refuse
            $table->unsignedInteger('montant_estime_cents');
            $table->unsignedInteger('montant_regle_cents')->nullable();
            $table->string('gestionnaire')->nullable();
            $table->timestamps();

            $table->index(['statut', 'declare_le']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinistres');
    }
};
