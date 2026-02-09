<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    if (Schema::hasColumn('filiaires', 'academic_level_id')) {
        try {
            Schema::table('filiaires', function (Blueprint $table) {
                $table->dropForeign(['academic_level_id']);
            });
        } catch (\Throwable $e) {
            // Ignore si la clé étrangère n'existe plus
        }
        try {
            Schema::table('filiaires', function (Blueprint $table) {
                $table->dropColumn('academic_level_id');
            });
        } catch (\Throwable $e) {
            // Ignore si la colonne n'existe plus
        }
    }
}

    public function down(): void
    {
        // Impossible de restaurer la colonne sans perte de données
    }
};
