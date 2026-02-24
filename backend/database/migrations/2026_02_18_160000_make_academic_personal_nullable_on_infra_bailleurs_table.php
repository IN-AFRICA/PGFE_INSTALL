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
        if (! Schema::hasTable('infra_bailleurs')) {
            return;
        }

        // Rendre la colonne academic_personal_id nullable sans dépendre de doctrine/dbal
        DB::statement('ALTER TABLE `infra_bailleurs` MODIFY `academic_personal_id` BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('infra_bailleurs')) {
            return;
        }

        // Revenir à NOT NULL (si besoin de rollback)
        DB::statement('ALTER TABLE `infra_bailleurs` MODIFY `academic_personal_id` BIGINT UNSIGNED NOT NULL');
    }
};
