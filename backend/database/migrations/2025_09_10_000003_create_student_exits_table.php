<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_exits', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->time('exit_time');
            $table->string('motif');
            $table->foreignId('filiere_id')->nullable()->constrained('filiaires')->onDelete('set null');
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');
            $table->string('semester');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_exits');
    }
};
