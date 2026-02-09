<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('person_affectations', function (Blueprint $table) {
            if (!Schema::hasColumn('person_affectations', 'academic_personal_id')) {
                $table->foreignId('academic_personal_id')->nullable()->constrained('academic_personals')->nullOnDelete()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('person_affectations', function (Blueprint $table) {
            if (Schema::hasColumn('person_affectations', 'academic_personal_id')) {
                $table->dropConstrainedForeignId('academic_personal_id');
            }
        });
    }
};

