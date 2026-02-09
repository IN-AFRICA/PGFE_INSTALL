<?php

declare(strict_types=1);

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
        Schema::table('fiche_cotations', function (Blueprint $table) {
            if (Schema::hasColumn('fiche_cotations', 'maxima')) {
                $table->dropColumn('maxima');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fiche_cotations', function (Blueprint $table) {
            $table->double('maxima')->default(0);
        });
    }
};
