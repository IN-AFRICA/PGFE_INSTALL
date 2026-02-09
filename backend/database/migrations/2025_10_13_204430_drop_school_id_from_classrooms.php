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
        if (! Schema::hasTable('classrooms') || ! Schema::hasColumn('classrooms', 'school_id')) {
            return;
        }

        // 1) Supprimer la contrainte FK réelle si elle existe
        $fk = DB::scalar("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'classrooms'
              AND COLUMN_NAME = 'school_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");
        if ($fk) {
            DB::statement("ALTER TABLE `classrooms` DROP FOREIGN KEY `$fk`");
        }

        // 2) Supprimer tous les index réels sur `school_id` (quel que soit leur nom)
        $indexes = DB::select("
            SELECT DISTINCT INDEX_NAME
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'classrooms'
              AND COLUMN_NAME = 'school_id'
        ");
        foreach ($indexes as $row) {
            $name = $row->INDEX_NAME;
            if ($name !== 'PRIMARY') {
                DB::statement("DROP INDEX `$name` ON `classrooms`");
            }
        }

        // 3) Supprimer la colonne
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn('school_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('classrooms')) {
            return;
        }

        Schema::table('classrooms', function (Blueprint $table) {
            if (! Schema::hasColumn('classrooms', 'school_id')) {
                $table->unsignedBigInteger('school_id')->nullable()->index();
                $table->foreign('school_id')
                    ->references('id')
                    ->on('schools')
                    ->cascadeOnDelete();
            }
        });
    }
};
