<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('from_school_id')->constrained('schools');
            $table->foreignId('to_school_id')->constrained('schools');
            $table->foreignId('from_classroom_id')->nullable()->constrained('classrooms');
            $table->foreignId('to_classroom_id')->nullable()->constrained('classrooms');
            $table->foreignId('school_year_id')->nullable()->constrained('school_years');
            $table->date('transfer_date')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transfers');
    }
};
