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
        // MODULE: ACCOUNTING
        Schema::table('payments', function (Blueprint $table) {
            $table->index('student_id');
            $table->index('classroom_id');
            $table->index('school_year_id');
            $table->index('paid_at');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index('school_id');
            $table->index('expense_date');
        });

        // MODULE: STOCK
        Schema::table('stock_articles', function (Blueprint $table) {
            $table->index('school_id');
            $table->index('category_id');
            $table->index('provider_id');
            $table->index('name');
        });

        Schema::table('stock_entries', function (Blueprint $table) {
            $table->index('school_id');
            $table->index('article_id');
            $table->index('entry_date');
        });

        Schema::table('stock_exits', function (Blueprint $table) {
            $table->index('school_id');
            $table->index('article_id');
            $table->index('exit_date');
        });

        Schema::table('stock_operations', function (Blueprint $table) {
            $table->index('school_id');
            $table->index('article_id');
            $table->index('type');
        });

        // MODULE: INFRASTRUCTURE & PRESENCE
        Schema::table('infra_infrastructures', function (Blueprint $table) {
            $table->index('school_id');
        });

        Schema::table('presences', function (Blueprint $table) {
            $table->index('school_id');
            $table->index('student_id');
            $table->index('classroom_id');
            $table->index('created_at'); // Présence par date
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['classroom_id']);
            $table->dropIndex(['school_year_id']);
            $table->dropIndex(['paid_at']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
            $table->dropIndex(['expense_date']);
        });

        Schema::table('stock_articles', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['provider_id']);
            $table->dropIndex(['name']);
        });

        Schema::table('stock_entries', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
            $table->dropIndex(['article_id']);
            $table->dropIndex(['entry_date']);
        });

        Schema::table('stock_exits', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
            $table->dropIndex(['article_id']);
            $table->dropIndex(['exit_date']);
        });

        Schema::table('stock_operations', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
            $table->dropIndex(['article_id']);
            $table->dropIndex(['type']);
        });

        Schema::table('infra_infrastructures', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
        });

        Schema::table('presences', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
            $table->dropIndex(['student_id']);
            $table->dropIndex(['classroom_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
