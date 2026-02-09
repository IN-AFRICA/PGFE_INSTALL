<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            // La colonne s'appelle actuellement academic_personal_id (renommée depuis personal_id)
            $table->unsignedBigInteger('academic_personal_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_personal_id')->nullable(false)->change();
        });
    }
};
