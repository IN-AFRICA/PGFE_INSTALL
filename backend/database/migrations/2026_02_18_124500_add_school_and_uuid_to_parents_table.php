<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('parents')) {
            Schema::table('parents', function (Blueprint $table) {
                if (! Schema::hasColumn('parents', 'school_id')) {
                    $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete()->after('id');
                }

                if (! Schema::hasColumn('parents', 'uuid')) {
                    $table->uuid('uuid')->nullable()->after('id')->unique();
                }

                if (! Schema::hasColumn('parents', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Renseigner un UUID pour les enregistrements existants si nécessaire
            DB::table('parents')
                ->whereNull('uuid')
                ->get()
                ->each(function ($record): void {
                    DB::table('parents')
                        ->where('id', $record->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('parents')) {
            Schema::table('parents', function (Blueprint $table): void {
                if (Schema::hasColumn('parents', 'uuid')) {
                    $table->dropColumn('uuid');
                }

                if (Schema::hasColumn('parents', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }

                if (Schema::hasColumn('parents', 'school_id')) {
                    $table->dropConstrainedForeignId('school_id');
                }
            });
        }
    }
};
