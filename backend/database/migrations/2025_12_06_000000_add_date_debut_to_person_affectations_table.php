<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('person_affectations', function (Blueprint $table) {
            // Add start date as a DATE, nullable to avoid breaking existing rows
            $table->date('date_debut')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('person_affectations', function (Blueprint $table) {
            $table->dropColumn('date_debut');
        });
    }
};

