<?php

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
        if (Schema::hasTable('visits')) {
            if (Schema::hasColumn('visits', 'visit_hour')) {
                Schema::table('visits', function (Blueprint $table) {
                    $table->dateTime('visit_hour')->nullable()->change();
                });
            }

            if (Schema::hasColumn('visits', 'datefin')) {
                Schema::table('visits', function (Blueprint $table) {
                    $table->dateTime('datefin')->nullable()->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('visits')) {
            if (Schema::hasColumn('visits', 'visit_hour')) {
                Schema::table('visits', function (Blueprint $table) {
                    $table->string('visit_hour')->nullable()->change();
                });
            }

            if (Schema::hasColumn('visits', 'datefin')) {
                Schema::table('visits', function (Blueprint $table) {
                    $table->date('datefin')->nullable()->change();
                });
            }
        }
    }
};
