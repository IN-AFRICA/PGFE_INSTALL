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
            $table->text('action')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('indiscipline_cases', function (Blueprint $table) {
            $table->string('action')->nullable()->change();
        });
    }
};
