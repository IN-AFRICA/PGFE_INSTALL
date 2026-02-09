<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('registrations', 'filiaire_id')) {
                $table->foreignId('filiaire_id')->nullable()->constrained('filiaires')->nullOnDelete();
            }
            if (! Schema::hasColumn('registrations', 'cycle_id')) {
                $table->foreignId('cycle_id')->nullable()->constrained('cycles')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (Schema::hasColumn('registrations', 'filiaire_id')) {
                $table->dropForeign(['filiaire_id']);
                $table->dropColumn('filiaire_id');
            }
            if (Schema::hasColumn('registrations', 'cycle_id')) {
                $table->dropForeign(['cycle_id']);
                $table->dropColumn('cycle_id');
            }
        });
    }
};
