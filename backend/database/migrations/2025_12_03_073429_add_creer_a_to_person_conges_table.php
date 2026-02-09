<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('person_conges', function (Blueprint $table) {
            // Ajouter la colonne creer_a (date) sans contrainte de position
            if (!Schema::hasColumn('person_conges', 'creer_a')) {
                $table->date('creer_a')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('person_conges', function (Blueprint $table) {
            if (Schema::hasColumn('person_conges', 'creer_a')) {
                $table->dropColumn('creer_a');
            }
        });
    }
};
