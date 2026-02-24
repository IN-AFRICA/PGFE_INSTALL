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
     * Tables satellites liées aux étudiants à préparer pour la synchro.
     * - student_transfers
     * - student_exits
     * - student_activities
     * - registration_parents
     */
    protected array $tables = [
        'student_transfers',
        'student_exits',
        'student_activities',
        'registration_parents',
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

            // Backfill UUIDs pour les enregistrements existants
            DB::table($tableName)
                ->whereNull('uuid')
                ->get()
                ->each(function ($record) use ($tableName): void {
                    DB::table($tableName)
                        ->where('id', $record->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                });

            // Rendre uuid non nul après remplissage
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
