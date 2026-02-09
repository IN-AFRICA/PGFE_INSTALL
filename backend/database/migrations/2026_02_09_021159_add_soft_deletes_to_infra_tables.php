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
        Schema::table('infra_infrastructures', function (Blueprint $table) {
            if (!Schema::hasColumn('infra_infrastructures', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('infra_etats', function (Blueprint $table) {
            if (!Schema::hasColumn('infra_etats', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('infra_equipements', function (Blueprint $table) {
            if (!Schema::hasColumn('infra_equipements', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('infra_inventaires', function (Blueprint $table) {
            if (!Schema::hasColumn('infra_inventaires', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('infra_infrastructures', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('infra_etats', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('infra_equipements', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('infra_inventaires', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
