<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old FKs to personals first (safe if they don't exist)
        Schema::table('person_conges', function (Blueprint $table) {
            try { $table->dropForeign(['personal_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['author_id']); } catch (\Throwable $e) {}
        });

        // Rename personal_id -> academic_personal_id if needed
        if (Schema::hasColumn('person_conges', 'personal_id') && ! Schema::hasColumn('person_conges', 'academic_personal_id')) {
            Schema::table('person_conges', function (Blueprint $table) {
                $table->renameColumn('personal_id', 'academic_personal_id');
            });
        }

        // Add FKs to academic_personals
        Schema::table('person_conges', function (Blueprint $table) {
            try {
                $table->foreign('academic_personal_id')
                    ->references('id')
                    ->on('academic_personals')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {}
            try {
                $table->foreign('author_id')
                    ->references('id')
                    ->on('academic_personals')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        // Drop FKs to academic_personals
        Schema::table('person_conges', function (Blueprint $table) {
            try { $table->dropForeign(['academic_personal_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['author_id']); } catch (\Throwable $e) {}
        });

        // Rename column back if present
        if (Schema::hasColumn('person_conges', 'academic_personal_id') && ! Schema::hasColumn('person_conges', 'personal_id')) {
            Schema::table('person_conges', function (Blueprint $table) {
                $table->renameColumn('academic_personal_id', 'personal_id');
            });
        }

        // Restore FKs to personals
        Schema::table('person_conges', function (Blueprint $table) {
            try {
                $table->foreign('personal_id')
                    ->references('id')
                    ->on('personals')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {}
            try {
                $table->foreign('author_id')
                    ->references('id')
                    ->on('personals')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {}
        });
    }
};
