<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('person_conges', function (Blueprint $table) {
            if (!Schema::hasColumn('person_conges', 'school_year_id')) {
                $table->foreignId('school_year_id')->nullable()->constrained('school_years')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('person_conges', function (Blueprint $table) {
            if (Schema::hasColumn('person_conges', 'school_year_id')) {
                try { $table->dropForeign(['school_year_id']); } catch (\Throwable $e) {}
                $table->dropColumn('school_year_id');
            }
        });
    }
};
