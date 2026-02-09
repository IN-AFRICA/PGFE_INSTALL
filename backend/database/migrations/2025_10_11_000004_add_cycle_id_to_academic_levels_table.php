<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_levels', function (Blueprint $table) {
            if (! Schema::hasColumn('academic_levels', 'cycle_id')) {
                $table->foreignId('cycle_id')->nullable()->constrained('cycles')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('academic_levels', function (Blueprint $table) {
            if (Schema::hasColumn('academic_levels', 'cycle_id')) {
                $table->dropForeign(['cycle_id']);
                $table->dropColumn('cycle_id');
            }
        });
    }
};
