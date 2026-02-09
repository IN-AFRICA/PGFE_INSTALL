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
        Schema::create('registration_parents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registration_id');
            $table->unsignedBigInteger('parent1_id'); // parent 1 obligatoire
            $table->unsignedBigInteger('parent2_id')->nullable();
            $table->unsignedBigInteger('parent3_id')->nullable();
            $table->timestamps();

            // Contrainte FK vers registrations (cascade en suppression)
            $table->foreign('registration_id')
                ->references('id')->on('registrations')
                ->onDelete('cascade');

            // Contraintes FK vers parents
            $table->foreign('parent1_id')
                ->references('id')->on('parents')
                ->onDelete('restrict');
            $table->foreign('parent2_id')
                ->references('id')->on('parents')
                ->onDelete('set null');
            $table->foreign('parent3_id')
                ->references('id')->on('parents')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_parents');
    }
};
