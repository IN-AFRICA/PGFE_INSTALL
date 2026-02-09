<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repechages', function (Blueprint $table) {
            if (! Schema::hasColumn('repechages', 'student_score')) {
                $table->float('student_score')->nullable()->after('score_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('repechages', function (Blueprint $table) {
            if (Schema::hasColumn('repechages', 'student_score')) {
                $table->dropColumn('student_score');
            }
        });
    }
};
