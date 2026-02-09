<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            // Drop old single-column unique index on name if it exists
            try {
                $table->dropUnique('classrooms_name_unique');
            } catch (Throwable $e) {
                // ignore if it does not exist
            }

            // Add composite unique index (school_id, name)
            $table->unique(['school_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            try {
                $table->dropUnique(['school_id', 'name']);
            } catch (Throwable $e) {
                // ignore
            }
            // Restore original unique on name only
            $table->unique('name');
        });
    }
};
