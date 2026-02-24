<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'infra_bailleurs',
            'infra_types',
            'infra_equipements',
            'infra_equipment',
            'infra_inventories',
            'infra_iventaire_infrastructures',
            'infra_states'
        ];

        foreach ($tables as $tableName) {
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
                DB::table($tableName)
                    ->whereNull('uuid')
                    ->get()
                    ->each(function ($record) use ($tableName) {
                        DB::table($tableName)
                            ->where('id', $record->id)
                            ->update(['uuid' => (string) Str::uuid()]);
                    });

                // Rendre uuid non nul
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->uuid('uuid')->nullable(false)->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'infra_bailleurs',
            'infra_types',
            'infra_equipements',
            'infra_equipment',
            'infra_inventories',
            'infra_iventaire_infrastructures',
            'infra_states'
        ];
        foreach ($tables as $tableName) {
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
