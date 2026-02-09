<?php

declare(strict_types=1);

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
        Schema::create('validation_aureats', function (Blueprint $table) {
            $table->id();
            $table->string('last_name'); // Nom
            $table->string('middle_name')->nullable(); // Postnom
            $table->string('first_name'); // Prénom
            $table->string('registration_number'); // Matricule
            $table->enum('gender', ['male', 'female', 'other']); // Sexe
            $table->string('department'); // Filière
            $table->string('class'); // Classe
            $table->string('year'); // Année
            $table->string('cycle'); // Cycle
            $table->boolean('present')->default(false); // Présent
            $table->text('comment')->nullable(); // Commentaire
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validation_aureats');
    }
};
