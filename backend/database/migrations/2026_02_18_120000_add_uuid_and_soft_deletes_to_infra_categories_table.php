<?php

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
        if (Schema::hasTable('infra_categories')) {
            Schema::table('infra_categories', function (Blueprint $table) {
                if (! Schema::hasColumn('infra_categories', 'uuid')) {
                    $table->uuid('uuid')->nullable()->after('id')->unique();
                }

                if (! Schema::hasColumn('infra_categories', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Renseigner un UUID pour les enregistrements existants si nécessaire
            DB::table('infra_categories')
                ->whereNull('uuid')
                ->get()
                ->each(function ($record) {
                    DB::table('infra_categories')
                        ->where('id', $record->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('infra_categories')) {
            Schema::table('infra_categories', function (Blueprint $table) {
                if (Schema::hasColumn('infra_categories', 'uuid')) {
                    $table->dropColumn('uuid');
                }

                if (Schema::hasColumn('infra_categories', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
