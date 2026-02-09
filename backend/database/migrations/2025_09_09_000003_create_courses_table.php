<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->foreignId('level_id')->constrained('academic_levels')->onDelete('cascade');
            $table->foreignId('filiere_id')->constrained('filiaires')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('personals')->onDelete('cascade');
            $table->integer('hourly_volume');
            $table->double('max_period_1');
            $table->double('max_period_2');
            $table->double('max_period_3');
            $table->double('max_period_4');
            $table->double('max_exam_1');
            $table->double('max_exam_2');
            $table->foreignId('author_id')->constrained('personals')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
