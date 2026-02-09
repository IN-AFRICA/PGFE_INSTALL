<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('filiaires', function ($table) {
            $table->dropUnique('filiaires_level_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('filiaires', function ($table) {
            $table->unique(['level_name']); // Remet la contrainte si besoin
        });
    }
};
