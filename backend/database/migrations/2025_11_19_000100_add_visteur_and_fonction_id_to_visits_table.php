<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('visits')) {
            Schema::table('visits', function (Blueprint $table) {
                if (! Schema::hasColumn('visits', 'visiteur')) {
                    $table->string('visiteur')->nullable()->after('subject');
                }
                if (! Schema::hasColumn('visits', 'fonction_id')) {
                    $table->foreignId('fonction_id')
                        ->nullable()
                        ->constrained('fonctions')
                        ->nullOnDelete()
                        ->after('personal_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('visits')) {
            Schema::table('visits', function (Blueprint $table) {
                if (Schema::hasColumn('visits', 'fonction_id')) {
                    $table->dropForeign(['fonction_id']);
                    $table->dropColumn('fonction_id');
                }
                if (Schema::hasColumn('visits', 'visiteur')) {
                    $table->dropColumn('visiteur');
                }
            });
        }
    }
};
