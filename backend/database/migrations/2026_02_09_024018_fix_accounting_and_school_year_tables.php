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
        // Add softDeletes to Accounting tables
        if (Schema::hasTable('account_plan')) {
            Schema::table('account_plan', function (Blueprint $table) {
                if (!Schema::hasColumn('account_plan', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (Schema::hasTable('sub_account_plan')) {
            Schema::table('sub_account_plan', function (Blueprint $table) {
                if (!Schema::hasColumn('sub_account_plan', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Create periods table if not exists
        if (!Schema::hasTable('periods')) {
            Schema::create('periods', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('account_plan')) {
            Schema::table('account_plan', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('sub_account_plan')) {
            Schema::table('sub_account_plan', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        Schema::dropIfExists('periods');
    }
};
