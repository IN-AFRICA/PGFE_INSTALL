<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('person_presences', function (Blueprint $table) {
            if (!Schema::hasColumn('person_presences', 'sick')) {
                $table->boolean('sick')->default(0)->after('presence');
            }
            if (!Schema::hasColumn('person_presences', 'absences_justified')) {
                $table->boolean('absences_justified')->default(false)->after('sick');
            }

        });
    }

    public function down(): void
    {
        Schema::table('person_presences', function (Blueprint $table) {
            $table->dropColumn(['sick', 'absences_justified']);
        });
    }
};

