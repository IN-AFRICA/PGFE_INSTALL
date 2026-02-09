<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filiaires', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable()->after('id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
        });
        Schema::table('cycles', function (Blueprint $table) {
            $table->unsignedBigInteger('filiaire_id')->nullable()->after('school_id');
            $table->foreign('filiaire_id')->references('id')->on('filiaires')->onDelete('set null');
        });
        Schema::table('classrooms', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_level_id')->nullable()->after('filiaire_id');
            $table->foreign('academic_level_id')->references('id')->on('academic_levels')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('filiaires', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
        Schema::table('cycles', function (Blueprint $table) {
            $table->dropForeign(['filiaire_id']);
            $table->dropColumn('filiaire_id');
        });
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign(['academic_level_id']);
            $table->dropColumn('academic_level_id');
        });
    }
};
