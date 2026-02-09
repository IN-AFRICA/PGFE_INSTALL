<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('validation_aureats', function (Blueprint $table) {
            if (! Schema::hasColumn('validation_aureats', 'percentage')) {
                $table->unsignedTinyInteger('percentage')->nullable()->after('present');
            }
            $table->index(['class', 'percentage'], 'va_class_percentage_idx');
        });
    }

    public function down(): void
    {
        Schema::table('validation_aureats', function (Blueprint $table) {
            try {
                $table->dropIndex('va_class_percentage_idx');
            } catch (Throwable $e) {
            }
            if (Schema::hasColumn('validation_aureats', 'percentage')) {
                $table->dropColumn('percentage');
            }
        });
    }
};
