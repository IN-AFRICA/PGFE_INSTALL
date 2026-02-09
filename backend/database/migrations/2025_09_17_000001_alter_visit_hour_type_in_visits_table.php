<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nécessite doctrine/dbal pour change() si non installée: composer require doctrine/dbal --dev
        if (Schema::hasColumn('visits', 'visit_hour')) {
            Schema::table('visits', function (Blueprint $table) {
                $table->dateTime('visit_hour')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('visits', 'visit_hour')) {
            Schema::table('visits', function (Blueprint $table) {
                $table->integer('visit_hour')->nullable()->change();
            });
        }
    }
};
