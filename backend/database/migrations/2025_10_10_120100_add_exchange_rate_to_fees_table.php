<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            if (! Schema::hasColumn('fees', 'exchange_rate_id')) {
                $table->foreignId('exchange_rate_id')
                    ->nullable()
                    ->after('currency_id')
                    ->constrained('exchange_rates')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            if (Schema::hasColumn('fees', 'exchange_rate_id')) {
                $table->dropConstrainedForeignId('exchange_rate_id');
            }
        });
    }
};
