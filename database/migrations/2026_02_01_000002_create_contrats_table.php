<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assure_id')->constrained('assures')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('produit', 20);   // auto | habitation | sante | rc_pro
            $table->string('formule', 20);   // essentiel | confort | premium
            $table->date('date_effet');
            $table->date('date_echeance');
            $table->string('statut', 20);    // actif | suspendu | resilie
            $table->unsignedInteger('prime_annuelle_cents');
            $table->unsignedInteger('franchise_cents');
            $table->timestamps();

            $table->index(['produit', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
