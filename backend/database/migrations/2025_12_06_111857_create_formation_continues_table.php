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
        Schema::create('formation_continues', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('academic_personal_id')->constrained('academic_personals');
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms');
            $table->foreignId('filiere_id')->nullable()->constrained('filiaires');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formation_continues');
    }
};
