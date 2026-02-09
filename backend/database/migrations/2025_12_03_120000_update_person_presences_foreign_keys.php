<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Swap foreign keys to point to academic_personals
        Schema::table('person_presences', function (Blueprint $table) {
            // Drop existing FKs if they exist (personals)
            try {
                $table->dropForeign(['personnel_id']);
            } catch (\Throwable $e) {
                // ignore if not present
            }
            try {
                $table->dropForeign(['author_id']);
            } catch (\Throwable $e) {
                // ignore if not present
            }

            // Re-add FKs to academic_personals
            $table->foreign('personnel_id')
                ->references('id')
                ->on('academic_personals')
                ->onDelete('cascade');
            $table->foreign('author_id')
                ->references('id')
                ->on('academic_personals')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Revert foreign keys back to personals
        Schema::table('person_presences', function (Blueprint $table) {
            try {
                $table->dropForeign(['personnel_id']);
            } catch (\Throwable $e) {
                // ignore if not present
            }
            try {
                $table->dropForeign(['author_id']);
            } catch (\Throwable $e) {
                // ignore if not present
            }

            $table->foreign('personnel_id')
                ->references('id')
                ->on('personals')
                ->onDelete('cascade');
            $table->foreign('author_id')
                ->references('id')
                ->on('personals')
                ->onDelete('cascade');
        });
    }
};
