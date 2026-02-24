<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update AcademicLevels first
        if (Schema::hasTable('academic_levels')) {
            Schema::table('academic_levels', function (Blueprint $table) {
                if (! Schema::hasColumn('academic_levels', 'school_id')) {
                    $table->foreignId('school_id')->nullable()->after('uuid')->constrained()->onDelete('cascade');
                }
            });

            // Backfill school_id for academic_levels
            // cycle_id -> cycles -> filiaire_id -> filiaires -> school_id
            DB::statement("
                UPDATE academic_levels al
                JOIN cycles c ON al.cycle_id = c.id
                JOIN filiaires f ON c.filiaire_id = f.id
                SET al.school_id = f.school_id
                WHERE al.school_id IS NULL
            ");
        }

        // 2. Update Cycles
        if (Schema::hasTable('cycles')) {
            Schema::table('cycles', function (Blueprint $table) {
                if (! Schema::hasColumn('cycles', 'school_id')) {
                    $table->foreignId('school_id')->nullable()->after('uuid')->constrained()->onDelete('cascade');
                }
            });

            // Backfill school_id for cycles
            // filiaire_id -> filiaires -> school_id
            DB::statement("
                UPDATE cycles c
                JOIN filiaires f ON c.filiaire_id = f.id
                SET c.school_id = f.school_id
                WHERE c.school_id IS NULL
            ");
        }

        // 3. Update Classrooms
        if (Schema::hasTable('classrooms')) {
            Schema::table('classrooms', function (Blueprint $table) {
                if (! Schema::hasColumn('classrooms', 'school_id')) {
                    $table->foreignId('school_id')->nullable()->after('uuid')->constrained()->onDelete('cascade');
                }
                if (! Schema::hasColumn('classrooms', 'filiaire_id')) {
                    $table->foreignId('filiaire_id')->nullable()->after('school_id')->constrained('filiaires')->onDelete('cascade');
                }
            });

            // Backfill school_id and filiaire_id for classrooms
            // academic_level_id -> academic_levels -> cycle_id -> cycles -> filiaire_id -> filiaires -> school_id
            DB::statement("
                UPDATE classrooms cl
                JOIN academic_levels al ON cl.academic_level_id = al.id
                JOIN cycles c ON al.cycle_id = c.id
                JOIN filiaires f ON c.filiaire_id = f.id
                SET cl.school_id = f.school_id, cl.filiaire_id = f.id
                WHERE cl.school_id IS NULL OR cl.filiaire_id IS NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn(['school_id', 'filiaire_id']);
        });
        Schema::table('cycles', function (Blueprint $table) {
            $table->dropColumn('school_id');
        });
        Schema::table('academic_levels', function (Blueprint $table) {
            $table->dropColumn('school_id');
        });
    }
};
