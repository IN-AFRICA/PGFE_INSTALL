<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            if (Schema::hasColumn('presences', 'filiere_id')) {
                $table->dropForeign(['filiere_id']);
                $table->dropColumn('filiere_id');
            }
            if (!Schema::hasColumn('presences', 'academic_level_id')) {
                $table->foreignId('academic_level_id')->nullable()->after('classroom_id')->constrained('academic_levels')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            if (Schema::hasColumn('presences', 'academic_level_id')) {
                $table->dropForeign(['academic_level_id']);
                $table->dropColumn('academic_level_id');
            }
            if (!Schema::hasColumn('presences', 'filiere_id')) {
                $table->foreignId('filiere_id')->nullable()->after('classroom_id')->constrained('filiaires')->nullOnDelete();
            }
        });
    }
};
