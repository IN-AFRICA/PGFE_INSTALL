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
            try {
                $table->index(['academic_level_id', 'name'], 'filiaires_level_name_index');
            } catch (Throwable $e) {
                // ignorer si déjà présent
            }
        });
    }

    public function down(): void
    {
        Schema::table('filiaires', function (Blueprint $table) {
            try {
                $table->dropIndex('filiaires_level_name_index');
            } catch (Throwable $e) {
                // ignorer
            }
        });
    }
};
