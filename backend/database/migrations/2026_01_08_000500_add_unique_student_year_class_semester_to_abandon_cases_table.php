<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abandon_cases', function (Blueprint $table) {
            $table->unique([
                'student_id',
                'school_year_id',
                'classroom_id',
                'semester_id',
            ], 'abandon_cases_student_year_class_semester_unique');
        });
    }

    public function down(): void
    {
        Schema::table('abandon_cases', function (Blueprint $table) {
            $table->dropUnique('abandon_cases_student_year_class_semester_unique');
        });
    }
};
