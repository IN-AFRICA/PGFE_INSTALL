<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('person_affectations', function (Blueprint $table) {
            if (Schema::hasColumn('person_affectations', 'personal_id')) {
                // Drop foreign key and column safely
                $table->dropConstrainedForeignId('personal_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('person_affectations', function (Blueprint $table) {
            if (!Schema::hasColumn('person_affectations', 'personal_id')) {
                $table->foreignId('personal_id')->constrained('personals')->onDelete('cascade')->after('id');
            }
        });
    }
};

