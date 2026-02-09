<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('conduite_semesters', 'conduite_id')) {
            Schema::table('conduite_semesters', function (Blueprint $table) {
                $table->foreignId('conduite_id')->constrained('conduites')->cascadeOnDelete()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('conduite_semesters', 'conduite_id')) {
            Schema::table('conduite_semesters', function (Blueprint $table) {
                $table->dropConstrainedForeignId('conduite_id');
            });
        }
    }
};
