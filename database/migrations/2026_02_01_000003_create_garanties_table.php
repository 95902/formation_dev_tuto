<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garanties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrat_id')->constrained('contrats')->cascadeOnDelete();
            $table->string('code', 10);      // DDE | VOL | INC | BDG | RC | ...
            $table->string('libelle');
            $table->unsignedInteger('plafond_cents');
            $table->unsignedInteger('franchise_cents');
            $table->boolean('incluse')->default(true);
            $table->timestamps();

            $table->unique(['contrat_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garanties');
    }
};
