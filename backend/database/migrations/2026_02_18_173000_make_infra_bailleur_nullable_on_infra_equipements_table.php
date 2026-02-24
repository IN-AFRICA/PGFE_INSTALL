<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rendre infra_bailleur_id nullable pour permettre les équipements sans bailleur explicite
        DB::statement('ALTER TABLE infra_equipements MODIFY infra_bailleur_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // Revenir au comportement précédent (non nul)
        DB::statement('ALTER TABLE infra_equipements MODIFY infra_bailleur_id BIGINT UNSIGNED NOT NULL');
    }
};
