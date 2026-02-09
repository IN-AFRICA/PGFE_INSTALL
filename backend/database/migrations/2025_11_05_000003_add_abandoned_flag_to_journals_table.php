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
        Schema::table('journals', function (Blueprint $table) {
            if (! Schema::hasColumn('journals', 'abandoned')) {
                $table->boolean('abandoned')->default(false)->after('account_id');
            }
        });
        // Backfill: si abandoned_at existe et non nul, marquer abandoned=true
        if (Schema::hasColumn('journals', 'abandoned_at')) {
            DB::table('journals')->whereNotNull('abandoned_at')->update(['abandoned' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            if (Schema::hasColumn('journals', 'abandoned')) {
                $table->dropColumn('abandoned');
            }
        });
    }
};
