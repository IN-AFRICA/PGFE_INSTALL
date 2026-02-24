<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->foreignId('classroom_id')->constrained('classrooms');
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('author_id')->constrained('academic_personals');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_files');
    }
};
