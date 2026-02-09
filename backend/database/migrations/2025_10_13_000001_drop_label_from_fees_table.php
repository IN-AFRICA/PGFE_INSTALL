<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('fees', 'label')) {
            Schema::table('fees', function (Blueprint $table) {
                $table->dropColumn('label');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('fees', 'label')) {
            Schema::table('fees', function (Blueprint $table) {
                // On rétablit la colonne label; nullable pour éviter des erreurs sur des lignes existantes
                $table->string('label')->nullable();
            });
        }
    }
};
