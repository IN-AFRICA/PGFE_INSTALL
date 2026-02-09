<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('person_evaluations', function (Blueprint $table) {
            if (! Schema::hasColumn('person_evaluations', 'academic_personal_id')) {
                $table->foreignId('academic_personal_id')
                    ->nullable()
                    ->after('c5_dr_att_posit_collab')
                    ->constrained('academic_personals')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('person_evaluations', function (Blueprint $table) {
            if (Schema::hasColumn('person_evaluations', 'academic_personal_id')) {
                $table->dropForeign(['academic_personal_id']);
                $table->dropColumn('academic_personal_id');
            }
        });
    }
};
