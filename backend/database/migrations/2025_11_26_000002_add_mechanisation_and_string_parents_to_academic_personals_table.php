<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('academic_personals', function (Blueprint $table) {
            // Ajout du champ mechanisation_id si non existant
            if (!Schema::hasColumn('academic_personals', 'mechanisation_id')) {
                $table->foreignId('mechanisation_id')->nullable()->after('user_id');
            }
            // Conversion des parents en string si besoin
            if (Schema::hasColumn('academic_personals', 'father_id')) {
                $table->dropForeign(['father_id']);
                $table->dropColumn('father_id');
                $table->string('father_id')->nullable()->after('type_id');
            }
            if (Schema::hasColumn('academic_personals', 'mother_id')) {
                $table->dropForeign(['mother_id']);
                $table->dropColumn('mother_id');
                $table->string('mother_id')->nullable()->after('father_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('academic_personals', function (Blueprint $table) {
            if (Schema::hasColumn('academic_personals', 'mechanisation_id')) {
                $table->dropColumn('mechanisation_id');
            }
            if (Schema::hasColumn('academic_personals', 'father_id')) {
                $table->dropColumn('father_id');
            }
            if (Schema::hasColumn('academic_personals', 'mother_id')) {
                $table->dropColumn('mother_id');
            }
        });
    }
};
