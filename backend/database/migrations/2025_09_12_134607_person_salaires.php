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
        Schema::create('person_salaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mois_id')->constrained('mois')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->foreignId('author_id')->constrained('personals')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
