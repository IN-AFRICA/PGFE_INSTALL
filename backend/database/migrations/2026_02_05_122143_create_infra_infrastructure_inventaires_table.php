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
        Schema::create('infra_infrastructure_inventaires', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(App\Models\InfraInfrastructure::class)->constrained('infra_infrastructures')->cascadeOnDelete();
            $table->string('title'); // Titre de l'inventaire
            $table->text('description')->nullable(); // Description détaillée
            $table->date('inventory_date'); // Date de l'inventaire
            $table->enum('status', ['excellent', 'bon', 'moyen', 'mauvais', 'critique'])->default('bon');
            $table->json('observations')->nullable(); // Observations structurées
            $table->foreignIdFor(App\Models\School::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(App\Models\User::class, 'author_id')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infra_infrastructure_inventaires');
    }
};
