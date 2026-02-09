<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('InputAccount', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->nullOnDelete();
        });
        Schema::table('OutputAccount', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('InputAccount', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_year_id');
        });
        Schema::table('OutputAccount', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_year_id');
        });
    }
};
