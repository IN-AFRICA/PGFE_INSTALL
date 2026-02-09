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
        // MODULE: CORE & ACADEMIC
        Schema::table('students', function (Blueprint $table) {
            $table->index('school_id');
            $table->index('name');
            $table->index(['firstname', 'lastname']);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->index('school_id');
            $table->index('student_id');
            $table->index('classroom_id');
            $table->index('school_year_id');
            $table->index('registration_status');
        });

        Schema::table('academic_personals', function (Blueprint $table) {
            $table->index('school_id');
            $table->index('user_id');
            $table->index('fonction_id');
            $table->index('name');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->index('school_id');
            $table->index('classroom_id');
            $table->index('academic_level_id');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->index('academic_level_id');
            $table->index('indicator');
        });

        Schema::table('academic_levels', function (Blueprint $table) {
            $table->index('cycle_id');
        });

        Schema::table('cycles', function (Blueprint $table) {
            $table->index('filiaire_id');
        });

        Schema::table('filiaires', function (Blueprint $table) {
            $table->index('school_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
            $table->dropIndex(['name']);
            $table->dropIndex(['firstname', 'lastname']);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
            $table->dropIndex(['student_id']);
            $table->dropIndex(['classroom_id']);
            $table->dropIndex(['school_year_id']);
            $table->dropIndex(['registration_status']);
        });

        Schema::table('academic_personals', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['fonction_id']);
            $table->dropIndex(['name']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
            $table->dropIndex(['classroom_id']);
            $table->dropIndex(['academic_level_id']);
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropIndex(['academic_level_id']);
            $table->dropIndex(['indicator']);
        });

        Schema::table('academic_levels', function (Blueprint $table) {
            $table->dropIndex(['cycle_id']);
        });

        Schema::table('cycles', function (Blueprint $table) {
            $table->dropIndex(['filiaire_id']);
        });

        Schema::table('filiaires', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
        });
    }
};
