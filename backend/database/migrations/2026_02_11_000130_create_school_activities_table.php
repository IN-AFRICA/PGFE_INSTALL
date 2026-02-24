<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_activities', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('place')->nullable();
            $table->double('quantity')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('author_id')->constrained('academic_personals');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_activities');
    }
};
