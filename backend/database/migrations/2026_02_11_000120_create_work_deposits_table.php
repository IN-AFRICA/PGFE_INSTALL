<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schoolwork_planning_id')->constrained('schoolwork_plannings');
            $table->foreignId('file_id')->nullable()->constrained('planning_files');
            $table->foreignId('classroom_id')->constrained('classrooms');
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('author_id')->constrained('academic_personals');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_deposits');
    }
};
