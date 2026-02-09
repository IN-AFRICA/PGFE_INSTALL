<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('provinces')) {
            // Try dropping old unique index if exists
            try {
                Schema::table('provinces', function (Blueprint $table) {
                    $table->dropUnique('provinces_name_unique');
                });
            } catch (Throwable $e) {
                // ignore if it does not exist
            }
            Schema::table('provinces', function (Blueprint $table) {
                $table->unique(['country_id', 'name'], 'provinces_country_id_name_unique');
            });
        }

        if (Schema::hasTable('territories')) {
            try {
                Schema::table('territories', function (Blueprint $table) {
                    $table->dropUnique('territories_name_unique');
                });
            } catch (Throwable $e) {
                // ignore
            }
            Schema::table('territories', function (Blueprint $table) {
                $table->unique(['province_id', 'name'], 'territories_province_id_name_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('provinces')) {
            try {
                Schema::table('provinces', function (Blueprint $table) {
                    $table->dropUnique('provinces_country_id_name_unique');
                    $table->unique('name');
                });
            } catch (Throwable $e) {
                // ignore
            }
        }
        if (Schema::hasTable('territories')) {
            try {
                Schema::table('territories', function (Blueprint $table) {
                    $table->dropUnique('territories_province_id_name_unique');
                    $table->unique('name');
                });
            } catch (Throwable $e) {
                // ignore
            }
        }
    }
};
