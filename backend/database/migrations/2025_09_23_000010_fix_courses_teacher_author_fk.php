<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Drop old foreign keys referencing personals if they exist
            try {
                $table->dropForeign(['teacher_id']);
            } catch (Throwable $e) {
            }
            try {
                $table->dropForeign(['author_id']);
            } catch (Throwable $e) {
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('academic_personals')->cascadeOnDelete();
            $table->foreign('author_id')->references('id')->on('academic_personals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            try {
                $table->dropForeign(['teacher_id']);
            } catch (Throwable $e) {
            }
            try {
                $table->dropForeign(['author_id']);
            } catch (Throwable $e) {
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('personals')->cascadeOnDelete();
            $table->foreign('author_id')->references('id')->on('personals')->cascadeOnDelete();
        });
    }
};
