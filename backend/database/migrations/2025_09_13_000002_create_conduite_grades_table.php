<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conduite_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');
            $table->foreignId('filiere_id')->nullable()->constrained('filiaires')->onDelete('set null');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->integer('fault_count');
            $table->foreignId('conduite_semester_1_id')->nullable()->constrained('conduite_semesters')->onDelete('set null');
            $table->foreignId('conduite_semester_2_id')->nullable()->constrained('conduite_semesters')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conduite_grades');
    }
};
