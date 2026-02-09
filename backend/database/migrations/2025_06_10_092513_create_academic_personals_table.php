<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_personals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('country_id')->constrained();
            $table->foreignId('province_id')->constrained();
            $table->foreignId('territory_id')->constrained();
            $table->foreignId('commune_id')->constrained();
            $table->foreignId('school_id')->constrained();
            $table->foreignId('type_id')->constrained();
            $table->foreignId('father_id')->constrained('parents');
            $table->foreignId('mother_id')->constrained('parents');
            $table->foreignId('academic_level_id')->constrained();
            $table->foreignId('fonction_id')->constrained();
            $table->string('matricule')->unique();
            $table->string('name');
            $table->string('firstname');
            $table->string('username')->nullable();
            $table->string('phone_number')->unique();
            $table->string('email')->unique();
            $table->string('identity_card')->unique();
            $table->string('gender');
            $table->string('civil_status');
            $table->string('address');
            $table->date('birth_date');
            $table->string('birth_place')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_personals');
    }
};
