<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) S'assurer d'un index sur school_id
        Schema::table('classrooms', function (Blueprint $table) {
            try {
                $table->index('school_id', 'classrooms_school_id_idx');
            } catch (Throwable $e) {
                // ignore
            }
        });

        // 2) Supprimer l'unique existant (school_id, name)
        Schema::table('classrooms', function (Blueprint $table) {
            try {
                $table->dropUnique('classrooms_school_id_name_unique');
            } catch (Throwable $e) {
                try {
                    $table->dropUnique(['school_id', 'name']);
                } catch (Throwable $e2) {
                    // ignore si absent
                }
            }
        });

        // 3) Créer l'unique composite (school_id, filiaire_id, name)
        Schema::table('classrooms', function (Blueprint $table) {
            try {
                $table->unique(['school_id', 'filiaire_id', 'name'], 'classrooms_school_filiere_name_unique');
            } catch (Throwable $e) {
                // ignore si déjà présent
            }
        });
    }

    public function down(): void
    {
        // Supprimer l'unique composite (school_id, filiaire_id, name)
        Schema::table('classrooms', function (Blueprint $table) {
            try {
                $table->dropUnique('classrooms_school_filiere_name_unique');
            } catch (Throwable $e) {
                // ignore
            }
        });

        // Restaurer l'unique (school_id, name)
        Schema::table('classrooms', function (Blueprint $table) {
            try {
                $table->unique(['school_id', 'name']);
            } catch (Throwable $e) {
                // ignore
            }
        });
    }
};
