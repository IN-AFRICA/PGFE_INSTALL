<?php

declare(strict_types=1);

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
        Schema::table('schools', function (Blueprint $table) {
            // Clé étrangère province (nullable pour compatibilité données existantes)
            $table->foreignId('province_id')
                ->nullable()
                ->after('city')
                ->constrained('provinces')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Supprimer la contrainte puis la colonne province_id
            if (Schema::hasColumn('schools', 'province_id')) {
                // Selon le SGBD, dropForeign nécessite le nom de l'index; on tente la convention
                try {
                    $table->dropConstrainedForeignId('province_id');
                } catch (Throwable $e) {
                    // fallback si nécessaire
                    if (method_exists($table, 'dropForeign')) {
                        try {
                            $table->dropForeign(['province_id']);
                        } catch (Throwable $e2) {
                        }
                    }
                    $table->dropColumn('province_id');
                }
            }
            $table->dropColumn('province_id');
        });
    }
};
