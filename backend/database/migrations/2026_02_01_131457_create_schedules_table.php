<?php

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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_personal_id')->constrained('academic_personals')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('day'); // Lundi, Mardi, etc.
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('week_number')->nullable(); // Si spécifique à une semaine
            $table->timestamps();
            
            // Index pour accélérer les recherches de conflits
            $table->index(['school_id', 'academic_personal_id', 'day', 'start_time', 'end_time'], 'teacher_schedule_index');
            $table->index(['school_id', 'classroom_id', 'day', 'start_time', 'end_time'], 'classroom_schedule_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
