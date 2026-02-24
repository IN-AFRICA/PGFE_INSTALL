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
        if (Schema::hasTable('infra_infrastructures')) {
            Schema::table('infra_infrastructures', function (Blueprint $table) {
                if (! Schema::hasColumn('infra_infrastructures', 'uuid')) {
                    $table->uuid('uuid')->nullable()->after('id')->unique();
                }
            });

            // Renseigner un UUID pour les enregistrements existants si nécessaire
            DB::table('infra_infrastructures')
                ->whereNull('uuid')
                ->get()
                ->each(function ($record) {
                    DB::table('infra_infrastructures')
                        ->where('id', $record->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('infra_infrastructures') && Schema::hasColumn('infra_infrastructures', 'uuid')) {
            Schema::table('infra_infrastructures', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }
    }
};
