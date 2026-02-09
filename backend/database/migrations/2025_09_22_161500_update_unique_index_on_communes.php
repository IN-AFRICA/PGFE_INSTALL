<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('communes')) {
            try {
                Schema::table('communes', function (Blueprint $table) {
                    $table->dropUnique('communes_name_unique');
                });
            } catch (Throwable $e) {
                // ignore if index absent
            }
            Schema::table('communes', function (Blueprint $table) {
                $table->unique(['province_id', 'name'], 'communes_province_id_name_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('communes')) {
            try {
                Schema::table('communes', function (Blueprint $table) {
                    $table->dropUnique('communes_province_id_name_unique');
                    $table->unique('name');
                });
            } catch (Throwable $e) {
                // ignore
            }
        }
    }
};
