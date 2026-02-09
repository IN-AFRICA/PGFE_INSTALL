<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('person_affectations', function (Blueprint $table) {
            if (!Schema::hasColumn('person_affectations', 'school_id')) {
                $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null')->after('author_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('person_affectations', function (Blueprint $table) {
            if (Schema::hasColumn('person_affectations', 'school_id')) {
                $table->dropConstrainedForeignId('school_id');
            }
        });
    }
};

