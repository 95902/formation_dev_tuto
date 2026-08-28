<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pieces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sinistre_id')->constrained('sinistres')->cascadeOnDelete();
            $table->string('libelle');
            $table->string('type', 30);      // constat | facture | photo | rapport_expertise | proces_verbal
            $table->date('recue_le');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pieces');
    }
};
