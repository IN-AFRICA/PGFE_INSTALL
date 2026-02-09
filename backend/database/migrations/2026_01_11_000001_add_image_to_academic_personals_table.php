<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_personals', function (Blueprint $table) {
            if (! Schema::hasColumn('academic_personals', 'image')) {
                $table->string('image')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('academic_personals', function (Blueprint $table) {
            if (Schema::hasColumn('academic_personals', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
