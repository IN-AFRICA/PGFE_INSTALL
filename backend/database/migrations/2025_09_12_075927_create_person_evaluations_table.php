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
        Schema::create('person_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('critiques')->nullable();
            $table->double('c1_quantite_travail')->nullable();
            $table->double('c2_theorie_pratique')->nullable();
            $table->double('c3_determ_ress_perso')->nullable();
            $table->double('c4_ponctualite')->nullable();
            $table->double('c5_dr_att_posit_collab')->nullable();
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('set null');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('personals')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_evaluations');
    }
};
