<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiche_cotations', function (Blueprint $table) {
            $table->foreignId('deliberation_id')->nullable()->constrained('deliberations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('fiche_cotations', function (Blueprint $table) {
            $table->dropForeign(['deliberation_id']);
            $table->dropColumn('deliberation_id');
        });
    }
};
