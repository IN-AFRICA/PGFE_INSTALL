<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            if (! Schema::hasColumn('presences', 'absent_justified')) {
                $table->boolean('absent_justified')->default(false)->after('filiere_id');
            }
            if (! Schema::hasColumn('presences', 'sick')) {
                $table->boolean('sick')->default(false)->after('absent_justified');
            }
        });

        // Backfill: presence=true => present=true
        try {
            DB::table('presences')->where('presence', true)->update(['present' => true]);
        } catch (Throwable $e) {
            // ignore if table/column not present in some environments
        }
    }

    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            if (Schema::hasColumn('presences', 'sick')) {
                $table->dropColumn('sick');
            }
            if (Schema::hasColumn('presences', 'absent_justified')) {
                $table->dropColumn('absent_justified');
            }

        });
    }
};
