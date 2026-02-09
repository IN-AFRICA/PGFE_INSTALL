<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('person_salaires', function (Blueprint $table) {
            if (!Schema::hasColumn('person_salaires', 'academic_personal_id')) {
                $table->foreignId('academic_personal_id')->nullable()->after('author_id')->constrained('academic_personals');
            }
        });
    }

    public function down(): void
    {
        Schema::table('person_salaires', function (Blueprint $table) {
            if (Schema::hasColumn('person_salaires', 'academic_personal_id')) {
                $table->dropForeign(['academic_personal_id']);
                $table->dropColumn('academic_personal_id');
            }
        });
    }
};
