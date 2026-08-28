<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assures', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('civilite', 4);
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('telephone', 20);
            $table->date('date_naissance');
            $table->string('adresse');
            $table->string('code_postal', 5);
            $table->string('ville');
            $table->timestamps();

            $table->index(['nom', 'prenom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assures');
    }
};
