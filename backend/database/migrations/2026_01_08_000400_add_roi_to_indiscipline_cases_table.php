<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indiscipline_cases', function (Blueprint $table) {
            $table->text('roi')->nullable()->after('action');
        });
    }

    public function down(): void
    {
        Schema::table('indiscipline_cases', function (Blueprint $table) {
            if (Schema::hasColumn('indiscipline_cases', 'roi')) {
                $table->dropColumn('roi');
            }
        });
    }
};
