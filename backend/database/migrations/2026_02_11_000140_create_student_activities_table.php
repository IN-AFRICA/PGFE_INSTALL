<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_activity_id')->constrained('school_activities');
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms');
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('author_id')->constrained('academic_personals');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_activities');
    }
};
