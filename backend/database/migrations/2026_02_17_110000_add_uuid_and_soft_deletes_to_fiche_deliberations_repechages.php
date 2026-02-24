<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['fiche_cotations', 'deliberations', 'repechages'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'uuid')) {
                    $table->uuid('uuid')->nullable()->after('id')->index();
                }
                if (! Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Backfill UUIDs for existing rows
            DB::table($tableName)->whereNull('uuid')->get()->each(function ($record) use ($tableName) {
                DB::table($tableName)->where('id', $record->id)->update([
                    'uuid' => (string) Str::uuid(),
                ]);
            });

            // Ensure uuid is non-nullable
            Schema::table($tableName, function (Blueprint $table) {
                $table->uuid('uuid')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['fiche_cotations', 'deliberations', 'repechages'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'uuid')) {
                    $table->dropColumn('uuid');
                }
                if (Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->dropColumn('deleted_at');
                }
            });
        }
    }
};
