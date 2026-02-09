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
        // Vérifier si l'unicité sur 'name' existe réellement pour éviter l'erreur 1091
        $hasUniqueOnName = false;
        try {
            $indexes = DB::select("SHOW INDEX FROM `filiaires` WHERE `Key_name` = 'filiaires_name_unique'");
            $hasUniqueOnName = count($indexes) > 0;
        } catch (Throwable $e) {
            $hasUniqueOnName = false;
        }

        if ($hasUniqueOnName) {
            Schema::table('filiaires', function (Blueprint $table): void {
                $table->dropUnique('filiaires_name_unique');
            });
        }

        // Ajouter l'unicité composite (academic_level_id, name) si absente
        $hasCompositeUnique = false;
        try {
            $existing = DB::select("SHOW INDEX FROM `filiaires` WHERE `Key_name` = 'filiaires_level_name_unique'");
            $hasCompositeUnique = count($existing) > 0;
        } catch (Throwable $e) {
            $hasCompositeUnique = false;
        }

        if (! $hasCompositeUnique) {
            Schema::table('filiaires', function (Blueprint $table): void {
                $table->unique(['academic_level_id', 'name'], 'filiaires_level_name_unique');
            });
        }
    }

    public function down(): void
    {
        // Supprimer l'unique composite (academic_level_id, name) si présent
        $hasCompositeUnique = false;
        try {
            $existing = DB::select("SHOW INDEX FROM `filiaires` WHERE `Key_name` = 'filiaires_level_name_unique'");
            $hasCompositeUnique = count($existing) > 0;
        } catch (Throwable $e) {
            $hasCompositeUnique = false;
        }

        if ($hasCompositeUnique) {
            Schema::table('filiaires', function (Blueprint $table): void {
                $table->dropUnique('filiaires_level_name_unique');
            });
        }

        // Restaurer l'unicité sur name (optionnel)
        $hasUniqueOnName = false;
        try {
            $indexes = DB::select("SHOW INDEX FROM `filiaires` WHERE `Key_name` = 'filiaires_name_unique'");
            $hasUniqueOnName = count($indexes) > 0;
        } catch (Throwable $e) {
            $hasUniqueOnName = false;
        }

        if (! $hasUniqueOnName) {
            Schema::table('filiaires', function (Blueprint $table): void {
                $table->unique('name');
            });
        }
    }
};
