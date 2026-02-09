<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('parent2_id')->nullable()->after('parents_id');
            $table->unsignedBigInteger('parent3_id')->nullable()->after('parent2_id');
            $table->foreign('parent2_id')->references('id')->on('parents')->nullOnDelete();
            $table->foreign('parent3_id')->references('id')->on('parents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['parent2_id']);
            $table->dropForeign(['parent3_id']);
            $table->dropColumn(['parent2_id', 'parent3_id']);
        });
    }
};
