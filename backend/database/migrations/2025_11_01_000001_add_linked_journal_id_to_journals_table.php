<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->unsignedBigInteger('linked_journal_id')->nullable()->after('account_id');
            $table->foreign('linked_journal_id')->references('id')->on('journals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropForeign(['linked_journal_id']);
            $table->dropColumn('linked_journal_id');
        });
    }
};
