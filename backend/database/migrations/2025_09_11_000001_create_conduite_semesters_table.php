<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conduite_semesters', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('conduite_id')->constrained('conduites')->onDelete('cascade');

            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');
            $table->string('semester');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conduite_semesters');
    }
};
