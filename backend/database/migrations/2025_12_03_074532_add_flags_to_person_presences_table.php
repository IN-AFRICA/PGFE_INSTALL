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
        Schema::table('person_presences', function (Blueprint $table) {
            if (! Schema::hasColumn('person_presences', 'absent_justified')) {
                $table->boolean('absent_justified')->default(false);
            }
            if (! Schema::hasColumn('person_presences', 'sick')) {
                $table->boolean('sick')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('person_presences', function (Blueprint $table) {
            if (Schema::hasColumn('person_presences', 'absent_justified')) {
                $table->dropColumn('absent_justified');
            }
            if (Schema::hasColumn('person_presences', 'sick')) {
                $table->dropColumn('sick');
            }
        });
    }
};
