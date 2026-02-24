<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Tables du module stock à compléter pour la synchro (uuid + soft deletes)
        foreach (['stock_states', 'stock_inventories'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (! Schema::hasColumn($tableName, 'uuid')) {
                        $table->uuid('uuid')->nullable()->after('id')->index();
                    }
                    if (! Schema::hasColumn($tableName, 'deleted_at')) {
                        $table->softDeletes();
                    }
                });

                // Backfill UUIDs
                DB::table($tableName)->whereNull('uuid')->get()->each(function ($record) use ($tableName) {
                    DB::table($tableName)->where('id', $record->id)->update([
                        'uuid' => (string) Str::uuid(),
                    ]);
                });

                // Rendre uuid non nul
                Schema::table($tableName, function (Blueprint $table) {
                    $table->uuid('uuid')->nullable(false)->change();
                });
            }
        }

        // Tables infra supplémentaires (au-delà de infra_infrastructures / infra_categories) à préparer pour la synchro
        foreach (['infra_etats', 'infra_inventaires'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (! Schema::hasColumn($tableName, 'uuid')) {
                        $table->uuid('uuid')->nullable()->after('id')->index();
                    }
                });

                DB::table($tableName)->whereNull('uuid')->get()->each(function ($record) use ($tableName) {
                    DB::table($tableName)->where('id', $record->id)->update([
                        'uuid' => (string) Str::uuid(),
                    ]);
                });

                Schema::table($tableName, function (Blueprint $table) {
                    $table->uuid('uuid')->nullable(false)->change();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['stock_states', 'stock_inventories', 'infra_etats', 'infra_inventaires'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'uuid')) {
                        $table->dropColumn('uuid');
                    }
                    if (Schema::hasColumn($tableName, 'deleted_at')) {
                        $table->dropSoftDeletes();
                    }
                });
            }
        }
    }
};
