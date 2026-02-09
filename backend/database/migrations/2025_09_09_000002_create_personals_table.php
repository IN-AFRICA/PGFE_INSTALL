<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personals', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('name');
            $table->string('post_name');
            $table->string('pre_name');
            $table->string('gender');
            $table->string('civil_status');
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->foreignId('territory_id')->constrained('territories')->onDelete('cascade');
            $table->foreignId('commune_id')->constrained('communes')->onDelete('cascade');
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null');
            $table->foreignId('type_id')->constrained('types')->onDelete('cascade');
            $table->string('physical_address');
            $table->date('birth_date');
            $table->string('birth_place');
            $table->string('identity_card_number');
            $table->foreignId('father_id')->nullable()->constrained('personals')->onDelete('set null');
            $table->foreignId('mother_id')->nullable()->constrained('personals')->onDelete('set null');
            $table->foreignId('academic_level_id')->nullable()->constrained('academic_levels')->onDelete('set null');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->foreignId('fonction_id')->constrained('fonctions')->onDelete('cascade');
            $table->foreignId('mechanisation_id')->constrained('mecanisations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personals');
    }
};
