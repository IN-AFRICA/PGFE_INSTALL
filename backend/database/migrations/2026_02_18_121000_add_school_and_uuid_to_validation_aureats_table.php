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
        if (Schema::hasTable('validation_aureats')) {
            Schema::table('validation_aureats', function (Blueprint $table) {
                if (! Schema::hasColumn('validation_aureats', 'school_id')) {
                    $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete()->after('id');
                }
                if (! Schema::hasColumn('validation_aureats', 'uuid')) {
                    $table->uuid('uuid')->nullable()->after('id')->unique();
                }
                if (! Schema::hasColumn('validation_aureats', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Renseigner un UUID pour les enregistrements existants si nécessaire
            DB::table('validation_aureats')
                ->whereNull('uuid')
                ->get()
                ->each(function ($record) {
                    DB::table('validation_aureats')
                        ->where('id', $record->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('validation_aureats')) {
            Schema::table('validation_aureats', function (Blueprint $table) {
                if (Schema::hasColumn('validation_aureats', 'uuid')) {
                    $table->dropColumn('uuid');
                }
                if (Schema::hasColumn('validation_aureats', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
                if (Schema::hasColumn('validation_aureats', 'school_id')) {
                    $table->dropConstrainedForeignId('school_id');
                }
            });
        }
    }
};
