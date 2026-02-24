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
     * Tables comptables à préparer pour la synchro (uuid + soft deletes).
     */
    protected array $tables = [
        'account_plan',
        'sub_account_plan',
        'account_numbers',
        'account_types',
        'InputAccount',
        'OutputAccount',
        'journals',
        'credits',
        'debits',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (! Schema::hasColumn($tableName, 'uuid')) {
                    $table->uuid('uuid')->nullable()->after('id')->index();
                }

                if (! Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Backfill UUIDs for existing rows
            DB::table($tableName)
                ->whereNull('uuid')
                ->get()
                ->each(function ($record) use ($tableName): void {
                    DB::table($tableName)
                        ->where('id', $record->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                });

            // Make uuid non-nullable after population
            Schema::table($tableName, function (Blueprint $table): void {
                $table->uuid('uuid')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                if (Schema::hasColumn($table->getTable(), 'uuid')) {
                    $table->dropColumn('uuid');
                }

                if (Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
