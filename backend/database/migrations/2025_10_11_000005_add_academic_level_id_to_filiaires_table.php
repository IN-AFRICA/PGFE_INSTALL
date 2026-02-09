<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filiaires', function (Blueprint $table) {
            if (! Schema::hasColumn('filiaires', 'academic_level_id')) {
                $table->foreignId('academic_level_id')->nullable()->constrained('academic_levels')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('filiaires', function (Blueprint $table) {
            if (Schema::hasColumn('filiaires', 'academic_level_id')) {
                $table->dropForeign(['academic_level_id']);
                $table->dropColumn('academic_level_id');
            }
        });
    }
};
