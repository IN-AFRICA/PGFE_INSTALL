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
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(App\Models\School::class)->constrained();
            $table->string('name'); // ex: 2024-2025
            $table->boolean('is_active')->default(false); // année active pour l'école
            $table->text('description')->nullable();
            $table->timestamps();

            // Empêche deux années avec le même nom dans une même école
            $table->unique(['school_id', 'name']);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_years');
    }
};
