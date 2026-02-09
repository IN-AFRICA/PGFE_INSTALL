<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer l'ancien chainage implicite
        if (Schema::hasColumn('filiaires', 'academic_level_id')) {
            try {
                Schema::table('filiaires', function (Blueprint $table) {
                    $table->dropForeign(['academic_level_id']);
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('filiaires', function (Blueprint $table) {
                    $table->dropColumn('academic_level_id');
                });
            } catch (\Throwable $e) {}
        }
        if (Schema::hasColumn('cycles', 'school_id')) {
            try {
                Schema::table('cycles', function (Blueprint $table) {
                    $table->dropForeign(['school_id']);
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('cycles', function (Blueprint $table) {
                    $table->dropColumn('school_id');
                });
            } catch (\Throwable $e) {}
        }
        if (Schema::hasColumn('academic_levels', 'cycle_id')) {
            try {
                Schema::table('academic_levels', function (Blueprint $table) {
                    $table->dropForeign(['cycle_id']);
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('academic_levels', function (Blueprint $table) {
                    $table->dropColumn('cycle_id');
                });
            } catch (\Throwable $e) {}
        }
        if (Schema::hasColumn('classrooms', 'filiaire_id')) {
            try {
                Schema::table('classrooms', function (Blueprint $table) {
                    $table->dropForeign(['filiaire_id']);
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('classrooms', function (Blueprint $table) {
                    $table->dropColumn('filiaire_id');
                });
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        // Impossible de restaurer les anciennes colonnes sans perte de données
    }
};
