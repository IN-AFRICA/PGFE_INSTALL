<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    protected $tables = [
        'schools', 'filiaires', 'cycles', 'academic_levels', 'classrooms',
        'courses', 'academic_personals', 'students', 'registrations',
        'fees', // added to support UUID + soft deletes on fees
        'stock_articles', 'stock_categories', 'stock_providers', 'stock_entries',
        'stock_exits', 'stock_operations', 'payments', 'expenses',
        'schedules', 'teacher_unavailabilities', 'presences',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'uuid')) {
                        $table->uuid('uuid')->nullable()->after('id')->index();
                    }
                    if (!Schema::hasColumn($tableName, 'deleted_at')) {
                        $table->softDeletes();
                    }
                });

                // Generate UUIDs for existing records
                DB::table($tableName)->whereNull('uuid')->get()->each(function ($record) use ($tableName) {
                    DB::table($tableName)->where('id', $record->id)->update(['uuid' => (string) Str::uuid()]);
                });

                // Make uuid unique after population
                Schema::table($tableName, function (Blueprint $table) {
                    $table->uuid('uuid')->nullable(false)->change();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn(['uuid', 'deleted_at']);
                });
            }
        }
    }
};
