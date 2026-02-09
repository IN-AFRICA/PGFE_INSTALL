<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_presences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personals')->onDelete('cascade');
            $table->boolean('presence');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('personals')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_presences');
    }
};
