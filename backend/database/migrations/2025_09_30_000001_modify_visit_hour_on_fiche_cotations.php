<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Note: this uses Schema::table(...)->change() which requires doctrine/dbal
        // Install with: composer require doctrine/dbal
        if (Schema::hasTable('visits') && Schema::hasColumn('visits', 'visit_hour')) {
            Schema::table('visits', function (Blueprint $table) {
                $table->string('visit_hour')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('visits') && Schema::hasColumn('visits', 'visit_hour')) {
            Schema::table('visits', function (Blueprint $table) {
                $table->dateTime('visit_hour')->nullable()->change();
            });
        }
    }
};
