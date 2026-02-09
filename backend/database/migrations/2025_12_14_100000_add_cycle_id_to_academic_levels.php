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
            if (!Schema::hasColumn('academic_levels', 'cycle_id')) {
                $table->unsignedBigInteger('cycle_id')->nullable()->after('id');
                $table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('set null');
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
