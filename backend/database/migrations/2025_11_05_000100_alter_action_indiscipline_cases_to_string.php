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
        // Ajouter la colonne string 'action' (nullable pour compat rétro) puis supprimer la contrainte/colonne 'action_id'
        Schema::table('indiscipline_cases', function (Blueprint $table) {
            $table->string('action')->nullable()->after('fault_count');
        });

        // Optionnel: migrer les anciennes valeurs depuis la table types.name -> colonne action
        // DB::statement('UPDATE indiscipline_cases ic JOIN types t ON t.id = ic.action_id SET ic.action = t.name');

        Schema::table('indiscipline_cases', function (Blueprint $table) {
            try {
                $table->dropForeign(['action_id']);
            } catch (Throwable $e) {
                // ignorer si la contrainte n'existe pas
            }
            if (Schema::hasColumn('indiscipline_cases', 'action_id')) {
                $table->dropColumn('action_id');
            }
        });
    }

    public function down(): void
    {
        // Recréer la colonne action_id et supprimer la colonne string action
        Schema::table('indiscipline_cases', function (Blueprint $table) {
            $table->foreignId('action_id')->after('fault_count')->constrained('types')->onDelete('cascade');
        });

        Schema::table('indiscipline_cases', function (Blueprint $table) {
            if (Schema::hasColumn('indiscipline_cases', 'action')) {
                $table->dropColumn('action');
            }
        });
    }
};
