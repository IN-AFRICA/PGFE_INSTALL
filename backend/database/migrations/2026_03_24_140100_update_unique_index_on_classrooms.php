<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Supprime tous les index uniques connus qui pourraient bloquer le seed
        $indexesToDrop = [
            'classrooms_name_unique',
            'classrooms_school_id_name_unique',
            'classrooms_academic_level_name_unique',
        ];
        foreach ($indexesToDrop as $idx) {
            try {
                DB::statement("ALTER TABLE classrooms DROP INDEX $idx");
            } catch (\Throwable $e) {}
        }
        // Ajoute l'unique composite (ne plante pas si déjà présent)
        Schema::table('classrooms', function (Blueprint $table) {
            try {
                $table->unique(['school_id', 'filiaire_id', 'name'], 'classrooms_school_filiere_name_unique');
            } catch (\Throwable $e) {}
        });

        // S'assure que la colonne cycle_id existe sur academic_levels
        Schema::table('academic_levels', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_levels', 'cycle_id')) {
                $table->foreignId('cycle_id')->nullable()->constrained('cycles')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            // Supprimer l'unique composite
            $table->dropUnique('classrooms_school_filiere_name_unique');
            // Restaurer l'unique sur name
            $table->unique('name');
        });
    }
};
